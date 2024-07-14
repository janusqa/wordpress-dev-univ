<?php

namespace JanusQA;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Options
{

    function __construct()
    {
        add_action('after_setup_theme', array($this, 'load_carbon_fields'));
        add_action('carbon_fields_register_fields', array($this, 'create_options_page'));
    }

    function load_carbon_fields()
    {
        \Carbon_Fields\Carbon_Fields::boot();
    }

    function create_options_page()
    {
        Container::make('theme_options', __('Scotia Options'))
            ->set_icon('dashicons-bank')
            ->add_fields(array(
                Field::make('text', 'crb_scotia_store_id', __('Store Id'))
                    ->set_attribute('type', 'number')
                    ->set_help_text(("Enter your Scotia provided Store Id")),
                Field::make('text', 'crb_scotia_secret', __('Store Secret'))
                    ->set_attribute('type', 'password')
                    ->set_help_text(("Enter your Scotia provided Secret"))
            ));
    }
}

// $scotiaGatewayCustomOptions = new Options();
