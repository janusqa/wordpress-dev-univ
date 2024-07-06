<?php
get_header();

while (have_posts()) {
    the_post();
?>
    <?php page_banner(); ?>
    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('event') ?>">
                    <i class="fa fa-home" aria-hidden="true"></i> Events Home
                </a> <span class="metabox__main"><?php the_title() ?></span>
            </p>
        </div>
        <div class="generic-content"><?php the_content() ?></div>
        <?php
        $related_programs = get_field('related_programs'); // returns an array of WP_objects with meta on each object
        if ($related_programs) {
            echo '<hr class="section-break" />';
            echo '<h2 class="headline hedline--medium">Related Programs</h2>';
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