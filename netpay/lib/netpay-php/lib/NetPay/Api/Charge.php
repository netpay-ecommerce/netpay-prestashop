<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright 2018 NetPay. All rights reserved.
 */

namespace NetPay\Api;

use Exception;
use \NetPay\Config;
use \NetPay\Api\Curl;
use \NetPay\Exceptions\HandlerHTTP;
use \NetPay\Handlers\ChargeDataHandler;

class Charge
{
    /**
     * Send a post request to make the checkout.
     */
    public static function post($sandbox, $jwt, $transactionTokenId, $grandTotalAmount, $transactionType)
    {
        $fields = ChargeDataHandler::prepare($transactionTokenId, $grandTotalAmount, $transactionType);

        $fields_string = json_encode($fields);

        if($sandbox) {
            $curl_result = Curl::post(Config::SANDBOX_CHARGE_URL, $fields_string, $jwt);
        }
        else {
            $curl_result = Curl::post(Config::CHARGE_URL, $fields_string, $jwt);
        }

        $result = json_decode($curl_result['result'], true);

        /*if ($curl_result['code'] != 200 && $curl_result['code'] != 405) {
            throw HandlerHTTP::errorHandler($result, $curl_result['code']);
        }*/

        return compact('result');
    }
}