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
						`created_at` INT UNSIGNED NOT NULL,
						`used_at` INT UNSIGNED DEFAULT NULL,
						`used_by` BIGINT UNSIGNED DEFAULT NULL,
						`status` VARCHAR(16) NOT NULL DEFAULT 'active',
						`reserved_at` INT UNSIGNED DEFAULT NULL,
						`reservation_key` CHAR(64) CHARACTER SET ascii DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `sreda_invites_token_hash` (`token_hash`),
						KEY `sreda_invites_status_created` (`status`, `created_at`),
						KEY `sreda_invites_reservation_key` (`reservation_key`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

				if(self::execute($sql) !== false) {
						$ready = true;
						return true;
				}
				return false;
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
		 * @return array
		 */
		public static function reserve($token) {
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
				$hash      = self::hashToken($token);
				$statement = self::query(
						"UPDATE {$table} SET `status` = 'reserved', `reserved_at` = ?, `reservation_key` = ? WHERE `token_hash` = ? AND `status` = 'active'",
						array(time(), $reservationKey, $hash)
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
				return $statement !== false && $statement->rowCount() === 1;
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
						"SELECT `id`, `token_hash`, `created_by`, `status` FROM {$table} WHERE " . implode(' AND ', $where) . " ORDER BY `id` DESC LIMIT 1",
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
				$base      = rtrim(ossn_site_url(), '/');
				$inviteUrl = $base . '/?invite=' . rawurlencode($token);
				return array(
						'token'      => $token,
						'invite_url' => $inviteUrl,
				);
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
