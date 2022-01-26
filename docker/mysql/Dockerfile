FROM mysql:5.7

LABEL maintainer="Victoria Etim <victoria@initsng.com>"

COPY docker-entrypoint-initdb.d/createdb.sql /docker-entrypoint-initdb.d/createdb.sql

CMD ["mysqld"]

EXPOSE 3306
