<?php

class Api {

    public function __construct()
    {
        // connect to the db
        self::$db = '';

        // connect the caches
        self::$rates_cache = '';
        self::$users_cache = '';

    }

  
    public function authorize($token)
    {
        echo "authorixe called<br/>";
        return TRUE;

    }

    public function get_exchange_rate($currency_code)
    {
        // look up in cache or db
        echo "get_exchange_rate called<br/>";

        return 45;
    }

    public function check_speed($user)
    {
    
        echo "check_speed called<br/>";
        return TRUE;
    }

    public function handle_request($user, $request)
    {
        echo "handle_request called<br/>";
        // validate that this is a legit method
        return 'hi josh';

    }

}

