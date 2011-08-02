<?php

require_once 'config.php';
require_once 'Api.class.php';
require_once 'Response.class.php';
require_once 'Cache.class.php';

// check auth token
$api = new Api();


$api->connect_db(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
$api->connect_users_cache($cache['users']);
$api->connect_rates_cache($cache['rates']);

// check that user is authorixed
$user = $api->authorize();

// handle request type
$result = $api->handle_request($user);

var_export($result);
exit;
Response::render($result['code'], $result['body']);

exit;

