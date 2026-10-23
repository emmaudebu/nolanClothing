/**
 * NolaSharon checkout (CHECKOUT.md §3–§6): the three-step presentation of WooCommerce's
 * single checkout form, per-step validation and summaries, the Delivery/Pickup toggle,
 * payment method notes and button label, the processing state, the order summary's
 * quantity buttons and the collapsible summary on mobile.
 *
 * Vanilla JS. jQuery is used only to listen for and trigger WooCommerce's own checkout
 * events (updated_checkout, checkout_error, update_checkout, checkout_place_order).
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-ns-checkout]' );
	var form = root && root.querySelector( 'form.checkout' );
	if ( ! form ) {
		return;
	}

	var cfg = window.nsCheckout || { i18n: {}, payNotes: {} };
	var t = cfg.i18n || {};
	var $ = window.jQuery;
	var $body = $ ? $( document.body ) : null;
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var STORE_KEY = 'ns-checkout-step';
	var CLOSE_MS = reduceMotion ? 0 : 450;

	var steps = [].slice.call( form.querySelectorAll( '[data-step]' ) );
	var stepper = root.querySelector( '[data-stepper]' );
	var current = 1;
	var mode = form.querySelector( '[data-pickup-rate]:checked' ) ? 'pickup' : 'delivery';
	var lastDelivery = '';

	root.classList.add( 'is-enhanced', 'is-instant' );

	var escape = function ( value ) {
		return window.CSS && CSS.escape ? CSS.escape( value ) : String( value ).replace( /["\\]/g, '\\$&' );
	};
	var field = function ( name ) {
		return form.querySelector( '[name="' + name + '"]' );
	};
	var value = function ( name ) {
		var el = field( name );
		return el ? el.value.trim() : '';
	};
	var change = function ( input ) {
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	};
	var stored = function () {
		try {
			return parseInt( window.sessionStorage.getItem( STORE_KEY ), 10 ) || 1;
		} catch ( e ) {
			return 1;
		}
	};
	var store = function ( n ) {
		try {
			window.sessionStorage.setItem( STORE_KEY, String( n ) );
		} catch ( e ) {}
	};

	// Errors --------------------------------------------------------------------

	var rules = {
		billing_email: function ( v ) {
			return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( v );
		},
		billing_phone: function ( v ) {
			return /^(?:234|0)?[789][01]\d{8}$/.test( v.replace( /\D/g, '' ) );
		},
	};

	var showError = function ( row, input, message ) {
		var id = row.id ? row.id + '-ns-error' : '';
		var el = row.querySelector( '.ns-error' );
		if ( ! el ) {
			el = document.createElement( 'p' );
			el.className = 'ns-error';
			el.setAttribute( 'role', 'alert' );
			if ( id ) {
				el.id = id;
			}
			row.appendChild( el );
		}
		el.textContent = message;
		row.classList.add( 'woocommerce-invalid', 'ns-invalid' );
		if ( input ) {
			input.setAttribute( 'aria-invalid', 'true' );
			if ( id ) {
				input.setAttribute( 'aria-describedby', id );
			}
		}
	};

	var clearError = function ( row ) {
		if ( ! row || ! row.classList.contains( 'ns-invalid' ) ) {
			return;
		}
		var el = row.querySelector( '.ns-error' );
		if ( el ) {
			el.remove();
		}
		row.classList.remove( 'ns-invalid', 'woocommerce-invalid' );
		row.querySelectorAll( '[aria-invalid]' ).forEach( function ( input ) {
			input.removeAttribute( 'aria-invalid' );
		} );
	};

	// Checks one step. With `silent`, only reports whether it is complete.
	var validateStep = function ( n, silent ) {
		var step = steps[ n - 1 ];
		var errors = [];
		if ( ! step || n > 2 ) {
			return true;
		}
		var inactive = step.querySelector( '[data-mode-panel]:not(.is-active)' );

		step.querySelectorAll( '.form-row' ).forEach( function ( row ) {
			if ( row.classList.contains( 'ns-row-hidden' ) || ( inactive && inactive.contains( row ) ) || row.closest( '.create-account' ) && ! form.querySelector( '#createaccount:checked' ) ) {
				return;
			}
			var radios = row.querySelectorAll( 'input[type="radio"]' );
			if ( row.id === 'shipping_method_field' ) {
				// Delivery needs one of the delivery rates, not the (hidden) pickup rate.
				if ( ! row.querySelector( 'input.shipping_method:checked:not([data-pickup-rate])' ) ) {
					errors.push( [ row, row.querySelector( 'input.shipping_method:not([data-pickup-rate])' ) || row, t.shipping_method || t.required ] );
				}
				return;
			}
			if ( radios.length ) {
				if ( ! row.querySelector( 'input[type="radio"]:checked' ) && row.classList.contains( 'validate-required' ) ) {
					errors.push( [ row, radios[ 0 ], t[ radios[ 0 ].name ] || t.required ] );
				}
				return;
			}
			var input = row.querySelector( 'input:not([type="hidden"]), select, textarea' );
			if ( ! input ) {
				return;
			}
			var v = input.type === 'checkbox' ? ( input.checked ? '1' : '' ) : input.value.trim();
			if ( ! v && row.classList.contains( 'validate-required' ) ) {
				errors.push( [ row, input, t[ input.name ] || t.required ] );
			} else if ( v && rules[ input.name ] && ! rules[ input.name ]( v ) ) {
				errors.push( [ row, input, t[ input.name ] || t.required ] );
			}
		} );

		if ( ! silent ) {
			step.querySelectorAll( '.ns-invalid' ).forEach( clearError );
			errors.forEach( function ( err ) {
				showError( err[ 0 ], err[ 1 ], err[ 2 ] );
			} );
			if ( errors.length ) {
				errors[ 0 ][ 1 ].focus();
			}
		}
		return ! errors.length;
	};

	// Summaries -----------------------------------------------------------------

	var checkedLabel = function ( selector ) {
		var input = form.querySelector( selector );
		return input ? input.getAttribute( 'data-label' ) || '' : '';
	};

	var summaryText = function ( n ) {
		if ( n === 1 ) {
			var phone = value( 'billing_phone' ).replace( /\D/g, '' ).replace( /^(?:234|0)/, '' );
			return [
				[ value( 'billing_first_name' ), value( 'billing_last_name' ) ].join( ' ' ).trim(),
				value( 'billing_email' ),
				phone ? '+234 ' + phone : '',
			].filter( Boolean ).join( '  ·  ' );
		}
		if ( mode === 'pickup' ) {
			return ( t.pickupSummary || '%s' ).replace( '%s', checkedLabel( '[name="ns_pickup_branch"]:checked' ) );
		}
		var state = field( 'billing_state' );
		var stateText = state && state.options && state.selectedIndex > -1 ? state.options[ state.selectedIndex ].text : value( 'billing_state' );
		var area = checkedLabel( 'input.shipping_method:checked' );
		return [ value( 'billing_address_1' ), value( 'billing_city' ), stateText ].filter( Boolean ).join( ', ' ) + ( area ? '  ·  ' + area : '' );
	};

	// Steps ---------------------------------------------------------------------

	var setPanel = function ( panel, open ) {
		if ( ! panel ) {
			return;
		}
		clearTimeout( panel.nsTimer );
		if ( open ) {
			panel.inert = false;
			panel.classList.remove( 'is-settled' );
			// Let focus rings and menus show once the opening transition is over.
			panel.nsTimer = setTimeout( function () {
				panel.classList.add( 'is-settled' );
			}, reduceMotion ? 0 : 650 );
		} else {
			panel.classList.remove( 'is-settled' );
			panel.nsTimer = setTimeout( function () {
				panel.inert = true;
			}, CLOSE_MS );
		}
	};

	var setStep = function ( n, focus ) {
		current = n;
		steps.forEach( function ( step ) {
			var k = parseInt( step.getAttribute( 'data-step' ), 10 );
			var done = k < n;
			step.classList.toggle( 'is-open', k === n );
			step.classList.toggle( 'is-done', done );
			step.classList.toggle( 'is-future', k > n );
			setPanel( step.querySelector( '[data-panel]' ), k === n );

			var edit = step.querySelector( '[data-edit-step]' );
			if ( edit ) {
				edit.hidden = ! done;
			}
			var summary = step.querySelector( '[data-summary]' );
			if ( summary ) {
				if ( done ) {
					summary.querySelector( 'p' ).textContent = summaryText( k );
				}
				summary.hidden = ! done;
			}
		} );

		if ( stepper ) {
			stepper.style.setProperty( '--progress', String( ( n - 1 ) / 2 ) );
			stepper.querySelectorAll( '[data-stepper-item]' ).forEach( function ( item ) {
				var k = parseInt( item.getAttribute( 'data-stepper-item' ), 10 );
				item.classList.toggle( 'is-active', k === n );
				item.classList.toggle( 'is-done', k < n );
				if ( k === n ) {
					item.setAttribute( 'aria-current', 'step' );
				} else {
					item.removeAttribute( 'aria-current' );
				}
			} );
		}

		store( n );

		if ( focus ) {
			var heading = steps[ n - 1 ].querySelector( '.ns-step__title' );
			setTimeout( function () {
				heading.focus( { preventScroll: true } );
				var top = steps[ n - 1 ].getBoundingClientRect().top;
				if ( top < 0 || top > window.innerHeight * 0.6 ) {
					steps[ n - 1 ].scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
				}
			}, reduceMotion ? 0 : 120 );
		}
	};

	var stepOf = function ( el ) {
		var step = el && el.closest( '[data-step]' );
		return step ? parseInt( step.getAttribute( 'data-step' ), 10 ) : 0;
	};

	// Delivery or pickup --------------------------------------------------------

	var toggle = form.querySelector( '[data-mode-toggle]' );

	var setMode = function ( next, byUser ) {
		if ( next === 'pickup' && ! form.querySelector( '[data-pickup-rate]' ) && ! byUser ) {
			next = 'delivery';
		}
		mode = next;
		if ( toggle ) {
			toggle.classList.toggle( 'is-pickup', mode === 'pickup' );
			toggle.querySelectorAll( '[data-mode]' ).forEach( function ( btn ) {
				btn.setAttribute( 'aria-pressed', String( btn.getAttribute( 'data-mode' ) === mode ) );
			} );
		}
		form.querySelectorAll( '[data-mode-panel]' ).forEach( function ( panel ) {
			var on = panel.getAttribute( 'data-mode-panel' ) === mode;
			panel.classList.toggle( 'is-active', on );
			setPanel( panel, on );
		} );
		if ( ! byUser ) {
			return;
		}
		steps[ 1 ].querySelectorAll( '.ns-invalid' ).forEach( clearError );
		syncRate();
	};

	// Select the shipping rate that matches the chosen mode, so WooCommerce recalculates.
	var syncRate = function () {
		var chosen = form.querySelector( 'input.shipping_method:checked' );
		var pickupRate = form.querySelector( '[data-pickup-rate]' );
		if ( mode === 'pickup' ) {
			if ( pickupRate && ! pickupRate.checked ) {
				pickupRate.checked = true;
				change( pickupRate );
			}
			return;
		}
		if ( chosen && ! chosen.hasAttribute( 'data-pickup-rate' ) ) {
			return;
		}
		var fallback = ( lastDelivery && form.querySelector( 'input.shipping_method[value="' + escape( lastDelivery ) + '"]' ) ) ||
			form.querySelector( 'input.shipping_method:not([data-pickup-rate])' );
		if ( fallback ) {
			fallback.checked = true;
			change( fallback );
		}
	};

	// Payment -------------------------------------------------------------------

	var gateway = function () {
		var input = form.querySelector( 'input[name="payment_method"]:checked' );
		return input ? input.value : '';
	};

	var totalText = function () {
		var el = form.querySelector( '.order-total .woocommerce-Price-amount' );
		return el ? el.textContent.trim() : '';
	};

	var LOCK = '<svg class="ns-place__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>';

	// WooCommerce resets the button text when the method changes; rebuild it each time.
	var renderButton = function () {
		var btn = document.getElementById( 'place_order' );
		if ( ! btn ) {
			return;
		}
		if ( ! btn.querySelector( '.ns-place__label' ) ) {
			btn.innerHTML = '<span class="ns-place__fill" aria-hidden="true"></span>' + LOCK + '<span class="ns-place__label"></span>';
		}
		btn.classList.add( 'ns-place' );
		var busy = btn.classList.contains( 'is-processing' );
		var label = ( gateway() === 'paystack' ? t.pay : t.place ) || '%s';
		label = label.replace( '%s', totalText() );
		btn.querySelector( '.ns-place__label' ).textContent = busy ? t.processing : label;
		if ( ! busy ) {
			btn.value = label;
			btn.setAttribute( 'data-value', label );
		}
	};

	var renderPayment = function () {
		var gw = gateway();
		form.querySelectorAll( '.wc_payment_method' ).forEach( function ( li ) {
			var input = li.querySelector( 'input[name="payment_method"]' );
			li.classList.toggle( 'is-selected', !! ( input && input.checked ) );
		} );
		var note = form.querySelector( '[data-pay-note]' );
		var notes = cfg.payNotes || {};
		if ( note ) {
			note.hidden = ! notes[ gw ];
			note.querySelector( '[data-pay-note-text]' ).textContent = notes[ gw ] || '';
		}
		renderButton();
	};

	var setProcessing = function ( on ) {
		var btn = document.getElementById( 'place_order' );
		if ( ! btn ) {
			return;
		}
		btn.classList.toggle( 'is-processing', on );
		btn.setAttribute( 'aria-busy', String( on ) );
		btn.disabled = on;
		root.classList.toggle( 'is-processing', on );
		renderButton();
	};

	// WooCommerce adds "processing" to the form once the order is really being submitted
	// (after gateways have had their say), and removes it when an error comes back.
	if ( 'MutationObserver' in window ) {
		new MutationObserver( function () {
			setProcessing( form.classList.contains( 'processing' ) );
		} ).observe( form, { attributes: true, attributeFilter: [ 'class' ] } );
	}

	// Order summary -------------------------------------------------------------

	var summary = root.querySelector( '.ns-summary' );
	var summaryToggle = root.querySelector( '[data-summary-toggle]' );

	var renderSummary = function () {
		var table = form.querySelector( '.woocommerce-checkout-review-order-table' );
		var count = root.querySelector( '[data-summary-count]' );
		var total = root.querySelector( '[data-summary-total]' );
		if ( table && count && table.getAttribute( 'data-count-label' ) ) {
			count.textContent = table.getAttribute( 'data-count-label' );
		}
		if ( total ) {
			total.textContent = totalText();
		}
	};

	if ( summaryToggle ) {
		summaryToggle.hidden = false;
		summaryToggle.addEventListener( 'click', function () {
			var open = summaryToggle.getAttribute( 'aria-expanded' ) !== 'true';
			var label = summaryToggle.querySelector( '.ns-summary__bar-label' );
			summaryToggle.setAttribute( 'aria-expanded', String( open ) );
			summary.classList.toggle( 'is-open', open );
			label.textContent = label.getAttribute( open ? 'data-label-hide' : 'data-label-show' );
		} );
	}

	var updateQty = function ( btn ) {
		var wrap = btn.closest( '[data-qty]' );
		var valueEl = wrap.querySelector( '.ns-qty__val' );
		var max = parseInt( wrap.getAttribute( 'data-max' ), 10 );
		var next = parseInt( valueEl.textContent, 10 ) + parseInt( btn.getAttribute( 'data-qty-step' ), 10 );
		if ( next < 1 || ( max > 0 && next > max ) || wrap.classList.contains( 'is-busy' ) || ! window.fetch ) {
			return;
		}
		wrap.classList.add( 'is-busy' );
		var data = new URLSearchParams();
		data.append( 'security', cfg.qtyNonce );
		data.append( 'key', wrap.getAttribute( 'data-key' ) );
		data.append( 'qty', String( next ) );
		fetch( cfg.qtyUrl, { method: 'POST', credentials: 'same-origin', body: data } )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				if ( ! json || ! json.success ) {
					throw new Error( 'qty' );
				}
				valueEl.textContent = json.data.qty;
				if ( $body ) {
					$body.trigger( 'update_checkout' );
				}
			} )
			.catch( function () {
				wrap.classList.remove( 'is-busy' );
				showError( wrap.closest( 'td' ), null, t.qtyError );
			} );
	};

	// Events --------------------------------------------------------------------

	form.addEventListener( 'click', function ( e ) {
		var next = e.target.closest( '[data-next-step]' );
		if ( next ) {
			e.preventDefault();
			var target = parseInt( next.getAttribute( 'data-next-step' ), 10 );
			if ( validateStep( target - 1 ) ) {
				setStep( target, true );
			}
			return;
		}
		var edit = e.target.closest( '[data-edit-step]' );
		if ( edit ) {
			setStep( parseInt( edit.getAttribute( 'data-edit-step' ), 10 ), true );
			return;
		}
		var modeBtn = e.target.closest( '[data-mode]' );
		if ( modeBtn ) {
			setMode( modeBtn.getAttribute( 'data-mode' ), true );
			return;
		}
		var qtyBtn = e.target.closest( '[data-qty-step]' );
		if ( qtyBtn ) {
			updateQty( qtyBtn );
			return;
		}
		// After WooCommerce's own handler has reset the button text.
		if ( e.target.matches( 'input[name="payment_method"]' ) ) {
			setTimeout( renderPayment, 0 );
		}
	} );

	// Enter in steps 1 and 2 continues to the next step instead of placing the order.
	form.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Enter' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'BUTTON' ) {
			return;
		}
		var n = stepOf( e.target );
		if ( n && n < 3 ) {
			e.preventDefault();
			var btn = steps[ n - 1 ].querySelector( '[data-next-step]' );
			if ( btn ) {
				btn.click();
			}
		}
	} );

	var clearOnEdit = function ( e ) {
		var row = e.target.closest( '.form-row' );
		if ( row ) {
			clearError( row );
		}
		if ( e.target.matches( 'input.shipping_method' ) && ! e.target.hasAttribute( 'data-pickup-rate' ) ) {
			lastDelivery = e.target.value;
		}
	};
	form.addEventListener( 'input', clearOnEdit );
	form.addEventListener( 'change', clearOnEdit );

	if ( $body ) {
		$body.on( 'updated_checkout', function () {
			var chosen = form.querySelector( 'input.shipping_method:checked' );
			if ( chosen && ! chosen.hasAttribute( 'data-pickup-rate' ) ) {
				lastDelivery = chosen.value;
			}
			if ( mode === 'pickup' && ! form.querySelector( '[data-pickup-rate]' ) ) {
				setMode( 'delivery', false );
			}
			syncRate();
			renderPayment();
			renderSummary();
			if ( current > 2 ) {
				steps[ 1 ].querySelector( '[data-summary] p' ).textContent = summaryText( 2 );
			}
		} );

		// Server-side errors: open the step holding the first field in error.
		$body.on( 'checkout_error', function ( e, html ) {
			setProcessing( false );
			var holder = document.createElement( 'div' );
			holder.innerHTML = html || '';
			var first = holder.querySelector( '[data-id]' );
			var id = first ? first.getAttribute( 'data-id' ) : '';
			var target = id ? document.getElementById( id ) || field( id ) || document.getElementById( id + '_field' ) : null;
			var n = stepOf( target );
			if ( n && n !== current ) {
				if ( target.closest( '[data-mode-panel="pickup"]' ) ) {
					setMode( 'pickup', false );
				}
				setStep( n, false );
			}
		} );

		// Before WooCommerce submits: catch an incomplete earlier step without a round trip.
		$( form ).on( 'checkout_place_order', function () {
			for ( var n = 1; n <= 2; n++ ) {
				if ( ! validateStep( n, true ) ) {
					setStep( n, false );
					validateStep( n );
					return false;
				}
			}
		} );
	}

	// Start ---------------------------------------------------------------------

	if ( toggle ) {
		toggle.hidden = false;
	}
	setMode( mode, false );
	var start = Math.min( Math.max( stored(), 1 ), 3 );
	for ( var k = 1; k < start; k++ ) {
		if ( ! validateStep( k, true ) ) {
			start = k;
			break;
		}
	}
	setStep( start, false );
	renderPayment();
	// Skip the transitions for the first layout, then animate from here on.
	window.requestAnimationFrame( function () {
		window.requestAnimationFrame( function () {
			root.classList.remove( 'is-instant' );
		} );
	} );
}() );
