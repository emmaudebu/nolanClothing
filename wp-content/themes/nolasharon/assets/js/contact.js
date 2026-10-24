/**
 * Contact form (CONTENT-PAGES.md §2): Fluent Forms' topic radios become pill chips, the
 * message gets a live character counter, a failed send shakes the card, and a successful
 * one swaps the form for a thank-you panel. Fluent Forms reports results as jQuery events,
 * so those are the only place jQuery is used.
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-ct]' );
	var form = root && root.querySelector( 'form.frm-fluent-form' );
	if ( ! form ) {
		return;
	}

	var ui = window.nsUI || {};
	var l10n = window.nsContact || {};
	var body = root.querySelector( '[data-ct-body]' );
	var sent = root.querySelector( '[data-ct-sent]' );

	// Topic chips: the radios stay real inputs; their labels are styled as pills.
	var chips = form.querySelector( '.ns-ff-chips' );
	if ( chips ) {
		chips.classList.add( 'is-chips' );
		var group = chips.querySelector( '.ff-el-input--content' );
		if ( group ) {
			group.setAttribute( 'role', 'radiogroup' );
			var label = chips.querySelector( '.ff-el-input--label label' );
			if ( label ) {
				label.id = label.id || 'ct-topic-label';
				group.setAttribute( 'aria-labelledby', label.id );
			}
		}
		var sync = function () {
			chips.querySelectorAll( '.ff-el-form-check' ).forEach( function ( check ) {
				var input = check.querySelector( 'input' );
				check.classList.toggle( 'is-checked', !! ( input && input.checked ) );
			} );
		};
		chips.addEventListener( 'change', sync );
		sync();
	}

	// Phone keyboard on mobiles.
	var phone = form.querySelector( 'input[name="phone"]' );
	if ( phone ) {
		phone.type = 'tel';
		phone.autocomplete = 'tel';
		phone.inputMode = 'tel';
	}
	var name = form.querySelector( 'input[name="full_name"]' );
	if ( name ) {
		name.autocomplete = 'name';
	}
	var email = form.querySelector( 'input[name="email"]' );
	if ( email ) {
		email.autocomplete = 'email';
	}
	var order = form.querySelector( 'input[name="order_number"]' );
	if ( order ) {
		order.inputMode = 'numeric';
		order.addEventListener( 'input', function () {
			order.value = order.value.replace( /^#?\s*ns-?/i, '' );
		} );
	}

	// Character counter under the message.
	var message = form.querySelector( 'textarea[name="message"]' );
	if ( message ) {
		var max = parseInt( message.getAttribute( 'maxlength' ), 10 ) || 1000;
		var counter = document.createElement( 'span' );
		counter.className = 'ct-count';
		counter.setAttribute( 'aria-live', 'polite' );
		counter.setAttribute( 'aria-atomic', 'true' );
		message.parentNode.appendChild( counter );
		var update = function () {
			var n = message.value.length;
			counter.textContent = ( l10n.count || '%1$d / %2$d' ).replace( '%1$d', n ).replace( '%2$d', max );
			counter.classList.toggle( 'is-near', n > max * 0.9 );
		};
		message.addEventListener( 'input', update );
		update();
	}

	// A field's error clears as soon as it is edited.
	var clearError = function ( event ) {
		var group = event.target.closest && event.target.closest( '.ff-el-is-error' );
		if ( group ) {
			group.classList.remove( 'ff-el-is-error' );
			group.querySelectorAll( '.error' ).forEach( function ( el ) {
				el.remove();
			} );
		}
	};
	form.addEventListener( 'input', clearError );
	form.addEventListener( 'change', clearError );

	var shake = function () {
		if ( ui.reduced ) {
			return;
		}
		root.classList.remove( 'is-shake' );
		void root.offsetWidth; // Restart the animation.
		root.classList.add( 'is-shake' );
	};
	root.addEventListener( 'animationend', function () {
		root.classList.remove( 'is-shake' );
	} );

	var showSent = function () {
		var first = name ? name.value.trim().split( /\s+/ )[ 0 ] : '';
		var mail = email ? email.value.trim() : '';
		sent.querySelector( '[data-ct-sent-title]' ).textContent = first ? ( l10n.thanks || '%s' ).replace( '%s', first ) : l10n.thanks0;
		sent.querySelector( '[data-ct-sent-text]' ).textContent = mail ? ( l10n.sent || '%s' ).replace( '%s', mail ) : l10n.sent0;
		body.hidden = true;
		sent.hidden = false;
		sent.classList.add( 'is-in' );
		sent.focus();
		var top = root.getBoundingClientRect().top + window.scrollY - 100;
		if ( window.scrollY > top ) {
			window.scrollTo( { top: top, behavior: ui.reduced ? 'auto' : 'smooth' } );
		}
	};

	if ( window.jQuery ) {
		window.jQuery( form ).on( 'fluentform_submission_success', showSent );
		window.jQuery( form ).on( 'fluentform_submission_failed', shake );
	}

	// Client-side check before Fluent Forms sends, so short messages fail fast.
	form.addEventListener( 'submit', function () {
		if ( form.querySelector( '.ff-el-is-error' ) ) {
			shake();
		}
	} );
	form.addEventListener( 'invalid', shake, true );

	var again = root.querySelector( '[data-ct-again]' );
	if ( again ) {
		again.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			form.reset();
			form.style.display = '';
			form.classList.remove( 'ff_force_hide' );
			var done = body.querySelector( '.ff-message-success' );
			if ( done ) {
				done.remove();
			}
			if ( chips ) {
				chips.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}
			if ( message ) {
				message.dispatchEvent( new Event( 'input' ) );
			}
			sent.hidden = true;
			body.hidden = false;
			if ( name ) {
				name.focus();
			}
		} );
	}
}() );
