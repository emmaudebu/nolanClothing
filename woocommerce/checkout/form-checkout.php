<?php
/**
 * Checkout form: ONE WooCommerce form shown as three steps (Contact, Delivery, Payment)
 * beside the order summary (CHECKOUT.md §1–§4). checkout.js turns the sections into a
 * stepper; without JavaScript every step stays open and the order can still be placed.
 *
 * Override of WooCommerce templates/checkout/form-checkout.php, version 9.4.0 (checked
 * against WooCommerce 11.2.1). The original's hooks are all fired, except that the fields are
 * printed here instead of through woocommerce_checkout_billing / woocommerce_checkout_shipping
 * (their templates' hooks are fired in place). Ship to billing address only, so there is no
 * separate shipping form.
 *
 * @package NolaSharon
 * @var WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_fields   = $checkout->get_checkout_fields();
$nolasharon_billing  = isset( $nolasharon_fields['billing'] ) ? $nolasharon_fields['billing'] : array();
$nolasharon_address  = array_intersect_key( $nolasharon_billing, array_flip( array( 'billing_country', 'billing_address_1', 'billing_city', 'billing_state' ) ) );
$nolasharon_contact  = array_diff_key( $nolasharon_billing, $nolasharon_address );
$nolasharon_branches = nolasharon_pickup_branches();
$nolasharon_pickup   = nolasharon_is_pickup_method( nolasharon_chosen_method() ) && $nolasharon_branches;
$nolasharon_arrow    = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';

$nolasharon_print_fields = function ( $fields ) use ( $checkout ) {
	foreach ( $fields as $key => $field ) {
		woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
	}
};
?>
<div class="ns-co" data-ns-checkout>
	<div class="ns-co__top ns-in">
		<h1 class="ns-co__title"><?php esc_html_e( 'Checkout', 'nolasharon' ); ?></h1>
		<ol class="ns-stepper" aria-label="<?php esc_attr_e( 'Checkout progress', 'nolasharon' ); ?>" data-stepper>
			<?php
			foreach ( array(
				1 => __( 'Contact', 'nolasharon' ),
				2 => __( 'Delivery', 'nolasharon' ),
				3 => __( 'Payment', 'nolasharon' ),
			) as $nolasharon_n => $nolasharon_label ) :
				?>
				<li class="ns-stepper__item<?php echo 1 === $nolasharon_n ? ' is-active' : ''; ?>" data-stepper-item="<?php echo esc_attr( $nolasharon_n ); ?>"<?php echo 1 === $nolasharon_n ? ' aria-current="step"' : ''; ?>>
					<span class="ns-stepper__dot" aria-hidden="true">
						<span class="ns-stepper__num"><?php echo esc_html( $nolasharon_n ); ?></span>
						<svg class="ns-stepper__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" focusable="false"><path d="M5 12l5 5L20 7"/></svg>
					</span>
					<span class="ns-stepper__label"><?php echo esc_html( $nolasharon_label ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>

	<?php
	do_action( 'woocommerce_before_checkout_form', $checkout );

	// If checkout registration is disabled and not logged in, the user cannot checkout.
	if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
		echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
		echo '</div>';
		return;
	}
	?>

	<form name="checkout" method="post" class="checkout woocommerce-checkout ns-co__form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">
		<div class="ns-co__grid">

			<div class="ns-co__steps">
				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<?php // Step 1: contact details. ?>
				<section class="ns-step is-open ns-in ns-in--1" id="ns-step-1" data-step="1" aria-labelledby="ns-step-1-title">
					<div class="ns-step__head">
						<h2 class="ns-step__title" id="ns-step-1-title" tabindex="-1"><?php esc_html_e( '1. Contact details', 'nolasharon' ); ?></h2>
						<button type="button" class="ns-step__edit" data-edit-step="1" hidden><?php esc_html_e( 'Edit', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( 'contact details', 'nolasharon' ); ?></span></button>
					</div>
					<div class="ns-step__summary" data-summary="1" hidden><p></p></div>
					<div class="ns-step__panel" data-panel="1">
						<div class="ns-step__inner">
							<div class="ns-step__body">
								<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>
								<div class="ns-fields">
									<?php $nolasharon_print_fields( $nolasharon_contact ); ?>
								</div>
								<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>

								<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
									<div class="woocommerce-account-fields ns-account">
										<?php if ( ! $checkout->is_registration_required() ) : ?>
											<p class="form-row form-row-wide create-account">
												<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
													<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
												</label>
											</p>
										<?php endif; ?>
										<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>
										<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
											<div class="create-account">
												<?php $nolasharon_print_fields( $checkout->get_checkout_fields( 'account' ) ); ?>
												<div class="clear"></div>
											</div>
										<?php endif; ?>
										<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
									</div>
								<?php endif; ?>

								<p class="ns-help"><?php esc_html_e( 'We’ll use these to send your order confirmation and updates.', 'nolasharon' ); ?></p>
								<button type="button" class="ns-btn ns-continue" data-next-step="2"><?php esc_html_e( 'Continue to delivery', 'nolasharon' ); ?><?php echo $nolasharon_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></button>
							</div>
						</div>
					</div>
				</section>

				<?php // Step 2: delivery or store pickup. ?>
				<section class="ns-step ns-in ns-in--2" id="ns-step-2" data-step="2" aria-labelledby="ns-step-2-title">
					<div class="ns-step__head">
						<h2 class="ns-step__title" id="ns-step-2-title" tabindex="-1">
							<?php echo esc_html( $nolasharon_branches ? __( '2. Delivery or pickup', 'nolasharon' ) : __( '2. Delivery', 'nolasharon' ) ); ?>
						</h2>
						<button type="button" class="ns-step__edit" data-edit-step="2" hidden><?php esc_html_e( 'Edit', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( 'delivery details', 'nolasharon' ); ?></span></button>
					</div>
					<div class="ns-step__summary" data-summary="2" hidden><p></p></div>
					<div class="ns-step__panel" data-panel="2">
						<div class="ns-step__inner">
							<div class="ns-step__body">
								<?php if ( $nolasharon_branches ) : ?>
									<div class="ns-toggle<?php echo $nolasharon_pickup ? ' is-pickup' : ''; ?>" role="group" aria-label="<?php esc_attr_e( 'How would you like to get your order?', 'nolasharon' ); ?>" data-mode-toggle hidden>
										<span class="ns-toggle__thumb" aria-hidden="true"></span>
										<button type="button" class="ns-toggle__btn" data-mode="delivery" aria-pressed="<?php echo $nolasharon_pickup ? 'false' : 'true'; ?>"><?php echo nolasharon_icon( 'truck', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Home delivery', 'nolasharon' ); ?></button>
										<button type="button" class="ns-toggle__btn" data-mode="pickup" aria-pressed="<?php echo $nolasharon_pickup ? 'true' : 'false'; ?>"><?php echo nolasharon_icon( 'store', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Store pickup', 'nolasharon' ); ?></button>
									</div>
								<?php endif; ?>

								<div class="ns-mode" data-mode-panel="delivery">
									<div class="ns-mode__inner">
										<div class="ns-fields ns-fields--address">
											<?php $nolasharon_print_fields( $nolasharon_address ); ?>
										</div>

										<p class="ns-label" id="ns-areas-label"><?php esc_html_e( 'Delivery area', 'nolasharon' ); ?></p>
										<div class="form-row ns-areas" id="shipping_method_field">
											<?php nolasharon_checkout_shipping_options(); ?>
										</div>

										<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>
										<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
											<div class="ns-fields woocommerce-additional-fields">
												<?php $nolasharon_print_fields( $checkout->get_checkout_fields( 'order' ) ); ?>
											</div>
										<?php endif; ?>
										<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
									</div>
								</div>

								<?php if ( $nolasharon_branches ) : ?>
									<div class="ns-mode" data-mode-panel="pickup">
										<div class="ns-mode__inner">
											<?php // DRAFT: client to approve. ?>
											<p class="ns-mode__intro" id="ns-branches-label"><?php esc_html_e( 'Collect your order from any of our Lagos stores. Our Ikeja stores remain open, and Ogudu is our newest branch.', 'nolasharon' ); ?></p>
											<?php nolasharon_checkout_branch_options(); ?>
											<?php // [[CONFIRM: pickup process]] ?>
											<p class="ns-help"><?php esc_html_e( 'Our team will confirm when your order is ready to collect.', 'nolasharon' ); ?></p>
										</div>
									</div>
								<?php endif; ?>

								<button type="button" class="ns-btn ns-continue" data-next-step="3"><?php esc_html_e( 'Continue to payment', 'nolasharon' ); ?><?php echo $nolasharon_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></button>
							</div>
						</div>
					</div>
				</section>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				<?php // Step 3: payment. ?>
				<section class="ns-step ns-in ns-in--3" id="ns-step-3" data-step="3" aria-labelledby="ns-step-3-title">
					<div class="ns-step__head">
						<h2 class="ns-step__title" id="ns-step-3-title" tabindex="-1"><?php esc_html_e( '3. Payment', 'nolasharon' ); ?></h2>
					</div>
					<div class="ns-step__panel" data-panel="3">
						<div class="ns-step__inner">
							<div class="ns-step__body">
								<?php woocommerce_checkout_payment(); ?>
							</div>
						</div>
					</div>
				</section>
			</div>

			<?php // Order summary: beside the steps on desktop, a collapsible bar above them on mobile. ?>
			<aside class="ns-summary ns-in ns-in--2" aria-labelledby="ns-summary-title">
				<button type="button" class="ns-summary__bar" aria-expanded="false" aria-controls="ns-summary-panel" data-summary-toggle hidden>
					<?php echo nolasharon_icon( 'cart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="ns-summary__bar-label" data-label-show="<?php esc_attr_e( 'Show order summary', 'nolasharon' ); ?>" data-label-hide="<?php esc_attr_e( 'Hide order summary', 'nolasharon' ); ?>"><?php esc_html_e( 'Show order summary', 'nolasharon' ); ?></span>
					<span class="ns-summary__bar-total" data-summary-total><?php echo wp_kses_post( WC()->cart->get_total() ); ?></span>
					<?php echo nolasharon_icon( 'chevron', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<div class="ns-summary__panel" id="ns-summary-panel">
					<div class="ns-summary__inner">
						<div class="ns-summary__card">
							<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
							<div class="ns-summary__head">
								<h2 class="ns-summary__title" id="ns-summary-title"><?php esc_html_e( 'Order summary', 'nolasharon' ); ?></h2>
								<span class="ns-summary__count" data-summary-count>
									<?php
									$nolasharon_count = WC()->cart->get_cart_contents_count();
									/* translators: %s: number of items. */
									echo esc_html( sprintf( _n( '%s item', '%s items', $nolasharon_count, 'nolasharon' ), number_format_i18n( $nolasharon_count ) ) );
									?>
								</span>
							</div>
							<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
							<div id="order_review" class="woocommerce-checkout-review-order">
								<?php do_action( 'woocommerce_checkout_order_review' ); ?>
							</div>
							<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
							<p class="ns-summary__currency"><?php esc_html_e( 'Prices in Nigerian naira (₦)', 'nolasharon' ); ?></p>
							<ul class="ns-trust">
								<li><?php echo nolasharon_icon( 'card', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Secure payment. We never store your card details.', 'nolasharon' ); ?></li>
								<li><?php echo nolasharon_icon( 'receipt', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( nolasharon_page_url( 'returns-policy' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Returns & exchange policy', 'nolasharon' ); ?></a></li>
								<?php
								$nolasharon_wa = nolasharon_whatsapp_url( __( 'Hi, I have a question about my order.', 'nolasharon' ) );
								if ( $nolasharon_wa ) :
									?>
									<li><?php echo nolasharon_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Questions?', 'nolasharon' ); ?> <a href="<?php echo esc_url( $nolasharon_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat with us', 'nolasharon' ); ?></a></span></li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			</aside>

		</div>
	</form>

	<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
</div>
