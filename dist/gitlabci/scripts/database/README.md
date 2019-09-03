This directory contains a Dockerfile and an SQL script to install a database inside a
MariaDB image. It is based on the article
[Achieve Rocketship-Fast Jobs in CircleCI by Preinstalling the Database](https://www.lullabot.com/articles/rocket-ship-fast-jobs-circleci-preinstalling-database).

# Description

This Dockerfile creates and populates a database with the name drupal8.

# Building the image for the first time

## Dump the Drupal 8 database
Create a database dump of the database and save it to `scripts/database/dumps/drupal8.sql`.

## Authenticate, build, and push the image

GitLab projects can host Docker images via their Container Registry. For example, this
project's database image lives at https://gitlab.com/juampynr/drupal8-gitlab/container_registry.

Here is how to build and push a new image for the first time:

```
cd scripts-database
docker login registry.gitlab.com
docker build --tag registry.gitlab.com/juampynr/drupal8-gitlab:master
docker push registry.gitlab.com/juampynr/drupal8-gitlab:master
```

## Test the image

The image can be used by GitLab CI jobs by adding the following to a job definition:

```yaml
  services:
      - registry.gitlab.com/juampynr/drupal8-gitlab:latest
```

You can test the image locally with the following commands:

```bash
docker pull registry.gitlab.com/juampynr/drupal8-gitlab:master
docker run -d --name drupal8_gitlab -p 3306:3306 registry.gitlab.com/juampynr/drupal8-gitlab:master
mysql -h127.0.0.1 -uroot -p -e 'show databases;'
Enter password:
+--------------------+
| Database           |
+--------------------+
| drupal8            |
| information_schema |
| mysql              |
| performance_schema |
+--------------------+
```
