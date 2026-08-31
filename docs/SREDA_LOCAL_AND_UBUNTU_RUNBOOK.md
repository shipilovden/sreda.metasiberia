# SREDA (OSSN): локальный запуск и Ubuntu-сервер

Статус: рабочая эксплуатационная документация.

Документ относится к форку Open Source Social Network (OSSN) Metasiberia Social, который находится в каталоге:

```text
C:\programming\Metasiberia official\sreda.metasiberia
```

Пароли, токены, cookie, SSH-ключи и содержимое секретных конфигураций в этот документ не записываются.

## 1. Текущая схема

```text
Windows / Laragon
  Apache :80 + PHP 8.3.33
  MySQL 8.4.3
  http://localhost/

Интернет
  sreda.metasiberia.com (DNS REG.RU -> 87.103.196.229)
    -> Caddy :80/:443, автоматический HTTPS
    -> Apache 127.0.0.1:8081
    -> /srv/sreda/app
    -> MySQL 8.4.10, база sreda_ossn
```

REG.RU используется для домена и DNS. PHP-приложение, база и серверная часть SREDA работают на Ubuntu-сервере `metasiberia-server`.

## 2. Репозитории и каталоги

Локально:

- исходный код: `C:\programming\Metasiberia official\sreda.metasiberia`;
- пользовательские данные OSSN: `C:\programming\Metasiberia official\ossn_data\`;
- virtual host Laragon: `C:\laragon\etc\apache2\sites-enabled\social.metasiberia.local.conf`;
- URL и путь данных OSSN: `configurations\ossn.config.site.php`;
- локальная конфигурация БД: `configurations\ossn.config.db.php` (не публиковать).

На Ubuntu:

- приложение: `/srv/sreda/app`;
- пользовательские файлы и фотографии: `/srv/sreda/data`;
- резервные копии SREDA: `/srv/metasiberia/data/sreda-backups`;
- Apache: `/etc/apache2/sites-available/sreda.conf`;
- Caddy: `/etc/caddy/Caddyfile`;
- логи Apache: `/var/log/apache2/sreda_access.log` и `/var/log/apache2/sreda_error.log`.

На сервере нет `.git`: `/srv/sreda/app` является runtime-копией. Поэтому обновление на Ubuntu не делается через `git pull`.

## 3. Требования OSSN

Штатный установщик проверяет:

- PHP 8.0 или новее;
- Apache и `mod_rewrite`;
- MySQL/MariaDB и PHP `mysqli`;
- `allow_url_fopen`, `ZipArchive`, `curl`, `gd`, `openssl`;
- `SimpleXML`, `json`, `fileinfo`, `mbstring`, `exif`;
- запись в `cache` и конфигурацию во время установки;
- отдельный каталог пользовательских данных вне web root.

Фактически проверены PHP 8.3.33/MySQL 8.4.3 в Laragon и PHP 8.5.4/MySQL 8.4.10 на Ubuntu.

## 4. Локальный запуск Windows

1. Открыть `C:\laragon\laragon.exe`.
2. Запустить Apache и MySQL либо нажать `Start All`.
3. Открыть `http://localhost/`.

Проверка из PowerShell:

```powershell
netstat -ano | Select-String ':80 |:3306 '
curl.exe -I http://localhost/
```

После переименования путь `DocumentRoot` в Laragon virtual host обновлён на `sreda.metasiberia`. Если Apache уже был запущен, один раз нажать `Stop`/`Start` для перечитывания конфигурации.

Остановка выполняется через Laragon (`Stop` или `Stop All`).

Основные маршруты:

- сайт/лента: `http://localhost/` или `http://localhost/home`;
- вход: `http://localhost/login`;
- администрирование: `http://localhost/administrator/`;
- неподтверждённые пользователи: `http://localhost/administrator/unvalidated_users`.

Локальная БД называется `ossn_metasiberia_local`. Проверка без вывода пароля:

```powershell
& 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe' -uroot -N -e "SHOW DATABASES LIKE 'ossn_metasiberia_local';"
```

Если команда не вывела имя базы, локальная БД отсутствует. Не удаляй `installation/INSTALLED` и не переустанавливай приложение: создай только локальную БД и импортируй штатную схему:

```powershell
$mysql = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe'

& $mysql -uroot -e "CREATE DATABASE IF NOT EXISTS ossn_metasiberia_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

Get-Content -Raw -LiteralPath 'C:\programming\Metasiberia official\sreda.metasiberia\installation\sql\opensource-socialnetwork.sql' |
  & $mysql -uroot ossn_metasiberia_local
```

После импорта открой `http://localhost/` и проверь страницу в браузере. Этот импорт создаёт пустую локальную установку из схемы; старые локальные посты и пользователи восстановятся только из backup базы.

Письма активации в локальной среде не гарантируются. Для теста пользователя можно подтвердить администратором через `unvalidated_users`; реальную доставку нужно настраивать через SMTP.

## 5. Ubuntu: адрес и прокси

Публичный адрес:

```text
https://sreda.metasiberia.com/
```

Фактическая цепочка:

- Caddy принимает внешний HTTP/HTTPS на `80/443` и обслуживает TLS;
- Caddy проксирует домен на `127.0.0.1:8081`;
- Apache слушает только `127.0.0.1:8081` и обслуживает `/srv/sreda/app`;
- `/srv/sreda/data` запрещён для прямой выдачи Apache;
- MySQL работает локально и использует БД `sreda_ossn`.

Установка OSSN завершена: `installation/INSTALLED` присутствует, публичная проверка возвращает HTTP `200`.

Параметры БД:

- БД: `sreda_ossn`;
- пользователь: `sreda_ossn`@`localhost`;
- кодировка: `utf8mb4`;
- сортировка: `utf8mb4_unicode_ci`;
- пароль хранится только в runtime-конфигурации и приватном хранилище.

Проверка таблиц без credentials:

```bash
sudo mysql -e "SHOW TABLES FROM sreda_ossn;"
```

Текущие права:

```text
/srv/sreda/app   denshipilov:www-data  drwxrwsr-x
/srv/sreda/data  denshipilov:www-data  drwxrws---
```

В установщике путь данных нужно вводить точно как `/srv/sreda/data/` с завершающим `/`. Без него OSSN склеивает путь неправильно, например в `/srv/sreda/datawriteable`.

## 6. Включение, остановка и перезапуск Ubuntu

Отдельного `sreda.service` нет. Приложение работает через MySQL, Apache и Caddy.

Запуск после перезагрузки:

```bash
sudo systemctl enable --now mysql
sudo systemctl enable --now apache2
sudo systemctl enable --now caddy
systemctl is-active mysql apache2 caddy
systemctl is-enabled mysql apache2 caddy
```

Остановка:

```bash
sudo systemctl stop caddy
sudo systemctl stop apache2
sudo systemctl stop mysql
```

Порядок запуска: MySQL -> Apache -> Caddy. Порядок остановки обратный.

После изменения Apache:

```bash
sudo apachectl configtest
sudo systemctl reload apache2
```

После изменения Caddy:

```bash
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

Проверка:

```bash
systemctl is-active caddy apache2 mysql
ss -ltn | grep -E ':80 |:443 |:8081 '
curl -k -I https://sreda.metasiberia.com/
curl -I -H 'Host: sreda.metasiberia.com' http://127.0.0.1:8081/
```

Логи:

```bash
sudo tail -f /var/log/apache2/sreda_error.log
sudo tail -f /var/log/apache2/sreda_access.log
sudo journalctl -u apache2 -f
sudo journalctl -u caddy -f
```

На сервере `/etc/hosts` содержит локальное разрешение `sreda.metasiberia.com -> 127.0.0.1`. Это только обход NAT hairpin для локальной проверки и не заменяет DNS REG.RU.

## 7. Рабочий алгоритм: локальная правка → проверка → публикация

Рабочая последовательность для SREDA:

```text
локальная правка в Windows
        ↓
локальная проверка в Laragon и браузере
        ↓
git diff --check и просмотр diff
        ↓
локальный commit проверенных изменений
        ↓
подготовка серверной копии и backup
        ↓
перенос проверенного кода на Ubuntu
        ↓
проверка Apache/Caddy/БД и HTTP
        ↓
публикация на https://sreda.metasiberia.com/
```

### 7.1. Перед началом

1. Уточнить задачу, затронутые страницы, роли пользователей и ожидаемое поведение.
2. Проверить рабочий каталог и текущее состояние Git:

   ```powershell
   Set-Location -LiteralPath 'C:\programming\Metasiberia official\sreda.metasiberia'
   git status --short --branch
   ```

3. Не смешивать новую задачу с незавершёнными изменениями другой задачи.
4. Не читать и не включать в commit пароли, токены, cookies, runtime-конфигурации и пользовательские данные.

### 7.2. Локальная разработка и проверка

1. Внести минимальную правку только в необходимые исходные файлы.
2. Запустить Apache и MySQL в Laragon.
3. Проверить `http://localhost/` и все затронутые маршруты.
4. Проверить сценарии авторизации и роли, если правка их затрагивает.
5. Выполнить:

   ```powershell
   git diff --check
   git diff --stat
   git diff
   ```

6. Убедиться, что в diff нет случайных изменений, секретов, `ossn_data`, cache или локальных конфигураций.

### 7.3. Commit и подготовка релиза

1. Commit создаётся только после успешной локальной проверки и просмотра diff.
2. Commit должен содержать одну логически завершённую задачу и понятное сообщение.
3. Push в `origin` не является самим deployment и выполняется только отдельным решением.
4. Для публикации используется именно проверенный commit/состояние рабочего дерева, а не случайная копия каталога.

### 7.4. Публикация на Ubuntu

1. Перед переносом сохранить backup базы `sreda_ossn` и текущей серверной копии приложения. Команды приведены в разделе 9.
2. Подготовить временную серверную копию и проверить её до замены рабочей версии.
3. Переносить только нужный код. Не заменять серверные DB/site-конфигурации, пользовательские данные, cache и маркер `installation/INSTALLED`.
4. Проверить владельцев и права `/srv/sreda/app` и `/srv/sreda/data`.
5. Проверить конфигурации:

   ```bash
   sudo apachectl configtest
   sudo caddy validate --config /etc/caddy/Caddyfile
   ```

6. Применить конфигурацию штатной перезагрузкой соответствующего сервиса, если конфигурация менялась.
7. Проверить:

   ```bash
   systemctl is-active mysql apache2 caddy
   curl -k -I https://sreda.metasiberia.com/
   ```

8. После публикации проверить в браузере все изменённые сценарии на публичном адресе.
9. При ошибке остановить публикацию, сохранить логи и восстановить предыдущую серверную копию из backup. Не исправлять production экспериментами.

Важно: `Ctrl+F5` очищает только кэш браузера и не исправляет OSSN-кэш на сервере. При белом экране сначала проверить непустой HTTP-ответ, `plugins_paths`, `ossn.ru.json` и Apache error log. Не удалять каталог `/srv/sreda/data/system` вручную без последующего полного `DisableCache -> EnableCache` и проверки.

## 8. Правила обновления приложения

Перед обновлением:

1. Проверить `git status --short --branch` в локальном репозитории.
2. Сделать копию текущего приложения и базы.
3. Подготовить отдельную временную копию на сервере и проверить её до замены рабочей.
4. Не перезаписывать серверные runtime-конфигурации и пользовательские данные.

В runtime-копию кода нельзя переносить:

- `.git`;
- `configurations/ossn.config.db.php`;
- `configurations/ossn.config.site.php`;
- `ossn_data` или `/srv/sreda/data`;
- `cache` как источник логики;
- `installation/INSTALLED`.

У OSSN есть два независимых набора генерируемого кэша: `/srv/sreda/app/cache` и `/srv/sreda/data/system`. Во втором находятся `plugins_paths` и `locales/ossn.*.json`; отсутствие этих файлов при включённом cache может дать белый экран или старые переводы. Оба каталога должны быть доступны пользователю деплоя и группе `www-data`.

Кэш обновляется только полной парой команд и одним и тем же пользователем:

```bash
cd /srv/sreda/app
/usr/bin/php system/handlers/cli --handler=DisableCache
/usr/bin/php system/handlers/cli --handler=EnableCache
```

Нельзя считать обновление успешным только по сообщению `Cache has been enabled`. После него обязательны проверки:

```bash
test -s /srv/sreda/data/system/plugins_paths
test -s /srv/sreda/data/system/locales/ossn.ru.json
grep -Fq 'Муж' /srv/sreda/data/system/locales/ossn.ru.json
grep -Fq 'Жен' /srv/sreda/data/system/locales/ossn.ru.json
curl -ksS https://sreda.metasiberia.com/ | wc -c
```

Последняя команда должна вернуть ненулевой размер HTML. При нулевом размере, `HTTP 500`, белом экране или отсутствии любого обязательного файла deployment считается неуспешным.

Серверные конфигурации OSSN сохраняются отдельно. На сервере нужны `libraries.php`, `classes.php`, `ossn.config.dcache.php`, DB/site-конфигурации и данные установки; их нельзя случайно удалить при синхронизации.

Минимальная проверка после обновления:

```bash
sudo apachectl configtest
systemctl is-active apache2 caddy mysql
curl -k -I https://sreda.metasiberia.com/
```

## 9. Резервные копии

Перед изменением базы:

```bash
stamp=$(date +%Y%m%d-%H%M%S)
sudo mysqldump --single-transaction --routines --triggers sreda_ossn \
  | sudo tee "/srv/metasiberia/data/sreda-backups/sreda_ossn-${stamp}.sql" >/dev/null
```

Перед изменением файлов приложения:

```bash
stamp=$(date +%Y%m%d-%H%M%S)
sudo tar -C /srv/sreda -czf \
  "/srv/metasiberia/data/sreda-backups/sreda-app-${stamp}.tar.gz" app
```

Архивы содержат чувствительные данные и должны оставаться недоступными web-пользователю.

На текущем сервере нет отдельного `sreda`/`ossn` systemd-timer. Существующий `metasiberia-critical-backup.timer` относится к критическому состоянию основного сервера и не доказывает, что БД/данные SREDA входят в его набор. Автоматическое резервирование SREDA нельзя считать настроенным без отдельной проверки состава и retention.

## 10. Повторяемый deployment одной командой

Для обычного обновления PHP/CSS/JavaScript и других tracked-файлов приложения используется готовый workflow:

```text
локальная правка
    ↓
локальная проверка и браузерный тест
    ↓
git diff --check и просмотр diff
    ↓
commit в локальном репозитории
    ↓
Deploy-Sreda.ps1 -DryRun
    ↓
git push origin master (если commit нужно сохранить на GitHub)
    ↓
Deploy-Sreda.ps1 -ConfirmProduction
    ↓
backup приложения и БД на Ubuntu
    ↓
проверка SHA256 staged release
    ↓
безопасная синхронизация /srv/sreda/app
    ↓
refresh OSSN cache и smoke-test
    ↓
при ошибке — автоматический rollback файлов приложения
```

Скрипты находятся в `ops/`:

- `ops/Deploy-Sreda.ps1` — запускается на Windows из корня репозитория;
- `ops/sreda-deploy-remote.sh` — временный helper, загружаемый скриптом в server inbox и удаляемый после успешного запуска.

### 10.1. Обязательный порядок обновления

Каждое изменение SREDA проходит один и тот же порядок:

1. Внести правки и проверить их локально в браузере.
2. Проверить diff и создать commit:

```powershell
git diff --check
git add <изменённые-файлы>
git commit -m "краткое описание изменений"
```

3. Запустить безопасную локальную проверку release:

```powershell
cd 'C:\programming\Metasiberia official\sreda.metasiberia'
.\ops\Deploy-Sreda.ps1 -DryRun
```

4. Если dry-run завершился успешно, при необходимости сохранить commit в GitHub:

```powershell
git push origin master
```

5. После этого выполнить production deployment:

```powershell
.\ops\Deploy-Sreda.ps1 -ConfirmProduction
```

`-DryRun` создаёт release-архив и SHA256 manifest, но не подключается к серверу и ничего не изменяет. Реальный запуск разрешён только с явным `-ConfirmProduction` и только для `https://sreda.metasiberia.com/`.

`git push` и deployment — разные операции: push сохраняет commit на GitHub, а скрипт deployment обновляет production из текущего локального `HEAD`. Скрипт не делает commit и push автоматически и остановится, если рабочее дерево не чистое.

Во время реального запуска потребуется обычная SSH-аутентификация и пароль `sudo` на Ubuntu. Пароли, ключи и runtime-конфигурации в скрипты не записываются.

### 10.2. Что проверяет workflow

Перед загрузкой скрипт проверяет чистое Git-дерево, `git diff --check`, commit и PHP-файлы commit. Затем создаёт архив именно из `HEAD` и manifest SHA256 для tracked-файлов.

На Ubuntu helper:

1. сохраняет архив текущего `/srv/sreda/app` и SQL backup базы `sreda_ossn` в `/srv/metasiberia/data/sreda-backups`;
2. распаковывает staged release во временный каталог пользователя;
3. проверяет SHA256 каждого файла staged release;
4. синхронизирует код в `/srv/sreda/app`;
5. сохраняет без изменений `configurations/ossn.config.db.php`, `configurations/ossn.config.site.php`, `installation/INSTALLED`, `/srv/sreda/data`, cache, `docs` и `ops`;
6. восстанавливает права на оба набора генерируемого cache и выполняет штатный `DisableCache` → `EnableCache`;
7. проверяет `plugins_paths` и русскую локализацию (`Муж`/`Жен` без устаревших `Самец`/`Самка`);
8. проверяет `apache2`, `caddy`, `mysql`, непустой HTML главной страницы и `/login`;
9. если ошибка произошла после замены приложения, восстанавливает предыдущий архив приложения и повторно пересоздаёт OSSN cache.

Скрипт не меняет Caddy/Apache-конфигурацию, DNS, systemd, MySQL-схему или пользовательские данные. Перезапуск сервисов для обычного обновления кода не требуется; если отдельно меняется конфигурация сервера, это выполняется отдельной процедурой после проверки конфигурации.

### 10.3. Исключения

- `-KeepArtifacts` оставляет локальный архив и manifest для диагностики;
- `-SkipLint` допускается только если PHP CLI временно недоступен и PHP-файлы были проверены другим способом;
- при любой ошибке deployment не повторять вслепую: сначала сохранить вывод скрипта и проверить `FAILED_STAGE`, backup и серверные логи.

### 10.4. Стоп-условия и восстановление

Deployment останавливается и не выводит `DEPLOYMENT=success`, если:

- отсутствует или пуст `/srv/sreda/data/system/plugins_paths`;
- отсутствует или пуст `/srv/sreda/data/system/locales/ossn.ru.json`;
- в русской локализации остались `Самец` или `Самка`;
- главная страница или `/login` возвращает пустой HTML;
- сервисы `mysql`, `apache2` или `caddy` не active.

При белом экране не выполнять произвольные очистки и не менять production-код вручную. Сначала сохранить вывод:

```bash
curl -k -sS -D - https://sreda.metasiberia.com/ -o /tmp/sreda-response.html
wc -c /tmp/sreda-response.html
find /srv/sreda/data/system -maxdepth 2 -type f -ls
sudo tail -n 100 /var/log/apache2/sreda_error.log
```

Затем выполнить восстановление кэша тем же пользователем, который владеет runtime-каталогами, и повторить все проверки. Если проверка не проходит, deployment считать остановленным и использовать backup/rollback.

Этот workflow не выполняет `git commit` и `git push`; commit и push остаются отдельными действиями владельца репозитория.

## 11. Администрирование и безопасность

- админка: `https://sreda.metasiberia.com/administrator/`;
- неподтверждённые пользователи: `https://sreda.metasiberia.com/administrator/unvalidated_users`;
- не удалять `installation/INSTALLED` на работающем сайте;
- не открывать наружу MySQL и Apache `8081`;
- не публиковать runtime-конфигурации и секреты;
- не выполнять `git pull`, `commit` или `push` на сервере без отдельного решения.
