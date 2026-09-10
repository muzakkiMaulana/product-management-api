#!/bin/sh
set -eu

case "$MYSQL_USER" in
    ''|*[!a-zA-Z0-9_]*) echo 'MYSQL_USER must contain only letters, numbers, or underscores.' >&2; exit 1 ;;
esac

MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql --protocol=socket --user=root <<SQL
CREATE DATABASE IF NOT EXISTS product_management_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON product_management_test.* TO '$MYSQL_USER'@'%';
SQL
