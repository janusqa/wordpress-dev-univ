<?php get_header(); ?>
<?php page_banner(array(
    'title' => "All Events",
    'subtitle' => get_the_archive_description()
)); ?>

<div class="container container--narrow page-section">
    <?php
    while (have_posts()) {
        the_post();
        // first argument to get_template_part gives prefix of file to look for, second arg 
        // looks for the suffix. the previx and suffix are seperated by hyphen
        // eg below if you have a file called content-event.php in folder template-parts, then
        // the function below will look for a file called content-event if we are dealening with 
        // and event query  or content-professor if we are dealing with a professor query. 
        // USE get_template_part where what you are de-duplicationg is just a static blob of html or php 
        // where its dispay is not dependant on variables, other wise use a function if the code needs
        // to be custommize via passing in variables that will change what is displayed from page to page.
        get_template_part('template-parts/content', get_post_type());
    } ?>
    <?php echo paginate_links() ?>
    <hr class="section-break">
    <p>Looking for a recap of past events? <a href="<?php echo site_url('/past-events'); ?>">Check out our past events archive</a>.</p>
</div>

<?php get_footer(); ?>