<?php
/**
 * Local seed for the NolaSharon build (run with `studio wp eval-file`).
 * Pages (brief §3/§5), categories, store settings and shipping zones.
 * Facts the client has not confirmed are written as [[CONFIRM: ...]].
 */

$page = function ( $slug, $title, $content, $old_slug = '' ) {
	$post = get_page_by_path( $slug ) ?: ( $old_slug ? get_page_by_path( $old_slug ) : null );
	$data = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
	);
	if ( $post ) {
		$data['ID'] = $post->ID;
		return wp_update_post( $data );
	}
	return wp_insert_post( $data );
};

$p = function ( $text ) {
	return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
};
$h = function ( $text ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->\n\n";
};
$draft = "<!-- DRAFT: client to approve -->\n";
$sections = function ( $list ) use ( $h, $p ) {
	$out = '';
	foreach ( $list as $heading => $confirm ) {
		$out .= $h( $heading ) . $p( '[[CONFIRM: ' . $confirm . ']]' );
	}
	return $out;
};
// About Us (brief §5.6). Only the "Our stores" section is drafted; the rest is client copy.
$page(
	'about-us',
	'About Us',
	$sections(
		array(
			'Our story'                 => 'brand story',
			'What we offer'             => 'what the business offers (product range)',
			'Our values'                => 'brand values',
			'The shopping experience'   => 'what customers can expect when shopping with NolaSharon Clothing',
		)
	) . $h( 'Our stores' ) . $draft . $p( 'Shop with us online or visit us in Lagos. Our Ikeja stores remain open, and Ogudu is our newest branch. <a href="/store-locations/">See our store locations</a>.' ),
	'our-story'
);

$page( 'store-locations', 'Store Locations', '' );

$page(
	'contact',
	'Contact Us',
	$p( '[[CONFIRM: contact form. Install WPForms Lite or Fluent Forms (with spam protection) and paste the form shortcode here]]' )
);

// FAQs (brief §5.9, CONTENT-PAGES.md §3): the questions are FAQ posts seeded by the theme
// (inc/faq.php); the page itself only carries the FAQ template.
update_post_meta( $page( 'faqs', 'FAQs', '' ), '_wp_page_template', 'page-templates/faq.php' );
nolasharon_seed_faqs();

// Policy pages (brief §5.10, CONTENT-PAGES.md §4): structure only, from the theme's
// definitions (inc/policies.php), on the Policy template.
nolasharon_seed_policy_pages( true );

$page(
	'size-guide',
	'Size Guide',
	$p( '[[CONFIRM: size charts (per category if they differ) and how-to-measure guidance]]' )
);

// Pages from the earlier sitemap that the new brief does not include.
foreach ( array( 'our-policies' ) as $slug ) {
	$old = get_page_by_path( $slug );
	if ( $old ) {
		wp_trash_post( $old->ID );
	}
}

// Cart and Checkout use WooCommerce's classic templates so the theme's checkout changes apply.
wp_update_post( array( 'ID' => wc_get_page_id( 'cart' ), 'post_content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->' ) );
wp_update_post( array( 'ID' => wc_get_page_id( 'checkout' ), 'post_content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->' ) );

// Categories: keep Women and Men (examples from the brief), remove the invented ones, add "Sample".
foreach ( array( 'adire-edit', 'occasion-wear', 'made-to-fit' ) as $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term ) {
		wp_delete_term( $term->term_id, 'product_cat' );
	}
}
if ( ! get_term_by( 'slug', 'sample', 'product_cat' ) ) {
	wp_insert_term( 'Sample', 'product_cat', array( 'slug' => 'sample', 'description' => 'Demo products. Delete before launch.' ) );
}

// Store settings (brief §5.4, §7).
$options = array(
	'woocommerce_currency'                          => 'NGN',
	'woocommerce_default_country'                   => 'NG:LA',
	'woocommerce_allowed_countries'                 => 'specific',
	'woocommerce_specific_allowed_countries'        => array( 'NG' ),
	'woocommerce_ship_to_countries'                 => '',
	'woocommerce_ship_to_destination'               => 'billing_only',
	'woocommerce_enable_guest_checkout'             => 'yes',
	'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
	'woocommerce_enable_checkout_login_reminder'    => 'yes',
	// Accounts (STORE-PAGES.md §2): sign-up on My Account, username from the email, the
	// customer sets their own password on the form.
	'woocommerce_enable_myaccount_registration'     => 'yes',
	'woocommerce_registration_generate_username'    => 'yes',
	'woocommerce_registration_generate_password'    => 'no',
	// Cart page (STORE-PAGES.md §4): delivery is worked out at checkout.
	'woocommerce_enable_shipping_calc'              => 'no',
	// Shop filters (SHOP-PRODUCT.md §2.3) use the attribute lookup table; reviews stay off
	// until the client asks for them.
	'woocommerce_attribute_lookup_enabled'          => 'yes',
	'woocommerce_enable_reviews'                    => 'no',
	'woocommerce_default_catalog_orderby'           => 'date',
	'woocommerce_manage_stock'                      => 'yes',
	'woocommerce_hide_out_of_stock_items'           => 'no',
	'woocommerce_email_base_color'                  => '#2B1B12',
	'woocommerce_email_background_color'            => '#F7F7F8',
	'woocommerce_email_body_background_color'       => '#FFFFFF',
	'woocommerce_email_text_color'                  => '#111111',
	'woocommerce_email_footer_text'                 => '{site_title}',
	'woocommerce_coming_soon'                       => 'no',
	'comment_moderation'                            => 1, // Reviews are published only after approval.
	'blogname'                                      => 'NolaSharon Clothing',
	'woocommerce_terms_page_id'                     => get_page_by_path( 'terms-and-conditions' ) ? get_page_by_path( 'terms-and-conditions' )->ID : '',
);
foreach ( $options as $key => $value ) {
	update_option( $key, $value );
}

// Shipping zones with placeholder rates (brief §7). Fees stay at 0 until the client confirms them.
foreach ( WC_Shipping_Zones::get_zones() as $zone_data ) {
	WC_Shipping_Zones::delete_zone( $zone_data['zone_id'] );
}
$rest = new WC_Shipping_Zone( 0 );
foreach ( $rest->get_shipping_methods() as $instance_id => $method ) {
	$rest->delete_shipping_method( $instance_id );
}
$add_rate = function ( $zone, $title ) {
	$id = $zone->add_shipping_method( 'flat_rate' );
	update_option( "woocommerce_flat_rate_{$id}_settings", array( 'enabled' => 'yes', 'title' => $title, 'tax_status' => 'none', 'cost' => '0' ) );
};
$lagos = new WC_Shipping_Zone();
$lagos->set_zone_name( 'Lagos' );
$lagos->add_location( 'NG:LA', 'state' );
$lagos->set_zone_order( 1 );
$lagos->save();
$add_rate( $lagos, 'Lagos – Mainland [[CONFIRM: fee]]' );
$add_rate( $lagos, 'Lagos – Island [[CONFIRM: fee]]' );
$add_rate( $lagos, 'Lagos – Outskirts [[CONFIRM: fee]]' );
$pickup = $lagos->add_shipping_method( 'local_pickup' );
update_option( "woocommerce_local_pickup_{$pickup}_settings", array( 'enabled' => 'yes', 'title' => 'Store pickup', 'tax_status' => 'none', 'cost' => '0' ) );

$nigeria = new WC_Shipping_Zone();
$nigeria->set_zone_name( 'Other Nigerian states' );
$nigeria->add_location( 'NG', 'country' );
$nigeria->set_zone_order( 2 );
$nigeria->save();
$add_rate( $nigeria, 'Delivery to other states [[CONFIRM: fee]]' );
WC_Cache_Helper::invalidate_cache_group( 'shipping_zones' );

echo "content seeded\n";
