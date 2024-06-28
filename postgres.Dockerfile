FROM postgres

COPY config/postgres/backup/export.sql /docker-entrypoint-initdb.d/export.sql
