FROM debian:bullseye

ENV DEBIAN_FRONTEND=noninteractive

# Instala dependencias necesarias
RUN apt-get update && apt-get install -y \
    asterisk \
    mariadb-server \
    php php-mysqli \
    sox lame mpg123 \
    supervisor \
    curl vim net-tools iputils-ping \
    && apt-get clean

# Crear directorios necesarios para Asterisk
RUN mkdir -p /var/lib/asterisk/agi-bin /etc/asterisk /var/log/asterisk /var/spool/asterisk

# Copiar archivos de configuración y scripts AGI
COPY matriculador/extensions_custom.conf /etc/asterisk/extensions_custom.conf
COPY matriculador/matriculaAGI.php /var/lib/asterisk/agi-bin/matriculaAGI.php
COPY matriculador/googletts.agi /var/lib/asterisk/agi-bin/googletts.agi
COPY matriculador/definiciones.inc /var/lib/asterisk/agi-bin/definiciones.inc
COPY matriculador/estructura.sql /docker-entrypoint-initdb.d/estructura.sql
COPY matriculador/privilegios.sql /docker-entrypoint-initdb.d/privilegios.sql
COPY matriculador/index.php /var/www/html/index.php
COPY matriculador/procesar_matricula.php /var/www/html/procesar_matricula.php
COPY matriculador/verificar_usuario.php /var/www/html/verificar_usuario.php

# Copiar script de entrada
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Dar permisos de ejecución a scripts
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /var/lib/asterisk/agi-bin/*.php \
    && chmod +x /var/lib/asterisk/agi-bin/*.agi

# Exponer puertos para SIP y RTP
EXPOSE 5060/udp 5060/tcp 10000-20000/udp

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
