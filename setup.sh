#!/usr/bin/env bash

wget https://github.com/juampynr/drupal8ci/archive/master.zip
unzip master.zip 'drupal8ci/dist/*'
rsync -vaz drupal8ci/dist/* .
rm master.zip
