<?php
require get_theme_file_path('/includes/search-route.php');

add_action('wp_enqueue_scripts', 'university_files'); // load css/js/fonts
add_action('after_setup_theme', 'university_features'); // register dynamic menus made in wp-admin
add_action('pre_get_posts', 'university_adjust_queries'); // using this to make tweaks to the query on the events archive page
add_action('rest_api_init', 'university_custom_rest'); // hook into rest api to customize data it returns


function university_files()
{
    wp_enqueue_style('google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
    wp_enqueue_script('university-main-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);

    // this injects a varible called "universityData" into the js file tage with 'university-main-js'
    // that is, the file located at /build/index.js. The variable consist of a js object made up
    // from the third argument of wp_localize_script which is an associative array.
    // This is how we can add global settings we want to make available to JS in a wordpress theme.
    wp_localize_script('university-main-js', 'universityData', array(
        'baseUrl' => get_site_url()
    ));
}

function university_features()
{
    add_theme_support('title-tag'); // enable title in browser tab

    //allow post to support featured images. Must further enable them for custom post types in their respective config
    // by adding 'thumbnail' to 'support' key
    add_theme_support('post-thumbnails');

    // configure the various image sizes we want wordpress to generate from one base image we uploaded
    // fourth argument indicates if to crop image or not
    add_image_size('professor_landscape', 400, 260, true);
    add_image_size('professor_portrait', 480, 650, true);
    add_image_size('page-banner', 1500, 350, true);

    register_nav_menu('header-menu-location', "Header Menu Location"); // create a dynamic menu location. Appearence -> Menus
    register_nav_menu('footer-location-1', "Footer Location 1"); // create a dynamic menu location. Appearence -> Menus
    register_nav_menu('footer-location-2', "Footer Location 2"); // create a dynamic menu location. Appearence -> Menus
}

function university_adjust_queries($query)
{
    // modify event main query
    if (!is_admin() and is_post_type_archive('event') and $query->is_main_query()) {
        // if the query is not being run in the admin side, i.e a front end page is being displayed
        // AND we only want to query to affect the front end 
        // AND only for queries targeting event post type
        // AND only if this is a main query and not one of our custom queries
        $today = date('Ymd');
        $query->set('orderby', 'meta_value_num'); // order by numeric type field
        $query->set('meta_key', 'event_date');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
            array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
            ),
        ));
    }

    // modify program main query
    if (!is_admin() and is_post_type_archive('program') and $query->is_main_query()) {
        // if the query is not being run in the admin side, i.e a front end page is being displayed
        // AND we only want to query to affect the front end 
        // AND only for queries targeting event post type
        // AND only if this is a main query and not one of our custom queries
        $query->set('posts_per_page', -1); // show ALL post, no pagination
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
    }
}

function university_custom_rest()
{
    // add a custom field to be returned by api
    // 1st param is the posttype this applies to
    // 2nd param is the key name you want your custom field to be named by
    // 3r param is an array of callbacks that seeds the data that will be returned
    // eg the below registers a field called author_name which will hold the author's
    // name that created that post.  You can register as many of these fields as you like
    register_rest_field('post', 'author_name', array(
        'get_callback' => function () {
            return get_the_author();
        }
    ));
}

function page_banner($args = NULL)
{
    if (!isset($args['title'])) $args['title'] = get_the_title();
    if (!isset($args['subtitle'])) $args['subtitle'] = get_field('page_banner_subtitle');
    if (!isset($args['photo'])) {
        if (get_field('page_banner_background_image') && !is_archive() && !is_home()) {
            $args['photo'] =  get_field('page_banner_background_image')['sizes']['page-banner'];
        } else {
            $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
        }
    }
?>
    <div class="page-banner">
        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo'] ?>)">
        </div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle'] ?></p>
            </div>
        </div>
    </div>
<?php
}
