<?php

function generate_professor_html($prof_id)
{
    $prof_post = new WP_Query(array(
        'post_type' => 'professor',
        'p' => $prof_id
    ));
    ob_start();
    while ($prof_post->have_posts()) {
        $prof_post->the_post();
?>
        <div class="professor-callout">
            <div class="professor-callout__photo" style="background-image: url(<?php the_post_thumbnail_url('professor_portrait') ?>)"></div>
            <div class="professor-callout__text">
                <h5><?php the_title() ?></h5>
                <p><?php echo wp_trim_words(get_the_content(), 30) ?></p>

                <?php
                $related_programs = get_field('related_programs');
                if ($related_programs) { ?>
                    <p><?php the_title() ?> teaches:
                        <?php echo implode(', ', array_map(function ($program) {
                            return get_the_title($program);
                        }, $related_programs)) . ".";  ?>
                    </p>
                <?php
                }
                ?>
                <p><strong><a href="<?php the_permalink() ?>">Learn more about <?php the_title() ?> &raquo;</a></strong></p>
            </div>
        </div>
<?php
    }
    wp_reset_postdata();
    return ob_get_clean();
}
