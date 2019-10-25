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

    $transaction_token_id = '4fdb9edb-a340-4cdc-affa-2545eb2ac759';

    $status = \NetPay\Api\Transaction::get($jwt, $transaction_token_id, Config::STORE_ID_ACQ);
    print_r($status);
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
?>