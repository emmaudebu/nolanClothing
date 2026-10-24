<?php
/**
 * Draft wording for the Privacy Policy and Terms & Conditions (run with
 * `studio wp eval-file tools/seed-policy-drafts.php`, or `wp eval-file` on the live site).
 *
 * DRAFT for NolaSharon Clothing and their legal adviser to review. Facts that come from how
 * the site is built are written in; business facts and legal choices are [[CONFIRM: ...]]
 * items. The pages stay hidden from customers until "Wording approved" is ticked on each page
 * (Policy box), so running this never publishes unapproved terms. Re-running overwrites both
 * pages' content, so only run it again before the client has edited them.
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
$link = function ( $key, $text ) {
	return '<a href="' . esc_url( nolasharon_policy_url( $key ) ) . '">' . $text . '</a>';
};
$contact = '<a href="' . esc_url( nolasharon_page_url( 'contact' ) ) . '">Contact Us</a>';
$draft   = "<!-- DRAFT: client to approve. Legal review recommended before ticking \"Wording approved\". -->\n";

/* Privacy Policy ---------------------------------------------------------------------- */

$privacy = $draft
	. $h( 'Who we are' )
	. $p( 'This Privacy Policy explains how NolaSharon Clothing (“we”, “us”, “our”) collects, uses, shares and protects your personal information when you visit this website or buy from us online.' )
	. $p( 'NolaSharon Clothing is [[CONFIRM: registered business name and CAC registration number (RC or BN)]], of [[CONFIRM: registered business address]]. We are responsible for the personal information described in this policy, and we handle it in line with the Nigeria Data Protection Act 2023.' )
	. $p( 'For any question about your privacy, email [[CONFIRM: privacy contact email]] or use our ' . $contact . ' page.' )

	. $h( 'Information we collect' )
	. $p( 'We only ask for the information we need to sell to you, deliver your order and help you.' )
	. $ul(
		array(
			'<strong>When you place an order:</strong> your name, email address, phone number, delivery address (street address, city or area, and state), any delivery instructions you add, the items you order and the amount paid. If you choose store pickup, we record the branch you choose.',
			'<strong>If you create an account:</strong> your name, email address, phone number (optional) and a password, which is stored in a scrambled (hashed) form that we cannot read. Your account also keeps your order history, saved addresses and wishlist.',
			'<strong>When you contact us:</strong> through the contact form, your name, email address, phone number (optional), what your message is about, your order number (if it is about an order) and your message. We do not store your IP address with contact form messages. If you call, email or message us on WhatsApp, we receive the details you share there.',
			'<strong>When you track an order:</strong> the order number and email address you enter, used only to find your order. To stop misuse, we keep a coded (hashed) version of your IP address for up to 10 minutes and then delete it.',
			'<strong>When you pay:</strong> you enter your card or bank details on our payment provider’s secure page. We never see or store your full card details; we receive confirmation of payment and a transaction reference.',
			'<strong>Automatically:</strong> small files and browser storage that make the site work (see “Cookies and similar technologies”), and standard technical logs kept by our hosting provider, such as IP address, browser type and pages requested. [[CONFIRM: hosting provider and how long server logs are kept]]',
		)
	)
	. $p( 'We do not currently use analytics or advertising tracking on this website. If we add them, we will update this policy first.' )

	. $h( 'Cookies and similar technologies' )
	. $p( 'We use only the cookies and browser storage needed for the website to work. We do not use advertising cookies.' )
	. $ul(
		array(
			'<strong>Shopping cart:</strong> WooCommerce cookies (such as <code>woocommerce_cart_hash</code>, <code>woocommerce_items_in_cart</code> and <code>wp_woocommerce_session_</code>) remember what is in your cart. They last up to two days.',
			'<strong>Signing in:</strong> if you sign in to your account, WordPress sets cookies (such as <code>wordpress_logged_in_</code>) to keep you signed in. They end when you sign out or after a set period.',
			'<strong>Wishlist:</strong> the <code>ns_wishlist</code> cookie remembers the items you save, for up to 90 days.',
			'<strong>Browser storage:</strong> your browser remembers if you closed the announcement bar and which shop grid layout you prefer. During checkout and sign-in, some details are kept in your browser only until you close the tab.',
		)
	)
	. $p( 'You can delete or block cookies in your browser settings, but your cart, sign-in and wishlist will not work without them.' )

	. $h( 'How we use your information' )
	. $p( 'We use your information to:' )
	. $ul(
		array(
			'process, deliver or prepare your order for pickup, and take payment (to perform our contract with you);',
			'send you emails about your order, such as confirmations and status updates, and account emails such as password resets (to perform our contract with you);',
			'reply to your messages and give customer support (our legitimate interest in helping customers, or to perform our contract with you);',
			'prevent fraud and misuse of the website and keep it secure (our legitimate interest);',
			'keep records of sales and transactions as required by law (legal obligation).',
		)
	)
	. $p( 'We do not send marketing emails or messages. If we offer them in future, we will only send them if you have agreed, and you will be able to unsubscribe at any time.' )

	. $h( 'Payments' )
	. $p( 'Online payments are handled by [[CONFIRM: payment provider, e.g. Paystack]]. When you pay, you share your payment details directly with them, and they process them under their own privacy policy and security standards. This website does not store your card details.' )

	. $h( 'Sharing your information' )
	. $p( 'We do not sell your personal information. We share it only when needed to run our business, with:' )
	. $ul(
		array(
			'<strong>our payment provider</strong>, to take payment (see “Payments”);',
			'<strong>delivery partners</strong>, who receive your name, phone number, delivery address and order details so they can deliver to you. [[CONFIRM: courier or logistics provider(s) used, if any]]',
			'<strong>service providers</strong> who host the website and send our emails, under agreements that require them to protect your information. [[CONFIRM: hosting provider and email delivery service]]',
			'<strong>authorities or professional advisers</strong>, where the law requires it or to protect our rights.',
		)
	)
	. $p( 'If you message us on WhatsApp, that conversation is also handled by WhatsApp (Meta) under its own terms and privacy policy.' )
	. $p( 'Some parts of our pages load from outside services: our fonts come from Google Fonts, and the store map uses map images from OpenStreetMap and a mapping library from Cloudflare (cdnjs). When your browser loads these, the provider receives your IP address and basic browser information.' )
	. $p( '[[CONFIRM: whether any provider stores data outside Nigeria, and the safeguards relied on for those transfers]]' )

	. $h( 'Storage and security' )
	. $p( 'We protect your information with an encrypted (HTTPS) connection, by limiting access to staff who need it for their work, and by keeping our website software up to date. Account passwords are stored in hashed form, and card details are handled by our payment provider rather than stored on our website.' )
	. $p( 'We keep personal information only for as long as we need it:' )
	. $ul(
		array(
			'order records: [[CONFIRM: how long order and transaction records are kept, e.g. for tax and accounting purposes]];',
			'contact form messages: [[CONFIRM: how long contact messages are kept]];',
			'accounts: until you ask us to delete your account, after which we keep only the order records we are required to keep.',
		)
	)

	. $h( 'Your rights' )
	. $p( 'Under the Nigeria Data Protection Act 2023, you have the right to:' )
	. $ul(
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
	. $p( 'If you have an account, you can update your details in My Account. For anything else, email [[CONFIRM: privacy contact email]]. We may need to confirm your identity first, and we will reply within [[CONFIRM: response time for privacy requests]]. If you are not happy with how we have handled your information, you can complain to the Nigeria Data Protection Commission (NDPC).' )
	. $p( 'Our website is meant for adults. If you are under 18, please shop with the help of a parent or guardian.' )

	. $h( 'Changes and contact' )
	. $p( 'We may update this policy when our website or the way we work changes. The “Last updated” date at the top of this page shows when it last changed; for important changes, we will also let customers know on the website.' )
	. $p( 'Questions about this policy or your information: email [[CONFIRM: privacy contact email]], or use our ' . $contact . ' page.' );

/* Terms & Conditions ------------------------------------------------------------------ */

$terms = $draft
	. $h( 'About these terms' )
	. $p( 'These terms apply when you use this website and buy from NolaSharon Clothing (“we”, “us”, “our”), which is [[CONFIRM: registered business name and CAC registration number (RC or BN)]], of [[CONFIRM: registered business address]].' )
	. $p( 'By placing an order, you agree to these terms, together with our ' . $link( 'delivery', 'Delivery &amp; Shipping Policy' ) . ', ' . $link( 'returns', 'Returns &amp; Exchange Policy' ) . ' and ' . $link( 'privacy', 'Privacy Policy' ) . '. Please read them before you order.' )
	. $p( 'We currently sell and deliver to customers in Nigeria only.' )

	. $h( 'Orders' )
	. $p( 'To order, choose your size and colour, add items to your cart and complete checkout. You can check out as a guest or with an account. Each order gets an order number (for example, NS-1042), which you can use to track it.' )
	. $p( 'After you place an order, we send a confirmation email to the address you give. This email confirms that we have received your order. [[CONFIRM: when an order is accepted, e.g. when payment is confirmed or when the order is dispatched]]' )
	. $p( 'We may decline or cancel an order if an item turns out to be unavailable, if a product was listed with the wrong price or details, if payment is not completed, or if we suspect fraud. If this happens after you have paid, we will refund the full amount you paid.' )

	. $h( 'Pricing' )
	. $p( 'Prices are shown in Nigerian naira (₦). [[CONFIRM: whether prices include VAT]] Delivery fees are shown at checkout before you pay, and the total you pay is shown before you confirm your order.' )
	. $p( 'Sale prices apply only for the period shown. If a product is listed at the wrong price by mistake, we will contact you before processing the order, and you can choose to continue at the correct price or cancel for a full refund.' )

	. $h( 'Product availability' )
	. $p( 'Stock levels on the website update as orders are placed. Sizes and colours that are sold out are marked and cannot be added to your cart. [[CONFIRM: whether online stock is shared with the stores, and what happens if an item sells out in store after you order]]' )
	. $p( 'We try to show our products accurately, but colours can look slightly different on different screens. Please check the size guide on the product page before you order; if you are unsure about a size, contact us first.' )

	. $h( 'Payment' )
	. $p( 'Payment is made online at checkout through [[CONFIRM: payment provider and methods accepted, e.g. Paystack: card, bank transfer, USSD]]. We start preparing your order once payment is confirmed. We do not store your card details.' )
	. $p( 'Orders that are not paid show as “Pending payment”. [[CONFIRM: whether and when unpaid orders are cancelled]]' )

	. $h( 'Delivery' )
	. $p( 'Delivery areas, fees and timelines are set out in our ' . $link( 'delivery', 'Delivery &amp; Shipping Policy' ) . '. Please make sure your delivery address and phone number are correct, as we cannot be responsible for delays caused by incorrect details.' )
	. $p( 'Where store pickup is offered, you can choose a branch at checkout. [[CONFIRM: what customers must bring to collect an order, and how long orders are held]]' )

	. $h( 'Cancellations' )
	. $p( '[[CONFIRM: whether customers can cancel an order, up to what point (e.g. before dispatch), how to request it, and how refunds for cancelled orders are made]]' )
	. $p( 'We may cancel an order for the reasons listed under “Orders”. If we do, we will tell you and refund the full amount you paid.' )

	. $h( 'Returns and exchanges' )
	. $p( 'Returns and exchanges are covered by our ' . $link( 'returns', 'Returns &amp; Exchange Policy' ) . '. Nothing in these terms affects your rights under Nigerian consumer protection law, including the Federal Competition and Consumer Protection Act 2018.' )

	. $h( 'Customer responsibilities' )
	. $p( 'When you use our website, you agree to:' )
	. $ul(
		array(
			'give accurate and complete details when you order or create an account;',
			'keep your account password private, and tell us if you think someone else has used your account;',
			'use the website lawfully, and not try to disrupt it, gain unauthorised access to it, or misuse it in any other way.',
		)
	)
	. $p( 'All content on this website, including photos, text, logos and designs, belongs to NolaSharon Clothing or is used with permission. You may not copy or use it for commercial purposes without our written consent.' )

	. $h( 'Other terms' )
	. $p( '[[CONFIRM: limitation of liability wording, after professional advice]] Nothing in these terms limits any liability that cannot be limited by law.' )
	. $p( 'These terms are governed by [[CONFIRM: governing law and courts, e.g. the laws of the Federal Republic of Nigeria and the courts of Lagos State]].' )
	. $p( 'We may update these terms from time to time. The version on this page when you place an order is the one that applies to that order. If any part of these terms is found to be invalid, the rest still applies.' )
	. $p( 'Questions about these terms: use our ' . $contact . ' page or email [[CONFIRM: customer service email]].' );

/* Save ---------------------------------------------------------------------------------- */

foreach ( array(
	'privacy' => $privacy,
	'terms'   => $terms,
) as $key => $content ) {
	$id = nolasharon_policy_page_id( $key );
	if ( ! $id ) {
		WP_CLI::warning( "No {$key} page found." );
		continue;
	}
	wp_update_post(
		array(
			'ID'           => $id,
			'post_content' => $content,
		)
	);
	// Drafts are never shown to customers until the client approves them.
	update_post_meta( $id, 'ns_policy_approved', '' );
	update_post_meta( $id, '_wp_page_template', 'page-templates/policy.php' );
	WP_CLI::success( "Draft written to page {$id} (" . get_permalink( $id ) . ')' );
}
