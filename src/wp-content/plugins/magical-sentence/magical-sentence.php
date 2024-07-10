<?php
/*
    Plugin Name: Magical Sentence
    Description: A magical way to add a sentence to a post
    Version: 1.0
    Author: JanusQA
    Author URI: http://localhost:8080
    Text Domain: wcpdomain
    Domain Path: /languages
*/

class WorkCountAndTimePlugin
{
    function __construct()
    {
        add_filter('the_content', array($this, 'get_statistics'));
        add_action('admin_menu', array($this, 'admin_page'));
        add_action('admin_init', array($this, 'settings'));
        add_action('init',  array($this, 'languages'));
    }

    function languages()
    {
        load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function get_statistics($content)
    {
        // only return magical sentence on detail (is_single) pages and if this is a main query
        if (is_single() && is_main_query()) {
            if (get_option('wcp_wordcount', '1') || get_option('wcp_charactercount', '1') || get_option('wcp_readtime', '1')) {
                return $this->createStatistics($content);
            }
        }

        return $content;
    }

    function createStatistics($content)
    {
        $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

        if (get_option('wcp_wordcount', '1') ||  get_option('wcp_readtime', '1')) {
            $word_count = str_word_count(strip_tags($content));
        }

        if (get_option('wcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'wcpdomain') . ' ' .  $word_count . ' ' . esc_html__('words', 'wcpdomain') . '.<br/>';
        }

        if (get_option('wcp_charactercount', '1')) {
            $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br/>';
        }

        if (get_option('wcp_readtime', '1')) {
            $html .= 'This post will take about ' . round($word_count / 225) . ' minute(s) to read.<br/>';
        }

        $html .= '</p>';

        if (get_option('wcp_location', '0') === '0') {
            return $html . $content;
        }

        return $content . $html;
    }

    function settings()
    {
        add_settings_section(
            'wcp_first_section',
            null,
            null,
            'word-count-settings-page',
        );

        // the data we want to store for our admin settings/options that controls this plugin

        // Display Location
        add_settings_field(
            'wcp_location',
            'Display Location',
            array($this, 'display_location_html'),
            'word-count-settings-page',
            'wcp_first_section'
        );
        register_setting(
            'word_count_plugin',
            'wcp_location',
            array(
                'sanitize_callback' => array($this, 'sanitize_location'),
                'default' => '0'
            )
        );

        // Headline Text
        add_settings_field(
            'wcp_headline',
            'Headline Text',
            array($this, 'headline_html'),
            'word-count-settings-page',
            'wcp_first_section'
        );
        register_setting(
            'word_count_plugin',
            'wcp_headline',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'Post Statistics'
            )
        );

        // Word Count
        add_settings_field(
            'wcp_wordcount',
            'Word Count',
            array($this, 'checkbox_html'),
            'word-count-settings-page',
            'wcp_first_section',
            array('setting_name' => 'wcp_wordcount')
        );
        register_setting(
            'word_count_plugin',
            'wcp_wordcount',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );

        // Character Count
        add_settings_field(
            'wcp_charactercount',
            'Character Count',
            array($this, 'checkbox_html'),
            'word-count-settings-page',
            'wcp_first_section',
            array('setting_name' => 'wcp_charactercount')
        );
        register_setting(
            'word_count_plugin',
            'wcp_charactercount',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );

        // Read Time
        add_settings_field(
            'wcp_readtime',
            'Read Time',
            array($this, 'checkbox_html'),
            'word-count-settings-page',
            'wcp_first_section',
            array('setting_name' => 'wcp_readtime')
        );
        register_setting(
            'word_count_plugin',
            'wcp_readtime',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );
    }

    function sanitize_location($input)
    {
        if ($input !== '0' && $input !== '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display must be either beginning or the end.');
            return get_option('wcp_location');
        }
        return $input;
    }

    function display_location_html()
    { ?>

        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), "0") ?>>Beginning of post</option>
            <option value="1" <?php selected(get_option('wcp_location'), "1") ?>>End of post</option>
        </select>

    <?php
    }

    function headline_html()
    { ?>

        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>" />

    <?php
    }

    function admin_page()
    {
        add_options_page('Word Count Settings', esc_html__('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'admin_page_html'));
    }

    function checkbox_html($args)
    { ?>

        <input type="checkbox" name="<?php echo $args['setting_name'] ?>" value="1" <?php checked(get_option($args['setting_name']), "1") ?> />

    <?php
    }

    function admin_page_html()
    { ?>

        <div class="wrap">
            <h1>Word Count Settings</h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('word_count_plugin');
                do_settings_sections('word-count-settings-page');
                submit_button();
                ?>
            </form>
        </div>

<?php }
}

$wordCountAndTimePlugin = new WorkCountAndTimePlugin();
