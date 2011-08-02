<?php

define('DATABASE_HOST', 'localhost');
define('DATABASE_NAME', 'j0shua');
define('DATABASE_USER', 'j0shua');
define('DATABASE_PASSWORD', 'e9e4f7b4');

$cache['users'] = array(
    'driver' => 'memcache',
    'servers' => array(
        'host' => 'unix:///home/j0shua/memcahched.sock',
        'port' => 0,
        'persistent' => false,
    ),
);
