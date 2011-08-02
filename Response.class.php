<?php

class Response {

    protected static $body;

    public static function add_response($data)
    {
        $this->body .= $data;
    }
    
    public static function get_status_code_message($status)
    {
        // can lookup the rest on wikipedia
        $codes = array(
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            301 => 'Moved Permanently',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    public static function render($status = 200, $body = '', $content_type = 'text/html')
    {
        $status_header = 'HTTP/1.1 ' . $status . ' ' . Response::get_status_code_message($status);

        // set the status
        header($status_header);

        // set the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if($body != '')
        {
            // send the body
            if (self::$body)
            {
                echo $this->body;
            }

            echo $body;
            exit;
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = '';

            switch($status)
            {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }


            $status_message = Response::get_status_code_message($status);

            // really should use template
            $body =<<<EOF
<html>
    <head>
        <title>$status :: $message </title> 
    </head>
    <body>
        <h1>$status_message</h1>
        <p>$message</p>
    </body>
</html>
EOF;
            echo $body;
            exit;
        }
    }

}
