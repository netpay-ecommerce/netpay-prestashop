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

namespace NetPay;

class Billing {
    /**
     * Prepares the billing information of a order for being send to the checkout.
     */
    public static function format($order)
    {
        $phone_number = str_replace("+", "", $order['billing_phone']);
        return [
            "city" => $order['billing_city'],
            "country" => $order['billing_country'],
            "firstName" => $order['billing_first_name'],
            "lastName" => $order['billing_last_name'],
            "email" => $order['billing_email'],
            "phoneNumber" => $phone_number,
            "postalCode" => $order['billing_postcode'],
            "state" => $order['billing_state'],
            "street1" => $order['billing_address_1'],
            "street2" => $order['billing_address_2'],
            "ipAddress" => $order['customer_ip_address'],
        ];
    }
}