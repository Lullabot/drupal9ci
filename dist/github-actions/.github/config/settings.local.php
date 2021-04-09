<?php

/**
 * Local settings for GitHub Actions.
 */

$databases['default']['default'] = [
  'database' => 'drupal',
  'username' => 'root',
  'password' => 'root',
  'prefix' => '',
  'host' => 'mariadb',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

// Display errors.
$config['system.logging']['error_level'] = 'verbose';

if (empty($settings['hash_salt'])) {
  $settings['hash_salt'] = 'drupal-ci';
}
