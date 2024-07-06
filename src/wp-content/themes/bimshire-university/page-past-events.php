<?php get_header(); ?>
<?php page_banner(array(
    'title' => "Past Events",
    'subtitle' => "A recap of our past events."
)); ?>

<div class="container container--narrow page-section">
    <?php
    $today = date('Ymd');
    $past_events = new WP_Query(array(
        'paged' => get_query_var('paged', 1), // get number from paged url to identify page we are on else default to page 1
        // 'posts_per_page' => 1,
        // 'category_name' => 'awards',
        'post_type' => 'event',
        'orderby' => 'meta_value_num', # sort by a custom field in your post type
        // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
        'meta_key' => 'event_date', # this is the field to sort by
        'order' => 'ASC',
        'meta_query' => array( // filter post within the base query. Example filter out all most whose date has past the current date
            array(
                'key' => 'event_date',
                'compare' => '<',
                'value' => $today,
                'type' => 'numeric'
            ),
        )
    ));

    while ($past_events->have_posts()) {
        $past_events->the_post();
        // first argument to get_template_part gives prefix of file to look for, second arg 
        // looks for the suffix. the previx and suffix are seperated by hyphen
        // eg below if you have a file called content-event.php in folder template-parts, then
        // the function below will look for a file called content-event if we are dealening with 
        // and event query  or content-professor if we are dealing with a professor query. 
        // USE get_template_part where what you are de-duplicationg is just a static blob of html or php 
        // where its dispay is not dependant on variables, other wise use a function if the code needs
        // to be custommize via passing in variables that will change what is displayed from page to page.
        get_template_part('template-parts/content', get_post_type());
    } ?>
    <?php
    // pagination will not work with custom queries, so give it extra configuration to let it work 
    // with a custome query
    echo paginate_links(array(
        'total' => $past_events->max_num_pages
    ));
    ?>
</div>

<?php get_footer(); ?>