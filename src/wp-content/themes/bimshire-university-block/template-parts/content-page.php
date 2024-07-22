<div class="post-item">
    <h2 class="headline headline--medium headline--post-title"><a href="<?php echo the_permalink() ?>"><?php the_title() ?></a></h2>
    <div class="generic-content">
        <!-- alternatively you can use the_content() to show full text of blog  -->
        <?php the_excerpt() ?>
        <p><a class="btn btn--blue" href="<?php echo the_permalink() ?>">Continue reading &raquo;</a></p>
    </div>
</div>