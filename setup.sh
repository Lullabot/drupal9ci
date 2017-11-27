#!/usr/bin/env bash

wget https://github.com/juampynr/drupal8ci/archive/master.zip
unzip master.zip 'drupal8ci-master/dist/*'
rsync -vaz drupal8ci-master/dist/ .
rm master.zip
