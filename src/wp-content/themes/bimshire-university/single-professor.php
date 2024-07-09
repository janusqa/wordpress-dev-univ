<?php
get_header();

while (have_posts()) {
    the_post();
?>
    <?php page_banner(); ?>
    <div class="container container--narrow page-section">
        <div class="generic-content">
            <div class="row group">
                <div class="one-third"><?php the_post_thumbnail('professor_portrait'); ?></div>
                <div class="two-third">
                    <?php
                    $likes = new WP_Query(array(
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
                                'value' => get_the_ID(),
                                'type' => 'numeric'
                            ),
                        ),
                    ));

                    $likedByUser = 'no';

                    if (is_user_logged_in()) {
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
                                    'value' => get_the_ID(),
                                    'type' => 'numeric'
                                ),
                            ),
                        ));

                        if ($likedByUserQuery->found_posts) $likedByUser = "yes";
                    }

                    ?>
                    <span class="like-box" data-professor-id="<?php echo get_the_ID() ?>" data-exists="<?php echo $likedByUser ?>">
                        <i class="fa fa-heart-o" aria-hidden="true"></i>
                        <i class="fa fa-heart" aria-hidden="true"></i>
                        <span class="like-count"><?php echo $likes->found_posts ?></span>
                    </span>
                    <?php
                    the_content();
                    ?>
                </div>
            </div>
        </div>
        <?php
        $related_programs = get_field('related_programs'); // returns an array of WP_objects with meta on each object
        if ($related_programs) {
            echo '<hr class="section-break" />';
            echo '<h2 class="headline hedline--medium">Subject(s) Taught</h2>';
            echo '<ul class="link-list min-list">';

            foreach ($related_programs as $program) { //$program is a WP_post object 
        ?>
                <li><a href="<?php echo get_the_permalink($program) ?>"><?php echo get_the_title($program); ?></a></li>
        <?php }

            echo '</ul>';
        }
        ?>
    </div>
<?php
}
get_footer();
?>