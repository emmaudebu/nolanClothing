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

$h  = function ( $text ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->\n\n";
};
$p  = function ( $text ) {
	return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
};
$ul = function ( $items ) {
	$li = '';
	foreach ( $items as $item ) {
		$li .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->\n";
	}
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">{$li}</ul>\n<!-- /wp:list -->\n\n";
};
$a = function ( $url, $text ) {
	return '<a href="' . esc_url( $url ) . '">' . $text . '</a>';
};
$contact  = $a( nolasharon_page_url( 'contact' ), 'contact us' );
$stores   = $a( nolasharon_page_url( 'store-locations' ), 'our two Ikeja stores' );
$returns  = $a( nolasharon_policy_url( 'returns' ), 'Returns &amp; Exchange Policy' );
$delivery = $a( nolasharon_policy_url( 'delivery' ), 'Delivery &amp; Shipping Policy' );

/* Delivery & Shipping ------------------------------------------------------------------ */

$delivery_page = $h( 'Delivery locations' )
	. $p( 'We deliver to all areas within Lagos and nationwide to all 36 states in Nigeria.' )
	. $p( 'We also deliver internationally. To order for delivery outside Nigeria, please ' . $contact . ' first so we can confirm the delivery fee for your destination.' )

	. $h( 'Delivery charges' )
	. $ul(
		array(
			'<strong>Within Lagos:</strong> ₦2,500 – ₦5,000',
			'<strong>Outside Lagos (Nigeria):</strong> ₦3,500 – ₦7,500',
			'<strong>International:</strong> calculated by weight and destination',
		)
	)
	. $p( 'Charges depend on your location and on the weight of your items. The exact fee is shown at checkout before you pay.' )

	. $h( 'Estimated delivery timelines' )
	. $ul(
		array(
			'<strong>Lagos:</strong> 24–48 hours',
			'<strong>Interstate:</strong> 2–5 working days',
			'<strong>International:</strong> 5–10 working days',
		)
	)
	. $p( 'Delivery timelines start once your order has been processed, after payment is confirmed.' )

	. $h( 'Order processing time' )
	. $p( 'Orders are processed within 12–24 hours of payment confirmation. Orders placed after 4pm, at weekends or on public holidays are processed on the next working day.' )

	. $h( 'Dispatch and updates' )
	. $p( 'Once your order is shipped, you will receive a dispatch notification by WhatsApp or SMS with the rider or tracking details. To check on your order, ' . $contact . ' or use our ' . $a( nolasharon_page_url( 'track-order' ), 'Track your order' ) . ' page.' )

	. $h( 'Failed delivery attempts' )
	. $p( 'We attempt delivery 2 times. If delivery fails because of a wrong address, or because your phone is switched off or unreachable, a re-delivery fee applies. If the failure is caused by our courier, re-delivery is free.' )

	. $h( 'Store pickup' )
	. $p( 'You can collect your order for free from either of ' . $stores . '. Select “Pickup” at checkout and choose the store closest to you. Your order will be ready within 24 hours and we will send you a notification when it is. Please bring your Order ID when you come to collect.' );

/* Returns & Exchange ------------------------------------------------------------------- */

$returns_page = $h( 'Returns' )
	. $p( 'We prioritise exchanges. Refunds are only given when an exchange is not possible (see “Refunds” below).' )

	. $h( 'Exchanges' )
	. $p( 'We offer an exchange if your item arrives damaged or defective, or if we sent the wrong item or size. You can also exchange an item if you ordered the wrong size or changed your mind, in which case you pay the return and new delivery fees (see “Return delivery costs”).' )

	. $h( 'Timeframe' )
	. $p( 'Requests must be made within 48 hours (2 days) of receiving your item. We cannot accept returns or exchanges after 48 hours.' )

	. $h( 'Condition of items' )
	. $p( 'Items must be unworn, unwashed and unperfumed, with their tags still attached.' )

	. $h( 'Exclusions' )
	. $p( 'The following cannot be returned or exchanged:' )
	. $ul(
		array(
			'underwear, lingerie and swimwear;',
			'earrings and body jewellery (for hygiene reasons);',
			'items that have been worn, washed or perfumed, or have had their tags removed;',
			'sale, promotional and discounted items (final sale);',
			'custom-made or tailored items.',
		)
	)

	. $h( 'How to make a request' )
	. $p( 'Contact us within 48 hours of receiving your order, by WhatsApp, phone or our ' . $a( nolasharon_page_url( 'contact' ), 'Contact page' ) . ', with your order number and photo or video proof of the issue. An unboxing video is required for all delivery complaints, so please record one when you open your package.' )

	. $h( 'Return delivery costs' )
	. $ul(
		array(
			'<strong>Our error</strong> (wrong or damaged item): we cover the cost of the return and deliver your replacement free of charge.',
			'<strong>Customer’s choice</strong> (changed mind or ordered the wrong size): you pay the return fee and the delivery fee for the exchange.',
		)
	)

	. $h( 'Refunds' )
	. $p( 'A refund is only given if the size you need for an exchange is unavailable. Refunds are made after we have inspected the item, which must be unworn with its tag attached, and are processed to your bank account within 3–5 working days. Delivery fees are non-refundable.' )
	. $p( 'International orders are not refunded; we offer an exchange only where we made an error.' );

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
		$p( 'Within Lagos, delivery costs ₦2,500 – ₦5,000, and outside Lagos (within Nigeria) ₦3,500 – ₦7,500, depending on your location and the weight of your items. International delivery is calculated by weight and destination. The exact fee is shown at checkout before you pay, and store pickup is free.' ),
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
$set_if_empty( 'nolasharon_delivery_info', "Lagos: 24–48 hours · Other states: 2–5 working days · International: 5–10 working days, after your order is processed (12–24 hours after payment).\nWithin Lagos ₦2,500 – ₦5,000, outside Lagos ₦3,500 – ₦7,500; the exact fee is shown at checkout. Free pickup from our Ikeja stores." );
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
