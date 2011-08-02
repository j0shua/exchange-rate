<?php

require_once('api.class.php');
require_once('response.class.php');

// check auth token
$api = new Api;

    
ob_start();

try 
{
    // check that user is authorixed
    $user = $api->authorize($token);

    // check for speeding (use memcache NOT db)
    $api->check_speed($user);

    // handle request type
    $response = $api->handle_request($user, $request);

    Response::render($response);

} 
catch (Exception $e) 
{

    // go through determining the exception

    // prepare response
    $response = '';

    // render response
    Response::render($response);
}

exit;


/*
exchange_rate

id
currency char
conversion_rate
as_of datetime
deleted tinyint(1)
last_modified


url:
method=get_rate
currency=[JPY, EUR,...]  explode
day=blahblah


CREATE TABLE `exchange_rate` (
  id int(11) auto increment,
  `currrency` CHAR(3) NOT NULL,
  conversion_rate DECIMAL(12,6),
  as_of datetime on UPDATE CURRENT_TIMESTAMP ??,
  deleted tinyint(1) default 0,
  last_modified timestamp on insert current_timestamp,
  PRIMARY KEY `id`:
  );

*/

