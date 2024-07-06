<?php
add_action('init', 'university_post_types'); // register/create custom post types

function university_post_types()
{
    // event post_type
    $event_labels = array(
        'name' => 'Events',
        'add_new' => 'Add New Event',
        'add_new_item' => 'Add New Event',
        'edit_item' => 'Edit Event',
        'all_items' => 'All Events',
        'singular_name' => 'Event',
        'search_items' => 'Search Events',
    );

    $event_args = array(
        'public' => true,
        'labels' => $event_labels,
        'menu_icon' => 'dashicons-calendar', // get icons from wordpress dashicons
        'show_in_rest' => true, // enable block editor for this post type
        'has_archive' => true,
        'rewrite' => array('slug' => 'events'),
        'description' => 'See what is going on in our world.',
        'supports' => array('title', 'editor', 'excerpt'), # will use Advanced Custom Fields plugin to implement custom fields
    );

    register_post_type('event', $event_args);

    // program post_type
    $program_labels = array(
        'name' => 'Programs',
        'add_new' => 'Add New Program',
        'add_new_item' => 'Add New Program',
        'edit_item' => 'Edit Program',
        'all_items' => 'All Programs',
        'singular_name' => 'Program',
        'search_items' => 'Search Programs',
    );

    $program_args = array(
        'public' => true,
        'labels' => $program_labels,
        'menu_icon' => 'dashicons-awards', // get icons from wordpress dashicons
        'show_in_rest' => true, // enable block editor for this post type
        'has_archive' => true,
        'rewrite' => array('slug' => 'programs'),
        'description' => 'There is something for everyone. Have a look around.',
        'supports' => array('title', 'editor'), # will use Advanced Custom Fields plugin to implement custom fields
    );

    register_post_type('program', $program_args);
}
