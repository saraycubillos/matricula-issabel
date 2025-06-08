FROM debian:bullseye

ENV DEBIAN_FRONTEND=noninteractive

# Instala dependencias necesarias
RUN apt-get update && apt-get install -y \
    asterisk \
    mariadb-server \
    php php-mysqli \
    perl libwww-perl cpanminus build-essential libdbi-perl libdbd-mysql-perl \
    sox lame mpg123 \
    supervisor \
    curl vim net-tools iputils-ping \
    && apt-get clean

RUN cpanm Asterisk::AGI

# Crear directorios necesarios para Asterisk
RUN mkdir -p /var/lib/asterisk/agi-bin /etc/asterisk /var/log/asterisk /var/spool/asterisk

# Copiar archivos de configuración y scripts AGI
COPY matriculador/configuration/* /etc/asterisk/
COPY matriculador/php/* /usr/share/asterisk/agi-bin/
COPY matriculador/sql/* /docker-entrypoint-initdb.d/
COPY matriculador/agi/* /usr/share/asterisk/agi-bin/

COPY docker-entrypoint.sh /docker-entrypoint.sh

RUN chmod +x /docker-entrypoint.sh \
    && chmod +x /usr/share/asterisk/agi-bin/*.php \
    && chmod +x /usr/share/asterisk/agi-bin/*.agi

EXPOSE 5060/udp 5060/tcp 10000-20000/udp

ENTRYPOINT ["/docker-entrypoint.sh"]

