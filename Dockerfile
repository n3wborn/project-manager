#syntax=docker/dockerfile:1.4

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target

################ APP CADDY BUILDER ################
FROM caddy:2.7-builder-alpine AS app_caddy_builder

RUN xcaddy build v2.7.2 \
	--with github.com/dunglas/mercure/caddy \
	--with github.com/dunglas/vulcain/caddy

############### APP PHP PROD ###############
FROM php:8.2-fpm-alpine AS app_php

ENV APP_ENV=prod

WORKDIR /srv/app

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
# hadolint ignore=DL3007
COPY --from=mlocati/php-extension-installer:latest --link /usr/bin/install-php-extensions /usr/local/bin/

# persistent / runtime deps
# hadolint ignore=DL3018
RUN apk add --no-cache acl fcgi file gettext git postgresql-dev;
RUN set -eux; install-php-extensions apcu intl opcache zip gd pgsql redis pdo_pgsql;

# copy config ini files
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# create php "run" dir (were php-fpm.sock is)
RUN mkdir -p /var/run/php

# copy/exec healthcheck
COPY --link docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck
HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

# copy/run entrypoint (that check for php/php-fpm/console commands)
COPY --link docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# set what entrypoint is supposed to execut
ENTRYPOINT ["docker-entrypoint"]

# start php-fpm
CMD ["php-fpm"]

# allow root user for composer (this need to be fixed !)
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# copy composer exec
COPY --from=composer/composer:2-bin --link /composer /usr/bin/composer

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* ./
RUN set -eux; \
    if [ -f composer.json ]; then \
		composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress; \
		composer clear-cache; \
    fi

# copy sources and cleanup
COPY --link  . ./
RUN rm -Rf docker/

#
RUN set -eux; \
	mkdir -p var/cache var/log; \
    if [ -f composer.json ]; then \
		composer dump-autoload --classmap-authoritative --no-dev; \
		if [ "$APP_ENV" = 'prod' ]; then \
			composer dump-env prod; \
		fi; \
		composer run-script --no-dev post-install-cmd; \
		chmod +x bin/console; sync; \
    fi


############################ APP PHP DEV ############################
FROM app_php AS app_php_dev

ENV APP_ENV=dev XDEBUG_MODE=off
ENV HOST_USER=${HOST_USER} HOST_USERGOUP=${HOST_USERGOUP}

VOLUME /srv/app/var/

RUN rm "$PHP_INI_DIR/conf.d/app.prod.ini"; \
	mv "$PHP_INI_DIR/php.ini" "$PHP_INI_DIR/php.ini-production"; \
	mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --link docker/php/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/


COPY --from=app_php --link /srv/app/bin .
RUN setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX bin
RUN setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX bin


RUN set -eux; install-php-extensions xdebug;
RUN rm -f .env.local.php

# Caddy image
FROM caddy:2-alpine AS app_caddy

WORKDIR /srv/app

COPY --from=app_caddy_builder --link /usr/bin/caddy /usr/bin/caddy
COPY --from=app_php --link /srv/app/public public/
COPY --link docker/caddy/Caddyfile /etc/caddy/Caddyfile
