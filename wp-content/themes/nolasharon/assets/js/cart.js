/**
 * Cart page (STORE-PAGES.md §4): quantities update after a 600ms pause, removed items slide
 * away with an "Undo" toast, the last removal cross-fades to the empty cart, and simple
 * products in "You may also like" add in place. Each change goes through WooCommerce's own
 * cart form, remove and undo URLs; the page that comes back refreshes the parts marked
 * data-sync. Without JavaScript the form and links work as normal.
 */
( function () {
	'use strict';

	var t = window.nsCart || {};
	var ui = window.nsUI;
	var root = document.querySelector( '[data-cart]' );
	if ( ! root || ! window.fetch || ! window.DOMParser ) {
		return;
	}
	root.classList.add( 'is-enhanced' );

	var timer;
	var queue = Promise.resolve();

	function parse( html ) {
		return new DOMParser().parseFromString( html, 'text/html' );
	}

	function get( url ) {
		return fetch( url, { credentials: 'same-origin' } ).then( function ( response ) {
			if ( ! response.ok ) {
				throw new Error( response.status );
			}
			return response.text();
		} ).then( parse );
	}

	// One request at a time, in order.
	function run( job ) {
		queue = queue.then( job ).catch( function () {
			ui.toast( t.error );
		} );
		return queue;
	}

	function headerCount( doc ) {
		var badge = doc.querySelector( '.header-cart__count' );
		if ( badge ) {
			ui.cartCount( parseInt( badge.textContent, 10 ) || 0 );
		}
		if ( window.jQuery ) {
			window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
		}
	}

	// Show WooCommerce errors from the returned page (e.g. "only 2 in stock") inline.
	function errors( doc ) {
		var wrap = root.querySelector( '.woocommerce-notices-wrapper' );
		var error = doc.querySelector( '.woocommerce-error' );
		if ( wrap ) {
			wrap.innerHTML = error ? error.outerHTML : '';
			if ( error ) {
				ui.enhanceNotices( root );
			}
		}
	}

	// Swap to the page that came back: patch data-sync parts, or cross-fade when the cart
	// switched between empty and filled.
	function apply( doc ) {
		var next = doc.querySelector( '[data-cart]' );
		if ( ! next ) {
			throw new Error( 'cart' );
		}
		headerCount( doc );
		if ( next.classList.contains( 'ns-cart--empty' ) !== root.classList.contains( 'ns-cart--empty' ) ) {
			root.classList.add( 'is-fading' );
			return new Promise( function ( resolve ) {
				setTimeout( function () {
					root.className = next.className + ' is-enhanced is-fading';
					root.innerHTML = next.innerHTML;
					requestAnimationFrame( function () {
						root.classList.remove( 'is-fading' );
					} );
					resolve();
				}, ui.reduced ? 0 : 400 );
			} );
		}
		errors( doc );
		next.querySelectorAll( '[data-sync]' ).forEach( function ( fresh ) {
			var key = fresh.getAttribute( 'data-sync' );
			var current = root.querySelector( '[data-sync="' + key + '"]' );
			if ( current ) {
				current.replaceWith( fresh );
			}
		} );
		// The server may cap a quantity at the stock level.
		next.querySelectorAll( '[data-qty]' ).forEach( function ( fresh ) {
			var input = document.getElementById( fresh.id );
			if ( input && document.activeElement !== input ) {
				input.value = fresh.value;
			}
			if ( input ) {
				input.max = fresh.max;
			}
		} );
		return Promise.resolve();
	}

	/* Quantities ----------------------------------------------------------- */

	var form = function () {
		return root.querySelector( '[data-cart-form]' );
	};

	function update() {
		var f = form();
		if ( ! f ) {
			return;
		}
		run( function () {
			var data = new FormData( f );
			data.set( 'update_cart', '1' );
			root.setAttribute( 'aria-busy', 'true' );
			return fetch( f.action, { method: 'POST', body: data, credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.text();
				} )
				.then( function ( html ) {
					return apply( parse( html ) );
				} )
				.then( function () {
					root.removeAttribute( 'aria-busy' );
				} );
		} );
	}

	function schedule() {
		clearTimeout( timer );
		timer = setTimeout( update, 600 );
	}

	function clamp( input ) {
		var max = parseInt( input.max, 10 ) || Infinity;
		var value = parseInt( input.value, 10 );
		input.value = Math.min( max, Math.max( 1, isNaN( value ) ? 1 : value ) );
	}

	root.addEventListener( 'click', function ( e ) {
		var step = e.target.closest( '.ns-qty__btn' );
		if ( step ) {
			var input = step.parentElement.querySelector( '[data-qty]' );
			var before = input.value;
			input.value = ( parseInt( input.value, 10 ) || 1 ) + parseInt( step.getAttribute( 'data-step' ), 10 );
			clamp( input );
			if ( input.value !== before ) {
				schedule();
			}
		}
	} );

	root.addEventListener( 'change', function ( e ) {
		if ( e.target.matches( '[data-qty]' ) ) {
			clamp( e.target );
			schedule();
		}
	} );

	root.addEventListener( 'input', function ( e ) {
		if ( e.target.matches( '[data-qty]' ) && e.target.value !== '' ) {
			schedule();
		}
	} );

	// Enter in a quantity field updates straight away rather than posting the page.
	root.addEventListener( 'submit', function ( e ) {
		if ( e.target.matches( '[data-cart-form]' ) && ( ! e.submitter || e.submitter.hasAttribute( 'data-update' ) ) ) {
			e.preventDefault();
			clearTimeout( timer );
			update();
		}
	} );

	/* Remove and undo ------------------------------------------------------ */

	function undo( url ) {
		run( function () {
			return get( url ).then( function ( doc ) {
				if ( root.classList.contains( 'ns-cart--empty' ) ) {
					return apply( doc );
				}
				// Bring the list back with the restored item folding in.
				var list = doc.querySelector( '.ns-citems' );
				var known = Array.prototype.map.call( root.querySelectorAll( '[data-item]' ), function ( el ) {
					return el.getAttribute( 'data-item' );
				} );
				list.querySelectorAll( '[data-item]' ).forEach( function ( el ) {
					if ( known.indexOf( el.getAttribute( 'data-item' ) ) === -1 ) {
						el.classList.remove( 'is-open' );
						el.setAttribute( 'data-new', '' );
					}
				} );
				root.querySelector( '.ns-citems' ).replaceWith( list );
				var count = doc.querySelector( '[data-sync="count"]' );
				var heading = root.querySelector( '.ns-cart__title' );
				if ( count && heading && ! heading.querySelector( '[data-sync="count"]' ) ) {
					heading.appendChild( count.cloneNode( true ) );
				}
				return apply( doc ).then( function () {
					requestAnimationFrame( function () {
						requestAnimationFrame( function () {
							root.querySelectorAll( '[data-new]' ).forEach( function ( el ) {
								el.removeAttribute( 'data-new' );
								ui.collapse( el, true );
							} );
						} );
					} );
				} );
			} );
		} );
	}

	root.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( '[data-cart-remove]' );
		if ( ! link ) {
			return;
		}
		e.preventDefault();
		var row = link.closest( '[data-item]' );
		var name = row.getAttribute( 'data-name' );
		row.classList.add( 'is-removing' );
		ui.collapse( row, false );

		run( function () {
			return Promise.all( [
				get( link.href ),
				new Promise( function ( resolve ) {
					setTimeout( resolve, ui.reduced ? 0 : 500 );
				} )
			] ).then( function ( results ) {
				var doc = results[ 0 ];
				var restore = doc.querySelector( '.woocommerce-message a.restore-item' );
				row.remove();
				return apply( doc ).then( function () {
					ui.toast( ( t.removed || '%s removed' ).replace( '%s', name ), restore ? {
						label: t.undo,
						onClick: function () {
							undo( restore.href );
						}
					} : null );
				} );
			} );
		} );
	} );

	/* "You may also like": add simple products in place -------------------- */

	root.addEventListener( 'click', function ( e ) {
		var add = e.target.closest( '[data-rec-add]' );
		if ( ! add || ! t.addUrl ) {
			return;
		}
		e.preventDefault();
		var body = new FormData();
		body.set( 'product_id', add.getAttribute( 'data-rec-add' ) );
		body.set( 'quantity', '1' );
		add.classList.add( 'is-busy' );
		run( function () {
			return fetch( t.addUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json || json.error ) {
						window.location.href = add.href;
						return null;
					}
					return get( window.location.href ).then( function ( doc ) {
						var next = doc.querySelector( '[data-cart]' );
						// A new line item means new markup: swap the whole cart in.
						root.classList.add( 'is-fading' );
						return new Promise( function ( resolve ) {
							setTimeout( function () {
								root.className = next.className + ' is-enhanced is-fading';
								root.innerHTML = next.innerHTML;
								headerCount( doc );
								requestAnimationFrame( function () {
									root.classList.remove( 'is-fading' );
								} );
								ui.toast( ( t.added || '%s added' ).replace( '%s', add.getAttribute( 'data-name' ) ) );
								resolve();
							}, ui.reduced ? 0 : 300 );
						} );
					} );
				} );
		} );
	} );
}() );
