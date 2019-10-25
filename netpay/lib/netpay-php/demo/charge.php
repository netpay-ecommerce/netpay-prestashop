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

    $transaction_token_id = '482a6839-aab8-4a3b-81f9-97f6735bb92f';
    $grandTotalAmount = ''; //optional
    $transactionType = 'PostAuth'; //Auth, PreAuth, PostAuth

    $status = \NetPay\Api\Charge::post($jwt, $transaction_token_id, $grandTotalAmount, $transactionType);
    print_r($status);
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
?>