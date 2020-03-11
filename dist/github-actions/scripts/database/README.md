This directory contains a Dockerfile and an SQL script to install a database inside a
MariaDB image. It is based on the article
[Achieve Rocketship-Fast Jobs in CircleCI by Preinstalling the Database](https://www.lullabot.com/articles/rocket-ship-fast-jobs-circleci-preinstalling-database).

# Description

This Dockerfile creates and populates a database with the name drupal8.

# Building the image for the first time

## Dump the Drupal 8 database
Create a database dump of the database and save it to `scripts/database/dumps/drupal8.sql`.

## Authenticate, build, and push the image

This implementation uses a public Docker registry at Quay.io.

Here is how to build and push a new image for the first time:

```
cd scripts-database
docker login quay.io
docker build --tag quay.io/juampynr/drupal8-github-actions:latest
docker push quay.io/juampynr/drupal8-github-actions:latest
```

## Test the image

The image can be used by adding the following to a job definition:

```yaml
  services:
    mysql:
      image: quay.io/juampynr/drupal8-github-actions
```

You can test the image locally with the following commands:

```bash
docker pull quay.io/juampynr/drupal8-github-actions
docker run -d --name drupal8_github -p 3306:3306 quay.io/juampynr/drupal8-github-actions:latest
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
