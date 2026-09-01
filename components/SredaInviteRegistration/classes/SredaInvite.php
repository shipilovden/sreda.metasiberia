<?php
/**
 * SREDA invite registration storage.
 */
class SredaInvite {
		const COMPONENT_ID = 'SredaInviteRegistration';
		const TABLE = 'sreda_invites';

		private static $tablePrefix = null;

		/**
		 * Create the invite table. The operation is idempotent and is also safe
		 * to call from an action after a manually copied component is activated.
		 *
		 * @return bool
		 */
		public static function ensureTable() {
				static $ready = false;
				if($ready) {
						return true;
				}

				$table = self::table(self::TABLE);
				if($table === false) {
						return false;
				}

				$sql = "CREATE TABLE IF NOT EXISTS {$table} (
						`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						`token_hash` CHAR(64) CHARACTER SET ascii NOT NULL,
						`created_by` BIGINT UNSIGNED NOT NULL,
						`invited_email` VARCHAR(320) DEFAULT NULL,
						`created_at` INT UNSIGNED NOT NULL,
						`used_at` INT UNSIGNED DEFAULT NULL,
						`used_by` BIGINT UNSIGNED DEFAULT NULL,
						`status` VARCHAR(16) NOT NULL DEFAULT 'active',
						`reserved_at` INT UNSIGNED DEFAULT NULL,
						`reservation_key` CHAR(64) CHARACTER SET ascii DEFAULT NULL,
						`sent_at` INT UNSIGNED DEFAULT NULL,
						`last_sent_at` INT UNSIGNED DEFAULT NULL,
						`send_count` INT UNSIGNED NOT NULL DEFAULT 0,
						PRIMARY KEY (`id`),
						UNIQUE KEY `sreda_invites_token_hash` (`token_hash`),
						KEY `sreda_invites_status_created` (`status`, `created_at`),
						KEY `sreda_invites_reservation_key` (`reservation_key`),
						KEY `sreda_invites_admin_email` (`created_by`, `invited_email`(191))
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

				if(self::execute($sql) === false) {
						return false;
				}

				// Existing installations have the original schema. Add only missing
				// columns/indexes so no invite history is dropped during migration.
				$columnsStatement = self::execute("SHOW COLUMNS FROM {$table}");
				if($columnsStatement === false) {
						return false;
				}
				$columns = array();
				while($column = $columnsStatement->fetch(PDO::FETCH_ASSOC)) {
						if(isset($column['Field'])) {
							$columns[strtolower((string) $column['Field'])] = true;
						}
				}
				$missingColumns = array(
						'invited_email' => "ADD COLUMN `invited_email` VARCHAR(320) DEFAULT NULL",
						'sent_at' => "ADD COLUMN `sent_at` INT UNSIGNED DEFAULT NULL",
						'last_sent_at' => "ADD COLUMN `last_sent_at` INT UNSIGNED DEFAULT NULL",
						'send_count' => "ADD COLUMN `send_count` INT UNSIGNED NOT NULL DEFAULT 0",
				);
				foreach($missingColumns as $name => $alter) {
						if(!isset($columns[$name]) && self::execute("ALTER TABLE {$table} {$alter}") === false) {
							return false;
						}
				}

				$indexStatement = self::execute("SHOW INDEX FROM {$table}");
				if($indexStatement === false) {
						return false;
				}
				$indexes = array();
				while($index = $indexStatement->fetch(PDO::FETCH_ASSOC)) {
						if(isset($index['Key_name'])) {
							$indexes[(string) $index['Key_name']] = true;
						}
				}
				if(!isset($indexes['sreda_invites_admin_email']) && self::execute("ALTER TABLE {$table} ADD KEY `sreda_invites_admin_email` (`created_by`, `invited_email`(191))") === false) {
						return false;
				}

				// Legacy universal links must not become valid personal invites.
				// Keep used/revoked history intact; only invalidate live unbound rows.
				if(self::execute(
						"UPDATE {$table} SET `status` = 'revoked', `reserved_at` = NULL, `reservation_key` = NULL WHERE (`invited_email` IS NULL OR `invited_email` = '') AND `status` IN ('active', 'reserved')"
				) === false) {
						return false;
				}

				$ready = true;
				return true;
		}

		/**
		 * Read the invite-only setting from the OSSN component settings API.
		 *
		 * @return bool
		 */
		public static function isInviteOnlyEnabled() {
				$settings = ossn_components()->getSettings(self::COMPONENT_ID);
				return $settings && isset($settings->invite_only) && $settings->invite_only === 'on';
		}

		/**
		 * Persist the setting through OSSN's component settings API.
		 * The fallback only handles an empty settings collection; older OSSN
		 * builds cannot create the first entity through setSettings().
		 *
		 * @param bool $enabled
		 * @return bool
		 */
		public static function setInviteOnly($enabled) {
				$value     = $enabled ? 'on' : 'off';
				$component = new OssnComponents();
				$component->setSettings(self::COMPONENT_ID, array(
						'invite_only' => $value,
				));

				$settings = $component->getSettings(self::COMPONENT_ID);
				if($settings && isset($settings->invite_only) && $settings->invite_only === $value) {
						return true;
				}

				$record = $component->getbyName(self::COMPONENT_ID);
				if(!$record || empty($record->id)) {
						return false;
				}

				$entity               = new OssnEntities();
				$entity->owner_guid   = $record->id;
				$entity->type         = 'component';
				$entity->subtype      = 'invite_only';
				$entity->value        = $value;
				return (bool) $entity->add();
		}

		/**
		 * Normalize an email address without applying provider-specific rewrites.
		 *
		 * @param string $email
		 * @return string
		 */
		public static function normalizeEmail($email) {
				if(!is_string($email)) {
						return '';
				}
				$email = trim($email);
				return function_exists('mb_strtolower') ? mb_strtolower($email, 'UTF-8') : strtolower($email);
		}

		/**
		 * @param string $email
		 * @return bool
		 */
		public static function isValidEmail($email) {
				$email = self::normalizeEmail($email);
				return $email !== '' && strlen($email) <= 320 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		}

		/**
		 * Create an independent personal invite. Existing active invites for other
		 * addresses are never revoked.
		 *
		 * @param int $adminGuid
		 * @param string $email
		 * @return array
		 */
		public static function createPersonalForAdmin($adminGuid, $email) {
				$adminGuid = (int) $adminGuid;
				$email     = self::normalizeEmail($email);
				if($adminGuid < 1 || !self::isValidEmail($email)) {
						return array('ok' => false, 'error' => 'invalid_email');
				}
				if(!self::ensureTable()) {
						return array('ok' => false, 'error' => 'database');
				}

				$db          = self::database();
				$inviteTable = self::table(self::TABLE);
				$userTable   = self::table('users');
				if(!$db || $inviteTable === false || $userTable === false || $db->inTransaction()) {
						return array('ok' => false, 'error' => 'database');
				}

				try {
						$db->beginTransaction();
						$lock = self::query(
								"SELECT `guid` FROM {$userTable} WHERE `guid` = ? FOR UPDATE",
								array($adminGuid)
						);
						if($lock === false || !$lock->fetch(PDO::FETCH_ASSOC)) {
								throw new RuntimeException('SREDA invite administrator lock failed.');
						}

						// Recheck while holding the administrator lock. A registered user
						// must never receive a new personal invitation.
						$existingUser = self::query(
								"SELECT `guid` FROM {$userTable} WHERE LOWER(`email`) = LOWER(?) LIMIT 1",
								array($email)
						);
						if($existingUser === false) {
								throw new RuntimeException('SREDA invite user lookup failed.');
						}
						if($existingUser->fetch(PDO::FETCH_ASSOC)) {
								$db->commit();
								return array('ok' => false, 'error' => 'email_exists');
						}

						$existing = self::findPersonalForAdminEmail($adminGuid, $email, true);
						if($existing === null) {
								throw new RuntimeException('SREDA invite lookup failed.');
						}
						if($existing !== false) {
								$db->commit();
								$token = self::sessionTokenForHash($existing['token_hash']);
								if(!$token) {
										return array('ok' => false, 'error' => 'existing_token_unavailable', 'email' => $email);
								}
								return self::formatInviteRecord($existing, $token);
						}

						$token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
						$hash  = self::hashToken($token);
						$inserted = self::query(
								"INSERT INTO {$inviteTable} (`token_hash`, `created_by`, `invited_email`, `created_at`, `status`) VALUES (?, ?, ?, ?, 'active')",
								array($hash, $adminGuid, $email, time())
						);
						if($inserted === false) {
								throw new RuntimeException('SREDA personal invite creation failed.');
						}
						$id = (int) $db->lastInsertId();
						if(!$db->commit()) {
								throw new RuntimeException('SREDA personal invite transaction commit failed.');
						}
						self::rememberSessionToken($hash, $token);
						$row = array(
								'id' => $id,
								'token_hash' => $hash,
								'created_by' => $adminGuid,
								'invited_email' => $email,
								'created_at' => time(),
								'used_at' => null,
								'used_by' => null,
								'status' => 'active',
								'reserved_at' => null,
								'sent_at' => null,
								'last_sent_at' => null,
								'send_count' => 0,
						);
						return self::formatInviteRecord($row, $token);
				} catch(Throwable $exception) {
						if($db->inTransaction()) {
							try {
									$db->rollBack();
							} catch(Throwable $rollbackException) {
									// Keep the failure closed.
							}
						}
						error_log('SREDA invite database operation failed.');
						return array('ok' => false, 'error' => 'database');
				}
		}

		/**
		 * Send an existing personal invite through OSSN's configured mail API.
		 * The database is updated only after PHPMailer reports success.
		 *
		 * @param int $adminGuid
		 * @param string $token
		 * @return array
		 */
		public static function sendPersonalInvite($adminGuid, $token) {
				$adminGuid = (int) $adminGuid;
				$token     = self::normalizeToken($token);
				if($adminGuid < 1 || !$token || !self::ensureTable()) {
						return array('ok' => false, 'error' => 'invalid');
				}

				$hash = self::hashToken($token);
				$table = self::table(self::TABLE);
				if($table === false) {
						return array('ok' => false, 'error' => 'invalid');
				}
				// MySQL named locks are limited to 64 characters. Keep 50 hash
				// characters (200 bits) so the lock remains invite-specific without
				// exceeding that limit.
				$lockName = 'sreda_invite_' . substr($hash, 0, 50);
				$lockStatement = self::query('SELECT GET_LOCK(?, 5) AS `locked`', array($lockName));
				if($lockStatement === false || (int) $lockStatement->fetchColumn() !== 1) {
						return array('ok' => false, 'error' => 'rate_limited');
				}
				$locked = true;
				try {
						$row = self::findByHashForAdmin($hash, $adminGuid, true);
						if($row === null || $row === false) {
							return array('ok' => false, 'error' => $row === false ? 'invalid' : 'database');
						}
						if($row['status'] !== 'active') {
							return array('ok' => false, 'error' => 'not_active', 'status' => $row['status']);
						}
						if(empty($row['invited_email']) || !self::isValidEmail($row['invited_email'])) {
							return array('ok' => false, 'error' => 'invalid');
						}
						$now = time();
						if(!empty($row['last_sent_at']) && ($now - (int) $row['last_sent_at']) < 60) {
							return array('ok' => false, 'error' => 'rate_limited', 'retry_after' => 60 - ($now - (int) $row['last_sent_at']));
						}

						$sender = self::normalizeEmail((string) ossn_site_settings('notification_email'));
						if($sender !== 'mail@metasiberia.com') {
							return array('ok' => false, 'error' => 'from_not_configured');
						}
						if(!function_exists('ossn_smtp_settings') || !function_exists('ossn_smtp_configure_mail')) {
							return array('ok' => false, 'error' => 'smtp_not_configured');
						}
						$smtpSettings = ossn_smtp_settings();
						if(!$smtpSettings || empty($smtpSettings->host) || empty($smtpSettings->port) || empty($smtpSettings->username) || empty($smtpSettings->password)) {
							return array('ok' => false, 'error' => 'smtp_not_configured');
						}
						if(!class_exists('OssnMail')) {
							return array('ok' => false, 'error' => 'smtp_not_configured');
						}

						$mail = new OssnMail();
						$mail = ossn_call_hook('email', 'config', $mail, $mail);
						if(!is_object($mail) || !isset($mail->Mailer) || $mail->Mailer !== 'smtp') {
							return array('ok' => false, 'error' => 'smtp_not_configured');
						}
						$emailContent = self::buildEmailContent($row['invited_email'], self::inviteUrl($token));
						$mail->setFrom($sender, 'SREDA');
						$mail->addAddress($row['invited_email']);
						$mail->Subject = ossn_print('sreda:invite:mail:subject');
						$mail->Body = $emailContent['html'];
						$mail->AltBody = $emailContent['text'];
						$mail->CharSet = 'UTF-8';
						$mail->XMailer = ' ';
						$mail->isHTML(true);

						$sendPolicy = ossn_call_hook('email', 'send:policy', null, $mail);
						$sent = $sendPolicy ? $mail->send() : ossn_call_hook('email', 'send', null, $mail);
						if(!$sent) {
							return array('ok' => false, 'error' => 'send_failed');
						}
						$updated = self::query(
								"UPDATE {$table} SET `sent_at` = COALESCE(`sent_at`, ?), `last_sent_at` = ?, `send_count` = `send_count` + 1 WHERE `id` = ? AND `status` = 'active'",
								array($now, $now, (int) $row['id'])
						);
						if($updated === false || $updated->rowCount() !== 1) {
								error_log('SREDA invite email metadata update failed.');
								return array('ok' => false, 'error' => 'database');
						}
						$row['sent_at'] = !empty($row['sent_at']) ? $row['sent_at'] : $now;
						$row['last_sent_at'] = $now;
						$row['send_count'] = (int) $row['send_count'] + 1;
						self::rememberSessionToken($hash, $token);
						return self::formatInviteRecord($row, $token);
				} catch(Throwable $exception) {
					error_log('SREDA invite email send failed.');
					return array('ok' => false, 'error' => 'send_failed');
				} finally {
					if($locked) {
						self::query('SELECT RELEASE_LOCK(?)', array($lockName));
					}
				}
		}

		/**
		 * Return recent administrator invites without exposing token hashes.
		 * A raw token is included only when it is already held in the current
		 * administrator session, which is needed for copy/share/resend actions.
		 *
		 * @param int $adminGuid
		 * @param int $limit
		 * @return array|false
		 */
		public static function listForAdmin($adminGuid, $limit = 20) {
				$adminGuid = (int) $adminGuid;
				$limit = max(1, min(20, (int) $limit));
				if($adminGuid < 1 || !self::ensureTable()) {
						return false;
				}
				$table = self::table(self::TABLE);
				$statement = self::query(
						"SELECT `id`, `token_hash`, `created_by`, `invited_email`, `created_at`, `used_at`, `used_by`, `status`, `reserved_at`, `sent_at`, `last_sent_at`, `send_count` FROM {$table} WHERE `created_by` = ? AND `invited_email` IS NOT NULL AND `invited_email` <> '' ORDER BY `id` DESC LIMIT {$limit}",
						array($adminGuid)
				);
				if($statement === false) {
						return false;
				}
				$result = array();
				while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
						$token = self::sessionTokenForHash($row['token_hash']);
						$result[] = self::formatInviteRecord($row, $token ?: false);
				}
				return $result;
		}

		/**
		 * Revoke one administrator-owned active/reserved invite.
		 *
		 * @param int $adminGuid
		 * @param string $token
		 * @return bool
		 */
		public static function revokeForAdmin($adminGuid, $token) {
				$adminGuid = (int) $adminGuid;
				$token = self::normalizeToken($token);
				if($adminGuid < 1 || !$token || !self::ensureTable()) {
						return false;
				}
				$hash = self::hashToken($token);
				$table = self::table(self::TABLE);
				$statement = self::query(
						"UPDATE {$table} SET `status` = 'revoked', `reserved_at` = NULL, `reservation_key` = NULL WHERE `token_hash` = ? AND `created_by` = ? AND `status` IN ('active', 'reserved')",
						array($hash, $adminGuid)
				);
				if($statement === false || $statement->rowCount() !== 1) {
						return false;
				}
				self::forgetSessionToken($hash);
				return true;
		}

		/**
		 * Revoke one administrator-owned invite by its database id.
		 * The id is only a lookup hint: ownership and current state are checked
		 * against the database before the update.
		 *
		 * @param int $adminGuid
		 * @param int $inviteId
		 * @return bool
		 */
		public static function revokeForAdminById($adminGuid, $inviteId) {
				$adminGuid = (int) $adminGuid;
				$inviteId = (int) $inviteId;
				if($adminGuid < 1 || $inviteId < 1 || !self::ensureTable()) {
						return false;
				}

				$table = self::table(self::TABLE);
				if($table === false) {
						return false;
				}
				$row = self::query(
						"SELECT `token_hash` FROM {$table} WHERE `id` = ? AND `created_by` = ? AND `status` IN ('active', 'reserved') LIMIT 1",
						array($inviteId, $adminGuid)
				);
				if($row === false) {
						return false;
				}
				$record = $row->fetch(PDO::FETCH_ASSOC);
				if(!$record || !self::normalizeHash($record['token_hash'])) {
						return false;
				}

				$statement = self::query(
						"UPDATE {$table} SET `status` = 'revoked', `reserved_at` = NULL, `reservation_key` = NULL WHERE `id` = ? AND `created_by` = ? AND `status` IN ('active', 'reserved')",
						array($inviteId, $adminGuid)
				);
				if($statement === false || $statement->rowCount() !== 1) {
						return false;
				}
				self::forgetSessionToken($record['token_hash']);
				return true;
		}

		/**
		 * Rotate one administrator-owned personal invite. The old active/reserved
		 * row is revoked and a new token for the same e-mail is created atomically.
		 *
		 * @param int $adminGuid
		 * @param int $inviteId
		 * @return array
		 */
		public static function rotatePersonalForAdmin($adminGuid, $inviteId) {
				$adminGuid = (int) $adminGuid;
				$inviteId = (int) $inviteId;
				if($adminGuid < 1 || $inviteId < 1 || !self::ensureTable()) {
						return array('ok' => false, 'error' => 'invalid');
				}

				$db          = self::database();
				$inviteTable = self::table(self::TABLE);
				$userTable   = self::table('users');
				if(!$db || $inviteTable === false || $userTable === false || $db->inTransaction()) {
						return array('ok' => false, 'error' => 'database');
				}

				try {
						$db->beginTransaction();
						$lock = self::query(
								"SELECT `guid` FROM {$userTable} WHERE `guid` = ? FOR UPDATE",
								array($adminGuid)
						);
						if($lock === false || !$lock->fetch(PDO::FETCH_ASSOC)) {
								throw new RuntimeException('SREDA invite administrator lock failed.');
						}

						$statement = self::query(
								"SELECT `id`, `token_hash`, `created_by`, `invited_email`, `created_at`, `used_at`, `used_by`, `status`, `reserved_at`, `sent_at`, `last_sent_at`, `send_count` FROM {$inviteTable} WHERE `id` = ? AND `created_by` = ? AND `invited_email` IS NOT NULL AND `invited_email` <> '' AND `status` IN ('active', 'reserved') LIMIT 1 FOR UPDATE",
								array($inviteId, $adminGuid)
						);
						if($statement === false) {
								throw new RuntimeException('SREDA invite rotation lookup failed.');
						}
						$old = $statement->fetch(PDO::FETCH_ASSOC);
						if(!$old || !self::isValidEmail($old['invited_email'])) {
								$db->commit();
								return array('ok' => false, 'error' => 'invalid');
						}

						$existingUser = self::query(
								"SELECT `guid` FROM {$userTable} WHERE LOWER(`email`) = LOWER(?) LIMIT 1",
								array(self::normalizeEmail($old['invited_email']))
						);
						if($existingUser === false) {
								throw new RuntimeException('SREDA invite user lookup failed.');
						}
						if($existingUser->fetch(PDO::FETCH_ASSOC)) {
								$db->commit();
								return array('ok' => false, 'error' => 'email_exists');
						}

						$revoked = self::query(
								"UPDATE {$inviteTable} SET `status` = 'revoked', `reserved_at` = NULL, `reservation_key` = NULL WHERE `id` = ? AND `created_by` = ? AND `status` IN ('active', 'reserved')",
								array($inviteId, $adminGuid)
						);
						if($revoked === false || $revoked->rowCount() !== 1) {
								throw new RuntimeException('SREDA invite rotation revoke failed.');
						}

						$token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
						$hash  = self::hashToken($token);
						$createdAt = time();
						$inserted = self::query(
								"INSERT INTO {$inviteTable} (`token_hash`, `created_by`, `invited_email`, `created_at`, `status`) VALUES (?, ?, ?, ?, 'active')",
								array($hash, $adminGuid, self::normalizeEmail($old['invited_email']), $createdAt)
						);
						if($inserted === false) {
								throw new RuntimeException('SREDA invite rotation creation failed.');
						}
						$newId = (int) $db->lastInsertId();
						if(!$db->commit()) {
								throw new RuntimeException('SREDA invite rotation commit failed.');
						}

						self::rememberSessionToken($hash, $token);
						return self::formatInviteRecord(array(
								'id' => $newId,
								'token_hash' => $hash,
								'created_by' => $adminGuid,
								'invited_email' => self::normalizeEmail($old['invited_email']),
								'created_at' => $createdAt,
								'used_at' => null,
								'used_by' => null,
								'status' => 'active',
								'reserved_at' => null,
								'sent_at' => null,
								'last_sent_at' => null,
								'send_count' => 0,
						), $token);
				} catch(Throwable $exception) {
						if($db->inTransaction()) {
								try {
										$db->rollBack();
								} catch(Throwable $rollbackException) {
										// Keep the failure closed.
								}
						}
						error_log('SREDA invite database operation failed.');
						return array('ok' => false, 'error' => 'database');
				}
		}

		/**
		 * Return the email binding and state for a public invite URL.
		 *
		 * @param string $token
		 * @return array|false
		 */
		public static function getInviteForToken($token) {
				$token = self::normalizeToken($token);
				if(!$token || !self::ensureTable()) {
						return false;
				}
				$row = self::findByHash(self::hashToken($token));
				if(!$row || empty($row['invited_email'])) {
						return false;
				}
				return array(
						'email' => self::normalizeEmail($row['invited_email']),
						'status' => $row['status'],
				);
		}

		/**
		 * Mask an email address for mismatch errors.
		 *
		 * @param string $email
		 * @return string
		 */
		public static function maskEmail($email) {
				$email = self::normalizeEmail($email);
				$parts = explode('@', $email, 2);
				if(count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
						return '***';
				}
				$local = substr($parts[0], 0, 1);
				return $local . '***@' . $parts[1];
		}

		/**
		 * Return the current administrator's invite or create one when no current
		 * invite exists. A lost session never creates a second active invite.
		 *
		 * @param int $adminGuid
		 * @return array|false
		 */
		public static function getOrCreateForAdmin($adminGuid) {
				$adminGuid = (int) $adminGuid;
				if($adminGuid < 1 || !self::ensureTable()) {
						return false;
				}

				$sessionToken = self::sessionValue('token');
				$sessionHash  = self::sessionValue('hash');
				if(self::normalizeToken($sessionToken) && self::normalizeHash($sessionHash)) {
						$row = self::findByHash($sessionHash, array('active'), $adminGuid);
						if($row && hash_equals($sessionHash, $row['token_hash'])) {
							return self::formatInvite($sessionToken);
						}
				}

				// The database is the source of truth. The raw token cannot be
				// reconstructed from its hash, so ask the administrator to rotate it.
				$current = self::findCurrentForAdmin($adminGuid);
				if($current === null) {
						return false;
				}
				if($current !== false) {
						return array('ok' => false, 'error' => 'missing_token');
				}

				return self::createForAdmin($adminGuid, false);
		}

		/**
		 * Create a new link under a per-administrator database row lock.
		 * Explicit rotation revokes both active and reserved links atomically
		 * with creation of the replacement link.
		 *
		 * @param int $adminGuid
		 * @param bool $revokeExisting
		 * @return array|false
		 */
		public static function createForAdmin($adminGuid, $revokeExisting = true) {
				$adminGuid = (int) $adminGuid;
				if($adminGuid < 1 || !self::ensureTable()) {
						return false;
				}

				$db          = self::database();
				$inviteTable = self::table(self::TABLE);
				$userTable   = self::table('users');
				if(!$db || $inviteTable === false || $userTable === false || $db->inTransaction()) {
						return false;
				}

				try {
						$db->beginTransaction();

						// Every administrator has a stable OSSN user row. Locking it
						// serializes concurrent rotations for that administrator.
						$lock = self::query(
								"SELECT `guid` FROM {$userTable} WHERE `guid` = ? FOR UPDATE",
								array($adminGuid)
						);
						if($lock === false || !$lock->fetch(PDO::FETCH_ASSOC)) {
								throw new RuntimeException('SREDA invite administrator lock failed.');
						}

						if(!$revokeExisting) {
								$current = self::findCurrentForAdmin($adminGuid);
								if($current === null) {
										throw new RuntimeException('SREDA invite current state lookup failed.');
								}
								if($current !== false) {
										$db->commit();
										return array('ok' => false, 'error' => 'missing_token');
								}
						}

						if($revokeExisting) {
								$revoked = self::query(
										"UPDATE {$inviteTable} SET `status` = 'revoked', `reserved_at` = NULL, `reservation_key` = NULL WHERE `created_by` = ? AND `status` IN ('active', 'reserved')",
										array($adminGuid)
									);
								if($revoked === false) {
										throw new RuntimeException('SREDA invite rotation failed.');
								}
						}

						$token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
						$hash  = self::hashToken($token);
						$inserted = self::query(
								"INSERT INTO {$inviteTable} (`token_hash`, `created_by`, `created_at`, `status`) VALUES (?, ?, ?, 'active')",
								array($hash, $adminGuid, time())
						);
						if($inserted === false) {
								throw new RuntimeException('SREDA invite creation failed.');
						}

						if(!$db->commit()) {
								throw new RuntimeException('SREDA invite transaction commit failed.');
						}
						self::setSessionToken($token, $hash);
						return self::formatInvite($token);
				} catch(Throwable $exception) {
						if($db->inTransaction()) {
							try {
									$db->rollBack();
							} catch(Throwable $rollbackException) {
									// Keep the failure closed; never expose token data.
							}
						}
						error_log('SREDA invite database operation failed.');
						return false;
				}
		}

		/**
		 * Atomically reserve an active token before the core registration action
		 * starts. Reserved tokens are never revived by time-based recovery.
		 *
		 * @param string $token
		 * @param string $email
		 * @return array
		 */
		public static function reserve($token, $email = '') {
				$token = self::normalizeToken($token);
				if(!$token || !self::ensureTable()) {
						return array('ok' => false, 'error' => 'invalid');
				}

				$table = self::table(self::TABLE);
				if($table === false) {
						return array('ok' => false, 'error' => 'invalid');
				}

				try {
						$reservationKey = bin2hex(random_bytes(32));
				} catch(Throwable $exception) {
						return array('ok' => false, 'error' => 'invalid');
				}
				$email = self::normalizeEmail($email);
				if(!self::isValidEmail($email)) {
						return array('ok' => false, 'error' => 'invalid');
				}
				$hash = self::hashToken($token);
				$row = self::findByHash($hash);
				if(!$row || empty($row['invited_email'])) {
						return array('ok' => false, 'error' => 'invalid');
				}
				$invitedEmail = self::normalizeEmail($row['invited_email']);
				if($invitedEmail !== $email) {
						return array('ok' => false, 'error' => 'email_mismatch', 'invited_email' => $invitedEmail);
				}
				$statement = self::query(
						"UPDATE {$table} SET `status` = 'reserved', `reserved_at` = ?, `reservation_key` = ? WHERE `token_hash` = ? AND `invited_email` = ? AND `status` = 'active'",
						array(time(), $reservationKey, $hash, $email)
				);
				if($statement !== false && $statement->rowCount() === 1) {
						return array(
								'ok'              => true,
								'token_hash'      => $hash,
								'reservation_key' => $reservationKey,
						);
				}

				$status = self::status($token);
				return array(
						'ok'    => false,
						'error' => $status === 'used' ? 'used' : ($status === 'reserved' ? 'reserved' : 'invalid'),
				);
		}

		/**
		 * Lock a live reservation until the core user INSERT and user:created
		 * callback finish. A revoke from another request must either commit before
		 * this lock (and fail the lookup) or wait until this transaction commits.
		 *
		 * @param array $reservation
		 * @return bool
		 */
		public static function beginRegistrationReservation($reservation) {
				if(empty($reservation['token_hash']) || empty($reservation['reservation_key']) || !self::ensureTable()) {
						return false;
				}
				$db = self::database();
				$table = self::table(self::TABLE);
				if(!$db || $table === false || $db->inTransaction()) {
						return false;
				}

				try {
						$db->beginTransaction();
						$statement = self::query(
								"SELECT `token_hash` FROM {$table} WHERE `token_hash` = ? AND `reservation_key` = ? AND `status` = 'reserved' LIMIT 1 FOR UPDATE",
								array($reservation['token_hash'], $reservation['reservation_key'])
						);
						if($statement === false || !$statement->fetch(PDO::FETCH_ASSOC)) {
								$db->rollBack();
								return false;
						}
						return true;
				} catch(Throwable $exception) {
						if($db->inTransaction()) {
							try {
									$db->rollBack();
							} catch(Throwable $rollbackException) {
									// Keep the failure closed.
							}
						}
					error_log('SREDA invite registration reservation lock failed.');
						return false;
				}
		}

		/**
		 * Commit the transaction opened by beginRegistrationReservation().
		 *
		 * @return bool
		 */
		public static function commitRegistrationReservation() {
				$db = self::database();
				if(!$db || !$db->inTransaction()) {
						return false;
				}
				try {
						return $db->commit();
				} catch(Throwable $exception) {
						if($db->inTransaction()) {
							try {
									$db->rollBack();
							} catch(Throwable $rollbackException) {
									// Keep the failure closed.
							}
						}
					error_log('SREDA invite registration transaction commit failed.');
						return false;
				}
		}

		/**
		 * Roll back the transaction opened by beginRegistrationReservation().
		 *
		 * @return bool
		 */
		public static function rollbackRegistrationReservation() {
				$db = self::database();
				if(!$db || !$db->inTransaction()) {
						return true;
				}
				try {
						return $db->rollBack();
				} catch(Throwable $exception) {
					error_log('SREDA invite registration transaction rollback failed.');
						return false;
				}
		}

		/**
		 * Release a reservation only after the wrapper proves that core returned
		 * a normal pre-creation validation response.
		 *
		 * @param array $reservation
		 * @return bool
		 */
		public static function release($reservation) {
				if(empty($reservation['token_hash']) || empty($reservation['reservation_key'])) {
						return false;
				}
				$table = self::table(self::TABLE);
				if($table === false) {
						return false;
				}
				$statement = self::query(
						"UPDATE {$table} SET `status` = 'active', `reserved_at` = NULL, `reservation_key` = NULL WHERE `token_hash` = ? AND `reservation_key` = ? AND `status` = 'reserved'",
						array($reservation['token_hash'], $reservation['reservation_key'])
				);
				return $statement !== false && $statement->rowCount() === 1;
		}

		/**
		 * Mark a reserved token used only after user creation succeeded.
		 *
		 * @param array $reservation
		 * @param int $userGuid
		 * @return bool
		 */
		public static function consume($reservation, $userGuid) {
				if(empty($reservation['token_hash']) || empty($reservation['reservation_key']) || (int) $userGuid < 1) {
						return false;
				}
				$table = self::table(self::TABLE);
				if($table === false) {
						return false;
				}
				$statement = self::query(
						"UPDATE {$table} SET `status` = 'used', `used_at` = ?, `used_by` = ?, `reserved_at` = NULL, `reservation_key` = NULL WHERE `token_hash` = ? AND `reservation_key` = ? AND `status` = 'reserved'",
						array(time(), (int) $userGuid, $reservation['token_hash'], $reservation['reservation_key'])
				);
				if($statement === false || $statement->rowCount() !== 1) {
						return false;
				}
				// A used token no longer needs to remain in any PHP session.
				self::forgetSessionToken($reservation['token_hash']);
				return true;
		}

		/**
		 * Return the stored state without exposing the hash.
		 *
		 * @param string $token
		 * @return string
		 */
		public static function status($token) {
				$token = self::normalizeToken($token);
				if(!$token || !self::ensureTable()) {
						return 'invalid';
				}
				$row = self::findByHash(self::hashToken($token));
				return $row && !empty($row['status']) ? $row['status'] : 'invalid';
		}

		/**
		 * @param string $token
		 * @return string|false
		 */
		private static function normalizeToken($token) {
				if(!is_string($token)) {
						return false;
				}
				$token = trim($token);
				return preg_match('/^[A-Za-z0-9_-]{43}$/D', $token) ? $token : false;
		}

		/**
		 * @param string $hash
		 * @return string|false
		 */
		private static function normalizeHash($hash) {
				return is_string($hash) && preg_match('/^[a-f0-9]{64}$/D', $hash) ? $hash : false;
		}

		/**
		 * @param string $token
		 * @return string
		 */
		private static function hashToken($token) {
				return hash('sha256', $token);
		}

		/**
		 * Return the active/reserved invite for an administrator.
		 * null means a database error; false means no current invite.
		 *
		 * @param int $adminGuid
		 * @return array|false|null
		 */
		private static function findCurrentForAdmin($adminGuid) {
				$table = self::table(self::TABLE);
				if($table === false) {
						return null;
				}
				$statement = self::query(
						"SELECT `id`, `status` FROM {$table} WHERE `created_by` = ? AND `status` IN ('active', 'reserved') ORDER BY `id` DESC LIMIT 1",
						array((int) $adminGuid)
				);
				if($statement === false) {
						return null;
				}
				$row = $statement->fetch(PDO::FETCH_ASSOC);
				return $row ?: false;
		}

		/**
		 * @param int $adminGuid
		 * @param string $email
		 * @param bool $forUpdate
		 * @return array|false|null
		 */
		private static function findPersonalForAdminEmail($adminGuid, $email, $forUpdate = false) {
				$table = self::table(self::TABLE);
				if($table === false) {
						return null;
				}
				$lock = $forUpdate ? ' FOR UPDATE' : '';
				$statement = self::query(
						"SELECT `id`, `token_hash`, `created_by`, `invited_email`, `created_at`, `used_at`, `used_by`, `status`, `reserved_at`, `sent_at`, `last_sent_at`, `send_count` FROM {$table} WHERE `created_by` = ? AND `invited_email` = ? AND `status` IN ('active', 'reserved') ORDER BY `id` DESC LIMIT 1{$lock}",
						array((int) $adminGuid, $email)
				);
				if($statement === false) {
						return null;
				}
				$row = $statement->fetch(PDO::FETCH_ASSOC);
				return $row ?: false;
		}

		/**
		 * @param string $hash
		 * @param int $adminGuid
		 * @param bool $forUpdate
		 * @return array|false|null
		 */
		private static function findByHashForAdmin($hash, $adminGuid, $forUpdate = false) {
				$table = self::table(self::TABLE);
				if($table === false || !self::normalizeHash($hash)) {
						return null;
				}
				$lock = $forUpdate ? ' FOR UPDATE' : '';
				$statement = self::query(
						"SELECT `id`, `token_hash`, `created_by`, `invited_email`, `created_at`, `used_at`, `used_by`, `status`, `reserved_at`, `sent_at`, `last_sent_at`, `send_count` FROM {$table} WHERE `token_hash` = ? AND `created_by` = ? LIMIT 1{$lock}",
						array($hash, (int) $adminGuid)
				);
				if($statement === false) {
						return null;
				}
				$row = $statement->fetch(PDO::FETCH_ASSOC);
				return $row ?: false;
		}

		/**
		 * @param string $hash
		 * @param array|false $statuses
		 * @param int|false $createdBy
		 * @return array|false
		 */
		private static function findByHash($hash, $statuses = false, $createdBy = false) {
				if(!self::normalizeHash($hash)) {
						return false;
				}
				$table  = self::table(self::TABLE);
				if($table === false) {
						return false;
				}
				$where  = array('token_hash = ?');
				$values = array($hash);
				if($statuses !== false) {
						$statuses = is_array($statuses) ? $statuses : array($statuses);
						if(empty($statuses)) {
								return false;
						}
						$where[] = 'status IN (' . implode(', ', array_fill(0, count($statuses), '?')) . ')';
						$values  = array_merge($values, $statuses);
				}
				if($createdBy !== false) {
						$where[] = 'created_by = ?';
						$values[] = (int) $createdBy;
				}
				$statement = self::query(
						"SELECT `id`, `token_hash`, `created_by`, `invited_email`, `created_at`, `used_at`, `used_by`, `status`, `reserved_at`, `sent_at`, `last_sent_at`, `send_count` FROM {$table} WHERE " . implode(' AND ', $where) . " ORDER BY `id` DESC LIMIT 1",
						$values
				);
				if($statement === false) {
						return false;
				}
				$row = $statement->fetch(PDO::FETCH_ASSOC);
				return $row ?: false;
		}

		/**
		 * @param string $token
		 * @return array
		 */
		private static function formatInvite($token) {
				$inviteUrl = self::inviteUrl($token);
				return array(
						'token'      => $token,
						'invite_url' => $inviteUrl,
				);
		}

		/**
		 * @param array $row
		 * @param string|false $token
		 * @return array
		 */
		private static function formatInviteRecord($row, $token = false) {
				$result = array(
						'ok' => true,
						'id' => isset($row['id']) ? (int) $row['id'] : 0,
						'invited_email' => isset($row['invited_email']) ? self::normalizeEmail($row['invited_email']) : '',
						'created_at' => isset($row['created_at']) ? (int) $row['created_at'] : 0,
						'used_at' => !empty($row['used_at']) ? (int) $row['used_at'] : null,
						'used_by' => !empty($row['used_by']) ? (int) $row['used_by'] : null,
						'status' => isset($row['status']) ? (string) $row['status'] : 'invalid',
						'reserved_at' => !empty($row['reserved_at']) ? (int) $row['reserved_at'] : null,
						'sent_at' => !empty($row['sent_at']) ? (int) $row['sent_at'] : null,
						'last_sent_at' => !empty($row['last_sent_at']) ? (int) $row['last_sent_at'] : null,
						'send_count' => isset($row['send_count']) ? (int) $row['send_count'] : 0,
						'invite_url' => '',
				);
				if(is_string($token) && self::normalizeToken($token) && in_array($result['status'], array('active', 'reserved'), true)) {
						$result['token'] = $token;
						$result['invite_url'] = self::inviteUrl($token);
				}
				return $result;
		}

		/**
		 * @param string $token
		 * @return string
		 */
		private static function inviteUrl($token) {
				$base = rtrim(ossn_site_url(), '/');
				return $base . '/?invite=' . rawurlencode($token);
		}

		/**
		 * Build a small HTML email and a plain-text alternative. The raw token is
		 * used only to construct the recipient's URL and is never logged/stored.
		 *
		 * @param string $email
		 * @param string $url
		 * @return array
		 */
		private static function buildEmailContent($email, $url) {
				$email = self::normalizeEmail($email);
				$site = htmlspecialchars('SREDA', ENT_QUOTES, 'UTF-8');
				$safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
				$safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
				$html = '<!doctype html><html><body style="margin:0;background:#f3f6fa;font-family:Arial,sans-serif;color:#27364a;">'
						. '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;"><tr><td align="center">'
						. '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #dce3eb;">'
						. '<tr><td style="padding:22px 24px;background:#1e293b;color:#ffffff;font-size:22px;font-weight:bold;">' . $site . '</td></tr>'
						. '<tr><td style="padding:26px 24px;font-size:16px;line-height:1.6;">'
						. '<p style="margin:0 0 16px;">Вас пригласили присоединиться к SREDA.</p>'
						. '<p style="margin:0 0 16px;">Сейчас социальная сеть находится в активной разработке и тестировании, поэтому регистрация доступна только по приглашениям.</p>'
						. '<p style="margin:0 0 22px;">Это приглашение создано специально для:<br><strong>' . $safeEmail . '</strong></p>'
						. '<p style="margin:0 0 22px;"><a href="' . $safeUrl . '" style="display:inline-block;padding:12px 20px;background:#1e293b;color:#ffffff;text-decoration:none;font-weight:bold;">Принять приглашение</a></p>'
						. '<p style="margin:0 0 14px;font-size:12px;color:#6c7b8d;">Если кнопка не работает, откройте ссылку:<br><a href="' . $safeUrl . '" style="color:#2563eb;word-break:break-all;">' . $safeUrl . '</a></p>'
						. '<p style="margin:0;font-size:12px;color:#6c7b8d;">Если вы не ожидали это письмо, просто проигнорируйте его.</p>'
						. '</td></tr></table></td></tr></table></body></html>';
				$text = "Вас пригласили присоединиться к SREDA.\n\n"
						. "Сейчас социальная сеть находится в активной разработке и тестировании, поэтому регистрация доступна только по приглашениям.\n\n"
						. "Это приглашение создано специально для: {$email}\n\n"
						. "Принять приглашение: {$url}\n\n"
						. "Если вы не ожидали это письмо, просто проигнорируйте его.";
				return array('html' => $html, 'text' => $text);
		}

		/**
		 * @param string $key
		 * @return mixed
		 */
		private static function sessionValue($key) {
				if(session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['sreda_invite'][$key])) {
						return false;
				}
				return $_SESSION['sreda_invite'][$key];
		}

		/**
		 * @param string $token
		 * @param string $hash
		 * @return void
		 */
		private static function setSessionToken($token, $hash) {
				if(session_status() === PHP_SESSION_ACTIVE) {
						$_SESSION['sreda_invite'] = array(
							'token' => $token,
							'hash'  => $hash,
						);
						self::rememberSessionToken($hash, $token);
				}
		}

		/**
		 * Keep raw tokens only in the active administrator session when they are
		 * needed for the visible link, copy/share and resend controls. The database
		 * remains hash-only.
		 *
		 * @param string $hash
		 * @param string $token
		 * @return void
		 */
		private static function rememberSessionToken($hash, $token) {
				if(session_status() === PHP_SESSION_ACTIVE && self::normalizeHash($hash) && self::normalizeToken($token)) {
						if(!isset($_SESSION['sreda_invite_tokens']) || !is_array($_SESSION['sreda_invite_tokens'])) {
							$_SESSION['sreda_invite_tokens'] = array();
						}
						$_SESSION['sreda_invite_tokens'][$hash] = $token;
				}
		}

		/**
		 * @param string $hash
		 * @return string|false
		 */
		private static function sessionTokenForHash($hash) {
				if(session_status() !== PHP_SESSION_ACTIVE || !self::normalizeHash($hash)) {
						return false;
				}
				$candidates = array();
				if(isset($_SESSION['sreda_invite_tokens']) && is_array($_SESSION['sreda_invite_tokens']) && isset($_SESSION['sreda_invite_tokens'][$hash])) {
						$candidates[] = $_SESSION['sreda_invite_tokens'][$hash];
				}
				if(isset($_SESSION['sreda_invite']['hash'], $_SESSION['sreda_invite']['token']) && hash_equals((string) $_SESSION['sreda_invite']['hash'], $hash)) {
						$candidates[] = $_SESSION['sreda_invite']['token'];
				}
				foreach($candidates as $token) {
						$token = self::normalizeToken($token);
						if($token && hash_equals($hash, self::hashToken($token))) {
							return $token;
						}
				}
				return false;
		}

		/**
		 * @param string $hash
		 * @return void
		 */
		private static function forgetSessionToken($hash) {
				if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['sreda_invite_tokens'][$hash])) {
						unset($_SESSION['sreda_invite_tokens'][$hash]);
				}
				if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['sreda_invite']['hash']) && hash_equals((string) $_SESSION['sreda_invite']['hash'], (string) $hash)) {
						unset($_SESSION['sreda_invite']);
				}
		}

		/**
		 * Current OSSN 9.7 source has no configurable table-prefix API or config
		 * property; its core SQL uses ossn_* names directly. Detect the installed
		 * prefix from the actual OSSN table family through the existing PDO API.
		 * Ambiguous or unavailable detection fails closed.
		 *
		 * @return string|false
		 */
		private static function getTablePrefix() {
				if(self::$tablePrefix !== null) {
						return self::$tablePrefix;
				}

				$db = self::database();
				if(!$db) {
						self::$tablePrefix = false;
						return false;
				}

				try {
						$statement = $db->query('SHOW TABLES');
						if(!$statement) {
							self::$tablePrefix = false;
							return false;
						}
						$tables = $statement->fetchAll(PDO::FETCH_COLUMN);
						$lookup = array();
						foreach($tables as $tableName) {
								if(is_string($tableName)) {
										$lookup[strtolower($tableName)] = true;
								}
						}

						$candidates = array();
						foreach(array_keys($lookup) as $tableName) {
							if($tableName === 'components') {
									$prefix = '';
							} elseif(substr($tableName, -strlen('components')) === 'components') {
									$prefix = substr($tableName, 0, -strlen('components'));
									if($prefix === '' || substr($prefix, -1) !== '_') {
											continue;
									}
							} else {
									continue;
							}
							if(!preg_match('/^[a-z0-9_]*$/D', $prefix)) {
									continue;
							}
							$required = array('users', 'entities', 'entities_metadata');
							$complete = true;
							foreach($required as $suffix) {
									if(!isset($lookup[$prefix . $suffix])) {
											$complete = false;
											break;
									}
							}
							if($complete) {
									$candidates[$prefix] = true;
							}
						}

						if(isset($candidates['ossn_'])) {
							self::$tablePrefix = 'ossn_';
						} elseif(count($candidates) === 1) {
							self::$tablePrefix = (string) array_key_first($candidates);
						} else {
							self::$tablePrefix = false;
						}
				} catch(Throwable $exception) {
						self::$tablePrefix = false;
				}
				return self::$tablePrefix;
		}

		/**
		 * @param string $suffix
		 * @return string|false
		 */
		private static function table($suffix) {
				if(!is_string($suffix) || !preg_match('/^[a-z0-9_]+$/D', $suffix)) {
						return false;
				}
				$prefix = self::getTablePrefix();
				return $prefix === false ? false : '`' . $prefix . $suffix . '`';
		}

		/**
		 * @return PDO|false
		 */
		private static function database() {
				global $Ossn;
				return isset($Ossn->dbLINK) && $Ossn->dbLINK instanceof PDO ? $Ossn->dbLINK : false;
		}

		/**
		 * Execute a prepared query. SQL is internal to this component; values are
		 * always passed separately to PDO.
		 *
		 * @param string $sql
		 * @param array $values
		 * @return PDOStatement|false
		 */
		private static function query($sql, $values = array()) {
				$db = self::database();
				if(!$db) {
						return false;
				}
				try {
						$statement = $db->prepare($sql);
						$statement->execute($values);
						return $statement;
				} catch(Throwable $exception) {
						error_log('SREDA invite database operation failed.');
						return false;
				}
		}

		/**
		 * @param string $sql
		 * @param array $values
		 * @return PDOStatement|false
		 */
		private static function execute($sql, $values = array()) {
				return self::query($sql, $values);
		}
}
