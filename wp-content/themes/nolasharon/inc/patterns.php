<?php
/**
 * About Us (CONTENT-PAGES.md §1): block patterns built from core blocks with theme classes,
 * the seeded About page, and the render filters that keep unconfirmed copy away from
 * visitors (a block whose text is only a client placeholder renders nothing, and a section
 * marked "ns-needs-content" disappears when only its headings are left).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/* Block markup helpers -------------------------------------------------------------- */

/**
 * Block attributes as a comment JSON string.
 *
 * @param array $attrs Attributes.
 * @return string
 */
function nolasharon_block_attrs( $attrs ) {
	return $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '';
}

/**
 * Group block.
 *
 * @param string $class Class names.
 * @param string $inner Inner blocks.
 * @param string $tag   Tag name.
 * @return string
 */
function nolasharon_b_group( $class, $inner, $tag = 'div' ) {
	$attrs = array( 'className' => $class );
	if ( 'div' !== $tag ) {
		$attrs['tagName'] = $tag;
	}
	return '<!-- wp:group' . nolasharon_block_attrs( $attrs ) . " -->\n<{$tag} class=\"wp-block-group {$class}\">{$inner}</{$tag}>\n<!-- /wp:group -->\n";
}

/**
 * Columns block from column contents.
 *
 * @param string $class   Class names.
 * @param array  $columns Each column's inner blocks (string), or array( class, inner ).
 * @return string
 */
function nolasharon_b_columns( $class, $columns ) {
	$out = '';
	foreach ( $columns as $col ) {
		$c_class = is_array( $col ) ? $col[0] : '';
		$inner   = is_array( $col ) ? $col[1] : $col;
		$out    .= '<!-- wp:column' . nolasharon_block_attrs( $c_class ? array( 'className' => $c_class ) : array() ) . " -->\n<div class=\"wp-block-column" . ( $c_class ? ' ' . $c_class : '' ) . "\">{$inner}</div>\n<!-- /wp:column -->\n";
	}
	return '<!-- wp:columns' . nolasharon_block_attrs( array( 'className' => $class ) ) . " -->\n<div class=\"wp-block-columns {$class}\">{$out}</div>\n<!-- /wp:columns -->\n";
}

/**
 * Heading block.
 *
 * @param int    $level Level.
 * @param string $text  HTML text.
 * @param string $class Class names.
 * @return string
 */
function nolasharon_b_heading( $level, $text, $class = '' ) {
	$attrs = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	return '<!-- wp:heading' . nolasharon_block_attrs( $attrs ) . " -->\n<h{$level} class=\"wp-block-heading" . ( $class ? ' ' . $class : '' ) . "\">{$text}</h{$level}>\n<!-- /wp:heading -->\n";
}

/**
 * Paragraph block.
 *
 * @param string $text  HTML text.
 * @param string $class Class names.
 * @return string
 */
function nolasharon_b_p( $text, $class = '' ) {
	return '<!-- wp:paragraph' . nolasharon_block_attrs( $class ? array( 'className' => $class ) : array() ) . " -->\n<p" . ( $class ? " class=\"{$class}\"" : '' ) . ">{$text}</p>\n<!-- /wp:paragraph -->\n";
}

/**
 * Buttons block.
 *
 * @param array $buttons Each array( label, url, outline? ).
 * @return string
 */
function nolasharon_b_buttons( $buttons ) {
	$out = '';
	foreach ( $buttons as $btn ) {
		$outline = ! empty( $btn[2] );
		$out    .= '<!-- wp:button' . nolasharon_block_attrs( $outline ? array( 'className' => 'is-style-outline' ) : array() ) . " -->\n<div class=\"wp-block-button" . ( $outline ? ' is-style-outline' : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $btn[1] ) . '">' . esc_html( $btn[0] ) . "</a></div>\n<!-- /wp:button -->\n";
	}
	return "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\">{$out}</div>\n<!-- /wp:buttons -->\n";
}

/**
 * Image block.
 *
 * @param string $src   Image URL.
 * @param string $alt   Alt text.
 * @param string $class Class names.
 * @return string
 */
function nolasharon_b_image( $src, $alt, $class ) {
	return '<!-- wp:image' . nolasharon_block_attrs( array( 'className' => $class ) ) . " -->\n<figure class=\"wp-block-image {$class}\"><img src=\"" . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . "\"/></figure>\n<!-- /wp:image -->\n";
}

/* Patterns ----------------------------------------------------------------------------- */

/**
 * The About patterns' markup, keyed by pattern slug. Headline, offer, values and steps
 * wording is a neutral DRAFT for the client to approve; facts are client placeholders.
 *
 * @return array slug => array( title, content )
 */
function nolasharon_about_patterns() {
	$img = get_template_directory_uri() . '/assets/img/';

	$hero = nolasharon_b_group(
		'ab-hero',
		nolasharon_b_columns(
			'ab-hero__inner',
			array(
				array(
					'ab-hero__copy',
					nolasharon_b_p( __( 'About NolaSharon Clothing', 'nolasharon' ), 'ab-eyebrow' )
					. nolasharon_b_heading( 1, __( 'Fashion That Speaks <em>Your Style</em>', 'nolasharon' ), 'ab-hero__title' )
					. nolasharon_b_p( __( 'At NolaSharon Clothing, we believe fashion is more than what you wear. It is a way to express your personality, embrace your confidence, and show up as your unique self.', 'nolasharon' ), 'ab-hero__intro' )
					. nolasharon_b_p( __( 'We are a fashion retail brand offering a variety of fashion products, including clothing, bags, shoes, and more, to help you find pieces that complement your personal style.', 'nolasharon' ), 'ab-hero__intro' )
					. nolasharon_b_p( __( 'Whether you are refreshing your wardrobe, searching for something special, or looking for everyday fashion essentials, we want to make discovering your next favourite piece an enjoyable experience.', 'nolasharon' ), 'ab-hero__intro' )
					. nolasharon_b_buttons(
						array(
							array( __( 'Explore Our Collection', 'nolasharon' ), home_url( '/collections/' ) ),
							array( __( 'Visit Our Store', 'nolasharon' ), home_url( '/store-locations/' ), true ),
						)
					),
				),
				array(
					'ab-hero__media',
					nolasharon_b_image( $img . 'occasion-gown.svg', __( 'NolaSharon Clothing collection', 'nolasharon' ), 'ab-hero__main' )
					. nolasharon_b_image( $img . 'wrap-dress.svg', __( 'A dress from the collection', 'nolasharon' ), 'ab-hero__float' )
					. nolasharon_b_p( __( 'Lagos, Nigeria', 'nolasharon' ), 'ab-hero__badge ns-branch-areas' ),
				),
			)
		)
	);

	$story = nolasharon_b_group(
		'ab-section ab-story ns-needs-content',
		nolasharon_b_columns(
			'ab-story__grid',
			array(
				nolasharon_b_p( __( 'NolaSharon Clothing', 'nolasharon' ), 'ab-eyebrow' )
				. nolasharon_b_heading( 2, __( 'Our Story', 'nolasharon' ), 'ab-h2' )
				. "<!-- wp:quote {\"className\":\"ab-quote\"} -->\n<blockquote class=\"wp-block-quote ab-quote\">" . nolasharon_b_p( __( 'Our journey is one of growth, style, and a commitment to serving our customers.', 'nolasharon' ) ) . "</blockquote>\n<!-- /wp:quote -->\n",
				nolasharon_b_p( __( 'NolaSharon Clothing was established with a passion for fashion and a desire to make a variety of fashion products accessible to our customers.', 'nolasharon' ) )
				. nolasharon_b_p( __( 'Over time, we have continued to build our presence and serve customers through our physical stores. As we grow, we are expanding our reach by bringing the NolaSharon Clothing shopping experience online, making it easier for customers to explore our collections and shop from the comfort of their homes.', 'nolasharon' ) )
				. nolasharon_b_p( __( 'With our existing stores in Ikeja and our new branch opening in Ogudu, Lagos, we are excited about reaching more fashion lovers and making it more convenient for them to connect with us.', 'nolasharon' ) ),
			)
		)
	);

	$offer_card = function ( $tone, $image, $name, $url, $line ) use ( $img ) {
		return nolasharon_b_group(
			'ab-offer__card ab-offer__card--' . $tone,
			nolasharon_b_image( $img . $image, '', 'ab-offer__img' )
			. nolasharon_b_heading( 3, '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>', 'ab-offer__name' )
			. nolasharon_b_p( $line )
		);
	};
	$offer      = nolasharon_b_group(
		'ab-section ab-offer',
		nolasharon_b_p( __( 'Our collection', 'nolasharon' ), 'ab-eyebrow' )
		. nolasharon_b_heading( 2, __( 'What We Offer', 'nolasharon' ), 'ab-h2' )
		. nolasharon_b_p( __( 'At NolaSharon Clothing, you can explore a range of fashion products to suit different preferences and occasions. Our collection includes:', 'nolasharon' ), 'ab-lead' )
		. nolasharon_b_group(
			'ab-offer__grid',
			$offer_card( 'brand', 'two-piece.svg', __( 'Clothing', 'nolasharon' ), home_url( '/collections/' ), __( 'Explore fashion pieces that help you express your individual style.', 'nolasharon' ) )
			. $offer_card( 'dark', 'tote-bag.svg', __( 'Bags', 'nolasharon' ), home_url( '/product-category/bags/' ), __( 'Discover bags to complement your outfits and everyday needs.', 'nolasharon' ) )
			. $offer_card( 'soft', 'block-heels.svg', __( 'Shoes', 'nolasharon' ), home_url( '/product-category/shoes/' ), __( 'Find footwear to complete your look.', 'nolasharon' ) )
			. nolasharon_b_group(
				'ab-offer__card ab-offer__card--light',
				nolasharon_b_heading( 3, __( 'More Fashion Finds', 'nolasharon' ), 'ab-offer__name' )
				. nolasharon_b_p( __( 'Explore additional fashion products as our collection grows.', 'nolasharon' ) )
				. nolasharon_b_buttons( array( array( __( 'Shop Now', 'nolasharon' ), home_url( '/shop/' ) ) ) )
			)
		)
		. nolasharon_b_p( __( 'We aim to make your shopping experience convenient by bringing different fashion finds together in one place.', 'nolasharon' ), 'ab-offer__note' )
	);

	// The tiles use the client's own phrases from "Our Commitment to You".
	$value  = function ( $num, $key, $title ) {
		return nolasharon_b_group(
			'ab-value ab-value--' . $key,
			nolasharon_b_p( $num, 'ab-value__num' )
			. nolasharon_b_heading( 3, $title, 'ab-value__title' )
		);
	};
	$values = nolasharon_b_group(
		'ab-section ab-values',
		nolasharon_b_columns(
			'ab-commit__grid',
			array(
				nolasharon_b_p( __( 'Our commitment', 'nolasharon' ), 'ab-eyebrow' )
				. nolasharon_b_heading( 2, __( 'Our Commitment to You', 'nolasharon' ), 'ab-h2' )
				. nolasharon_b_p( __( 'We understand that shopping is about more than choosing a product. It is also about feeling confident in your decision and enjoying the experience.', 'nolasharon' ), 'ab-lead' ),
				nolasharon_b_p( __( 'That is why we are committed to providing a welcoming shopping experience, presenting our products clearly, and making it easier for you to discover items you love.', 'nolasharon' ) )
				. nolasharon_b_p( __( 'As we continue to grow, our goal is to build lasting relationships with our customers through attentive service, convenient shopping, and a continually evolving collection.', 'nolasharon' ) ),
			)
		)
		. nolasharon_b_group(
			'ab-values__grid',
			$value( '01', 'style', __( 'A welcoming shopping experience', 'nolasharon' ) )
			. $value( '02', 'quality', __( 'Products presented clearly', 'nolasharon' ) )
			. $value( '03', 'convenience', __( 'Convenient shopping', 'nolasharon' ) )
			. $value( '04', 'trust', __( 'Attentive service', 'nolasharon' ) )
		)
	);

	$step  = function ( $num, $title, $line ) {
		return nolasharon_b_group(
			'ab-step',
			nolasharon_b_p( $num, 'ab-step__num' )
			. nolasharon_b_heading( 3, $title, 'ab-step__title' )
			. nolasharon_b_p( $line )
		);
	};
	$steps = nolasharon_b_group(
		'ab-section ab-steps',
		nolasharon_b_p( __( 'Shopping with us', 'nolasharon' ), 'ab-eyebrow' )
		. nolasharon_b_heading( 2, __( 'Simple from start to finish', 'nolasharon' ), 'ab-h2' )
		. nolasharon_b_group(
			'ab-steps__grid',
			$step( '1', __( 'Browse', 'nolasharon' ), __( 'Explore the collection online by category.', 'nolasharon' ) )
			. $step( '2', __( 'Choose', 'nolasharon' ), __( 'Pick your size and colour on the product page and add it to your cart.', 'nolasharon' ) )
			. $step( '3', __( 'Pay securely', 'nolasharon' ), __( 'Check out online and see the full amount before you pay.', 'nolasharon' ) )
			. $step( '4', __( 'Receive', 'nolasharon' ), __( 'Choose delivery to your address or, where offered, collection from a store.', 'nolasharon' ) )
		)
	);

	$cta = nolasharon_b_group(
		'ab-cta',
		nolasharon_b_heading( 2, __( 'Get in Touch', 'nolasharon' ), 'ab-cta__title' )
		. nolasharon_b_p( __( 'Have questions about our products or need help locating a store? Contact us.', 'nolasharon' ) )
		// Filled with the phone and email from the Customizer when the page is shown.
		. nolasharon_b_p( '', 'ab-cta__contact' )
		. nolasharon_b_buttons(
			array(
				array( __( 'Contact Us', 'nolasharon' ), home_url( '/contact/' ) ),
				array( __( 'Shop Now', 'nolasharon' ), home_url( '/shop/' ), true ),
			)
		)
		. nolasharon_b_p( __( 'NolaSharon Clothing — Your Style, Your Way', 'nolasharon' ), 'ab-cta__tagline' )
	);

	return array(
		'about-hero'   => array( __( 'About: hero', 'nolasharon' ), $hero ),
		'about-story'  => array( __( 'About: our story', 'nolasharon' ), $story ),
		'about-offer'  => array( __( 'About: what we offer', 'nolasharon' ), $offer ),
		'about-values' => array( __( 'About: our values', 'nolasharon' ), $values ),
		'about-steps'  => array( __( 'About: shopping steps', 'nolasharon' ), $steps ),
		'about-cta'    => array( __( 'About: call to action', 'nolasharon' ), $cta ),
	);
}

/**
 * Register the patterns under "NolaSharon".
 */
function nolasharon_register_patterns() {
	register_block_pattern_category( 'nolasharon', array( 'label' => __( 'NolaSharon', 'nolasharon' ) ) );
	foreach ( nolasharon_about_patterns() as $slug => $pattern ) {
		register_block_pattern(
			'nolasharon/' . $slug,
			array(
				'title'      => $pattern[0],
				'categories' => array( 'nolasharon' ),
				'content'    => $pattern[1],
			)
		);
	}
}
add_action( 'init', 'nolasharon_register_patterns' );

/**
 * Build the About page from the patterns once, on the About template. Existing content is
 * replaced only when it is still the old placeholder-only seed.
 *
 * @param bool $force Rebuild even if done before.
 */
function nolasharon_seed_about_page( $force = false ) {
	if ( ! $force && get_option( 'nolasharon_about_v2' ) ) {
		return;
	}
	$page = get_page_by_path( 'about-us' );
	// v2 (2026-10-27): the client's About copy replaces the placeholder version, unless the page has been edited since.
	if ( $page && ( $force || ! nolasharon_has_text( nolasharon_strip_placeholders( preg_replace( '~<h2[^>]*>.*?</h2>~is', '', $page->post_content ) ) ) || false !== strpos( $page->post_content, 'Our stores' ) || false !== strpos( $page->post_content, '[[CONFIRM: brand story, paragraph 1]]' ) ) ) {
		wp_update_post(
			array(
				'ID'           => $page->ID,
				'post_content' => "<!-- About copy from the client (2026-10-27). DRAFT: client to approve the shopping steps wording -->\n" . implode( "\n", wp_list_pluck( nolasharon_about_patterns(), 1 ) ),
			)
		);
	}
	if ( $page ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-templates/about.php' );
	}
	nolasharon_seed_about_facts();
	update_option( 'nolasharon_about_v2', 1 );
}

/**
 * Client-supplied facts from the About copy (2026-10-27), filled in only where still empty:
 * the homepage brand introduction and the Ogudu branch address.
 */
function nolasharon_seed_about_facts() {
	if ( ! get_theme_mod( 'nolasharon_brand_title' ) ) {
		set_theme_mod( 'nolasharon_brand_title', 'Fashion That Speaks Your Style' );
	}
	if ( ! get_theme_mod( 'nolasharon_brand_text' ) ) {
		set_theme_mod( 'nolasharon_brand_text', "At NolaSharon Clothing, we believe fashion is more than what you wear. It is a way to express your personality, embrace your confidence, and show up as your unique self.\n\nWe are a fashion retail brand offering a variety of fashion products, including clothing, bags, shoes, and more, to help you find pieces that complement your personal style." );
	}
	$ogudu = get_posts(
		array(
			'post_type'      => 'ns_branch',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => 'ns_area', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off, a handful of posts.
			'meta_value'     => 'Ogudu', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $ogudu && ! get_post_meta( $ogudu[0]->ID, 'ns_address', true ) ) {
		update_post_meta( $ogudu[0]->ID, 'ns_address', '134 Ogudu Road, Ogudu, Lagos' );
		update_post_meta( $ogudu[0]->ID, 'ns_landmark', 'By Ogudu Roundabout' );
	}
}
add_action( 'after_switch_theme', 'nolasharon_seed_about_page', 20 );
add_action( 'admin_init', 'nolasharon_seed_about_page' );

/* Rendering ---------------------------------------------------------------------------- */

/**
 * Whether the request is a page on the About template.
 *
 * @return bool
 */
function nolasharon_is_about() {
	return ! is_admin() && is_page_template( 'page-templates/about.php' );
}

/**
 * For visitors: drop blocks whose text is only a placeholder and sections marked
 * "ns-needs-content" that have nothing confirmed beyond their headings. Fills the
 * "ns-branch-areas" badge with the branch areas, and puts the stores section before the CTA.
 *
 * @param string $html  Rendered block.
 * @param array  $block Block.
 * @return string
 */
function nolasharon_about_render_block( $html, $block ) {
	if ( ! nolasharon_is_about() ) {
		return $html;
	}
	$class = isset( $block['attrs']['className'] ) ? ' ' . $block['attrs']['className'] . ' ' : '';

	if ( false !== strpos( $class, ' ns-branch-areas ' ) ) {
		$areas = array_unique( array_filter( wp_list_pluck( ns_get_branches(), 'area' ) ) );
		if ( $areas ) {
			$html = preg_replace( '~</p>~', ' · ' . esc_html( implode( ' · ', $areas ) ) . '</p>', $html, 1 );
		}
	}

	if ( ! nolasharon_sees_placeholders() && false !== strpos( $class, ' ns-needs-content ' ) ) {
		// Inner blocks have already dropped their placeholders, so only real copy is left.
		$body = preg_replace( '~<h[1-6][^>]*>.*?</h[1-6]>|<p class="ab-eyebrow[^"]*">.*?</p>|<figure[^>]*>.*?</figure>~is', '', $html );
		if ( ! nolasharon_has_text( nolasharon_strip_placeholders( $body ) ) ) {
			return '';
		}
	}

	if ( ! nolasharon_sees_placeholders() && false !== strpos( $html, '[[CONFIRM' ) ) {
		$html = preg_replace( '~<(p|h[1-6]|cite)[^>]*>\s*\[\[CONFIRM:[^\]]*\]\]\s*</\1>~i', '', $html );
		$html = preg_replace( '~\[\[CONFIRM:[^\]]*\]\]~', '', $html );
		if ( 'core/quote' === $block['blockName'] && ! nolasharon_has_text( $html ) ) {
			return '';
		}
	}

	if ( false !== strpos( $class, ' ab-cta__contact ' ) ) {
		$links = array();
		foreach ( nolasharon_contact_channels() as $channel ) {
			if ( 'whatsapp' !== $channel['key'] ) {
				$links[] = '<span>' . esc_html( 'phone' === $channel['key'] ? __( 'Phone', 'nolasharon' ) : $channel['title'] ) . ': <a href="' . esc_url( $channel['url'], array( 'tel', 'mailto' ) ) . '">' . esc_html( $channel['value'] ) . '</a></span>';
			}
		}
		return $links ? '<p class="ab-cta__contact">' . implode( ' ', $links ) . '</p>' : '';
	}

	if ( false !== strpos( $class, ' ab-cta ' ) ) {
		$html = nolasharon_about_stores() . $html;
	}
	return $html;
}
add_filter( 'render_block', 'nolasharon_about_render_block', 10, 2 );

/**
 * The "Come and see us in Lagos" section from the Branch posts (printed once).
 *
 * @return string
 */
function nolasharon_about_stores() {
	static $done = false;
	if ( $done ) {
		return '';
	}
	$done = true;
	ob_start();
	get_template_part( 'template-parts/about-stores' );
	return ob_get_clean();
}

/**
 * About page styles.
 */
function nolasharon_about_assets() {
	if ( nolasharon_is_about() ) {
		wp_enqueue_style( 'nolasharon-content', get_template_directory_uri() . '/assets/css/content.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'nolasharon_about_assets', 20 );
