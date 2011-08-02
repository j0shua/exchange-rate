<?php

class Api {

    protected $db;
    protected $users_cache;
    protected $rates_cache;
    protected $response;

/*
    // should probably do the initialization od db + caches here
    public function __construct()
    {
    }
*/

    public function connect_db($host, $user, $password, $dbname)
    {
        
        if (!$this->db = mysql_connect($host, $user, $password))
        {
            die('Could not connect: ' . mysql_error());
        }

        if (!mysql_select_db($dbname, $this->db))
        {
            die ("Can't use i$dbname : " . mysql_error());
        }
        return TRUE;
    }

    public function connect_users_cache($config)
    {
        $this->users_cache = new Cache($config);
    }

    public function connect_rates_cache($config)
    {
        $this->rates_cache = new Cache($config);
    }

    public function check_speed($user)
    {

        $time = time();
        $data = $this->users_cache->get($user);
        if (!$data)
        {
            $data = array();
        }
        else 
        {
            $data = unserialize($data);
            $num_requests = count($data);

            // todo this should use the config not a magic # here
            $time_threshhold = 60;           //   one minute
            $throttle_limit = 100;      // number of requests

            // we're under limit good just add the new request else ...
            // check if we are really above limit or we just need to purge
            if ($num_requests >=  $throttle_limit)
            {
                $data = $this->purge($data, $time, $time_threshold);
                $this->users_cache->set($user, serialize($data));

                // still over count ? bounce him
                if (count($data) >= $throttle_limit)
                {
                    $data[] = $time;
                    $this->users_cache->set($user, serialize($data));
                    die(Response::render(401, 'You have reached the limit for requests'));
                }
            }
        }
        
        // maybe we need microtime if the request come very fast

        // add the new timestamp
        $data[] = time();
        $this->users_cache->set($user, serialize($data));

        return TRUE;
    }

    public function purge($data, $time, $threshold)
    {
        $cutoff = $time - $threshold;

        foreach ($data as $k => $timestamp)
        {
            // was it too long ago ? then get rid of it
            if ($timestamp < $cutoff)
            {
                unset($data[$k]);
            }
            // they are sorted so we can short circuit
            else
            {
                break;
            }

        }

        return $data;
    }

    // log every request for diagnostics etc
    public function log_request($user, $request)
    {
        $query = sprintf("INSERT INTO requests (`user`, `request`)
            VALUES ('%s', '%s')", $user, serialize($request));
        $result = mysql_query($query, $this->db);

        return $result;

    }


    public function handle_request($user)
    {
        
        $this->check_speed($user);

        // would prob log all this in the db:
        //$this->log_request($user, $request);

        $request_method = strtolower($_SERVER['REQUEST_METHOD']);

        switch ($request_method)
        {
            case 'get':
                $data = $_GET;
                break;
            default:
                //throw new Exception('Unsupported Method');
                Response::render(405);
        }
        if (empty($data['currency']))
        {
            Response::render(400, 'You must specify at least one currency, or a coma seperated list of currencies.');
        }

        $currencies = explode(',', $data['currency']);

        foreach ($currencies as $idx => $currency)
        {
            // look up code in cache 
            
            // fall back to db
            $as_of = null;
            if (!empty($data['as_of']))
            {
                $as_of = $data['as_of'];
                $rate = $this->get_rate_from_db($code);
            }

            $rate = $this->get_conversion_rate($currency, $as_of);
            
            // append to response
            $result .= json_encode(array(
                'currency' => $rate['currency'],
                'conversion_rate' => $rate['conversion_rate'],
                'as_of' => $rate['as_of'],
                'error' => $rate['error'],
                ));
        }

        return array('code' => 200, 'body' => $result);
    }

    public function get_conversion_rate($code, $as_of = null)
    {

        $defaults = array(
            'conversion_rate' => 0,
            'currency' => $code,
            'error' => '',
            'as_of' => time()
            );
  
        // look up in cache 
        if ($rate = $this->rates_cache->get($code))
        {
            return $rate;
        }
        // fall back on db
        else 
        {
            $rate = $this->get_rate_from_db($code);
            if ($rate)
            {
                // store rate in db
                $this->rates_cache->set($code, $rate);
                
                return array_merge($rate);
            }
            // not in db
            else 
            {
                return array_merge($defaults, array('error' => "currency $code not valid"));
            }
        }
    }

    public function get_rate_from_db($code)
    {
        $query = sprintf("SELECT * FROM exchange_rate 
            WHERE 
                currency='%s' 
                AND deleted = 0 
            ORDER BY as_of DESC 
            LIMIT 1", mysql_real_escape_string($code));
        // echo "$query";
        $result = mysql_query($query, $this->db);
        
        if (!$result)
        {
            die("Could not successfully run query ($sql) from DB: " . mysql_error());
        }

        if (mysql_num_rows($result) == 0) 
        {
            return FALSE;
        }
        $assoc = mysql_fetch_assoc($result);
        return $assoc; 
    }

    // copied from php.net
    public function authorize()
    {
        return 'josh';

        // auth simple 
        $realm = $_SERVER['SERVER_NAME'];

        if (!isset($_SERVER['PHP_AUTH_USER']))
        {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            die(Response::render(401, 'Text to send if user hits Cancel button'));
        } 
        else  
        {
            $message =  "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
            $message .=  "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
            die(Response::render(200, $message));
        }
        return TRUE;


        // if using digest auth:
        $realm = "Restricted area";

        //user => password
        $users = array('admin' => 'mypass', 'guest' => 'guest');

        if (empty($_SERVER['PHP_AUTH_DIGEST'])) 
        {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$realm.
                   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

            // show message if user hits Cancel button
            die(Response::render(401));
        }


        // analyze the PHP_AUTH_DIGEST variable
        if (!($data = $this->http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
            !isset($users[$data['username']]))
        {
            // bad auth
            die(Response::render(401));
        }


        // generate the valid response
        $A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
        $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
        $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

        if ($data['response'] != $valid_response)
        {
            die(Response::render(401));
        }

        // ok, valid username & password
        //return TRUE;
        return $data['username'];
    }

    // function to parse the http auth header
    public function http_digest_parse($txt)
    {
        // protect against missing data
        $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) 
        {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }
}
