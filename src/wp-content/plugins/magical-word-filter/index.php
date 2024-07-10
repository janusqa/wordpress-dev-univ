<?php
/*
    Plugin Name: Magical Word Filter
    Description: Magically censor words in your content
    Version: 1.0
    Author: JanusQA
    Author URI: http://localhost:8080
    Text Domain: mwfdomain
    Domain Path: /languages
*/

if (!defined('ABSPATH')) exit; // exit if accessed directly

class MagicalWordFilterPlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_page'));
        add_filter('the_content', array($this, 'filter_content'));
        add_action('admin_init', array($this, 'mwf_option_settings'));
    }

    function filter_content($content)
    {
        if (get_option('mwf_filter_words')) {
            $bad_words = explode(",", esc_html(get_option('mwf_filter_words')));
            $bad_words_clean = array_map('trim', $bad_words);

            return str_ireplace($bad_words_clean, esc_html(get_option('mwf_filter_pattern')), $content);
        }

        return $content;
    }

    function mwf_option_settings()
    {
        add_settings_section(
            'mwf_first_section',
            null,
            null,
            'magical-word-filter-settings-page-options',
        );

        // the data we want to store for our admin settings/options that controls this plugin

        // filter pattern
        add_settings_field(
            'mwf_filter_pattern',
            'Word Filter Pattern',
            array($this, 'display_filter_pattern_html'),
            'magical-word-filter-settings-page-options',
            'mwf_first_section'
        );
        register_setting(
            'magical_word_filter_plugin',
            'mwf_filter_pattern',
            array(
                'sanitize_callback' => 'sanitize_textarea_field',
                'default' => '****'
            )
        );
    }

    function display_filter_pattern_html()
    { ?>

        <input type="text" name="mwf_filter_pattern" id="mwf_filter_pattern" placeholder="Add your pattern here" value="<?php echo esc_attr(get_option('mwf_filter_pattern', '****')) ?>" />
        <p class="description">Leave blank to simply remove the filtered words.</p>

        <?php
    }

    function admin_page()
    {
        $admin_page_hook = add_menu_page(
            'Words to filter',
            'Word Filter',
            'manage_options',
            'magical-word-filter-settings-page',
            array($this, 'admin_page_html'),
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+Cg==',
            100,
        );

        add_submenu_page(
            'magical-word-filter-settings-page',
            'Words To Filter',
            'Words List',
            'manage_options',
            'magical-word-filter-settings-page',
            array($this, 'admin_page_html'),
        );

        add_submenu_page(
            'magical-word-filter-settings-page',
            'Magical Word Filter Options',
            'Options',
            'manage_options',
            'magical-word-filter-settings-page-options',
            array($this, 'admin_page_options_html'),
        );

        add_action("load-{$admin_page_hook}", array($this, 'main_page_assets'));
    }

    function main_page_assets()
    {
        wp_enqueue_style('admin_page_css', plugin_dir_url(__FILE__) . 'styles.css');
    }

    function formHandler()
    {
        if (wp_verify_nonce($_POST['mwf_nonce'], 'save_filter_words') && current_user_can('manage_options')) {
            update_option('mwf_filter_words', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>

            <div class="updated">
                <p>Your filter list was saved.</p>
            </div>

        <?php
        } else { ?>
            <div class="error">
                <p>Sorry you do not have permission to perform that action.</p>
            </div>
        <?php
        }
    }

    function admin_page_html()
    { ?>

        <div class="wrap">
            <h1>Magical Word Filter Settings</h1>
            <?php if (isset($_POST['justsubmitted']) && $_POST['justsubmitted'] === "true") $this->formHandler() ?>
            <form action="" method="POST">
                <?php wp_nonce_field('save_filter_words', 'mwf_nonce') ?>
                <input type="hidden" name="justsubmitted" value="true" />
                <label for="plugin_words_to_filter">
                    <p>Enter a <strong>comma-seperated</strong> list of words to filter from your site's content.</p>
                    <div class="word-filter__flex-container">
                        <textarea name="plugin_words_to_filter" id="plugin_words_to_filter" placeholder="bad, mean, awful, horrible"><?php echo esc_textarea(get_option('mwf_filter_words')) ?></textarea>
                    </div>
                    <button id="submit" name="submit" class="button button-primary">Save Changes</button>
                </label>
            </form>
        </div>

    <?php }

    function admin_page_options_html()
    { ?>

        <div class="wrap">
            <h1>Magical Word Filter Options</h1>
            <form action="options.php" method="POST">
                <?php
                settings_errors();
                settings_fields('magical_word_filter_plugin');
                do_settings_sections('magical-word-filter-settings-page-options');
                submit_button();
                ?>
            </form>
        </div>

<?php }
}

$magicalWordFilterPlugin = new MagicalWordFilterPlugin();
