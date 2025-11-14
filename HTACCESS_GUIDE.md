# Руководство по .htaccess для StormHosting

## Обзор

Корневой `.htaccess` файл настроен для:
- 🔒 Скрытия расширений `.php`
- 🌐 Красивых URL без расширений
- 🚀 Оптимизации производительности
- 🔐 Повышения безопасности
- 📦 Кеширования статических ресурсов

---

## Как работают URL

### До (со старым .htaccess):
```
https://sthost.pro/pages/hosting/domains.php
https://sthost.pro/pages/tools/site-check.php
https://sthost.pro/pages/contacts.php
```

### После (с новым .htaccess):
```
https://sthost.pro/hosting/domains
https://sthost.pro/tools/site-check
https://sthost.pro/contacts
```

**Оба варианта работают!** Но при обращении к `.php` файлу напрямую произойдет редирект 301 на URL без расширения.

---

## Примеры URL маршрутизации

### Одноуровневые маршруты:
```
/contacts           → /pages/contacts.php
/about              → /pages/about.php
/pricing            → /pages/pricing.php
```

### Двухуровневые маршруты:
```
/hosting/vps        → /pages/hosting/vps.php
/hosting/cloud      → /pages/hosting/cloud.php
/hosting/domains    → /pages/hosting/domains.php
/tools/site-check   → /pages/tools/site-check.php
/tools/ip-check     → /pages/tools/ip-check.php
/domains/whois      → /pages/domains/whois.php
/domains/dns        → /pages/domains/dns.php
```

### Трехуровневые маршруты:
```
/hosting/domains/transfer  → /pages/hosting/domains/transfer.php
/domains/manage/dns        → /pages/domains/manage/dns.php
```

---

## Исключения

Следующие пути **НЕ** обрабатываются rewrite правилами:

### 1. Реальные файлы и директории
Если файл или директория существует физически, он отдается как есть.

### 2. API endpoints
```
/v1/site-check      → /v1/site-check.php (свой .htaccess)
/v1/ip-check        → /v1/ip-check.php (свой .htaccess)
```

### 3. WHMCS billing
```
/billing/*          → Без изменений (WHMCS маршрутизация)
```

### 4. Статические ресурсы
```
/assets/*           → CSS, JS, изображения без изменений
/uploads/*          → Загруженные файлы без изменений
```

---

## Безопасность

### Защищенные файлы и директории:

❌ **Запрещен доступ к:**
- `.htaccess`, `.htpasswd`
- `.git`, `.env`
- `composer.json`, `composer.lock`
- `package.json`, `package-lock.json`
- `README.md`, `CHANGELOG.md`, `IMPLEMENTATION_GUIDE.md`
- Backup файлы: `.bak`, `.backup`, `.old`, `.tmp`, `.sql`, `.log`
- Config файлы: `config.php`, `db_connect.php` (если в корне)

### Security Headers:

Включены следующие заголовки безопасности:
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` (ограничение доступа к API браузера)

### HTTPS Redirect:

Все HTTP запросы автоматически перенаправляются на HTTPS:
```
http://sthost.pro/hosting/vps → https://sthost.pro/hosting/vps
```

### WWW Redirect:

Убирается `www` из URL:
```
https://www.sthost.pro/hosting/vps → https://sthost.pro/hosting/vps
```

**Если нужно добавить www**, раскомментируйте строки 25-27 в `.htaccess`.

---

## Производительность

### Сжатие (Gzip)

Включено сжатие для:
- HTML, CSS, JavaScript
- JSON, XML
- SVG, шрифты (TTF, OTF, WOFF, WOFF2)

**Экономия трафика:** до 70-80%

### Кеширование в браузере

Настроено кеширование по типам файлов:

| Тип файла | Время кеширования |
|-----------|-------------------|
| HTML/PHP  | Не кешируется (0s) |
| CSS/JS    | 1 год |
| Изображения | 1 год |
| Шрифты | 1 год |
| PDF | 1 месяц |
| JSON/XML | Не кешируется |

### Cache-Control заголовки:

```apache
CSS/JS:        Cache-Control: public, max-age=31536000, immutable
Изображения:   Cache-Control: public, max-age=31536000
HTML:          Cache-Control: no-cache, no-store, must-revalidate
```

**Важно:** CSS и JS файлы используют версионирование (`?v=timestamp`), поэтому кешируются на 1 год.

---

## Кастомные страницы ошибок

Все HTTP ошибки обрабатываются единой страницей `/error.php`:

| Код | Описание |
|-----|----------|
| 400 | Bad Request - Неправильный запрос |
| 401 | Unauthorized - Требуется авторизация |
| 403 | Forbidden - Доступ запрещен |
| 404 | Not Found - Страница не найдена |
| 500 | Internal Server Error - Внутренняя ошибка сервера |
| 502 | Bad Gateway - Ошибка шлюза |
| 503 | Service Unavailable - Сервис недоступен |

Страница `error.php` автоматически:
- Устанавливает правильный HTTP код
- Показывает пользователю понятное сообщение
- Предлагает вернуться назад или на главную
- Для 404 показывает полезные ссылки

---

## Настройка PHP

Через `.htaccess` установлены следующие лимиты PHP:

```apache
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
expose_php = Off
```

**Если нужно изменить**, отредактируйте строки 169-177 в `.htaccess`.

---

## Обновление ссылок на сайте

### Где нужно изменить ссылки:

#### 1. Внутренние ссылки в HTML/PHP:

**Было:**
```html
<a href="/pages/hosting/vps.php">VPS Хостинг</a>
```

**Стало:**
```html
<a href="/hosting/vps">VPS Хостинг</a>
```

#### 2. Меню навигации:

Обновить файл `/includes/header.php` (если там жестко прописаны ссылки):

```php
<!-- Старый вариант -->
<a href="/pages/hosting/vps.php">VPS</a>

<!-- Новый вариант -->
<a href="/hosting/vps">VPS</a>
```

#### 3. JavaScript редиректы:

```javascript
// Старый
window.location.href = '/pages/contacts.php';

// Новый
window.location.href = '/contacts';
```

#### 4. WHMCS интеграция:

В калькуляторе и формах заказа:
```javascript
// Старый
window.location.href = '/billing/cart.php?a=add&pid=cloud';

// Новый (без изменений, billing не трогается)
window.location.href = '/billing/cart.php?a=add&pid=cloud';
```

---

## Тестирование

### 1. Проверка редиректов:

```bash
# Должен редиректить на версию без .php
curl -I https://sthost.pro/pages/hosting/vps.php

# Должен вернуть 200 OK
curl -I https://sthost.pro/hosting/vps
```

### 2. Проверка статических файлов:

```bash
# Должен вернуть 200 OK без редиректа
curl -I https://sthost.pro/assets/css/main.css

# API должен работать
curl -X POST https://sthost.pro/v1/ip-check -H "Content-Type: application/json" -d '{"ip":"8.8.8.8"}'
```

### 3. Проверка защиты:

```bash
# Должен вернуть 403 Forbidden
curl -I https://sthost.pro/.env
curl -I https://sthost.pro/.git/config
curl -I https://sthost.pro/composer.json
```

### 4. Проверка кеширования:

```bash
# Должен вернуть Cache-Control заголовок
curl -I https://sthost.pro/assets/css/main.css
# Cache-Control: public, max-age=31536000, immutable

curl -I https://sthost.pro/hosting/vps
# Cache-Control: no-cache, no-store, must-revalidate
```

---

## Отладка

### Если страница не открывается (500 Error):

1. **Проверить логи Apache:**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

2. **Проверить синтаксис .htaccess:**
   ```bash
   apachectl configtest
   ```

3. **Убедиться что mod_rewrite включен:**
   ```bash
   a2enmod rewrite
   systemctl restart apache2
   ```

4. **Проверить AllowOverride в Apache конфиге:**
   ```apache
   <Directory /var/www/html>
       AllowOverride All  # Должно быть All, а не None
   </Directory>
   ```

### Если редиректы не работают:

1. **Очистить кеш браузера** (Ctrl + F5)

2. **Проверить права доступа:**
   ```bash
   chmod 644 .htaccess
   ```

3. **Временно отключить часть правил:**
   Закомментируйте блоки по очереди и найдите проблемный.

### Если CSS/JS не обновляются:

Это НЕ проблема .htaccess, а проблема HAProxy/Memcached кеша.

**Решение:**
```bash
# Очистить Memcached
echo "flush_all" | nc localhost 11211

# Перезагрузить HAProxy
systemctl reload haproxy
```

---

## Дополнительные настройки

### 1. Включить HSTS (после тестирования):

Раскомментировать строку 146 в `.htaccess`:
```apache
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

**Внимание:** После включения HSTS браузеры будут ПРИНУДИТЕЛЬНО использовать HTTPS в течение 1 года.

### 2. Защита от hotlinking изображений:

Раскомментировать строки 272-277 в `.htaccess`:
```apache
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^https?://(.+\.)?sthost\.pro [NC]
RewriteCond %{REQUEST_URI} \.(jpg|jpeg|png|gif|webp)$ [NC]
RewriteRule .* - [F,L]
```

Это запретит встраивание ваших изображений на других сайтах.

### 3. Content Security Policy (CSP):

Раскомментировать строку 141 в `.htaccess` и настроить под свои нужды:
```apache
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; ..."
```

**Важно:** Тщательно тестируйте CSP, так как он может сломать функциональность сайта.

---

## Совместимость с HAProxy

### Если .htaccess не применяется:

HAProxy может кешировать страницы. Добавить в HAProxy конфиг:

```haproxy
frontend http_front
    # Пропускать .htaccess правила через backend
    option forwardfor

    # Не кешировать HTML страницы
    acl is_html path_end .php .html
    acl is_dynamic url_reg ^/(hosting|tools|domains|contacts)
    http-request set-header Cache-Control "no-cache" if is_html
    http-request set-header Cache-Control "no-cache" if is_dynamic
```

---

## Миграция со старых URL

Если на сайте уже были страницы с `.php`, можно добавить редиректы:

```apache
# Добавить в конец .htaccess
<IfModule mod_rewrite.c>
    # Специфические редиректы для популярных страниц
    RewriteRule ^old-vps\.php$ /hosting/vps [R=301,L]
    RewriteRule ^contact-us\.php$ /contacts [R=301,L]
</IfModule>
```

---

## Производительность Apache

### Рекомендуемые модули Apache:

```bash
# Включить необходимые модули
a2enmod rewrite
a2enmod headers
a2enmod expires
a2enmod deflate
a2enmod mime
a2enmod ssl

# Перезапустить Apache
systemctl restart apache2
```

### Рекомендуемые настройки в apache2.conf:

```apache
# Оптимизация производительности
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5

# Включить сжатие
DeflateCompressionLevel 6

# Оптимизация для worker MPM
<IfModule mpm_worker_module>
    StartServers             4
    MinSpareThreads         25
    MaxSpareThreads         75
    ThreadLimit             64
    ThreadsPerChild         25
    MaxRequestWorkers      150
    MaxConnectionsPerChild   0
</IfModule>
```

---

## Чеклист после установки .htaccess

- [ ] Протестировать главную страницу
- [ ] Проверить все основные разделы (hosting/vps, tools/site-check и т.д.)
- [ ] Проверить API endpoints (/v1/site-check, /v1/ip-check)
- [ ] Проверить WHMCS billing (/billing/*)
- [ ] Проверить загрузку CSS/JS (/assets/*)
- [ ] Проверить редиректы HTTP → HTTPS
- [ ] Проверить редирект .php → без .php
- [ ] Проверить страницы ошибок (404, 500)
- [ ] Проверить защиту файлов (.git, .env)
- [ ] Очистить кеш (браузер, HAProxy, Memcached)
- [ ] Проверить работу сайта на мобильных устройствах
- [ ] Проверить скорость загрузки (PageSpeed Insights)

---

## Полезные команды

```bash
# Проверить синтаксис .htaccess
apachectl configtest

# Перезапустить Apache
systemctl restart apache2

# Перезагрузить Apache (без разрыва соединений)
systemctl reload apache2

# Посмотреть логи ошибок Apache
tail -f /var/log/apache2/error.log

# Посмотреть логи доступа Apache
tail -f /var/log/apache2/access.log

# Проверить включенные модули Apache
apache2ctl -M

# Очистить кеш Memcached
echo "flush_all" | nc localhost 11211

# Перезагрузить HAProxy
systemctl reload haproxy
```

---

## Поддержка

При возникновении проблем:
1. Проверьте логи Apache
2. Проверьте этот документ
3. Временно отключите .htaccess и проверьте работу сайта
4. Обратитесь к документации Apache mod_rewrite

---

**Дата создания:** 2024-11-14
**Версия:** 1.0
**Автор:** Claude AI Assistant
