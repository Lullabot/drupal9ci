<?php

/**
 * Local settings for GitLab CI jobs.
 */

$databases['default']['default'] = [
  'database' => 'drupal8',
  'username' => 'root',
  'password' => 'root',
  'prefix' => '',
  'host' => 'registry.gitlab.com__juampynr__drupal8-gitlab',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

// Display errors.
$config['system.logging']['error_level'] = 'verbose';
