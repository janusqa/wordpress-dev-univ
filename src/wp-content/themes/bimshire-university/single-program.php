<?php
get_header();

while (have_posts()) {
    the_post();
?>
    <?php page_banner(); ?>
    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('program') ?>">
                    <i class="fa fa-home" aria-hidden="true"></i> All Programs
                </a> <span class="metabox__main"><?php the_title() ?></span>
            </p>
        </div>
        <div class="generic-content"><?php the_content() ?></div>

        <!-- Related professors -->
        <?php
        $related_professors = new WP_Query(array(
            'posts_per_page' => -1,
            // 'category_name' => 'awards',
            'post_type' => 'professor',
            'orderby' => 'title', # sort by a custom field in your post type
            // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
            // 'meta_key' => 'event_date', # this is the field to sort by
            'order' => 'ASC',
            'meta_query' => array( // filter post within the base query. Example filter out all most whose date has past the current date
                array(
                    'key' => 'related_programs', // relate_programs is a serialized string of which the event id will be part of surrouned by double quotes
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"',
                    'type' => 'char'
                ),
            ),
        ));

        if ($related_professors->have_posts()) {
            echo '<hr class="section-break" />';
            echo '<h2 class="headline headline--medium">' . get_the_title() . ' Professors</h2>';
            echo '<ul class="professor-cards">';
            while ($related_professors->have_posts()) {
                $related_professors->the_post();
        ?>
                <li class="professor-card__list-item">
                    <a class="professor-card" href="<?php the_permalink() ?>">
                        <img class="professor-card__image" src="<?php the_post_thumbnail_url('professor_landscape') ?>" />
                        <span class="professor-card__name"><?php the_title() ?></span>
                    </a>
                </li>
        <?php
            }
            echo '</ul>';
        }
        wp_reset_postdata(); // clean up after using a custom query. DO IT ALWAYS!!!
        ?>

        <!-- Related events -->
        <?php
        $today = date('Ymd');
        $most_recent_events = new WP_Query(array(
            'posts_per_page' => 2,
            // 'category_name' => 'awards',
            'post_type' => 'event',
            'orderby' => 'meta_value_num', # sort by a custom field in your post type
            // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
            'meta_key' => 'event_date', # this is the field to sort by
            'order' => 'ASC',
            'meta_query' => array( // filter post within the base query. Example filter out all most whose date has past the current date
                array(
                    'key' => 'event_date',
                    'compare' => '>=',
                    'value' => $today,
                    'type' => 'numeric'
                ),
                array(
                    'key' => 'related_programs', // relate_programs is a serialized string of which the event id will be part of surrouned by double quotes
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"',
                    'type' => 'char'
                ),
            ),
        ));

        if ($most_recent_events->have_posts()) {
            echo '<hr class="section-break" />';
            echo '<h2 class="headline headline--medium">Upcoming ' . get_the_title() . ' Events</h2>';

            while ($most_recent_events->have_posts()) {
                $most_recent_events->the_post();
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
        <?php
            }
        }
        wp_reset_postdata(); // clean up after using a custom query. DO IT ALWAYS!!!
        ?>

    </div>
<?php
}
get_footer();
?>