#!/usr/bin/env bash
set -Eeuo pipefail

APP='/srv/sreda/app'
APP_ROOT='/srv/sreda'
DATA_ROOT='/srv/sreda/data'
BACKUP_ROOT='/srv/metasiberia/data/sreda-backups'
STATE_ROOT='/srv/sreda/deploy-state'
INBOX_ROOT='/home/denshipilov/sreda-deploy-inbox'

ARCHIVE=''
MANIFEST=''
COMMIT=''
PUBLIC_URL=''
REMOTE_HELPER="$0"
STAGE=''
BACKUP_APP=''
BACKUP_DB=''
ROLLBACK_REQUIRED=0
ROLLBACK_DONE=0

die() {
    echo "ERROR: $*" >&2
    exit 1
}

usage() {
    cat >&2 <<'EOF'
Usage: sreda-deploy-remote.sh --archive PATH --manifest PATH --commit SHA --public-url URL
EOF
    exit 2
}

while (($# > 0)); do
    case "$1" in
        --archive) ARCHIVE=${2:-}; shift 2 ;;
        --manifest) MANIFEST=${2:-}; shift 2 ;;
        --commit) COMMIT=${2:-}; shift 2 ;;
        --public-url) PUBLIC_URL=${2:-}; shift 2 ;;
        *) usage ;;
    esac
done

[[ -n "$ARCHIVE" && -n "$MANIFEST" && -n "$COMMIT" && -n "$PUBLIC_URL" ]] || usage
[[ "$COMMIT" =~ ^[0-9a-f]{40}$ ]] || die 'Некорректный commit SHA.'
[[ "$PUBLIC_URL" == 'https://sreda.metasiberia.com/' ]] || die 'Разрешён только production URL https://sreda.metasiberia.com/.'
[[ "$APP" == '/srv/sreda/app' && "$APP_ROOT" == '/srv/sreda' && "$DATA_ROOT" == '/srv/sreda/data' ]] || die 'Нарушены защитные пути приложения.'
[[ "$ARCHIVE" == "$INBOX_ROOT"/* && "$MANIFEST" == "$INBOX_ROOT"/* ]] || die 'Входные файлы должны находиться в server inbox.'
[[ "$REMOTE_HELPER" == "$INBOX_ROOT"/* ]] || die 'Remote helper должен находиться в server inbox.'

for required_command in sudo tar rsync sha256sum curl awk grep systemctl wc; do
    command -v "$required_command" >/dev/null 2>&1 || die "Не найдена команда: $required_command"
done
[[ -x /usr/bin/php ]] || die 'Не найден /usr/bin/php.'
[[ -x /usr/bin/mysqldump ]] || die 'Не найден /usr/bin/mysqldump.'

normalize_runtime_cache_permissions() {
    local owner
    owner=$(id -un)

    if ! sudo test -d "$APP/cache"; then
        sudo install -d -o "$owner" -g www-data -m 2775 "$APP/cache"
    fi
    sudo install -d -o "$owner" -g www-data -m 2775 "$DATA_ROOT/system"
    sudo chown -R "$owner":www-data "$APP/cache" "$DATA_ROOT/system"
    sudo find "$APP/cache" "$DATA_ROOT/system" -type d -exec chmod 2775 {} +
    sudo find "$APP/cache" "$DATA_ROOT/system" -type f -exec chmod 664 {} +
}

refresh_ossn_cache() {
    local cache_disable_output=''
    local cache_enable_output=''

    normalize_runtime_cache_permissions

    if ! cache_disable_output=$(cd "$APP" && /usr/bin/php system/handlers/cli --handler=DisableCache 2>&1); then
        echo "$cache_disable_output" >&2
        return 1
    fi
    if ! cache_enable_output=$(cd "$APP" && /usr/bin/php system/handlers/cli --handler=EnableCache 2>&1); then
        echo "$cache_enable_output" >&2
        return 1
    fi
    printf '%s\n%s\n' "$cache_disable_output" "$cache_enable_output"

    if printf '%s\n%s\n' "$cache_disable_output" "$cache_enable_output" | grep -Eiq 'Fatal error|Exception|Warning|Could not|Unable|failed'; then
        return 1
    fi
    printf '%s\n' "$cache_enable_output" | grep -Fq 'Cache has been enabled' || return 1

    normalize_runtime_cache_permissions
}

validate_ossn_cache() {
    local locale_file="$DATA_ROOT/system/locales/ossn.ru.json"

    sudo test -s "$DATA_ROOT/system/plugins_paths" || {
        echo 'OSSN cache validation failed: system/plugins_paths is missing or empty.' >&2
        return 1
    }
    sudo test -s "$locale_file" || {
        echo 'OSSN cache validation failed: Russian locale cache is missing or empty.' >&2
        return 1
    }
    sudo grep -Fq 'Муж' "$locale_file" || {
        echo 'OSSN cache validation failed: Муж is missing from Russian locale cache.' >&2
        return 1
    }
    sudo grep -Fq 'Жен' "$locale_file" || {
        echo 'OSSN cache validation failed: Жен is missing from Russian locale cache.' >&2
        return 1
    }
    if sudo grep -Eq 'Самец|Самка' "$locale_file"; then
        echo 'OSSN cache validation failed: stale gender labels remain in Russian locale cache.' >&2
        return 1
    fi
}

ensure_smtp_component_registered() {
    local registration_output=''

    if ! registration_output=$(cd "$APP" && /usr/bin/php -r '
define("OSSN_ALLOW_SYSTEM_START", true);
require_once "system/start.php";
$components = new OssnComponents();
$installed = $components->getComponents();
if (!is_array($installed) || !in_array("SMTP", $installed, true)) {
    if (!$components->newCom("SMTP")) {
        fwrite(STDERR, "SMTP component registration failed.\n");
        exit(1);
    }
}
' 2>&1); then
        printf '%s\n' "$registration_output" >&2
        return 1
    fi
}

sudo -v

STAMP=$(date +%Y%m%d-%H%M%S)
STAGE="$HOME/.sreda-stage-${COMMIT}-${STAMP}"
BACKUP_APP="$BACKUP_ROOT/sreda-app-before-${COMMIT}-${STAMP}.tar.gz"
BACKUP_DB="$BACKUP_ROOT/sreda-db-before-${COMMIT}-${STAMP}.sql"

cleanup_and_rollback() {
    local exit_code=$?

    if ((exit_code != 0 && ROLLBACK_REQUIRED == 1 && ROLLBACK_DONE == 0)); then
        echo 'Deployment failed after application replacement; starting rollback.' >&2
        set +e
        if sudo test -f "$BACKUP_APP" && sudo test -d "$APP_ROOT" && [[ "$APP" == '/srv/sreda/app' ]]; then
            sudo rm -rf -- "$APP"
            sudo tar -xzf "$BACKUP_APP" -C "$APP_ROOT"
            if sudo test -d "$APP"; then
                ROLLBACK_DONE=1
                echo 'ROLLBACK=completed' >&2
                if refresh_ossn_cache && validate_ossn_cache; then
                    echo 'ROLLBACK_CACHE=restored' >&2
                else
                    echo 'ROLLBACK_CACHE=failed: OSSN cache could not be rebuilt.' >&2
                fi
            else
                echo 'ROLLBACK=failed: application directory was not restored.' >&2
            fi
        else
            echo 'ROLLBACK=skipped: verified application backup is unavailable.' >&2
        fi
        set -e
    fi

    if ((exit_code == 0)); then
        rm -rf -- "$STAGE"
        rm -f -- "$ARCHIVE" "$MANIFEST" "$REMOTE_HELPER"
    else
        echo "FAILED_STAGE=$STAGE" >&2
        echo 'Server stage and uploaded inputs were retained for diagnosis.' >&2
    fi

    exit "$exit_code"
}

trap cleanup_and_rollback EXIT

sudo install -d -o root -g root -m 0750 "$BACKUP_ROOT"
sudo tar -C "$APP_ROOT" -czf "$BACKUP_APP" app
sudo sha256sum "$BACKUP_APP" | sudo tee "$BACKUP_APP.sha256" >/dev/null

if ! sudo /usr/bin/mysqldump --single-transaction --routines --triggers sreda_ossn | sudo tee "$BACKUP_DB" >/dev/null; then
    die 'Не удалось создать backup базы sreda_ossn.'
fi
sudo sha256sum "$BACKUP_DB" | sudo tee "$BACKUP_DB.sha256" >/dev/null

mkdir -p "$STAGE"
tar -xf "$ARCHIVE" -C "$STAGE"
[[ -d "$STAGE/release" ]] || die 'В архиве отсутствует каталог release/.'

manifest_count=0
while IFS= read -r line || [[ -n "$line" ]]; do
    line=${line%$'\r'}
    [[ "$line" =~ ^([0-9a-fA-F]{64})[[:space:]][[:space:]](.+)$ ]] || die 'Некорректная строка SHA256 manifest.'
    expected_hash=${BASH_REMATCH[1],,}
    relative_path=${BASH_REMATCH[2]}
    case "$relative_path" in
        ''|/*|../*|*/../*|*/..|.|..) die "Недопустимый путь в manifest: $relative_path" ;;
    esac
    candidate="$STAGE/release/$relative_path"
    [[ -f "$candidate" ]] || die "Файл из manifest отсутствует в staged release: $relative_path"
    actual_hash=$(sha256sum -- "$candidate" | awk '{print $1}')
    [[ "$actual_hash" == "$expected_hash" ]] || die "SHA256 mismatch в staged release: $relative_path"
    manifest_count=$((manifest_count + 1))
done < "$MANIFEST"
((manifest_count > 0)) || die 'SHA256 manifest пустой.'

ROLLBACK_REQUIRED=1
sudo rsync -a --delete \
    --exclude='/configurations/ossn.config.db.php' \
    --exclude='/configurations/ossn.config.site.php' \
    --exclude='/cache/' \
    --exclude='/installation/INSTALLED' \
    --exclude='/docs/' \
    --exclude='/ops/' \
    --exclude='/ossn_data/' \
    --exclude='/error_log' \
    --exclude='/.htaccess' \
    "$STAGE/release/" "$APP/"

is_runtime_excluded() {
    case "$1" in
        configurations/ossn.config.db.php|configurations/ossn.config.site.php|cache/*|installation/INSTALLED|docs/*|ops/*|ossn_data/*|error_log|.htaccess) return 0 ;;
        *) return 1 ;;
    esac
}

while IFS= read -r line || [[ -n "$line" ]]; do
    line=${line%$'\r'}
    [[ "$line" =~ ^([0-9a-fA-F]{64})[[:space:]][[:space:]](.+)$ ]] || die 'Некорректная строка SHA256 manifest при runtime-проверке.'
    expected_hash=${BASH_REMATCH[1],,}
    relative_path=${BASH_REMATCH[2]}
    if is_runtime_excluded "$relative_path"; then
        continue
    fi
    candidate="$APP/$relative_path"
    [[ -f "$candidate" ]] || die "После sync отсутствует runtime-файл: $relative_path"
    actual_hash=$(sudo sha256sum -- "$candidate" | awk '{print $1}')
    [[ "$actual_hash" == "$expected_hash" ]] || die "SHA256 mismatch после sync: $relative_path"
done < "$MANIFEST"

sudo test -f "$APP/installation/INSTALLED" || die 'Потерян installation/INSTALLED.'
sudo test -f "$APP/configurations/ossn.config.db.php" || die 'Потерян DB runtime config.'
sudo test -f "$APP/configurations/ossn.config.site.php" || die 'Потерян site runtime config.'

if ! refresh_ossn_cache; then
    die 'Не удалось безопасно пересоздать OSSN cache.'
fi
validate_ossn_cache || die 'OSSN cache не прошёл обязательную проверку.'
ensure_smtp_component_registered || die 'Не удалось зарегистрировать SMTP-компонент в OSSN.'

for service in apache2 caddy mysql; do
    sudo systemctl is-active --quiet "$service" || die "Сервис не active: $service"
done

root_body="$STAGE/http-root.html"
login_body="$STAGE/http-login.html"
root_code=$(curl -k -sS -o "$root_body" -w '%{http_code}' --max-time 15 "$PUBLIC_URL") || die 'HTTP smoke-test главной страницы не выполнен.'
login_code=$(curl -k -sS -o "$login_body" -w '%{http_code}' --max-time 15 "${PUBLIC_URL%/}/login") || die 'HTTP smoke-test login не выполнен.'
[[ "$root_code" =~ ^[23][0-9][0-9]$ ]] || die "Главная страница вернула HTTP $root_code."
[[ "$login_code" =~ ^[23][0-9][0-9]$ ]] || die "Страница login вернула HTTP $login_code."
root_bytes=$(wc -c < "$root_body")
login_bytes=$(wc -c < "$login_body")
((root_bytes > 0)) || die 'Главная страница вернула пустой HTML.'
((login_bytes > 0)) || die 'Страница login вернула пустой HTML.'
grep -Eq 'SREDA|Создать аккаунт' "$root_body" || die 'Главная страница не содержит ожидаемый SREDA HTML.'
grep -Eq 'Муж|Жен' "$root_body" || die 'Главная страница не содержит актуальные гендерные подписи.'
if grep -Eq 'Самец|Самка' "$root_body"; then
    die 'Главная страница содержит устаревшие подписи Самец/Самка.'
fi

sudo install -d -o root -g root -m 0755 "$STATE_ROOT"
printf '%s\n' "$COMMIT" | sudo tee "$STATE_ROOT/last-deployed-commit" >/dev/null
printf '%s\n' "$STAMP" | sudo tee "$STATE_ROOT/last-deployed-at" >/dev/null

ROLLBACK_REQUIRED=0
echo 'DEPLOYMENT=success'
echo "COMMIT=$COMMIT"
echo "BACKUP_APP=$BACKUP_APP"
echo "BACKUP_APP_SHA256=$BACKUP_APP.sha256"
echo "BACKUP_DB=$BACKUP_DB"
echo "BACKUP_DB_SHA256=$BACKUP_DB.sha256"
echo "HTTP_ROOT=$root_code"
echo "HTTP_LOGIN=$login_code"
echo "HTTP_ROOT_BYTES=$root_bytes"
echo "HTTP_LOGIN_BYTES=$login_bytes"
echo 'CACHE=enabled'
