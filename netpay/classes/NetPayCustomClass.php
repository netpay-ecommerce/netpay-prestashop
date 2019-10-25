<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Description of NetPayCustomClass
 * netpay.mx
 * @author    NetPay
 * @copyright 2019 NetPay
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class NetPayCustomClass
{
    public static function deleteOrderState($idOrderState)
    {
        $q = 'UPDATE '. _DB_PREFIX_ .'order_state SET deleted = 1 WHERE id_order_state = '.$idOrderState;
        $result = Db::getInstance()->execute($q);
        
        if ($result == 0) {
            error_log('Error trying to delete the order state id='.$idOrderState);
        } else {
            return true;
        }
    }
}
