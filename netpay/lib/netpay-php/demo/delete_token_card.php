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

    if ($jwt === false) {
        print_r($login);
        return false;
    }

    $data = array(
        'username' => 'ecommerce@netpay.com.mx',
        'storeApiKey' => 'oe1206Pv!VvBEG73F3HVllLd7K_9F2!K',
        'tokenCard' => array(
            'publicToken' => 'MP7VAa3/KWdTJd1Rg8r4NHyfWBzxmSFmzxhqCULUgYlIZTIpf/K/JNz6uMX6culsRzLnvklphkfJEdI6LuyPyV6DbbIWmWUo1KiGbo9I07k='
        )
    );

    $delete_token_card = \NetPay\Api\DeleteTokenCard::post($jwt, $data);
    print_r($delete_token_card);
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
?>