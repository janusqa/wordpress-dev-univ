<?php
/*
    Plugin Name: Are you paying attention quiz
    Description: Give your reader multiple choice questions
    Version: 1.0
    Author: JanusQA
    Author URI: http://localhost:8080
*/

if (!defined('ABSPATH')) exit; // exit if accessed directly

class AreYouPayingAttention
{
    function __construct()
    {
        add_action('init', array($this, 'register_block')); // load css/js/fonts for blocks
    }

    function register_block()
    {
        //3rd arg are the dependancies wp will load into the global browser so that these are accessible to our JS scripts
        wp_register_script('are-you-paying-attention-js', plugin_dir_url(__FILE__) . 'build/index.jsx.js', array('wp-blocks', 'wp-element', 'wp-editor'));
        wp_register_style('are-you-paying-attention-css', plugin_dir_url(__FILE__) . 'build/index.jsx.css');

        // the 1st arg is te block name as we had registered it in /src/index.jsx
        // This will take the place of save in /src/index.jsx
        register_block_type('janusplugin/are-you-paying-attention', array(
            'editor_script' => 'are-you-paying-attention-js', // signifiys which jsfile to load. wp_register_script 1st arg is used here
            'editor_style' => 'are-you-paying-attention-css',
            'render_callback' => array($this, 'front_end_html'),
        ));
    }

    function front_end_html($attributes)
    {
        if (!is_admin()) {
            // load front-end assets if not on an admin page
            wp_enqueue_script('are-you-paying-attention-view-js', plugin_dir_url(__FILE__) . 'build/view.jsx.js', array('wp-element'));
            wp_enqueue_style('are-you-paying-attention-view-css', plugin_dir_url(__FILE__) . 'build/view.jsx.css');
        }

        ob_start();
?>
        <div class="paying-attention-root">
            <pre style="display:none;"><?php echo wp_json_encode($attributes) ?></pre>
        </div>
<?php
        return ob_get_clean();
    }
}

$areYouPayingAttention = new AreYouPayingAttention();
