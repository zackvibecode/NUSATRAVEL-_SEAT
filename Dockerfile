FROM php:8.3-apache-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql pgsql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN composer dump-autoload --optimize \
    && test -f public/build/manifest.json \
    && mkdir -p storage/framework/{sessions,views,cache/data} storage/logs \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R ug+rwX storage bootstrap/cache database \
    && chmod +x docker/start.sh \
    && a2enmod rewrite headers \
    && sed -i 's|^Listen 80$|Listen ${PORT}|' /etc/apache2/ports.conf \
    && sed -i 's|/var/www/html|/app/public|' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|:80>|:${PORT}>|' /etc/apache2/sites-available/000-default.conf \
    && printf 'ServerName localhost\n' >> /etc/apache2/apache2.conf \
    && printf '<Directory /app/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

ENV PORT=10000
EXPOSE 10000

CMD ["docker/start.sh"]
