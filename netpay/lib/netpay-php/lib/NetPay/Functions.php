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

use \NetPay\Config;

class Functions
{
    /**
     * Return the lang options that need the plugin base in the locale.
     */
    public static function get_lang_options()
    {
        $lang = 'es';
        $filename = dirname(__FILE__).'/lang/' . $lang . ".php";
        if (!file_exists($filename)) {
            $filename = "../lang/en.php";
        }

        return require($filename);
    }

    /**
     * Get the path of the plugin.
     */
    public static function get_plugin_directory()
    {
        $explode_string = explode("/",plugin_basename(__FILE__));

        return WP_PLUGIN_DIR.'/'.$explode_string[0];
    }

    /**
     * Encode a url to base64.
     */
    public static function base64url_encode($data)
    {
        return rtrim(base64_encode($data), '=');
    }

    /**
     * Get the month of the promotion string.
     */
    public static function promotion_month($promotion_string)
    {
        if ($promotion_string != '000000') {
            return intval(substr($promotion_string, -4, 2));
        }

        return 0;
    }

    /**
     * Generate the available months without interest options.
     */
    public static function promotion_options($settings)
    {
        $lang_options = self::get_lang_options();

        $promotions = array();
        $promotions_default = array(
            '03' => $lang_options['form_fields']['promotion']['months_without_interest_3'],
            '06' => $lang_options['form_fields']['promotion']['months_without_interest_6'],
            '09' => $lang_options['form_fields']['promotion']['months_without_interest_9'],
            '12' => $lang_options['form_fields']['promotion']['months_without_interest_12'],
            '18' => $lang_options['form_fields']['promotion']['months_without_interest_18'],
        );

        foreach ($promotions_default as $key => $value) {
            if ($settings["promotion_{$key}"] == 'yes') {
                $promotions[$key] = $value;
            }
        }

        if (empty($promotions)) {
            return array();
        }

        return array_replace(
            array('00' => $lang_options['form_fields']['promotion']['months_without_interest_0']),
            $promotions
        );
    }

    /**
     * Return the card type name base in a card type code.
     */
    public static function card_type_name($type)
    {
        $card_types = Config::CARD_TYPES;

        if (isset($card_types[$type])) {
           return $card_types[$type];
        }

        return '';
    }

    /**
     * Return the http error message base in a code and the lang.
     */
    public static function http_code_message($code)
    {
        $lang_options = self::get_lang_options();

        $http_codes = $lang_options['http_codes'];

        $message = $lang_options['http_error'];

        if (isset($http_codes[$code])) {
            $message = $http_codes[$code];
        }

        return $message;
    }

    /**
     * Return the bank error message base in a code and the lang.
     */
    public static function bank_code_message($code)
    {
        $lang_options = self::get_lang_options();

        $bank_codes = $lang_options['bank_codes'];

        $message = $lang_options['bank_error'];

        if (isset($bank_codes[$code])) {
            $message = $bank_codes[$code];
        }

        return $message;
    }

    public static function days_to_today($ida)
    {
        $now = time();
        $date_diff = $now - strtotime($ida);
        $days = (round($date_diff / (60 * 60 * 24))) + 1;
        return $days < 0 ? 0 : $days;
    }

}