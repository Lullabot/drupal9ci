#!/usr/bin/env bash

# Download and extract CircleCI configuration and sample tests.
wget https://github.com/juampynr/drupal8ci/archive/master.zip
unzip master.zip 'drupal8ci-master/dist/*'
rsync -vaz drupal8ci-master/dist/ .
rm master.zip
rm -rf drupal8ci-master

# Add development dependencies to run the CircleCI jobs.
#
# behat/mink-extension is pinned until https://github.com/Behat/MinkExtension/pull/311 gets fixed.
composer require --dev \
    cweagans/composer-patches \
    behat/mink-extension:v2.2 \
    behat/mink-selenium2-driver:^1.3 \
    bex/behat-screenshot \
    drupal/coder:^8.2 \
    drupal/drupal-extension:master-dev \
    drush/drush:~8.1 \
    guzzlehttp/guzzle:^6.0@dev
