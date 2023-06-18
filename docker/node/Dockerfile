FROM node:lts-alpine

ARG HOST_USER=1000
ARG HOST_USERGROUP=1000
ENV APP_ENV=dev

WORKDIR /home/docker

COPY ./docker-entrypoint.sh /usr/local/bin/

# taken from:
# https://github.com/fred-lab/Docker_Symfony/blob/master/docker/nodejs/docker-entrypoint.sh
# Si jamais tu tombe là-dessus, merci Fred ! ;)
RUN apk update \
    && chmod 777 /usr/local/bin/docker-entrypoint.sh \
    && echo -e '\033[0;31m' Change user permissions '\033[0m' \
    && cd /home/docker \
    && apk --no-cache add shadow && usermod -u $HOST_USER node && groupmod -g $HOST_USERGROUP node \
    && echo -e '\033[0;31m' Check Permissions for Workdir'\033[0m' $PWD \
    && ls -la \
    && apk del shadow \
    # Clean up apk cache folder and the virtuals folders
    && rm -rf /var/cache/apk/*

ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 5173
EXPOSE 4173

USER node

CMD ["yarn", "build"]

