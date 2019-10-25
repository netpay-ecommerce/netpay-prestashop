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

namespace NetPay\Handlers;

class RiskManagerDataHandler
{
    /**
     * Prepares the given data for being send.
     */
    public static function prepare(array $input, $merchanDefinedDataList)
    {
        return [
            "storeApiKey" => $input['storeApiKey'],
            'riskManager' => array(
                "promotion" => $input['promotion'],
                "requestFraudService" => [
                    "merchantReferenceCode" => $input['order_id'],
                    'deviceFingerprintID' => $input['deviceFingerprintID'],
                    "bill" => $input['bill'],
                    "ship" => $input['ship'],
                    "itemList" => $input['itemList'],
                    "card" => array(
                        "cardToken" => $input['cardToken']
                    ),
                    "purchaseTotals" => [
                        "grandTotalAmount" => $input['total'],
                        "currency" => $input['currency'],
                    ],
                    "merchanDefinedDataList" => $merchanDefinedDataList
                ]
            )
        ];
    }
}