<?php
add_action('init', 'university_post_types'); // register/create custom post types

function university_post_types()
{
	$labels= array(
     	'name' => 'Events',
          'add_new' => 'Add New Event',
          'add_new_item' => 'Add New Event',
          'edit_item' => 'Edit Event',
          'all_items' => 'All Events',
          'singular_name' => 'Event',
          'search_items' => 'Search Events',
     );
    
    $args = array(
        'public' => true,
        'labels' => $labels,
        'menu_icon' => 'dashicons-calendar', // get icons from wordpress dashicons
        'show_in_rest' => true, // enable block editor for this post type
        'has_archive' => true,
        'rewrite'=> array('slug' => 'events'),
        'description' => 'See what is going on in our world',
        'supports' => array('title', 'editor', 'excerpt'), # will use Advanced Custom Fields plugin to implement custom fields
    );
    
    register_post_type('event', $args);
}
