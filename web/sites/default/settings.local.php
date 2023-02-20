<?php

/**
 * Database settings
 */
$databases['default']['default'] = array (
  'database' => 'itm',
  'username' => 'itm',
  'password' => 'itm',
  'host' => 'mariadb',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

/**
 * Config directory.
 */
$settings['config_sync_directory'] = '../config/sync';
