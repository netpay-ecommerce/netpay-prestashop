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

namespace NetPay\MID;

class Generales
{
    public static function get_mdds($input) {
        return [
            [
                "id" => 93,
                "value" => $input['phoneNumber'],
            ],
            [
                "id" => 2,
                "value" => "Web",
            ],
            [
                "id" => 37,
                "value" => "No",
            ],
            [
                "id" => 20,
                "value" => $input['category'],
            ],
            [
                "id" => 23,
                "value" => $input['firstName'].' '.$input['lastName'],
            ],
            [
                "id" => 38,
                "value" => $input['fecha_registro'],
            ],
            [
                "id" => 35,
                "value" => $input['avg_value'],
            ],
            [
                "id" => 39,
                "value" => $input['count_orders_completed'],
            ],
            [
                "id" => 36,
                "value" => "Regular",
            ],
            [
                "id" => 40,
                "value" => $input['count_orders_completed'],
            ],
            [
                "id" => 41,
                "value" => 0,//$input['store_service_type'],
            ],
            [
                "id" => 42,
                "value" => $input['site'],
            ],
            [
                "id" => 43,
                "value" => $input['storeIdAcq'],
            ],
            [
                "id" => 44,
                "value" => 0,//$input['store_city'],
            ],
            [
                "id" => 45,
                "value" => 0,//$input['store_postcode'],
            ],
            [
                "id" => 46,
                "value" => $input['storeIdAcq'],
            ],
            [
                "id" => 94,
                "value" => $input['client_id'],
            ],
            [
                "id" => 0,
                "value" => 'dummy',
            ],
        ];
    }

}
?>