/**
 * Shared UI kit (STORE-PAGES.md §0): toasts, WooCommerce notices, the password eye and
 * strength meter, segmented tabs, collapse, modal and the header cart count.
 * Exposed as window.nsUI for theme.js, account.js and cart.js.
 */
( function () {
	'use strict';

	var i18n = window.nsUIStrings || {};
	var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function text( key, fallback ) {
		return i18n[ key ] || fallback;
	}

	/* Toast ---------------------------------------------------------------- */

	var toastEl, toastText, toastAction, toastTimer;
	var queue = [];
	var showing = false;

	function toastNode() {
		if ( toastEl ) {
			return toastEl;
		}
		toastEl = document.createElement( 'div' );
		toastEl.className = 'ns-toast';
		toastEl.setAttribute( 'role', 'status' );
		toastEl.setAttribute( 'aria-live', 'polite' );
		toastEl.innerHTML = '<svg class="ns-toast__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="M5 12l5 5L20 7"/></svg><span class="ns-toast__text"></span>';
		toastText = toastEl.querySelector( '.ns-toast__text' );
		document.body.appendChild( toastEl );

		// Hovering or focusing the toast keeps it up (WCAG 2.2.1).
		[ 'mouseenter', 'focusin' ].forEach( function ( type ) {
			toastEl.addEventListener( type, function () {
				clearTimeout( toastTimer );
			} );
		} );
		[ 'mouseleave', 'focusout' ].forEach( function ( type ) {
			toastEl.addEventListener( type, function () {
				if ( showing ) {
					hideLater( 2000 );
				}
			} );
		} );
		return toastEl;
	}

	function hideLater( ms ) {
		clearTimeout( toastTimer );
		toastTimer = setTimeout( function () {
			toastEl.classList.remove( 'is-visible' );
			setTimeout( next, 450 );
		}, ms );
	}

	function next() {
		showing = false;
		if ( queue.length ) {
			render( queue.shift() );
		}
	}

	function render( item ) {
		toastNode();
		showing = true;
		toastText.textContent = item.message;
		if ( toastAction ) {
			toastAction.remove();
			toastAction = null;
		}
		if ( item.action ) {
			toastAction = document.createElement( item.action.href ? 'a' : 'button' );
			toastAction.className = 'ns-toast__action';
			toastAction.textContent = item.action.label;
			if ( item.action.href ) {
				toastAction.href = item.action.href;
			} else {
				toastAction.type = 'button';
			}
			toastAction.addEventListener( 'click', function ( e ) {
				if ( item.action.onClick ) {
					e.preventDefault();
					item.action.onClick( e );
				}
				clearTimeout( toastTimer );
				toastEl.classList.remove( 'is-visible' );
				setTimeout( next, 450 );
			} );
			toastEl.appendChild( toastAction );
		}
		// Next frame, so the slide-up runs and screen readers hear the new text.
		requestAnimationFrame( function () {
			toastEl.classList.add( 'is-visible' );
		} );
		hideLater( item.action ? 6000 : 3000 );
	}

	/**
	 * Show a toast. Several in a row are shown one after another.
	 *
	 * @param {string} message
	 * @param {Object} [action] { label, href } or { label, onClick }.
	 */
	function toast( message, action ) {
		if ( ! message ) {
			return;
		}
		var item = { message: message, action: action || null };
		if ( showing ) {
			queue.push( item );
		} else {
			render( item );
		}
	}

	/* WooCommerce notices ---------------------------------------------------- */

	// Success and info notices become toasts; errors stay inline and take focus.
	function enhanceNotices( scope ) {
		( scope || document ).querySelectorAll( '.woocommerce-notices-wrapper .woocommerce-message, .woocommerce-notices-wrapper .woocommerce-info' ).forEach( function ( notice ) {
			if ( notice.classList.contains( 'is-toasted' ) || notice.closest( '[data-keep-notices]' ) ) {
				return;
			}
			var link = notice.querySelector( 'a' );
			var clone = notice.cloneNode( true );
			if ( link ) {
				clone.querySelector( 'a' ).remove();
			}
			var message = clone.textContent.replace( /\s+/g, ' ' ).trim();
			notice.classList.add( 'is-toasted' );
			toast( message, link ? { label: link.textContent.trim(), href: link.href } : null );
		} );
		var error = ( scope || document ).querySelector( '.woocommerce-notices-wrapper .woocommerce-error, .woocommerce-error[data-focus]' );
		if ( error && ! error.hasAttribute( 'data-focused' ) ) {
			error.setAttribute( 'tabindex', '-1' );
			error.setAttribute( 'data-focused', '' );
			error.focus( { preventScroll: false } );
		}
	}

	/* Password eye and strength meter -------------------------------------- */

	document.addEventListener( 'click', function ( e ) {
		var eye = e.target.closest( '.ns-pw__eye' );
		if ( ! eye ) {
			return;
		}
		var input = eye.parentElement.querySelector( 'input' );
		var shown = input.type === 'password';
		input.type = shown ? 'text' : 'password';
		eye.setAttribute( 'aria-pressed', shown ? 'true' : 'false' );
		eye.setAttribute( 'aria-label', shown ? text( 'hidePassword', 'Hide password' ) : text( 'showPassword', 'Show password' ) );
	} );

	// zxcvbn score (0–4) from WordPress's meter when it is loaded, else a simple estimate.
	function strength( pw ) {
		if ( ! pw ) {
			return -1;
		}
		if ( window.wp && wp.passwordStrength && wp.passwordStrength.meter ) {
			var disallowed = wp.passwordStrength.userInputDisallowedList ? wp.passwordStrength.userInputDisallowedList() : [];
			return Math.max( 0, wp.passwordStrength.meter( pw, disallowed, pw ) );
		}
		var n = 0;
		if ( pw.length >= 8 ) {
			n++;
		}
		if ( /[A-Z]/.test( pw ) && /[a-z]/.test( pw ) ) {
			n++;
		}
		if ( /\d/.test( pw ) ) {
			n++;
		}
		if ( /[^A-Za-z0-9]/.test( pw ) || pw.length >= 12 ) {
			n++;
		}
		return n;
	}

	function updateMeter( meter, pw ) {
		var score = strength( pw );
		// Four bars: 0–1 → 1 (very weak), 2 → 2, 3 → 3 (good), 4 → 4 (strong).
		var bars = score < 0 ? 0 : Math.max( 1, score );
		var names = [ '', text( 'veryWeak', 'Very weak' ), text( 'weak', 'Weak' ), text( 'good', 'Good' ), text( 'strong', 'Strong' ) ];
		var label = meter.querySelector( '.ns-meter__text' );
		meter.setAttribute( 'data-score', bars );
		if ( label ) {
			var message = bars ? text( 'strength', 'Password strength: %s' ).replace( '%s', names[ bars ] ) : label.getAttribute( 'data-empty' );
			// Below WooCommerce's minimum its script disables the button, so say what to do.
			var params = window.wc_password_strength_meter_params;
			if ( bars && params && score < parseInt( params.min_password_strength, 10 ) ) {
				message += '. ' + text( 'tooWeak', 'Add more characters, numbers or symbols.' );
			}
			label.textContent = message;
		}
	}

	document.addEventListener( 'input', function ( e ) {
		if ( ! e.target.id ) {
			return;
		}
		document.querySelectorAll( '.ns-meter[data-for="' + e.target.id + '"]' ).forEach( function ( meter ) {
			updateMeter( meter, e.target.value );
		} );
	} );

	/* Segmented tabs ------------------------------------------------------- */

	/**
	 * Move a segmented control's indicator to the button at index.
	 *
	 * @param {Element} seg
	 * @param {number}  index
	 */
	function seg( seg, index ) {
		var buttons = seg.querySelectorAll( '.ns-seg__btn' );
		seg.style.setProperty( '--n', buttons.length );
		seg.style.setProperty( '--i', index );
		buttons.forEach( function ( btn, i ) {
			var attr = btn.getAttribute( 'role' ) === 'tab' ? 'aria-selected' : 'aria-pressed';
			btn.setAttribute( attr, i === index ? 'true' : 'false' );
			if ( attr === 'aria-selected' ) {
				btn.tabIndex = i === index ? 0 : -1;
			}
		} );
	}

	/* Collapse and modal --------------------------------------------------- */

	function collapse( el, open ) {
		el.classList.toggle( 'is-open', open );
		el.toggleAttribute( 'inert', ! open );
	}

	function modal( dialog, opener ) {
		if ( typeof dialog.showModal !== 'function' ) {
			return false;
		}
		dialog.showModal();
		dialog.addEventListener( 'close', function onClose() {
			dialog.removeEventListener( 'close', onClose );
			if ( opener && opener.focus ) {
				opener.focus();
			}
		} );
		return true;
	}

	/* Header cart count ---------------------------------------------------- */

	function bump( badge ) {
		if ( reduced || ! badge ) {
			return;
		}
		badge.classList.remove( 'is-bump' );
		void badge.offsetWidth; // Restart the animation.
		badge.classList.add( 'is-bump' );
	}

	function cartCount( count ) {
		document.querySelectorAll( '.header-cart' ).forEach( function ( link ) {
			var badge = link.querySelector( '.header-cart__count' );
			if ( ! badge ) {
				return;
			}
			var changed = badge.textContent.trim() !== String( count );
			badge.textContent = count;
			var label = link.getAttribute( count === 1 ? 'data-label-one' : 'data-label-many' );
			if ( label ) {
				link.setAttribute( 'aria-label', label.replace( '%d', count ) );
			}
			if ( changed ) {
				bump( badge );
			}
		} );
	}

	// WooCommerce's AJAX add-to-cart replaces the header cart through fragments.
	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'added_to_cart', function () {
			bump( document.querySelector( '.header-cart__count' ) );
		} );
	}

	window.nsUI = {
		toast: toast,
		enhanceNotices: enhanceNotices,
		updateMeter: updateMeter,
		strength: strength,
		seg: seg,
		collapse: collapse,
		modal: modal,
		cartCount: cartCount,
		reduced: reduced
	};

	enhanceNotices();
}() );
