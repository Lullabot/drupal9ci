<?php

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'root',
  'password' => '',
  'prefix' => '',
  'host' => '127.0.0.1',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

// Display errors.
$config['system.logging']['error_level'] = 'verbose';

if (empty($settings['hash_salt'])) {
  $settings['hash_salt'] = 'drupal-ci';
}
