<?php

add_action('rest_api_init', 'university_register_search');

function university_register_search()
{
    // adding custom routes to the wordpress api
    register_rest_route('university/v1', 'search', array(
        'methods' => WP_REST_SERVER::READABLE, // this const is really means 'GET'
        'callback' => 'university_search_results'
    ));
}

function university_search_results($query_string)
{

    $results = array(
        'posts' => array(),
        'professors' => array(),
        'programs' => array(),
        'events' => array(),
        'campuses' => array(),
    );

    $main_query = new WP_Query(array(
        'post_type' => array(
            'post', 'page', 'professor', 'program', 'event', 'campus'
        ), // can handle multiple post types to query 
        's' => sanitize_text_field($query_string['term']), // This param enables searching by the value. extract value of key "term" from querystring
    ));

    while ($main_query->have_posts()) {
        $main_query->the_post();

        switch (get_post_type()) {
            case "event":
                $event_date = new DateTime(get_field('event_date'));
                if (has_excerpt()) {
                    $event_summary = get_the_excerpt();
                } else {
                    $event_summary = wp_trim_words(get_the_content(), 18);
                }
                array_push($results['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'month' => $event_date->format('M'),
                    'day' => $event_date->format('d'),
                    'summary' => $event_summary,
                ));
                break;
            case "program":
                $relate_campuses = get_field('related_campuses');

                if ($relate_campuses) {
                    foreach ($relate_campuses as $campus) {
                        array_push($results['campuses'], array(
                            'title' => get_the_title($campus),
                            'permalink' => get_the_permalink($campus),
                        ));
                    }
                }

                array_push($results['programs'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'id' => get_the_ID(),
                ));
                break;
            case "campus":
                array_push($results['campuses'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                ));
                break;
            case "professor":
                array_push($results['professors'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'image' => get_the_post_thumbnail_url(get_the_ID(), 'professor_landscape')
                ));
                break;
            default:
                array_push($results['posts'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'postType' => get_post_type(),
                    'authorName' => get_the_author()
                ));
        }
    }

    wp_reset_postdata();

    if ($results['programs']) {
        $programs_meta_query = array(
            'relation' => 'OR', // 'AND' by default
        );
        foreach ($results['programs'] as $program) {
            array_push(
                $programs_meta_query,
                array(
                    'key' => 'related_programs',
                    'compare' => 'LIKE',
                    'value' => '"' . $program['id'] . '"',
                    'type' => 'char'
                ),
            );
        }
        $related_query = new WP_Query(array(
            'post_type' => array('professor', 'event'),
            'meta_query' => $programs_meta_query
        ));

        while ($related_query->have_posts()) {
            $related_query->the_post();
            switch (get_post_type()) {
                case "event":
                    $event_date = new DateTime(get_field('event_date'));
                    if (has_excerpt()) {
                        $event_summary = get_the_excerpt();
                    } else {
                        $event_summary = wp_trim_words(get_the_content(), 18);
                    }
                    array_push($results['events'], array(
                        'title' => get_the_title(),
                        'permalink' => get_the_permalink(),
                        'month' => $event_date->format('M'),
                        'day' => $event_date->format('d'),
                        'summary' => $event_summary,
                    ));
                    break;
                case "professor":
                    array_push($results['professors'], array(
                        'title' => get_the_title(),
                        'permalink' => get_the_permalink(),
                        'image' => get_the_post_thumbnail_url(get_the_ID(), 'professor_landscape')
                    ));
                    break;
                default:
                    break;
            }
        }
    }


    // Remove duplicates from each sub-array in results
    foreach ($results as &$result) {
        $result = array_values(array_unique($result, SORT_REGULAR));
    }
    unset($result); // break the reference with the last element

    wp_reset_postdata();

    return $results;
}