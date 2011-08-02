<?php

define('DATABASE_HOST', 'localhost');
define('DATABASE_NAME', 'j0shua');
define('DATABASE_USER', 'j0shua');
define('DATABASE_PASSWORD', 'e9e4f7b4');

$cache['users'] = array(
    'driver' => 'memcache',
    // users cache should be 60 seconds since that is the time amount for throttling
    'ttl' => 60, 
    'servers' => array(
        'host' => 'unix:///home/j0shua/memcached.sock',
        'port' => 0,
        'persistent' => false,
    ),
);

$cache['rates'] = array(
    'driver' => 'memcache',
    // should probably be once a day or however often we grab the new conversion rates
    'ttl' => 86400, 
    'servers' => array(
        'host' => 'unix:///home/j0shua/memcached.sock',
        'port' => 0,
        'persistent' => false,
    ),
);
