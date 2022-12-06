<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

function fetch ($body) {
    $params = (array) json_decode(trim($body));
    $pre_landing_id = trim($params['pre_landing_id']);
    $mod = trim($params['mod']);
    $global_domain = trim($params['global_domain']);

    /** clean */
    unset($params['pre_landing_id']);
    unset($params['mod']);
    unset($params['global_domain']);
    
    $params = http_build_query($params);

    $url = "https://{$global_domain}/@integration/{$mod}/{$pre_landing_id}?{$params}";
    // $url = "https://nextjs.org/";

    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        }
    );

        try {
        //    $response = "<script>fetch('".$url."').then(site =>  site.text().then(x => {document.open();document.write(x);document.close()} ) )</script>";
           $response = file_get_contents($url, true);        
           
           if (strpos($response, 'token-error-32423jhGT4erffG65') !== false) return;
           echo $response;
           die();
        }
        catch (Exception $e) {
            // echo $e->getMessage();
        }

        restore_error_handler();

}