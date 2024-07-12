<?php

/*
  Plugin Name: Featured Professor Block Type
  Version: 1.0
  Author: Your Name Here
  Author URI: https://www.udemy.com/user/bradschiff/
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once plugin_dir_path(__FILE__) . "includes/generate-professor-html.php";
require_once plugin_dir_path(__FILE__) . "includes/related-posts-html.php";

class FeaturedProfessor
{
  function __construct()
  {
    add_action('init', [$this, 'onInit']);
    add_action('rest_api_init', array($this, 'professor_api_routes'));
    add_filter('the_content', [$this, 'add_related_posts']);
  }

  function professor_api_routes()
  {
    // adding custom routes to the wordpress api
    register_rest_route('university/v1', 'professors/(?P<prof_id>\d+)/as-html', array(
      'methods' => WP_REST_SERVER::READABLE, // this const  really means 'GET'
      'callback' => array($this, 'professor_as_html')
    ));
  }

  function professor_as_html(WP_REST_Request $request)
  {
    $data = $request->get_params();
    $prof_id = sanitize_text_field($data['prof_id']);
    return array('data' => generate_professor_html($prof_id));
  }

  function add_related_posts($content)
  {
    if (is_singular('professor') && in_the_loop() && is_main_query()) {
      return $content . related_posts_html(get_the_ID());
    }

    return $content;
  }

  function onInit()
  {
    register_meta('post', 'featuredprofessor', array(
      'show_in_rest' => true,
      'type' => 'number',
      'single' => false
    ));

    wp_register_script('featuredProfessorScript', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks', 'wp-i18n', 'wp-editor'));
    wp_register_style('featuredProfessorStyle', plugin_dir_url(__FILE__) . 'build/index.css');

    register_block_type('ourplugin/featured-professor', array(
      'render_callback' => [$this, 'renderCallback'],
      'editor_script' => 'featuredProfessorScript',
      'editor_style' => 'featuredProfessorStyle'
    ));
  }

  function renderCallback($attributes)
  {
    if ($attributes['profId']) {
      wp_enqueue_style('featuredProfessorStyle');
      return generate_professor_html($attributes['profId']);
    } else {
      return null;
    }
  }
}

$featuredProfessor = new FeaturedProfessor();
