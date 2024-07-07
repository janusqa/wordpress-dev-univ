<?php
get_header();

while (have_posts()) {
    the_post();
?>
    <?php page_banner(); ?>
    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('campus') ?>">
                    <i class="fa fa-home" aria-hidden="true"></i> All Campuses
                </a> <span class="metabox__main"><?php the_title() ?></span>
            </p>
        </div>
        <div class="generic-content"><?php the_content() ?></div>
        <?php
        $map_location = get_field('map_location');
        echo $map_location
        ?>
        <!-- Related Programs -->
        <?php
        $related_programs = new WP_Query(array(
            'posts_per_page' => -1,
            // 'category_name' => 'awards',
            'post_type' => 'program',
            'orderby' => 'title', # sort by a custom field in your post type
            // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
            // 'meta_key' => 'event_date', # this is the field to sort by
            'order' => 'ASC',
            'meta_query' => array( // filter post within the base query. Example filter out all most whose date has past the current date
                array(
                    'key' => 'related_campuses', // relate_programs is a serialized string of which the event id will be part of surrouned by double quotes
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"',
                    'type' => 'char'
                ),
            ),
        ));

        if ($related_programs->have_posts()) {
            echo '<hr class="section-break" />';
            echo '<h2 class="headline headline--medium">Programs Avaliable</h2>';
            echo '<ul class="link-list min-list">';
            while ($related_programs->have_posts()) {
                $related_programs->the_post();
        ?>
                <li><a href="<?php the_permalink() ?>"><?php the_title() ?></a></li>
        <?php
            }
            echo '</ul>';
        }
        wp_reset_postdata(); // clean up after using a custom query. DO IT ALWAYS!!!
        ?>

    </div>
<?php
}
get_footer();
?>