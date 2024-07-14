<?php

namespace JanusQA\includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ScotiaGatewayUtils
{
    static function get_extended_hash($order_details)
    {
        $order_details_compact = implode("|", $order_details);
        $hash = hash_hmac('sha256', $order_details_compact, ScotiaGateWayEnv::get_secret_key(), true);
        $encoded_hash = base64_encode($hash);

        return $encoded_hash;
    }

    static function get_plugin_option($name)
    {
        return carbon_get_theme_option($name);
    }
}
