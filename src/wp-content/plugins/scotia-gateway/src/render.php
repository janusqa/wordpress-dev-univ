<?php

/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

global $post;

$block_unique_id = wp_generate_uuid4();

$timezone = "America/Barbados";

$temp_tz = date_default_timezone_get();
date_default_timezone_set($timezone);
$order_details = array(
	"chargetotal" => $attributes['product_price'],
	"checkoutoption" => "combinedpage",
	"currency" => "840", //BBD:052, USD:840
	"hash_algorithm" => "HMACSHA256",
	"parentUri" =>   $site_url,
	"responseFailURL" =>  rest_url("scotia-gateway/v1/callback/error"),
	"responseSuccessURL" =>  rest_url("scotia-gateway/v1/callback/success"),
	"storename" => get_store_name(),
	"timezone" => $timezone,
	"txndatetime" => date('Y:m:d-H:i:s'),
	"txntype" => "sale",
);
date_default_timezone_set($temp_tz);

$hash_extended = get_extended_hash($order_details);

$block_context = array(
	'product_code' => $attributes['product_code'],
	'product_price' => $attributes['product_price'],
	'blockId' => $block_unique_id,
);


?>

<div id="<?php echo $block_unique_id ?>" class="scotia-gateway-view" data-wp-interactive="create-block" <?php echo wp_interactivity_data_wp_context($block_context) ?> style="display: flex; flex-direction: column; gap: 0.2rem; height: 100vh; padding: 1rem;">
	<form method="POST" target="scotiaFrame" action="https://test.ipg-online.com/connect/gateway/processing">
		<div class="product-container">
			<span class="product-name">Product: <?php echo get_the_title($post->ID); ?></span>
			<span class="price">Price: <?php echo "$" . number_format($order_details['chargetotal'], 2); ?> </span>
			<span class="currency">Currency: USD</span>
			<?php
			foreach ($order_details as $key => $value) {
				echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />' . PHP_EOL;
			}
			?>
			<input type="hidden" name="hashExtended" value="<?php echo get_extended_hash($order_details, $shared_secret); ?>" />
			<button type="submit" class="checkout-button">Checkout</button>
		</div>
	</form>
	<iframe class="iframe-container" name="scotiaFrame" style="width: 100%; height: calc(100vh - 50px); border: none;"></iframe>
</div>