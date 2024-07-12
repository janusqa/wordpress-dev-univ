<?php

function related_posts_html($prof_id)
{
    $related_posts_for_professor = new WP_Query(array(
        'posts_per_page' => -1,
        'post_type' => 'post',
        'meta_query' => array(
            array(
                'key' => 'featuredprofessor',
                'compare' => '=',
                'value' => $prof_id,
                'type' => 'numeric'
            ),
        ),
    ));

    ob_start();
?>
    <?php if ($related_posts_for_professor->found_posts()) { ?>
        <p><?php the_title() ?> is mentioned in the following posts: </p>
    <?php } ?>
    <ul>
        <?php
        while ($related_posts_for_professor->have_posts()) {
            $related_posts_for_professor->the_post();
        ?>
            <li><a href="<?php the_permalink() ?>"><? the_title() ?></a></li>
        <?php } ?>
    </ul>

<?php
    wp_reset_postdata();

    return ob_get_clean();
}
