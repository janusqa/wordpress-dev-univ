<?php get_header(); ?>

<?php
while (have_posts()) {
    the_post();
?>

    <?php page_banner(); ?>

    <div class="container container--narrow page-section">

        <?php
        /**
         * get_the_ID() -- get the ID of the current page.  Each page/post has an ID
         * wp_get_post_parent_id(SOME_ID) -- get the ID of SOME_ID's parent
         * Using these two together we can test if a page/post has a parent
         * if wp_get_post_parent_id(get_the_ID()) == 0 aka false then no page has not parent
         *  and vice versa
         */
        $the_parent_id = wp_get_post_parent_id(get_the_ID());
        if ($the_parent_id) {
        ?>
            <div class="metabox metabox--position-up metabox--with-home-link">
                <p>
                    <a class="metabox__blog-home-link" href="<?php echo get_permalink($the_parent_id) ?>">
                        <i class="fa fa-home" aria-hidden="true"></i> Back to <?php echo get_the_title($the_parent_id) ?>
                    </a> <span class="metabox__main"><?php the_title() ?></span>
                </p>
            </div>
        <?php } ?>

        <?php
        // display child menu only if you are on a child page or if you are on a parent page that has children
        $testArray = get_pages(array(
            'child_of' => get_the_ID()
        ));
        if ($the_parent_id or $testArray) {
        ?>
            <div class="page-links">
                <h2 class="page-links__title">
                    <a href="<?php echo get_permalink($the_parent_id) ?>"><?php echo get_the_title($the_parent_id) ?></a>
                </h2>
                <ul class="min-list">
                    <?php
                    // display children if you are on a child page, or parent page
                    if ($the_parent_id) {
                        $children_of = $the_parent_id;
                    } else {
                        $children_of = get_the_ID();
                    }

                    wp_list_pages(array(
                        'title_li' => NULL, // do not show the default title that this list generates
                        'child_of' => $children_of, // wp_list_pages lists every page but this param filters it to only list children of the Id specified
                        'sort_column' => 'menu_order' // sorts menu items in the order they are given in the admin pages/post meta data
                    )) ?>
                </ul>
            </div>
        <?php } ?>

        <div class="generic-content">
            <?php the_content() ?>
        </div>
    </div>

<?php } ?>

<?php get_footer(); ?>