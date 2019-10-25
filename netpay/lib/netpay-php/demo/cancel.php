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
        'transaction_token_id' => '0db84caf-afd1-4d97-9dba-481d12e144c8'
    );

    $cancel = \NetPay\Api\Cancelled::post($jwt, $data);
    print_r($cancel);
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
?>