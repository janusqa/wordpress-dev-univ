<?php

namespace JanusQA;

use JanusQA\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Env
{
    static function get_secret_key()
    {
        $from_env = getenv('SCOTIA_SECRET_KEY');
        $from_options = Utils::get_plugin_option('crb_scotia_secret');
        $from_hard = "G'=Y2G6Znr";

        return isset($from_env) ? $from_env : (isset($from_options) ? $from_options : $from_hard);
    }

    static function get_store_name()
    {
        $from_env = getenv('SCOTIA_STORE_NAME');
        $from_options = Utils::get_plugin_option('crb_scotia_store_id');
        $from_hard = "811812100262";

        return isset($from_env) ? $from_env : (isset($from_options) ? $from_options : $from_hard);
    }
}
