FROM dunglas/frankenphp:1-php8.3

RUN install-php-extensions \
  apcu \
  gd \
  opcache \
  pdo_pgsql \
  zip

COPY . /opt/drupal

ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /opt/drupal
COPY Caddyfile /etc/caddy/Caddyfile

RUN set -eux; \
  chown -R www-data:www-data web/sites web/modules web/themes; \
  rm -rf /app/public; \
  ln -sf /opt/drupal/web /app/public;

ENV PATH=${PATH}:/opt/drupal/vendor/bin

RUN echo "max_execution_time = 300" >> /usr/local/etc/php/php.ini
