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

## 10. Администрирование и безопасность

- админка: `https://sreda.metasiberia.com/administrator/`;
- неподтверждённые пользователи: `https://sreda.metasiberia.com/administrator/unvalidated_users`;
- не удалять `installation/INSTALLED` на работающем сайте;
- не открывать наружу MySQL и Apache `8081`;
- не публиковать runtime-конфигурации и секреты;
- не выполнять `git pull`, `commit` или `push` на сервере без отдельного решения.
