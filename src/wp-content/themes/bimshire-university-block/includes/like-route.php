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
    register_rest_route('university/v1', 'likes/(?P<like_id>\d+)', array(
        'methods' => WP_REST_SERVER::DELETABLE, // this const  really means 'DELETE'
        'callback' => 'university_professor_unlike',
        'permission_callback' => 'is_user_logged_in',
        'args' => array(
            'like_id' => array(
                'validate_callback' => 'absint', // Ensures the ID is a positive integer
                'required'          => true, // ID is required
                'sanitize_callback' => 'absint', // Sanitizes the ID as integer
            ),
        ),
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

        return array(
            "message" => "You liked a professor",
            "data" => get_post($post_id)
        );
    } catch (Exception $ex) {
        return new WP_Error('post_creation_failed', $ex->getMessage(), array('status' => 500));
    }
}

function university_professor_unlike(WP_REST_Request $request)
{
    $data = $request->get_params();

    $like_id = sanitize_text_field($data['like_id']);

    // is this a valid id for like posttype and it belongs to current user
    if (empty($like_id) || get_post_type($like_id) !== 'like' || get_post_field('post_author', $like_id) !== (string)get_current_user_id()) {
        return new WP_Error('invalid_data', 'Oops! You cannot unlike this professor at this time', array('status' => 400));
    }

    $likedByUserQuery = new WP_Query(array(
        'author' => get_current_user_id(),
        'post_type' => 'like',
        'posts_per_page' => -1,
        'p' => $like_id, // return post whose id matches the param specified
        // 'category_name' => 'awards',
        // 'orderby' => 'meta_value_num', # sort by a custom field in your post type
        // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
        // 'meta_key' => 'event_date', # this is the field to sort by
        // 'order' => 'ASC',
    ));

    // check that you have likes to unlike
    if (!$likedByUserQuery->found_posts) {
        return new WP_Error('invalid_data', "Oops! It seems you have already unliked this professor.", array('status' => 400));
    }

    try {
        $deleted_post = wp_delete_post($like_id, true);

        if (!$deleted_post) throw new Exception("Nothing was deleted");

        return array(
            "message" => "You unliked a professor",
            "data" => $deleted_post
        );
    } catch (Exception $ex) {
        return new WP_Error('post_deletion_failed', $ex->getMessage(), array('status' => 500));
    }
}
