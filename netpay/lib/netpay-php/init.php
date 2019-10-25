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

if (!function_exists('curl_init')) {
    throw new Exception('NetPay needs the CURL PHP extension.');
}

if (!function_exists('json_decode')) {
    throw new Exception('NetPay needs the JSON PHP extension.');
}

if (!function_exists('get_called_class')) {
    throw new Exception('NetPay needs to be run on PHP >= 5.3.0.');
}

require_once dirname(__FILE__).'/lib/Config.php';

require_once dirname(__FILE__).'/lib/NetPay/Api/Checkout.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/Curl.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/Login.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/Cancelled.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/Transaction.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/Charge.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/CreateApiKey.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/CreateTokenCard.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/CustomerCards.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/DeleteTokenCard.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/RiskManager.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/IsCashEnable.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/Webhook.php';
require_once dirname(__FILE__).'/lib/NetPay/Api/Reference.php';

require_once dirname(__FILE__).'/lib/NetPay/Exceptions/HandlerBank.php';
require_once dirname(__FILE__).'/lib/NetPay/Exceptions/HandlerHTTP.php';

require_once dirname(__FILE__).'/lib/NetPay/Handlers/CheckoutDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/LoginDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/CancelledDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/ChargeDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/CreateApiKeyDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/CustomerCardsDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/DeleteTokenCardDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/RiskManagerDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/CreateTokenCardDataHandler.php';
require_once dirname(__FILE__).'/lib/NetPay/Handlers/WebhookDataHandler.php';

require_once dirname(__FILE__).'/lib/NetPay/Billing.php';
require_once dirname(__FILE__).'/lib/NetPay/Functions.php';
require_once dirname(__FILE__).'/lib/NetPay/ItemList.php';
require_once dirname(__FILE__).'/lib/NetPay/Order.php';
require_once dirname(__FILE__).'/lib/NetPay/Shipping.php';
require_once dirname(__FILE__).'/lib/NetPay/FormValidator.php';

require_once dirname(__FILE__).'/lib/NetPay/MID/Agencias.php';
require_once dirname(__FILE__).'/lib/NetPay/MID/Donaciones.php';
require_once dirname(__FILE__).'/lib/NetPay/MID/Escuelas.php';
require_once dirname(__FILE__).'/lib/NetPay/MID/Generales.php';
require_once dirname(__FILE__).'/lib/NetPay/MID/Profesionales.php';
require_once dirname(__FILE__).'/lib/NetPay/MID/Retail.php';
require_once dirname(__FILE__).'/lib/NetPay/MID/Tickets.php';
require_once dirname(__FILE__).'/lib/NetPay/MID/Restaurant.php';
