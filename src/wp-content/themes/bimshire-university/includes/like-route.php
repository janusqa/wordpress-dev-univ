<?php

add_action('rest_api_init', 'university_register_like_routes');

$is_user_logged_in = fn () => is_user_logged_in();

function university_register_like_routes()
{
    // adding custom routes to the wordpress api
    register_rest_route('university/v1', 'likes', array(
        'methods' => WP_REST_SERVER::CREATABLE, // this const  really means 'POST'
        'callback' => 'university_professor_like',
        'permission_callback' => 'is_user_logged_in',
    ));

    // adding custom routes to the wordpress api
    register_rest_route('university/v1', 'likes', array(
        'methods' => WP_REST_SERVER::DELETABLE, // this const  really means 'DELETE'
        'callback' => 'university_professor_unlike',
        'permission_callback' => 'is_user_logged_in',
    ));
}


function university_professor_like(WP_REST_Request $request)
{
    $data = $request->get_params();

    $liked_professor_id = sanitize_text_field($data['liked_professor_id']);

    if (empty($liked_professor_id) || get_post_type($liked_professor_id) !== 'professor') {
        return new WP_Error('invalid_data', 'The liked_professor_id field is required.', array('status' => 400));
    }


    $likedByUserQuery = new WP_Query(array(
        'author' => get_current_user_id(),
        'post_type' => 'like',
        'posts_per_page' => -1,
        // 'category_name' => 'awards',
        // 'orderby' => 'meta_value_num', # sort by a custom field in your post type
        // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
        // 'meta_key' => 'event_date', # this is the field to sort by
        // 'order' => 'ASC',
        'meta_query' => array( // filter post within the base query. Example filter out all most whose date has past the current date
            array(
                'key' => 'liked_professor_id',
                'compare' => '=',
                'value' => $liked_professor_id,
                'type' => 'numeric'
            ),
        ),
    ));

    if ($likedByUserQuery->found_posts) {
        return new WP_Error('invalid_data', "Oops! You already liked this professor.", array('status' => 400));
    }

    try {
        $post_id = wp_insert_post(array(
            'post_type' => 'like',
            'post_status' => 'publish',
            'post_title' => "User: " . get_current_user_id() .  " liked Professor: " . $liked_professor_id,
            'post_content' => "User: " . get_current_user_id() .  " liked Professor: " . $liked_professor_id,
            'meta_input' => array( //the fields in this array are how we add data submitted as body in api request. They must match back to request
                "liked_professor_id" => $liked_professor_id,
            ),
        ));

        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }

        return wp_send_json_success(array(
            "message" => "You liked a professor",
            "data" => get_post($post_id)
        ));
    } catch (Exception $e) {
        return new WP_Error('post_creation_failed', $e->getMessage(), array('status' => 500));
    }
}

function university_professor_unlike(WP_REST_Request $request)
{
    $data = $request->get_params();

    $liked_professor_id = sanitize_text_field($data['liked_professor_id']);


    if (empty($liked_professor_id) || get_post_type($liked_professor_id) !== 'professor') {
        return new WP_Error('invalid_data', 'The liked_professor_id field is required.', array('status' => 400));
    }

    $likedByUserQuery = new WP_Query(array(
        'author' => get_current_user_id(),
        'post_type' => 'like',
        'posts_per_page' => -1,
        // 'category_name' => 'awards',
        // 'orderby' => 'meta_value_num', # sort by a custom field in your post type
        // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
        // 'meta_key' => 'event_date', # this is the field to sort by
        // 'order' => 'ASC',
        'meta_query' => array( // filter post within the base query. Example filter out all most whose date has past the current date
            array(
                'key' => 'liked_professor_id',
                'compare' => '=',
                'value' => $liked_professor_id,
                'type' => 'numeric'
            ),
        ),
    ));

    if (!$likedByUserQuery->found_posts) {
        return new WP_Error('invalid_data', "Oops! You can't unlike this professor because you haven't liked them yet.", array('status' => 400));
    }

    return array("message" => "you unliked a professor");
}
