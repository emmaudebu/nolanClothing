<?php
/**
 * Page text that lives in the database, kept in the theme so it reaches the live site.
 *
 * Deploying the theme (WP Pusher) copies files only; page content, FAQs, branches and
 * Customizer values stay in each site's database. The client's Returns & Exchange policy
 * (received 2026-10-26) and the Privacy and Terms drafts were written locally by the seed
 * scripts and never reached the live site, so the theme applies them once by itself, on the
 * first wp-admin page load by a shop manager after this version is installed (0.15.4).
 * Text someone has already written on the site is kept. The Delivery & Shipping page and its
 * fees are handled the same way in inc/delivery-fees.php.
 *
 * Decision (2026-10-26): the returns wording is the client's and is published as approved.
 * Privacy and Terms are drafts with [[CONFIRM: …]] blanks: they are written but stay hidden
 * from customers ("being updated") until "Wording approved" is ticked on each page.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block markup for an H2 heading.
 *
 * @param string $text Heading HTML.
 * @return string
 */
function nolasharon_block_h( $text ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->\n\n";
}

/**
 * Block markup for a paragraph.
 *
 * @param string $text Paragraph HTML.
 * @return string
 */
function nolasharon_block_p( $text ) {
	return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
}

/**
 * Block markup for a bulleted list.
 *
 * @param string[] $items Item HTML.
 * @return string
 */
function nolasharon_block_ul( $items ) {
	$li = '';
	foreach ( $items as $item ) {
		$li .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->\n";
	}
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">{$li}</ul>\n<!-- /wp:list -->\n\n";
}

/**
 * A link for page text.
 *
 * @param string $url  URL.
 * @param string $text Link HTML.
 * @return string
 */
function nolasharon_block_link( $url, $text ) {
	return '<a href="' . esc_url( $url ) . '">' . $text . '</a>';
}

/**
 * The note at the top of a draft policy (not shown on the page).
 *
 * @return string
 */
function nolasharon_policy_draft_mark() {
	return "<!-- DRAFT: client to approve. Legal review recommended before ticking \"Wording approved\". -->\n";
}

/**
 * Returns & Exchange page: the client's policy (received 2026-10-26).
 *
 * @return string Block markup.
 */
function nolasharon_returns_policy_content() {
	return nolasharon_block_h( 'Returns' )
		. nolasharon_block_p( 'We prioritise exchanges. Refunds are only given when an exchange is not possible (see “Refunds” below).' )

		. nolasharon_block_h( 'Exchanges' )
		. nolasharon_block_p( 'We offer an exchange if your item arrives damaged or defective, or if we sent the wrong item or size. You can also exchange an item if you ordered the wrong size or changed your mind, in which case you pay the return and new delivery fees (see “Return delivery costs”).' )

		. nolasharon_block_h( 'Timeframe' )
		. nolasharon_block_p( 'Requests must be made within 48 hours (2 days) of receiving your item. We cannot accept returns or exchanges after 48 hours.' )

		. nolasharon_block_h( 'Condition of items' )
		. nolasharon_block_p( 'Items must be unworn, unwashed and unperfumed, with their tags still attached.' )

		. nolasharon_block_h( 'Exclusions' )
		. nolasharon_block_p( 'The following cannot be returned or exchanged:' )
		. nolasharon_block_ul(
			array(
				'underwear, lingerie and swimwear;',
				'earrings and body jewellery (for hygiene reasons);',
				'items that have been worn, washed or perfumed, or have had their tags removed;',
				'sale, promotional and discounted items (final sale);',
				'custom-made or tailored items.',
			)
		)

		. nolasharon_block_h( 'How to make a request' )
		. nolasharon_block_p( 'Contact us within 48 hours of receiving your order, by WhatsApp, phone or our ' . nolasharon_block_link( nolasharon_page_url( 'contact' ), 'Contact page' ) . ', with your order number and photo or video proof of the issue. An unboxing video is required for all delivery complaints, so please record one when you open your package.' )

		. nolasharon_block_h( 'Return delivery costs' )
		. nolasharon_block_ul(
			array(
				'<strong>Our error</strong> (wrong or damaged item): we cover the cost of the return and deliver your replacement free of charge.',
				'<strong>Customer’s choice</strong> (changed mind or ordered the wrong size): you pay the return fee and the delivery fee for the exchange.',
			)
		)

		. nolasharon_block_h( 'Refunds' )
		. nolasharon_block_p( 'A refund is only given if the size you need for an exchange is unavailable. Refunds are made after we have inspected the item, which must be unworn with its tag attached, and are processed to your bank account within 3–5 working days. Delivery fees are non-refundable.' )
		. nolasharon_block_p( 'International orders are not refunded; we offer an exchange only where we made an error.' );
}

/**
 * Privacy Policy draft: facts from how the site is built are written in; business facts and
 * legal choices are [[CONFIRM: …]] blanks for the client and their adviser.
 *
 * @return string Block markup.
 */
function nolasharon_privacy_policy_draft() {
	$contact = nolasharon_block_link( nolasharon_page_url( 'contact' ), 'Contact Us' );

	return nolasharon_policy_draft_mark()
		. nolasharon_block_h( 'Who we are' )
		. nolasharon_block_p( 'This Privacy Policy explains how NolaSharon Clothing (“we”, “us”, “our”) collects, uses, shares and protects your personal information when you visit this website or buy from us online.' )
		. nolasharon_block_p( 'NolaSharon Clothing is [[CONFIRM: registered business name and CAC registration number (RC or BN)]], of [[CONFIRM: registered business address]]. We are responsible for the personal information described in this policy, and we handle it in line with the Nigeria Data Protection Act 2023.' )
		. nolasharon_block_p( 'For any question about your privacy, email [[CONFIRM: privacy contact email]] or use our ' . $contact . ' page.' )

		. nolasharon_block_h( 'Information we collect' )
		. nolasharon_block_p( 'We only ask for the information we need to sell to you, deliver your order and help you.' )
		. nolasharon_block_ul(
			array(
				'<strong>When you place an order:</strong> your name, email address, phone number, delivery address (street address, city or area, and state), any delivery instructions you add, the items you order and the amount paid. If you choose store pickup, we record the branch you choose.',
				'<strong>If you create an account:</strong> your name, email address, phone number (optional) and a password, which is stored in a scrambled (hashed) form that we cannot read. Your account also keeps your order history, saved addresses and wishlist.',
				'<strong>When you contact us:</strong> through the contact form, your name, email address, phone number (optional), what your message is about, your order number (if it is about an order) and your message. We do not store your IP address with contact form messages. If you call, email or message us on WhatsApp, we receive the details you share there.',
				'<strong>When you track an order:</strong> the order number and email address you enter, used only to find your order. To stop misuse, we keep a coded (hashed) version of your IP address for up to 10 minutes and then delete it.',
				'<strong>When you pay:</strong> you enter your card or bank details on our payment provider’s secure page. We never see or store your full card details; we receive confirmation of payment and a transaction reference.',
				'<strong>Automatically:</strong> small files and browser storage that make the site work (see “Cookies and similar technologies”), and standard technical logs kept by our hosting provider, such as IP address, browser type and pages requested. [[CONFIRM: hosting provider and how long server logs are kept]]',
			)
		)
		. nolasharon_block_p( 'We do not currently use analytics or advertising tracking on this website. If we add them, we will update this policy first.' )

		. nolasharon_block_h( 'Cookies and similar technologies' )
		. nolasharon_block_p( 'We use only the cookies and browser storage needed for the website to work. We do not use advertising cookies.' )
		. nolasharon_block_ul(
			array(
				'<strong>Shopping cart:</strong> WooCommerce cookies (such as <code>woocommerce_cart_hash</code>, <code>woocommerce_items_in_cart</code> and <code>wp_woocommerce_session_</code>) remember what is in your cart. They last up to two days.',
				'<strong>Signing in:</strong> if you sign in to your account, WordPress sets cookies (such as <code>wordpress_logged_in_</code>) to keep you signed in. They end when you sign out or after a set period.',
				'<strong>Wishlist:</strong> the <code>ns_wishlist</code> cookie remembers the items you save, for up to 90 days.',
				'<strong>Browser storage:</strong> your browser remembers if you closed the announcement bar and which shop grid layout you prefer. During checkout and sign-in, some details are kept in your browser only until you close the tab.',
			)
		)
		. nolasharon_block_p( 'You can delete or block cookies in your browser settings, but your cart, sign-in and wishlist will not work without them.' )

		. nolasharon_block_h( 'How we use your information' )
		. nolasharon_block_p( 'We use your information to:' )
		. nolasharon_block_ul(
			array(
				'process, deliver or prepare your order for pickup, and take payment (to perform our contract with you);',
				'send you emails about your order, such as confirmations and status updates, and account emails such as password resets (to perform our contract with you);',
				'reply to your messages and give customer support (our legitimate interest in helping customers, or to perform our contract with you);',
				'prevent fraud and misuse of the website and keep it secure (our legitimate interest);',
				'keep records of sales and transactions as required by law (legal obligation).',
			)
		)
		. nolasharon_block_p( 'We do not send marketing emails or messages. If we offer them in future, we will only send them if you have agreed, and you will be able to unsubscribe at any time.' )

		. nolasharon_block_h( 'Payments' )
		. nolasharon_block_p( 'Online payments are handled by [[CONFIRM: payment provider, e.g. Paystack]]. When you pay, you share your payment details directly with them, and they process them under their own privacy policy and security standards. This website does not store your card details.' )

		. nolasharon_block_h( 'Sharing your information' )
		. nolasharon_block_p( 'We do not sell your personal information. We share it only when needed to run our business, with:' )
		. nolasharon_block_ul(
			array(
				'<strong>our payment provider</strong>, to take payment (see “Payments”);',
				'<strong>delivery partners</strong>, who receive your name, phone number, delivery address and order details so they can deliver to you. [[CONFIRM: courier or logistics provider(s) used, if any]]',
				'<strong>service providers</strong> who host the website and send our emails, under agreements that require them to protect your information. [[CONFIRM: hosting provider and email delivery service]]',
				'<strong>authorities or professional advisers</strong>, where the law requires it or to protect our rights.',
			)
		)
		. nolasharon_block_p( 'If you message us on WhatsApp, that conversation is also handled by WhatsApp (Meta) under its own terms and privacy policy.' )
		. nolasharon_block_p( 'Some parts of our pages load from outside services: our fonts come from Google Fonts, and the store map uses map images from OpenStreetMap and a mapping library from Cloudflare (cdnjs). When your browser loads these, the provider receives your IP address and basic browser information.' )
		. nolasharon_block_p( '[[CONFIRM: whether any provider stores data outside Nigeria, and the safeguards relied on for those transfers]]' )

		. nolasharon_block_h( 'Storage and security' )
		. nolasharon_block_p( 'We protect your information with an encrypted (HTTPS) connection, by limiting access to staff who need it for their work, and by keeping our website software up to date. Account passwords are stored in hashed form, and card details are handled by our payment provider rather than stored on our website.' )
		. nolasharon_block_p( 'We keep personal information only for as long as we need it:' )
		. nolasharon_block_ul(
			array(
				'order records: [[CONFIRM: how long order and transaction records are kept, e.g. for tax and accounting purposes]];',
				'contact form messages: [[CONFIRM: how long contact messages are kept]];',
				'accounts: until you ask us to delete your account, after which we keep only the order records we are required to keep.',
			)
		)

		. nolasharon_block_h( 'Your rights' )
		. nolasharon_block_p( 'Under the Nigeria Data Protection Act 2023, you have the right to:' )
		. nolasharon_block_ul(
			array(
				'know how we use your personal information (this policy);',
				'ask for a copy of the personal information we hold about you;',
				'ask us to correct information that is wrong or incomplete;',
				'ask us to delete your information, where we do not need to keep it by law;',
				'object to, or ask us to restrict, how we use your information;',
				'receive your information in a commonly used format;',
				'withdraw your consent at any time, where we rely on it.',
			)
		)
		. nolasharon_block_p( 'If you have an account, you can update your details in My Account. For anything else, email [[CONFIRM: privacy contact email]]. We may need to confirm your identity first, and we will reply within [[CONFIRM: response time for privacy requests]]. If you are not happy with how we have handled your information, you can complain to the Nigeria Data Protection Commission (NDPC).' )
		. nolasharon_block_p( 'Our website is meant for adults. If you are under 18, please shop with the help of a parent or guardian.' )

		. nolasharon_block_h( 'Changes and contact' )
		. nolasharon_block_p( 'We may update this policy when our website or the way we work changes. The “Last updated” date at the top of this page shows when it last changed; for important changes, we will also let customers know on the website.' )
		. nolasharon_block_p( 'Questions about this policy or your information: email [[CONFIRM: privacy contact email]], or use our ' . $contact . ' page.' );
}

/**
 * Terms & Conditions draft, with the client's delivery and pickup details.
 *
 * @return string Block markup.
 */
function nolasharon_terms_draft() {
	$contact = nolasharon_block_link( nolasharon_page_url( 'contact' ), 'Contact Us' );

	return nolasharon_policy_draft_mark()
		. nolasharon_block_h( 'About these terms' )
		. nolasharon_block_p( 'These terms apply when you use this website and buy from NolaSharon Clothing (“we”, “us”, “our”), which is [[CONFIRM: registered business name and CAC registration number (RC or BN)]], of [[CONFIRM: registered business address]].' )
		. nolasharon_block_p( 'By placing an order, you agree to these terms, together with our ' . nolasharon_block_link( nolasharon_policy_url( 'delivery' ), 'Delivery &amp; Shipping Policy' ) . ', ' . nolasharon_block_link( nolasharon_policy_url( 'returns' ), 'Returns &amp; Exchange Policy' ) . ' and ' . nolasharon_block_link( nolasharon_policy_url( 'privacy' ), 'Privacy Policy' ) . '. Please read them before you order.' )
		. nolasharon_block_p( 'Online orders are delivered within Nigeria. International delivery can be arranged by contacting us before you order.' )

		. nolasharon_block_h( 'Orders' )
		. nolasharon_block_p( 'To order, choose your size and colour, add items to your cart and complete checkout. You can check out as a guest or with an account. Each order gets an order number (for example, NS-1042), which you can use to track it.' )
		. nolasharon_block_p( 'After you place an order, we send a confirmation email to the address you give. This email confirms that we have received your order. [[CONFIRM: when an order is accepted, e.g. when payment is confirmed or when the order is dispatched]]' )
		. nolasharon_block_p( 'We may decline or cancel an order if an item turns out to be unavailable, if a product was listed with the wrong price or details, if payment is not completed, or if we suspect fraud. If this happens after you have paid, we will refund the full amount you paid.' )

		. nolasharon_block_h( 'Pricing' )
		. nolasharon_block_p( 'Prices are shown in Nigerian naira (₦). [[CONFIRM: whether prices include VAT]] Delivery fees are shown at checkout before you pay, and the total you pay is shown before you confirm your order.' )
		. nolasharon_block_p( 'Sale prices apply only for the period shown. If a product is listed at the wrong price by mistake, we will contact you before processing the order, and you can choose to continue at the correct price or cancel for a full refund.' )

		. nolasharon_block_h( 'Product availability' )
		. nolasharon_block_p( 'Stock levels on the website update as orders are placed. Sizes and colours that are sold out are marked and cannot be added to your cart. [[CONFIRM: whether online stock is shared with the stores, and what happens if an item sells out in store after you order]]' )
		. nolasharon_block_p( 'We try to show our products accurately, but colours can look slightly different on different screens. Please check the size guide on the product page before you order; if you are unsure about a size, contact us first.' )

		. nolasharon_block_h( 'Payment' )
		. nolasharon_block_p( 'Payment is made online at checkout through [[CONFIRM: payment provider and methods accepted, e.g. Paystack: card, bank transfer, USSD]]. We start preparing your order once payment is confirmed. We do not store your card details.' )
		. nolasharon_block_p( 'Orders that are not paid show as “Pending payment”. [[CONFIRM: whether and when unpaid orders are cancelled]]' )

		. nolasharon_block_h( 'Delivery' )
		. nolasharon_block_p( 'Delivery areas, fees and timelines are set out in our ' . nolasharon_block_link( nolasharon_policy_url( 'delivery' ), 'Delivery &amp; Shipping Policy' ) . '. Please make sure your delivery address and phone number are correct, as we cannot be responsible for delays caused by incorrect details.' )
		. nolasharon_block_p( 'You can choose free pickup from one of our stores at checkout. Your order will be ready within 24 hours and we will notify you; please bring your Order ID to collect it. [[CONFIRM: how long pickup orders are held]]' )

		. nolasharon_block_h( 'Cancellations' )
		. nolasharon_block_p( '[[CONFIRM: whether customers can cancel an order, up to what point (e.g. before dispatch), how to request it, and how refunds for cancelled orders are made]]' )
		. nolasharon_block_p( 'We may cancel an order for the reasons listed under “Orders”. If we do, we will tell you and refund the full amount you paid.' )

		. nolasharon_block_h( 'Returns and exchanges' )
		. nolasharon_block_p( 'Returns and exchanges are covered by our ' . nolasharon_block_link( nolasharon_policy_url( 'returns' ), 'Returns &amp; Exchange Policy' ) . '. Nothing in these terms affects your rights under Nigerian consumer protection law, including the Federal Competition and Consumer Protection Act 2018.' )

		. nolasharon_block_h( 'Customer responsibilities' )
		. nolasharon_block_p( 'When you use our website, you agree to:' )
		. nolasharon_block_ul(
			array(
				'give accurate and complete details when you order or create an account;',
				'keep your account password private, and tell us if you think someone else has used your account;',
				'use the website lawfully, and not try to disrupt it, gain unauthorised access to it, or misuse it in any other way.',
			)
		)
		. nolasharon_block_p( 'All content on this website, including photos, text, logos and designs, belongs to NolaSharon Clothing or is used with permission. You may not copy or use it for commercial purposes without our written consent.' )

		. nolasharon_block_h( 'Other terms' )
		. nolasharon_block_p( '[[CONFIRM: limitation of liability wording, after professional advice]] Nothing in these terms limits any liability that cannot be limited by law.' )
		. nolasharon_block_p( 'These terms are governed by [[CONFIRM: governing law and courts, e.g. the laws of the Federal Republic of Nigeria and the courts of Lagos State]].' )
		. nolasharon_block_p( 'We may update these terms from time to time. The version on this page when you place an order is the one that applies to that order. If any part of these terms is found to be invalid, the rest still applies.' )
		. nolasharon_block_p( 'Questions about these terms: use our ' . $contact . ' page or email [[CONFIRM: customer service email]].' );
}

/**
 * Write the policy pages, Customizer notes and store pickup set-up that the site is missing.
 * Without $force, only empty pages, the [[CONFIRM: …]] outline the theme creates, or the
 * WordPress sample privacy text are replaced, and only empty settings are filled.
 *
 * @param bool $force Overwrite the pages even if they have been written.
 * @return string[] What was updated or kept.
 */
function nolasharon_sync_page_content( $force = false ) {
	$log   = array();
	$pages = array(
		'returns' => array( 'Returns & Exchange', nolasharon_returns_policy_content(), true ),
		'privacy' => array( 'Privacy Policy', nolasharon_privacy_policy_draft(), false ),
		'terms'   => array( 'Terms & Conditions', nolasharon_terms_draft(), false ),
	);
	foreach ( $pages as $key => list( $label, $content, $approved ) ) {
		$id = nolasharon_policy_page_id( $key );
		if ( ! $id ) {
			$log[] = $label . ': page not found';
			continue;
		}
		$current = (string) get_post_field( 'post_content', $id );
		$outline = '' === trim( wp_strip_all_tags( $current ) )
			|| ( false !== strpos( $current, '[[CONFIRM' ) && false === strpos( $current, '<!-- DRAFT' ) )
			|| false !== strpos( $current, 'Suggested text:' );
		if ( $current === $content ) {
			$log[] = $label . ': already up to date';
		} elseif ( $force || $outline ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => $content,
				)
			);
			update_post_meta( $id, '_wp_page_template', 'page-templates/policy.php' );
			$log[] = $label . ( $approved ? ': written' : ': draft written (hidden until “Wording approved” is ticked)' );
		} else {
			$log[] = $label . ': kept (already written on this site)';
			continue;
		}
		// Only the client's own wording is published; drafts stay hidden until approved.
		if ( $approved ) {
			update_post_meta( $id, 'ns_policy_approved', '1' );
		}
	}

	$notes = array(
		'nolasharon_returns_info'   => 'Exchanges within 48 hours of delivery for damaged, defective or wrong items, with photo or video proof (unboxing video required for delivery complaints). Items must be unworn with tags attached. Sale items are final sale.',
		'nolasharon_trust_delivery' => 'Delivery across Lagos and all 36 states',
	);
	foreach ( $notes as $mod => $value ) {
		if ( '' === trim( (string) get_theme_mod( $mod, '' ) ) ) {
			set_theme_mod( $mod, $value );
			$log[] = 'Customizer: ' . $mod . ' set';
		}
	}

	// Pickup from the two Ikeja stores (client policy). The delivery policy offers it, so checkout must too.
	if ( null === get_theme_mod( 'nolasharon_pickup_enabled', null ) ) {
		set_theme_mod( 'nolasharon_pickup_enabled', true );
		$log[] = 'Store pickup switched on at checkout';
	}
	$branches = ns_get_branches( array( 'include_drafts' => true ) );
	if ( ! wp_list_filter( $branches, array( 'pickup_enabled' => true ) ) ) {
		foreach ( $branches as $branch ) {
			if ( false !== stripos( $branch['area'] . ' ' . $branch['name'], 'ikeja' ) ) {
				update_post_meta( $branch['id'], 'ns_pickup_enabled', '1' );
				$log[] = 'Pickup ticked for ' . $branch['name'];
			}
		}
		$branches = ns_get_branches( array( 'include_drafts' => true ) );
	}
	foreach ( $branches as $branch ) {
		if ( $branch['pickup_enabled'] && '' === trim( (string) get_post_meta( $branch['id'], 'ns_pickup_instructions', true ) ) ) {
			update_post_meta( $branch['id'], 'ns_pickup_instructions', 'Your order will be ready within 24 hours and we will send you a notification. Please bring your Order ID when you come to collect.' );
			$log[] = 'Pickup instructions added for ' . $branch['name'];
		}
	}
	return $log;
}

/**
 * Run the sync once on each site (e.g. the live site after a deploy) and keep the result for
 * the notice below.
 */
function nolasharon_page_content_once() {
	if ( get_option( 'nolasharon_page_content_v1' ) || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	update_option( 'nolasharon_page_content_v1', gmdate( 'c' ) );
	set_transient( 'nolasharon_page_content_notice', nolasharon_sync_page_content(), DAY_IN_SECONDS );
}
add_action( 'admin_init', 'nolasharon_page_content_once' );

/**
 * Tell the shop manager what was written, once.
 */
function nolasharon_page_content_notice() {
	$log = get_transient( 'nolasharon_page_content_notice' );
	if ( ! $log || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	delete_transient( 'nolasharon_page_content_notice' );
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>%1$s</strong> %2$s</p><p>%3$s</p></div>',
		esc_html__( 'Store pages and settings:', 'nolasharon' ),
		esc_html( implode( ' · ', $log ) ),
		esc_html__( 'Privacy Policy and Terms & Conditions are drafts: fill in the [[CONFIRM: …]] items, then tick “Wording approved” on each page to show it to customers.', 'nolasharon' )
	);
}
add_action( 'admin_notices', 'nolasharon_page_content_notice' );
