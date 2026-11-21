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

if ( ! function_exists( 'nolasharon_privacy_policy_draft' ) ) {
	WP_CLI::error( 'Activate the NolaSharon theme (version 0.15.4 or later) first.' );
}

// The wording is in the theme since 0.15.4 (inc/page-content.php), which also writes it once by
// itself on a site whose pages are still empty outlines.
$privacy = nolasharon_privacy_policy_draft();
$terms   = nolasharon_terms_draft();

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
