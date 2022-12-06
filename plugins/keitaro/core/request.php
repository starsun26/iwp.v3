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

    /** add params */
    $params['wp_domain'] = $_SERVER['HTTP_HOST'];
    
    $params = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => "https://{$global_domain}/@integration/{$mod}/{$pre_landing_id}?{$params}",
        CURLOPT_HTTPHEADER => [
                // "Content-Type: application/json"
                // "x-token: Ju76Yhtrddet6"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $info = curl_getinfo($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
        return;
    } else {
        if ($info['http_code'] !== 200) {
            // echo  $info['http_code'];            
            return;
        }

        // echo $info['url'];

        if (strpos($response, 'token-error-32423jhGT4erffG65') !== false) return;
        echo $response;
        die();
    }
}