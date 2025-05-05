<?php

// phpcs:ignoreFile

$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

$config['system.mail']['interface']['default'] = 'devel_mail_log';
$settings['mail_log'] = '/tmp/mail.log';

$databases['default']['default'] = [
  'database' => 'drupal11',
  'username' => 'drupal11',
  'password' => 'drupal11',
  'host' => 'database',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
];

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
