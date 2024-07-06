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
                <div class="two-third"><?php the_content(); ?></div>
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