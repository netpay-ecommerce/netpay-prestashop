<?php
/**
* netpay.mx
*
* @author    NetPay
* @copyright 2019 NetPay
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(_PS_MODULE_DIR_.'netpay/netpay.php');
$pg = new NetPay();
$pg->validationNetPay();
