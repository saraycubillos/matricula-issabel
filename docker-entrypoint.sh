#!/bin/#!/bin/bash
set -e

echo "[INFO] Iniciando servicio MariaDB..."
service mysql start

echo "[INFO] Esperando a que MariaDB esté disponible..."
until mysqladmin ping --silent; do
    sleep 1
done

echo "[INFO] Ejecutando scripts de inicialización SQL..."
for f in /docker-entrypoint-initdb.d/*.sql; do
    echo "Ejecutando $f..."
    mysql -u root < "$f"
done

echo "[INFO] Añadiendo extensiones personalizadas a extensions.conf..."
if ! grep -q "include extensions_custom.conf" /etc/asterisk/extensions.conf 2>/dev/null; then
    echo "#include extensions_custom.conf" >> /etc/asterisk/extensions.conf
fi

echo "[INFO] Arrancando Asterisk en primer plano..."
asterisk -f -U asterisk -G asterisk -vvv
