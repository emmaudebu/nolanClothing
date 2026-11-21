<?php
/**
 * Client policy content (from "NOLASHARON CLOTHING DELIVERY AND RETURN POLICY", received
 * 2026-10-26). Run with `studio wp eval-file` locally, or `wp eval-file` on the live site.
 *
 * Writes the Delivery & Shipping and Returns & Exchange pages (approved: the wording is the
 * client's), the FAQ answers that depended on them, the delivery/returns snippets and trust
 * line (only where still empty), turns store pickup on with instructions for the branches
 * that offer it, and updates the matching parts of the Terms draft.
 *
 * Decisions (2026-10-26): exact checkout delivery fees stay with the client (the policy gives
 * ranges); international delivery is arranged by contacting the store, online checkout stays
 * Nigeria-only. Re-running overwrites the two policy pages and these FAQ answers.
 */

$p  = function ( $text ) {
	return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
};
$a = function ( $url, $text ) {
	return '<a href="' . esc_url( $url ) . '">' . $text . '</a>';
};
$contact  = $a( nolasharon_page_url( 'contact' ), 'contact us' );

if ( ! function_exists( 'nolasharon_returns_policy_content' ) ) {
	WP_CLI::error( 'Activate the NolaSharon theme (version 0.15.4 or later) first.' );
}

/* Delivery & Shipping ------------------------------------------------------------------ */

// Since theme 0.15.3 the page lists the exact checkout fees and lives in the theme (inc/delivery-fees.php).
$delivery_page = nolasharon_delivery_policy_content();
$copy          = nolasharon_delivery_copy();

/* Returns & Exchange ------------------------------------------------------------------- */

$returns_page = nolasharon_returns_policy_content(); // In the theme since 0.15.4 (inc/page-content.php).

foreach ( array(
	'delivery' => $delivery_page,
	'returns'  => $returns_page,
) as $key => $content ) {
	$id = nolasharon_policy_page_id( $key );
	if ( ! $id ) {
		WP_CLI::warning( "No {$key} policy page." );
		continue;
	}
	wp_update_post(
		array(
			'ID'           => $id,
			'post_content' => $content,
		)
	);
	update_post_meta( $id, 'ns_policy_approved', '1' );
	WP_CLI::log( "Policy written: {$key} (page {$id})" );
}

/* FAQs --------------------------------------------------------------------------------- */

$faqs = array(
	'Where do you deliver?'                 => array(
		$p( 'We deliver to all areas within Lagos and to all 36 states in Nigeria. We also deliver internationally: please ' . $contact . ' before ordering so we can confirm the fee for your destination.' ),
		'Delivery policy',
		nolasharon_policy_url( 'delivery' ),
	),
	'How much does delivery cost?'          => array(
		$copy['faq'] . "\n\n",
		'Delivery policy',
		nolasharon_policy_url( 'delivery' ),
	),
	'How long does delivery take?'          => array(
		$p( 'Orders are processed within 12–24 hours of payment confirmation (orders placed after 4pm, at weekends or on public holidays are processed the next working day). After that, delivery takes 24–48 hours in Lagos, 2–5 working days to other states and 5–10 working days internationally.' ),
		'Delivery policy',
		nolasharon_policy_url( 'delivery' ),
	),
	'Can I collect my order from a store?'  => array(
		$p( 'Yes, pickup is free from either of our two Ikeja stores. Select “Pickup” at checkout and choose your store. Your order will be ready within 24 hours and we will notify you; please bring your Order ID when you collect.' ),
		'See our stores',
		nolasharon_page_url( 'store-locations' ),
	),
	'Can I exchange a product?'             => array(
		$p( 'Yes. We exchange items that arrive damaged or defective, or if we sent the wrong item or size, within 48 hours of delivery. You can also exchange an item if you ordered the wrong size or changed your mind, paying the return and new delivery fees. Items must be unworn, unwashed and unperfumed with tags attached, and sale items, underwear, lingerie, swimwear, earrings and custom-made items cannot be exchanged.' ),
		'Returns & exchange policy',
		nolasharon_policy_url( 'returns' ),
	),
	'How do I request a return or exchange?' => array(
		$p( 'Contact us within 48 hours of receiving your order by WhatsApp, phone or our Contact page, with your order number and photo or video proof. An unboxing video is required for all delivery complaints, so please record one when you open your package.' ),
		'Contact us',
		nolasharon_page_url( 'contact' ),
	),
	'How do I track my order?'              => array(
		$p( 'Once your order is shipped, we send a dispatch notification by WhatsApp or SMS with the rider or tracking details. You can also check your order on our Track your order page with your order number and the email address you used at checkout, or in My Account if you have an account.' ),
		'Track your order',
		nolasharon_page_url( 'track-order' ),
	),
);
foreach ( $faqs as $title => $faq ) {
	$post = get_page_by_title( $title, OBJECT, 'ns_faq' ); // phpcs:ignore WordPress.WP.DeprecatedFunctions
	if ( ! $post ) {
		WP_CLI::warning( "FAQ not found: {$title}" );
		continue;
	}
	wp_update_post(
		array(
			'ID'           => $post->ID,
			'post_content' => $faq[0],
		)
	);
	update_post_meta( $post->ID, 'ns_link_label', $faq[1] );
	update_post_meta( $post->ID, 'ns_link_url', $faq[2] );
}
WP_CLI::log( count( $faqs ) . ' FAQ answers updated.' );

/* Snippets, trust line and pickup ------------------------------------------------------ */

$set_if_empty = function ( $mod, $value ) {
	if ( '' === trim( (string) get_theme_mod( $mod, '' ) ) ) {
		set_theme_mod( $mod, $value );
		WP_CLI::log( "Set {$mod}" );
	} else {
		WP_CLI::log( "Kept existing {$mod}" );
	}
};
$set_if_empty( 'nolasharon_delivery_info', $copy['snippet'] );
$set_if_empty( 'nolasharon_returns_info', "Exchanges within 48 hours of delivery for damaged, defective or wrong items, with photo or video proof (unboxing video required for delivery complaints). Items must be unworn with tags attached. Sale items are final sale." );
$set_if_empty( 'nolasharon_trust_delivery', 'Delivery across Lagos and all 36 states' );

set_theme_mod( 'nolasharon_pickup_enabled', true );
foreach ( ns_get_branches( array( 'include_drafts' => true ) ) as $branch ) {
	if ( $branch['pickup_enabled'] && '' === trim( (string) get_post_meta( $branch['id'], 'ns_pickup_instructions', true ) ) ) {
		update_post_meta( $branch['id'], 'ns_pickup_instructions', 'Your order will be ready within 24 hours and we will send you a notification. Please bring your Order ID when you come to collect.' );
	}
}
WP_CLI::log( 'Store pickup enabled at checkout.' );

/* Terms draft: delivery, pickup and cancellation references ------------------------------ */

$terms_id = nolasharon_policy_page_id( 'terms' );
if ( $terms_id ) {
	$terms = get_post_field( 'post_content', $terms_id );
	$swap  = array(
		'We currently sell and deliver to customers in Nigeria only.' => 'Online orders are delivered within Nigeria. International delivery can be arranged by contacting us before you order.',
		'Where store pickup is offered, you can choose a branch at checkout. [[CONFIRM: what customers must bring to collect an order, and how long orders are held]]' => 'You can choose free pickup from one of our stores at checkout. Your order will be ready within 24 hours and we will notify you; please bring your Order ID to collect it. [[CONFIRM: how long pickup orders are held]]',
	);
	$new   = str_replace( array_keys( $swap ), array_values( $swap ), $terms );
	if ( $new !== $terms ) {
		wp_update_post(
			array(
				'ID'           => $terms_id,
				'post_content' => $new,
			)
		);
		WP_CLI::log( 'Terms draft updated.' );
	}
}

WP_CLI::success( 'Client policy content applied.' );
