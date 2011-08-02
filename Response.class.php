<?php

class Response {

    
    public static function render($data, $print = FALSE)
    {
        header('Content-Type: text/html; charset=UTF-8');
        $ob = ob_get_contents();
        ob_end_clean();
        echo $ob;
        echo $data;
    }

}
