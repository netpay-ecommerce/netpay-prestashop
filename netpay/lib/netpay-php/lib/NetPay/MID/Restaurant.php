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

class Restaurant
{
    public static function get_mdds($input) {
        return [
            [
                "id" => 2,
                "value" => 'Web',
            ],
            [
                "id" => 20,
                "value" => $input['category']
            ],
            [
                "id" => 21,
                "value" => "No",
            ],
            [
                "id" => 22,
                "value" => "R",
            ],
            [
                "id" => 23,
                "value" => $input['firstName'].' '.$input['lastName'],
            ],
            [
                "id" => 24,
                "value" => $input['site'],
            ],
            [
                "id" => 25,
                "value" => $input['storeIdAcq'],
            ],
            [
                "id" => 26,
                "value" => 0,//$input['store_city'],
            ],
            [
                "id" => 27,
                "value" => 0,//$input['store_postcode'],
            ],
            [
                "id" => 28,
                "value" => $input['storeIdAcq'],
            ],
            [
                "id" => 29,
                "value" => 0,//$input['store_primary_type_food'],
            ],
            [
                "id" => 30,
                "value" => 0,//$input['store_secundary_type_food'],
            ],
            [
                "id" => 31,
                "value" => 'Si',
            ],
            [
                "id" => 32,
                "value" => $input['fecha_registro'],
            ],
            [
                "id" => 33,
                "value" => $input['total_sum'],
            ],
            [
                "id" => 34,
                "value" => "0",
            ],
            [
                "id" => 35,
                "value" => $input['avg_value'],
            ],
            [
                "id" => 36,
                "value" => "Regular",
            ],
            [
                "id" => 0,
                "value" => 'dummy',
            ],
        ];
    }

}
?>