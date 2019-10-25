<?php
require_once ('../init.php');

use \NetPay\Config;

try {
    $data = array(
        'userName' => Config::USER_NAME,
        'password' => Config::PASS,
    );

    $login = \NetPay\Api\Login::post($data);
    $jwt = $login['result']['token'];

    $create_api_key = \NetPay\Api\CreateApiKey::post($jwt);
    print_r($create_api_key);
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
?>