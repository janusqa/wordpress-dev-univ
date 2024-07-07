<?php get_header(); ?>
<?php page_banner(array(
    'title' => "Our Campuses",
    'subtitle' => get_the_archive_description()
)); ?>

<div class="container container--narrow page-section">
    <ul class="link-list min-list">
        <?php
        while (have_posts()) {
            the_post();
        ?>
            <li>
                <a href=<?php echo the_permalink() ?>><?php echo the_title(); ?></a>
                <?php
                $map_location = get_field('map_location');
                echo $map_location
                ?>
            </li>
        <?php } ?>
    </ul>
    <?php echo paginate_links() ?>
</div>

<?php get_footer(); ?>