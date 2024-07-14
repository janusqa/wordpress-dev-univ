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

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
// require_once plugin_dir_path(__FILE__) . "Classes/Env.php";
// require_once plugin_dir_path(__FILE__) . "Classes/Utils.php";
// require_once plugin_dir_path(__FILE__) . "Classes/Options.php";

use JanusQA\Env;
use JanusQA\Options;
use JanusQA\Utils;

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */

class ScotiaGateWayPlugin
{
	private $order_fields_map = array(
		"approval_code" => "Approval Code",
		"associationResponseCode" => "Association Response Code",
		"associationResponseMessage" => "Association Response Message",
		"bname" => "Customer Name",
		"cardnumber" => "Card Number",
		"ccbin" => "Credit Card Bank Identification Number",
		"ccbrand" => "Credit Card Brand",
		"cccountry" => "Card Card issued In",
		"chargetotal" => "Charge Total",
		"currency" => "Currency",
		"endpointTransactionId" => "Endpoint Transaction Id",
		"expmonth" => "Expiry Month",
		"expyear" => "Expiry Year",
		"fail_rc" => "Failure Code",
		"fail_reason" => "Failure Reason",
		"hash_algorithm" => "Hash Algorithmn",
		"installments_interest" => "Installment Interest",
		"ipgTransactionId" => "IPG Transaction Id",
		"oid" => "Order Id",
		"paymentMethod" => "Payment Method",
		"processor_network_information" => "processor network information",
		"processor_response_code" => "Processor Response Code",
		"refnumber" => "Reference Number",
		"response_code_3dsecure" => "3D Secure",
		"response_hash" => "Response Hash",
		"schemeTransactionId" => "scheme Transaction Id",
		"status" => "Status",
		"tdate" => "Transaction Timestamp",
		"terminal_id" => "Terminal Id",
		"timezone" => "Timezone",
		"txndate_processed" => "Transaction Date Processed",
		"txndatetime" => "Transaction Date",
		"txntype" => "Transaction Type",
	);

	function __construct()
	{
		new Options();

		register_activation_hook(__FILE__, array($this, 'plugin_activation'));
		register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));

		add_action('init', array($this, 'create_block_scotia_gateway_block_init'));

		add_action('admin_init', array($this, 'enable_search_for_custom_fields'));

		add_action('rest_api_init', array($this, 'create_block_api_routes'));
		add_filter('wp_insert_post_data', array($this, 'set_post_privacy'), 10, 2);

		add_action('admin_menu', array($this, 'remove_publish_meta_box_for_orders'));
		add_action('add_meta_boxes', array($this, 'create_orders_meta_box'));
		add_filter('manage_order_posts_columns', array($this, 'order_posts_columns'));
		add_action('manage_order_posts_custom_column', array($this, 'order_posts_custom_column'), 10, 2);

		//Uncomment to add test data to orders
		// add_action('admin_head', array($this, 'insert_test_posts'));
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
			'edit_item' => 'View Order',
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
				// 'edit_posts' => 'do_not_allow'
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
		array_push($order_details, $response_data['approval_code'], $response_data['chargetotal'], $response_data['currency'], $response_data['txndatetime'], Env::get_store_name());
		$hash_extended = Utils::get_extended_hash($order_details);

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

	function remove_publish_meta_box_for_orders()
	{
		remove_meta_box('submitdiv', 'order', 'side');
	}

	function create_orders_meta_box()
	{
		add_meta_box('scotia_order_details', 'Meeting Order', array($this, 'display_order_detail'), 'order');
	}

	function display_order_detail()
	{
		// get_post_meta(get_the_ID(), 'bname', true);

		$post_meta = array_intersect_key(get_post_meta(get_the_ID()), $this->order_fields_map);
		echo '<ul>';
		foreach ($post_meta as $key => $value) {
			echo '<li>' . '<strong>' . esc_html($this->order_fields_map[$key]) . ':</strong> ' . esc_html($value[0]) . '</li>';
		}
		echo '</ul>';
	}

	function order_posts_columns($columns)
	{
		// Save the title column to prepend it later
		$title_column = isset($columns['title']) ? array('title' => $columns['title']) : array();

		// Remove title from the original columns
		unset($columns['title']);

		// Custom columns you want to add
		$custom_columns = array(
			'bname' => $this->order_fields_map['bname'],
			'status' => $this->order_fields_map['status'],
			'txndatetime' => $this->order_fields_map['txndatetime'],
			'chargetotal' => $this->order_fields_map['chargetotal'],
			'ccbrand' => $this->order_fields_map['ccbrand'],
			'fail_reason' => $this->order_fields_map['fail_reason'],
		);

		// Merge title column, custom columns, and the remaining original columns
		$columns = array_merge($title_column, $custom_columns, $columns);

		return $columns;
	}

	function order_posts_custom_column($column, $post_id)
	{
		// TODO: scaffold out the columns you wish to see on orders list in admin
		switch ($column) {
			case "bname":
				echo esc_html(get_post_meta($post_id, $column, true));
				break;
			case "status":
				echo esc_html(get_post_meta($post_id, $column, true));
				break;
			case "txndatetime":
				echo esc_html(get_post_meta($post_id, $column, true));
				break;
			case "chargetotal":
				echo esc_html(get_post_meta($post_id, $column, true));
				break;
			case "ccbrand":
				echo esc_html(get_post_meta($post_id, $column, true));
				break;
			case "fail_reason":
				echo esc_html(get_post_meta($post_id, $column, true));
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
						and meta_key in ('bname','oid','fail_reason', 'chargetotal', 'status','txndatetime','ccbrand')
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

	function insert_test_posts()
	{
		wp_insert_post(array(
			'post_type' => 'order',
			'post_title' => 'C-87039cba-d1f8-4932-b3c0-6300313482a4',
			'post_status' => 'publish',
			'meta_input' => array(
				"approval_code" => "Y:OK6687:4661907428:PPXX:505968",
				"associationResponseCode" => "00",
				"associationResponseMessage" => "Approved or completed successfully",
				"bname" => "test",
				"cardnumber" => "(MASTERCARD) ... 2745",
				"ccbin" => "520474",
				"ccbrand" => "MASTERCARD",
				"cccountry" => "GBR",
				"chargetotal" => "13,00",
				"currency" => "840",
				"endpointTransactionId" => "505968",
				"expmonth" => "08",
				"expyear" => "2024",
				"hash_algorithm" => "HMACSHA256",
				"installments_interest" => "false",
				"ipgTransactionId" => "84661907428",
				"oid" => "C-87039cba-d1f8-4932-b3c0-6300313482a4",
				"paymentMethod" => "M",
				"processor_network_information" => "MAST",
				"processor_response_code" => "00",
				"refnumber" => "84661907428",
				"response_code_3dsecure" => "1",
				"response_hash" => "SgWzffhkdYBPT4gcSJGsk9FtQ1fVYMLhGA/VC+NHhPY=",
				"schemeTransactionId" => "0712MCC133468",
				"status" => "APROBADO",
				"tdate" => "1720801223",
				"terminal_id" => "1657235",
				"timezone" => "America/Barbados",
				"txndate_processed" => "12/07/24 18:20:23",
				"txndatetime" => "2024:07:12-12:18:30",
				"txntype" => "sale",
			)
		));

		wp_insert_post(array(
			'post_type' => 'order',
			'post_title' => 'C-3d4eeb1d-011d-40ce-8316-f86804f221ce',
			'post_status' => 'publish',
			'meta_input' => array(
				"approval_code" => "N:-5005:FRAUD - Duplicate lockout",
				"bname" => "test",
				"cardnumber" => "(MASTERCARD) ... 4979",
				"ccbin" => "542606",
				"ccbrand" => "MASTERCARD",
				"cccountry" => "DEU",
				"chargetotal" => "13,00",
				"currency" => "840",
				"expmonth" => "08",
				"expyear" => "2024",
				"fail_rc" => "5005",
				"fail_reason" => "Duplicate transaction.",
				"hash_algorithm" => "HMACSHA256",
				"installments_interest" => "false",
				"ipgTransactionId" => "84661908303",
				"oid" => "C-3d4eeb1d-011d-40ce-8316-f86804f221ce",
				"paymentMethod" => "M",
				"response_hash" => "+kDn4ZWpACBtwwypL7sHItrizXfur1hnWuczwp6mIyw=",
				"status" => "NEGADO",
				"tdate" => "1720801438",
				"timezone" => "America/Barbados",
				"txndate_processed" => "12/07/24 18:23:58",
				"txndatetime" => "2024:07:12-12:16:51",
				"txntype" => "sale",
			)
		));
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
