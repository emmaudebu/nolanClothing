/**
 * Track your order (TRACK-ORDER.md §A3–§A4): validation with a shake, the loading state,
 * the reveal and scroll to the result, "Track another order" and copying the tracking
 * number. The form is a normal POST, so everything works without JavaScript.
 */
( function () {
	'use strict';

	var t = window.nsTrack || {};
	var ui = window.nsUI;
	var root = document.querySelector( '[data-track]' );
	var form = document.querySelector( '[data-track-form]' );
	if ( ! root || ! form ) {
		return;
	}
	var orderInput = form.querySelector( '[name="orderid"]' );
	var emailInput = form.querySelector( '[name="order_email"]' );
	var errorBox = form.querySelector( '[data-track-error]' );
	var errorText = form.querySelector( '[data-track-error-text]' );
	var submit = form.querySelector( '[data-track-submit]' );
	var result = root.querySelector( '[data-track-result]' );
	var tips = root.querySelector( '[data-track-tips]' );

	function shake() {
		form.classList.remove( 'is-shake' );
		void form.offsetWidth; // Restart the animation.
		form.classList.add( 'is-shake' );
	}

	function showError( message ) {
		errorText.textContent = message;
		ui.collapse( errorBox, true );
		shake();
	}

	// The server already shook the card for a failed lookup; focus the order field.
	if ( form.classList.contains( 'is-shake' ) && errorText.textContent.trim() ) {
		orderInput.focus( { preventScroll: true } );
	}

	form.addEventListener( 'input', function () {
		if ( errorBox.classList.contains( 'is-open' ) ) {
			ui.collapse( errorBox, false );
		}
	} );

	form.addEventListener( 'submit', function ( e ) {
		var digits = orderInput.value.replace( /\D+/g, '' );
		if ( ! digits || ! /^\S+@\S+\.\S+$/.test( emailInput.value.trim() ) ) {
			e.preventDefault();
			showError( t.invalid );
			( digits ? emailInput : orderInput ).focus();
			return;
		}
		submit.classList.add( 'is-loading' );
		submit.setAttribute( 'aria-busy', 'true' );
		submit.textContent = t.finding;
	} );

	// A result after the reload: scroll to it and play the tracker reveal.
	if ( result && result.classList.contains( 'is-open' ) ) {
		var tracker = result.querySelector( '[data-tracker]' );
		requestAnimationFrame( function () {
			var top = result.getBoundingClientRect().top + window.scrollY - 80;
			window.scrollTo( { top: top, behavior: ui.reduced ? 'auto' : 'smooth' } );
			result.focus( { preventScroll: true } );
			setTimeout( function () {
				if ( tracker ) {
					tracker.classList.add( 'is-shown' );
				}
			}, ui.reduced ? 0 : 450 );
		} );
	}

	// Track another order: fold the result away, bring the tips back, focus the order field.
	document.addEventListener( 'click', function ( e ) {
		if ( ! e.target.closest( '[data-track-another]' ) ) {
			return;
		}
		e.preventDefault();
		ui.collapse( result, false );
		ui.collapse( tips, true );
		orderInput.value = '';
		window.scrollTo( { top: 0, behavior: ui.reduced ? 'auto' : 'smooth' } );
		setTimeout( function () {
			orderInput.focus( { preventScroll: true } );
		}, ui.reduced ? 0 : 400 );
		history.replaceState( null, '', window.location.pathname );
	} );

	// Copy the courier tracking number.
	var copy = document.querySelector( '[data-copy]' );
	if ( copy && navigator.clipboard ) {
		copy.hidden = false;
		copy.addEventListener( 'click', function () {
			navigator.clipboard.writeText( document.querySelector( '[data-copy-source]' ).textContent.trim() ).then( function () {
				ui.toast( t.copied );
			} );
		} );
	}
}() );
