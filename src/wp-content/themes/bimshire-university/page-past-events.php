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
        $event_date = new DateTime(get_field('event_date'));
    ?>
        <div class="event-summary">
            <a class="event-summary__date t-center" href="<?php the_permalink() ?>">
                <span class="event-summary__month"><?php echo $event_date->format('M') ?></span>
                <span class="event-summary__day"><?php echo $event_date->format('d') ?></span>
            </a>
            <div class="event-summary__content">
                <h5 class="event-summary__title headline headline--tiny"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h5>
                <p>
                    <?php
                    if (has_excerpt()) {
                        echo get_the_excerpt();
                    } else {
                        echo wp_trim_words(get_the_content(), 18);
                    }
                    ?>
                    <a href="<?php the_permalink() ?>" class="nu gray">Learn more</a>
                </p>
            </div>
        </div>
    <?php } ?>
    <?php
    // pagination will not work with custom queries, so give it extra configuration to let it work 
    // with a custome query
    echo paginate_links(array(
        'total' => $past_events->max_num_pages
    ));
    ?>
</div>

<?php get_footer(); ?>