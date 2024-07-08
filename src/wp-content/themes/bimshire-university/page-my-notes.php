<?php
if (!is_user_logged_in()) {
    wp_redirect(esc_url(wp_login_url()));
    exit;
}
?>

<?php get_header(); ?>

<?php
while (have_posts()) {
    the_post();
?>

    <?php page_banner(); ?>

    <div class="container container--narrow page-section">
        <div class="create-note">
            <h2 headline headline--medium>Create New Note</h2>
            <input class="new-note-title" placeholder="Title">
            <textarea class="new-note-body" placeholder="Your note here..."></textarea>
            <span class="submit-note">Create Note</span>
            <span class="note-limit-message"></span>
        </div>

        <ul class="min-list link-list" id="my-notes">
            <?php
            $my_notes = new WP_Query(array(
                'post_type' => 'note',
                'author' => get_current_user_id(), // get only the logged in users posts
                'paged' => get_query_var('paged', 1), // get number from paged url to identify page we are on else default to page 1
                // 'posts_per_page' => 1,
                // 'category_name' => 'awards',
                // 'orderby' => 'meta_value_num', # sort by a custom field in your post type
                // 'orderby' => 'meta_value', # this is more suited for sorting textual fields
                // 'meta_key' => 'event_date', # this is the field to sort by
                // 'order' => 'ASC',
                // 'meta_query' => array( // filter post within the base query. Example filter out all most whose date has past the current date
                //     array(
                //         'key' => 'event_date',
                //         'compare' => '<',
                //         'value' => $today,
                //         'type' => 'numeric'
                //     ),
                // )
            ));
            echo '<ul>';
            while ($my_notes->have_posts()) {
                $my_notes->the_post();
            ?>
                <li data-id="<?php echo get_the_ID() ?>" data-state="cancel">
                    <input readonly class="note-title-field" value="<?php echo esc_attr(get_the_title()); ?>" />
                    <span class="edit-note"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</span>
                    <span class="delete-note"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</span>
                    <textarea readonly class="note-body-field"><?php echo esc_textarea(get_the_content()); ?></textarea>
                    <span class="update-note btn btn--blue btn--small"><i class="fa fa-pencil" aria-hidden="true"></i> Save</span>
                </li>
            <?php }
            echo '</ul>';
            // pagination will not work with custom queries, so give it extra configuration to let it work 
            // with a custome query
            echo paginate_links(array(
                'total' => $my_notes->max_num_pages
            ));
            ?>
        </ul>
    </div>

<?php } ?>

<?php get_footer(); ?>