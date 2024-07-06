<?php get_header(); ?>
<?php page_banner(array(
    'title' => get_the_archive_title(),
    'subtitle' => get_the_archive_description()
)); ?>

<div class="container container--narrow page-section">
    <?php
    while (have_posts()) {
        the_post();
    ?>
        <div class="post-item">
            <h2 class="headline headline--medium headline--post-title"><a href="<?php echo the_permalink() ?>"><?php the_title() ?></a></h2>
            <div class="metabox">
                <p>Posted by <?php the_author_posts_link() ?> on <?php the_time('Y-n-j') ?> in <?php echo get_the_category_list(', ') ?></p>
            </div>
            <div class="generic-content">
                <!-- alternatively you can use the_content() to show full text of blog  -->
                <?php
                if (has_excerpt()) {
                    echo get_the_excerpt();
                } else {
                    echo wp_trim_words(get_the_content(), 18);
                }
                ?>
                <p><a class="btn btn--blue" href="<?php echo the_permalink() ?>">Continue reading &raquo;</a></p>
            </div>
        </div>
    <?php } ?>
    <?php echo paginate_links() ?>
</div>

<?php get_footer(); ?>