# This image is used to copy and install the databases.
FROM ubuntu:18.04 AS build

RUN apt-get update \
  && apt-get install mariadb-server -y

COPY setup.sql /tmp/
COPY dumps/drupal8.sql /tmp/dumps/drupal8.sql

RUN service mysql start && \
  mysql < /tmp/setup.sql && \
  sleep 2 && \
  service mysql stop

# This image is used to copy the installed databases and configure MySQL.
FROM ubuntu:18.04

RUN apt-get update \
  && apt-get install mariadb-server -y

COPY --from=build --chown=mysql:mysql /var/lib/mysql /var/lib/mysql

RUN find /etc/mysql/ -name '*.cnf' -print0 \
    | xargs -0 grep -lZE '^(bind-address|log)' \
    | xargs -rt -0 sed -Ei 's/^(bind-address|log)/#&/'; \
    echo '[mysqld]\nskip-host-cache\nskip-name-resolve' > /etc/mysql/conf.d/docker.cnf

# Clear the mysql socket
RUN service mysql start && \
  sleep 2 && \
  service mysql stop

EXPOSE 3306
CMD ["mysqld"]
