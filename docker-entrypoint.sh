#!/bin/bash
set -e

echo "[INFO] Preparando entorno para MariaDB..."
mkdir -p /run/mysqld
chown -R mysql:mysql /run/mysqld

echo "[INFO] Iniciando servidor MariaDB..."
su mysql -s /bin/bash -c "mariadbd --skip-networking" &
MYSQLD_PID=$!

echo "[INFO] Esperando a que MariaDB esté disponible..."
for i in {1..30}; do
    if mysqladmin ping --silent; then break; fi
    sleep 1
done

if ! mysqladmin ping --silent; then
    echo "[ERROR] MariaDB no respondió a tiempo" >&2
    exit 1
fi

echo "[INFO] Ejecutando scripts de inicialización SQL..."
shopt -s nullglob
for f in /docker-entrypoint-initdb.d/*.sql; do
    echo "Ejecutando $f..."
    mysql -u root < "$f"
done
shopt -u nullglob

echo "[INFO] Detectando IP pública para externip..."
PUBLIC_IP=$(curl -s https://api.ipify.org)
if [[ -z "$PUBLIC_IP" ]]; then
    echo "[ERROR] No se pudo obtener la IP pública." >&2
    exit 1
fi
echo "[INFO] IP pública detectada: $PUBLIC_IP"

echo "[INFO] Actualizando externip en sip.conf..."
sed -i "s/__EXTERN_IP__/${PUBLIC_IP}/g" /etc/asterisk/sip.conf

echo "[INFO] Añadiendo extensiones personalizadas a extensions.conf..."
if ! grep -q "include extensions_custom.conf" /etc/asterisk/extensions.conf 2>/dev/null; then
    echo "#include extensions_custom.conf" >> /etc/asterisk/extensions.conf
fi

echo "[INFO] Arrancando Asterisk en primer plano..."
exec asterisk -f -U asterisk -G asterisk -vvv

