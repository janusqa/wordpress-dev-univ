<?php

/**
 * Plugin Name:       Scotia Gateway
 * Description:       An interactive block to connect to Scotia's Hosted Gateway
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Author:            JanusQA
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       scotia-gateway
 *
 * @package           create-block
 */

use JanusQA\includes\ScotiaGateWayEnv;
use JanusQA\includes\ScotiaGatewayUtils;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */

class ScotiaGateWayPlugin
{
	function __construct()
	{
		require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
		require_once plugin_dir_path(__FILE__) . "includes/env.php";
		require_once plugin_dir_path(__FILE__) . "includes/utils.php";
		require_once plugin_dir_path(__FILE__) . "includes/options-page.php";

		register_activation_hook(__FILE__, array($this, 'plugin_activation'));
		register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));

		add_action('init', array($this, 'create_block_scotia_gateway_block_init'));

		add_action('admin_init', array($this, 'enable_search_for_custom_fields'));

		add_action('rest_api_init', array($this, 'create_block_api_routes'));
		add_filter('wp_insert_post_data', array($this, 'set_post_privacy'), 10, 2);

		add_action('add_meta_boxes', array($this, 'create_orders_meta_box'));
		add_filter('manage_order_posts_columns', array($this, 'order_posts_columns'));
		add_action('manage_order_posts_custom_column', array($this, 'order_posts_custom_column'), 10, 2);
	}

	function create_block_scotia_gateway_block_init()
	{
		register_block_type_from_metadata(__DIR__ . '/build');
		$this->register_custom_types();
	}

	function register_custom_types()
	{
		// meeting post_type
		$meeting_labels = array(
			'name' => 'Meetings',
			'add_new' => 'Add New Meeting',
			'add_new_item' => 'Add New Meeting',
			'edit_item' => 'Edit Meeting',
			'all_items' => 'All Meetings',
			'singular_name' => 'Meeting',
			'search_items' => 'Search Meetings',
		);

		$meeting_args = array(
			'public' => true,
			'labels' => $meeting_labels,
			'menu_icon' => 'dashicons-schedule', // get icons from wordpress dashicons
			'show_in_rest' => true, // enable block editor for this post type
			'has_archive' => true,
			'rewrite' => array('slug' => 'meetings'),
			'description' => 'There is something for everyone. Have a look around.',
			'supports' => array('title', 'editor'), # will use Advanced Custom Fields plugin to implement custom fields
		);

		register_post_type('meeting', $meeting_args);

		// order post_type
		$order_labels = array(
			'name' => 'Orders',
			'add_new' => 'Add New Order',
			'add_new_item' => 'Add New Order',
			'edit_item' => 'Edit Order',
			'all_items' => 'All Orders',
			'singular_name' => 'Order',
			'search_items' => 'Search Orders',
		);

		$order_args = array(
			'public' => false, #hide these posts from showing up anywhere, so they are private and specific to each user. Do not display in public search results or queries
			'show_ui' => true, # if you hide post everywhere, re-enable them to show in admin dashboard
			'show_in_rest' => false,
			'labels' => $order_labels,
			'menu_icon' => 'dashicons-store',
			'has_archive' => false,
			// 'rewrite' => array('slug' => 'orders'),
			'supports' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'capability_type' => 'post',
			'capabilities' => array(
				'create_posts' => 'do_not_allow',
				'edit_posts' => 'do_not_allow'
			),
			'map_meta_cap' => true,
		);

		register_post_type('order', $order_args);
	}

	function create_block_api_routes()
	{
		// adding custom routes to the wordpress api
		register_rest_route('scotia-gateway/v1', '/callback/success', array(
			'methods' => WP_REST_SERVER::CREATABLE,
			'callback' => array($this, 'scotia_gateway_callback')
		));

		register_rest_route('scotia-gateway/v1', '/callback/error', array(
			'methods' => WP_REST_SERVER::CREATABLE,
			'callback' => array($this, 'scotia_gateway_callback')
		));
	}

	function scotia_gateway_callback(WP_REST_Request $request)
	{
		$data = $request->get_params();

		// validate Gateway response
		if (!$this->scotia_gateway_validate_response($data)) {
			return new WP_Error('invalid_gateway_response', "Oops! Gateway response failed validation check.", array('status' => 400));
		}

		$failure_class = isset($data['fail_rc']) ?  " order-card-header--failure" : "";
		$order_status_title = isset($data['fail_rc']) ? $data["fail_reason"] : $data["associationResponseMessage"];
		$html_response =
			'<!DOCTYPE html><html lang="en"><body><p class="order-message">' . $data["bname"] . ', here is your order status.</p>' . PHP_EOL .
			'<div class="order-status-card">' . PHP_EOL .
			'<div class="order-card-header' . $failure_class . '">' . $order_status_title . '</div>' . PHP_EOL .
			'<div class="order-card-body">' . PHP_EOL .
			'<p>Order Id: ' . $data["oid"] . '</p>' . PHP_EOL .
			'<p>Total: ' . $data["chargetotal"] . '</p>' . PHP_EOL .
			'<p>Paid with: ' . $data["ccbrand"] . '</p>' . PHP_EOL .
			'</div>' . PHP_EOL .
			'</div>' . PHP_EOL . '</body></html>';

		if (isset($data['fail_rc'])) {
			return new WP_Error('gateway_order_fail', "Oops! Gateway response failed validation check.", array('status' => 400, 'html' => $html_response));
		}

		return new WP_REST_Response(array('html' => $html_response), 200);
	}

	function scotia_gateway_validate_response($response_data)
	{
		$order_details = array();
		array_push($order_details, $response_data['approval_code'], $response_data['chargetotal'], $response_data['currency'], $response_data['txndatetime'], ScotiaGateWayEnv::get_store_name());
		$hash_extended = ScotiaGateWayUtils::get_extended_hash($order_details);

		return ($hash_extended === $response_data['response_hash']);
	}

	function set_post_privacy($post, $postarr)
	{
		// makes some post private when saved or created to prevent them appearing in queries
		switch ($post['post_type']) {
			case "order":
				if ($post['post_status'] !== 'trash') $post['post_status'] = "private";
				break;
		}

		return $post;
	}

	function create_orders_meta_box()
	{
		add_meta_box('scotia_order_details', 'Meeting Order', array($this, 'dispay_order_detail', 'order'));
	}

	function dispay_order_detail()
	{
		// get_post_meta(get_the_ID(), 'bname', true)

		$post_meta = get_post_meta(get_the_ID());
		echo '<ul>';
		foreach ($post_meta as $key => $value) {
			echo '<li>' . '<strong>' . $key . ':</strong> ' . $value[0] . '</li>';
		}
		echo '</ul>';
	}

	function order_posts_columns($columns)
	{
		// TODO: Update array with the custom meta fields of an order
		return array_merge($columns, array(
			'bname' => 'Name'
		));
	}

	function order_posts_custom_column($column, $post_id)
	{
		// TODO: scaffold out the columns you wish to see on orders list in admin
		switch ($column) {
			case "bname":
				echo get_post_meta($post_id, $column, true);
				break;
		}
	}

	function enable_search_for_custom_fields()
	{
		global $typenow;

		if ($typenow === 'order') add_filter('posts_search', array($this, 'order_search_override'), 10, 2);
	}

	function order_search_override($search, $query)
	{
		// TODO: adjust the sql below to actually use the fields of the order custom post type
		global $wpdb;

		if ($query->is_main_query() && !empty($query->query['s'])) {
			$sql = "
					or exists (
						select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
						and meta_key in ('name','email','phone')
						and meta_value like %s
					)
				";
			$like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
			$search = preg_replace(
				"#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
				$wpdb->prepare($sql, $like),
				$search
			);
		}

		return $search;
	}

	function plugin_activation()
	{
		$this->register_custom_types();
		flush_rewrite_rules();
	}

	function plugin_deactivation()
	{
		unregister_post_type('meeting');
		flush_rewrite_rules();
	}
}

$scotiaGateWayPlugin = new ScotiaGateWayPlugin();
