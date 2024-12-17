FROM php:8.4-cli-alpine

ENV SRC_EXT_DIR=/usr/lib/php84/modules
ENV DST_EXT_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20240924

ENV SRC_INI_DIR=/etc/php84/conf.d
ENV DST_INI_DIR=/usr/local/etc/php/conf.d

RUN apk update
RUN apk add composer php84-pdo_mysql php84-pecl-xdebug
RUN composer self-update
RUN ln -s $SRC_EXT_DIR/pdo_mysql.so $DST_EXT_DIR/pdo_mysql.so
RUN ln -s $SRC_EXT_DIR/xdebug.so $DST_EXT_DIR/xdebug.so
RUN cp $SRC_INI_DIR/02_pdo_mysql.ini $DST_INI_DIR/02_pdo_mysql.ini
COPY xdebug.ini $DST_INI_DIR/50_xdebug.ini
