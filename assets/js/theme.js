/**
 * NolaSharon theme interactions: menu, search, wishlist,
 * dialogs, gallery, buy form, cart panel, quick view, filters and the click-to-load video.
 */
( function () {
	'use strict';

	var root = document.documentElement;

	// Mobile menu.
	var menuToggle = document.querySelector( '.menu-toggle' );
	var nav = document.getElementById( 'main-nav' );
	if ( menuToggle && nav ) {
		menuToggle.addEventListener( 'click', function () {
			var open = nav.classList.toggle( 'is-open' );
			menuToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			menuToggle.setAttribute( 'aria-label', menuToggle.getAttribute( open ? 'data-label-close' : 'data-label-open' ) );
			document.body.classList.toggle( 'nav-open', open );
		} );
	}

	// Categories dropdown.
	document.querySelectorAll( '.main-nav__sub-toggle' ).forEach( function ( btn ) {
		var item = btn.parentElement;
		btn.addEventListener( 'click', function () {
			var open = item.classList.toggle( 'is-open' );
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
		item.addEventListener( 'focusout', function ( e ) {
			if ( ! item.contains( e.relatedTarget ) ) {
				item.classList.remove( 'is-open' );
				btn.setAttribute( 'aria-expanded', 'false' );
			}
		} );
		item.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && item.classList.contains( 'is-open' ) ) {
				item.classList.remove( 'is-open' );
				btn.setAttribute( 'aria-expanded', 'false' );
				btn.focus();
			}
		} );
	} );

	document.addEventListener( 'click', function ( e ) {
		document.querySelectorAll( '.has-sub.is-open' ).forEach( function ( item ) {
			if ( ! item.contains( e.target ) ) {
				item.classList.remove( 'is-open' );
				item.querySelector( '.main-nav__sub-toggle' ).setAttribute( 'aria-expanded', 'false' );
			}
		} );
	} );

	// Mobile search.
	var searchToggle = document.querySelector( '.search-toggle' );
	var search = document.getElementById( 'header-search' );
	if ( searchToggle && search ) {
		searchToggle.addEventListener( 'click', function () {
			var open = search.classList.toggle( 'is-open' );
			searchToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( open ) {
				search.querySelector( 'input[type="search"]' ).focus();
			}
		} );
	}

	// Wishlist hearts. State comes from the cookie the server keeps in sync, so cached pages stay correct.
	var wishCfg = window.nolasharonWishlist;

	function wishlistIds() {
		if ( ! wishCfg ) {
			return [];
		}
		var match = document.cookie.match( new RegExp( '(?:^|; )' + wishCfg.cookie + '=([^;]*)' ) );
		return match ? decodeURIComponent( match[ 1 ] ).split( ',' ).filter( Boolean ) : [];
	}

	function syncWishlist( ids ) {
		ids = ids || wishlistIds();
		document.querySelectorAll( '[data-wishlist]' ).forEach( function ( btn ) {
			btn.setAttribute( 'aria-pressed', ids.indexOf( btn.getAttribute( 'data-wishlist' ) ) > -1 ? 'true' : 'false' );
		} );
		document.querySelectorAll( '[data-wishlist-count]' ).forEach( function ( badge ) {
			badge.textContent = ids.length;
			badge.hidden = ids.length === 0;
			var link = badge.closest( 'a' );
			if ( link && wishCfg ) {
				link.setAttribute( 'aria-label', wishCfg.label.replace( '%d', ids.length ) );
			}
		} );
	}

	function toast( message, link ) {
		window.nsUI.toast( message, link ? { label: wishCfg.view, href: link } : null );
	}

	if ( wishCfg ) {
		syncWishlist();

		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-wishlist]' );
			if ( ! btn || btn.classList.contains( 'is-busy' ) ) {
				return;
			}
			e.preventDefault();
			var id = btn.getAttribute( 'data-wishlist' );
			var wasOn = btn.getAttribute( 'aria-pressed' ) === 'true';
			btn.classList.add( 'is-busy' );
			btn.setAttribute( 'aria-pressed', wasOn ? 'false' : 'true' ); // Optimistic.

			var body = new FormData();
			body.append( 'action', 'nolasharon_wishlist' );
			body.append( 'product_id', id );
			body.append( 'nonce', wishCfg.nonce );

			fetch( wishCfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json.success ) {
						throw new Error( 'wishlist' );
					}
					var ids = json.data.ids.map( String );
					syncWishlist( ids );
					toast( json.data.added ? wishCfg.added : wishCfg.removed, json.data.added ? wishCfg.url : '' );

					// On the wishlist page, drop the card that was removed.
					var page = btn.closest( '[data-wishlist-page]' );
					if ( page && ! json.data.added ) {
						btn.closest( '.pcard' ).remove();
						if ( ! page.querySelector( '.pcard' ) ) {
							page.querySelector( '.wishlist__empty' ).hidden = false;
						}
					}
				} )
				.catch( function () {
					btn.setAttribute( 'aria-pressed', wasOn ? 'true' : 'false' );
					toast( wishCfg.error );
				} )
				.then( function () {
					btn.classList.remove( 'is-busy' );
				} );
		} );
	}

	// Size guide: inches or centimetres, with the chosen size's row highlighted.
	function prepareSizeGuide( dialog, form ) {
		var units = dialog.querySelector( '[data-units]' );
		var chosen = form ? form.querySelector( '.attr-group--size input:checked' ) : null;
		var size = chosen ? chosen.getAttribute( 'data-label' ).toLowerCase() : '';
		dialog.querySelectorAll( 'tr[data-size]' ).forEach( function ( row ) {
			row.classList.toggle( 'is-chosen', row.getAttribute( 'data-size' ) === size );
		} );
		if ( units.hidden ) {
			units.hidden = false;
			units.addEventListener( 'click', function ( e ) {
				var btn = e.target.closest( '[data-unit]' );
				if ( ! btn ) {
					return;
				}
				var cm = btn.getAttribute( 'data-unit' ) === 'cm';
				window.nsUI.seg( units, cm ? 1 : 0 );
				var line = dialog.querySelector( '[data-unit-line]' );
				line.textContent = line.getAttribute( cm ? 'data-cm' : 'data-in' );
				dialog.querySelectorAll( 'td[data-in]' ).forEach( function ( cell ) {
					var value = cell.getAttribute( 'data-in' );
					cell.textContent = cm ? value.replace( /\d+(?:\.\d+)?/g, function ( n ) {
						return String( Math.round( parseFloat( n ) * 2.54 ) );
					} ) : value;
				} );
			} );
		}
	}

	// Dialogs (size guide, quick view, drawer): delegated so content loaded later works too.
	document.addEventListener( 'click', function ( e ) {
		var opener = e.target.closest( '[data-open-dialog]' );
		if ( opener ) {
			var target = document.getElementById( opener.getAttribute( 'data-open-dialog' ) );
			if ( target && target.showModal ) {
				if ( target.hasAttribute( 'data-size-guide' ) ) {
					prepareSizeGuide( target, opener.closest( 'form' ) );
				}
				window.nsUI.modal( target, opener );
			}
			return;
		}
		var closer = e.target.closest( '[data-close-dialog]' );
		if ( closer && closer.closest( 'dialog' ) ) {
			closer.closest( 'dialog' ).close();
			return;
		}
		// A click on the dialog element outside its box is a click on its backdrop.
		if ( e.target.tagName === 'DIALOG' ) {
			var box = e.target.getBoundingClientRect();
			if ( e.clientX < box.left || e.clientX > box.right || e.clientY < box.top || e.clientY > box.bottom ) {
				e.target.close();
			}
		}
	} );

	// Buy form (SHOP-PRODUCT.md §3.3–§3.4): colour and size choices, sold-out options, the
	// price and stock line, the WhatsApp link and Add to Cart in place. Used on the product
	// page and for forms loaded into the quick view. Dispatches "ns:selection" so the gallery
	// can show the chosen variation's photo and the sticky bar its summary.
	function initBuyForm( form ) {
		// The quick view's submit button sits in its footer, linked by the form attribute.
		var submit = form.querySelector( '.pbuy__submit' ) || document.querySelector( '.pbuy__submit[form="' + form.id + '"]' );
		var hint = form.querySelector( '[data-hint]' );
		var qty = form.querySelector( '.qty__input' );
		var variations = form.hasAttribute( 'data-variations' ) ? JSON.parse( form.getAttribute( 'data-variations' ) ) : null;
		var groups = Array.prototype.slice.call( form.querySelectorAll( '[data-attribute]' ) );
		var scope = form.closest( '.pinfo, .qv__inner' ) || document;
		var priceBox = scope.querySelector( '[data-price]' );
		var stickyPrice = form.id === 'ns-form' ? document.querySelector( '[data-sticky-price]' ) : null;
		var defaultPrice = priceBox ? priceBox.innerHTML : '';
		var stock = form.querySelector( '[data-stock]' );
		var variationInput = form.querySelector( 'input[name="variation_id"]' );
		var wa = form.querySelector( '[data-wa]' );
		var current = null;
		var busy = false;

		var selection = function () {
			var sel = {};
			groups.forEach( function ( group ) {
				var checked = group.querySelector( 'input:checked' );
				sel[ group.getAttribute( 'data-attribute' ) ] = checked ? checked.value : '';
			} );
			return sel;
		};

		var chosenLabel = function ( group ) {
			var checked = group.querySelector( 'input:checked' );
			return checked ? checked.getAttribute( 'data-label' ) : '';
		};

		// A variation fits when every chosen value matches (an empty variation value means "any").
		var fits = function ( variation, sel ) {
			return Object.keys( sel ).every( function ( key ) {
				return ! sel[ key ] || ! variation.attrs[ key ] || variation.attrs[ key ] === sel[ key ];
			} );
		};

		var ready = function () {
			return ! variations || !! current;
		};

		var setStock = function ( text, state ) {
			stock.className = 'pbuy__stock stock--' + state;
			stock.querySelector( '[data-stock-text]' ).textContent = text;
		};

		var summary = function () {
			var parts = [];
			groups.forEach( function ( group ) {
				var label = chosenLabel( group );
				if ( label ) {
					parts.push( group.classList.contains( 'attr-group--size' ) && wa ? wa.getAttribute( 'data-wa-size' ).replace( '%s', label ) : label );
				}
			} );
			return parts;
		};

		var refresh = function () {
			var sel = selection();
			if ( variations ) {
				groups.forEach( function ( group ) {
					var name = group.getAttribute( 'data-attribute' );
					group.querySelectorAll( 'input' ).forEach( function ( input ) {
						var test = Object.assign( {}, sel );
						test[ name ] = input.value;
						var available = variations.some( function ( v ) {
							return v.ok && fits( v, test );
						} );
						input.disabled = ! available;
						if ( ! available && input.checked ) {
							input.checked = false;
						}
						input.closest( 'label' ).classList.toggle( 'is-soldout', ! available );
						input.closest( 'label' ).querySelector( '[data-opt-status]' ).textContent = available ? '' : ', ' + hint.getAttribute( 'data-sold-out' );
					} );
					var chosen = group.querySelector( '[data-chosen]' );
					chosen.textContent = chosenLabel( group ) || chosen.getAttribute( 'data-empty' );
					if ( chosenLabel( group ) ) {
						group.querySelector( '[data-group-error]' ).hidden = true;
					}
				} );

				sel = selection();
				var complete = groups.every( function ( group ) {
					return sel[ group.getAttribute( 'data-attribute' ) ];
				} );
				current = null;
				if ( complete ) {
					variations.some( function ( v ) {
						if ( v.ok && fits( v, sel ) ) {
							current = v;
							return true;
						}
						return false;
					} );
				}

				variationInput.value = current ? current.id : 0;
				[ priceBox, stickyPrice ].forEach( function ( box ) {
					if ( box ) {
						box.innerHTML = current ? current.price : defaultPrice;
					}
				} );
				if ( current ) {
					setStock( current.stock, current.state );
				} else if ( complete ) {
					setStock( stock.getAttribute( 'data-sold-out' ), 'outofstock' );
				} else {
					setStock( stock.getAttribute( 'data-prompt' ), 'none' );
				}
				if ( qty ) {
					if ( current && current.max ) {
						qty.max = current.max;
						qty.value = Math.min( parseInt( qty.value, 10 ) || 1, current.max );
					} else {
						qty.removeAttribute( 'max' );
					}
				}
			}

			if ( ready() ) {
				hint.textContent = '';
			}

			// WhatsApp message with the chosen colour and size.
			if ( wa ) {
				var parts = summary();
				var name = form.getAttribute( 'data-name' ) + ( parts.length ? ' (' + parts.join( ', ' ) + ')' : '' );
				var text = wa.getAttribute( 'data-wa-text' ).replace( '%1$s', name ).replace( '%2$s', form.getAttribute( 'data-url' ) );
				wa.href = wa.getAttribute( 'data-wa' ).replace( '__NS_WA__', encodeURIComponent( text ) );
			}

			// The photo for the choice so far: the variation's, or any variation in the chosen colour.
			var image = current && current.image ? current.image : null;
			if ( ! image && variations ) {
				variations.some( function ( v ) {
					if ( v.image && fits( v, sel ) && Object.keys( sel ).some( function ( k ) {
						return sel[ k ];
					} ) ) {
						image = v.image;
						return true;
					}
					return false;
				} );
			}
			form.dispatchEvent( new CustomEvent( 'ns:selection', {
				bubbles: true,
				detail: { variation: current, image: image, summary: summary(), ready: ready() }
			} ) );
		};

		var shake = function ( group ) {
			group.classList.remove( 'is-shake' );
			void group.offsetWidth; // Restart the animation.
			group.classList.add( 'is-shake' );
		};

		var setButton = function ( state ) {
			if ( ! submit || ! submit.hasAttribute( 'data-label-adding' ) ) {
				return;
			}
			var label = submit.querySelector( '[data-submit-label]' );
			if ( ! label.hasAttribute( 'data-default' ) ) {
				label.setAttribute( 'data-default', label.textContent );
			}
			submit.classList.toggle( 'is-loading', state === 'adding' );
			submit.classList.toggle( 'is-added', state === 'added' );
			submit.setAttribute( 'aria-busy', state === 'adding' ? 'true' : 'false' );
			label.textContent = state === 'adding' ? submit.getAttribute( 'data-label-adding' ) : state === 'added' ? submit.getAttribute( 'data-label-added' ) : label.getAttribute( 'data-default' );
		};

		// Add without leaving the page, then show the drawer with the line just added.
		var addInPlace = function () {
			var dialog = form.closest( 'dialog' );
			var qvButton = dialog && submit ? submit : null;
			var qvLabel = qvButton ? qvButton.innerHTML : '';
			busy = true;
			hint.textContent = '';
			setButton( 'adding' );
			if ( qvButton ) {
				qvButton.disabled = true;
				qvButton.textContent = dialog.getAttribute( 'data-adding' );
			}

			fetch( form.getAttribute( 'data-add' ), { method: 'POST', body: new FormData( form ), credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json || ! json.success ) {
						throw new Error( json && json.data && json.data.message );
					}
					// WooCommerce's own fragments (mini cart widgets); the header badge bumps below.
					Object.keys( json.data.fragments || {} ).forEach( function ( selector ) {
						if ( selector.indexOf( 'header-cart' ) === -1 ) {
							document.querySelectorAll( selector ).forEach( function ( el ) {
								el.outerHTML = json.data.fragments[ selector ];
							} );
						}
					} );
					window.nsUI.cartCount( json.data.count );
					if ( dialog ) {
						dialog.close();
					} else {
						setButton( 'added' );
						setTimeout( function () {
							setButton( '' );
						}, 2600 );
					}
					if ( typeof openDrawer === 'function' ) {
						openDrawer( json.data.key );
					}
				} )
				.catch( function ( err ) {
					setButton( '' );
					hint.textContent = ( err && err.message ) || hint.getAttribute( 'data-error' );
				} )
				.then( function () {
					busy = false;
					if ( qvButton ) {
						qvButton.disabled = false;
						qvButton.innerHTML = qvLabel;
					}
				} );
		};

		form.addEventListener( 'change', refresh );

		form.addEventListener( 'submit', function ( e ) {
			if ( busy ) {
				e.preventDefault();
				return;
			}
			if ( ! ready() ) {
				e.preventDefault();
				var sel = selection();
				var empty = groups.filter( function ( group ) {
					return ! sel[ group.getAttribute( 'data-attribute' ) ];
				} );
				if ( empty.length ) {
					empty[ 0 ].querySelector( '[data-group-error]' ).hidden = false;
					shake( empty[ 0 ] );
					var focus = empty[ 0 ].querySelector( 'input:not(:disabled)' );
					if ( focus ) {
						focus.focus();
					}
				} else {
					hint.textContent = hint.getAttribute( 'data-msg-soldout' );
				}
				return;
			}
			if ( form.hasAttribute( 'data-ajax' ) && window.fetch ) {
				e.preventDefault();
				addInPlace();
			}
		} );

		form.querySelectorAll( '.qty__btn' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var min = parseInt( qty.min, 10 ) || 1;
				var max = parseInt( qty.max, 10 ) || Infinity;
				var next = ( parseInt( qty.value, 10 ) || min ) + parseInt( btn.getAttribute( 'data-step' ), 10 );
				qty.value = Math.min( max, Math.max( min, next ) );
			} );
		} );

		// A product with a single option per attribute is preselected.
		groups.forEach( function ( group ) {
			var inputs = group.querySelectorAll( 'input' );
			if ( inputs.length === 1 ) {
				inputs[ 0 ].checked = true;
			}
		} );
		refresh();
	}

	document.querySelectorAll( '.pbuy__form' ).forEach( initBuyForm );

	// Sticky Add to Cart bar on phones, shown once the main button scrolls out of view.
	var sticky = document.querySelector( '[data-sticky-buy]' );
	var stickyForm = document.getElementById( 'ns-form' );
	if ( sticky && stickyForm ) {
		var mainActions = stickyForm.querySelector( '.pbuy__actions' );
		var stickyMeta = sticky.querySelector( '[data-sticky-meta]' );
		var stickyFrame;
		var placeSticky = function () {
			cancelAnimationFrame( stickyFrame );
			stickyFrame = requestAnimationFrame( function () {
				sticky.classList.toggle( 'is-shown', mainActions.getBoundingClientRect().bottom < 0 );
			} );
		};
		window.addEventListener( 'scroll', placeSticky, { passive: true } );
		window.addEventListener( 'resize', placeSticky );
		placeSticky();

		stickyForm.addEventListener( 'ns:selection', function ( e ) {
			stickyMeta.textContent = e.detail.summary.length ? e.detail.summary.join( ' · ' ) : stickyMeta.getAttribute( 'data-empty' );
		} );

		sticky.querySelector( '[data-sticky-submit]' ).addEventListener( 'click', function () {
			var mainSubmit = stickyForm.querySelector( '.pbuy__submit' );
			var missing = stickyForm.querySelector( '[data-attribute]:not(:has(input:checked))' );
			if ( missing ) {
				missing.scrollIntoView( { behavior: window.nsUI.reduced ? 'auto' : 'smooth', block: 'center' } );
			}
			stickyForm.requestSubmit( mainSubmit );
		} );
	}

	// Cart panel: the bag, opened from the header cart. Checkout happens on the Checkout page.
	var drawer = document.getElementById( 'cart-drawer' );
	var openDrawer;
	if ( drawer && window.fetch ) {
		var drawerBody = drawer.querySelector( '[data-drawer-body]' );
		var qtyTimer;

		var setCount = window.nsUI.cartCount;

		var request = function ( op, extra ) {
			var body = new FormData();
			var nonce = drawerBody.querySelector( '[data-drawer-nonce]' );
			body.set( 'op', op );
			body.set( 'nonce', nonce ? nonce.value : '' );
			Object.keys( extra || {} ).forEach( function ( key ) {
				body.set( key, extra[ key ] );
			} );
			return fetch( drawer.getAttribute( 'data-endpoint' ), { method: 'POST', body: body, credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json.success ) {
						throw new Error( ( json.data && json.data.message ) || drawer.getAttribute( 'data-error' ) );
					}
					return json.data;
				} );
		};

		var showMessage = function ( text ) {
			var p = document.createElement( 'p' );
			p.className = 'drawer-msg';
			p.setAttribute( 'role', 'alert' );
			p.textContent = text;
			drawerBody.prepend( p );
		};

		var title = drawer.querySelector( '[data-drawer-title]' );
		var check = drawer.querySelector( '[data-drawer-check]' );

		var render = function ( data ) {
			drawerBody.innerHTML = data.body;
			title.textContent = title.getAttribute( data.added ? 'data-added' : 'data-bag' );
			check.hidden = ! data.added;
			setCount( data.count );
			if ( data.message ) {
				showMessage( data.message );
			}
		};

		var send = function ( op, extra ) {
			drawerBody.classList.add( 'is-busy' );
			return request( op, extra )
				.then( render )
				.catch( function ( err ) {
					showMessage( err.message );
				} )
				.then( function () {
					drawerBody.classList.remove( 'is-busy' );
				} );
		};

		// added: the cart key of a line just added, to show "Added to your cart".
		openDrawer = function ( added ) {
			drawer.showModal();
			root.classList.add( 'drawer-open' );
			send( 'load', added ? { added: added } : null );
		};

		drawer.addEventListener( 'close', function () {
			root.classList.remove( 'drawer-open' );
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( '.header-cart' ) ) {
				e.preventDefault();
				openDrawer();
			}
		} );

		drawerBody.addEventListener( 'click', function ( e ) {
			var remove = e.target.closest( '[data-bag-remove]' );
			if ( remove ) {
				send( 'remove', { key: remove.getAttribute( 'data-bag-remove' ) } );
				return;
			}
			var step = e.target.closest( '.qty__btn' );
			if ( step ) {
				var input = step.parentElement.querySelector( '[data-bag-qty]' );
				var max = parseInt( input.max, 10 ) || Infinity;
				input.value = Math.min( max, Math.max( 1, ( parseInt( input.value, 10 ) || 1 ) + parseInt( step.getAttribute( 'data-step' ), 10 ) ) );
				clearTimeout( qtyTimer );
				qtyTimer = setTimeout( function () {
					send( 'qty', { key: input.getAttribute( 'data-bag-qty' ), qty: input.value } );
				}, 400 );
			}
		} );

		drawerBody.addEventListener( 'change', function ( e ) {
			if ( e.target.matches( '[data-bag-qty]' ) ) {
				send( 'qty', { key: e.target.getAttribute( 'data-bag-qty' ), qty: Math.max( 1, parseInt( e.target.value, 10 ) || 1 ) } );
			}
		} );

		if ( drawer.hasAttribute( 'data-autopen' ) ) {
			openDrawer();
		}
	}

	// Announcement bar: folds away when dismissed, remembered per message in this browser.
	var announce = document.getElementById( 'announce' );
	if ( announce ) {
		announce.querySelector( '.announce__close' ).addEventListener( 'click', function () {
			window.nsUI.collapse( announce, false );
			setTimeout( function () {
				announce.hidden = true;
			}, window.nsUI.reduced ? 0 : 400 );
			try {
				localStorage.setItem( 'ns-announce', announce.getAttribute( 'data-key' ) );
			} catch ( e ) {}
		} );
	}

	// Quick view: opened from the product cards, filled on demand.
	var quickView = document.getElementById( 'quick-view' );
	if ( quickView && window.fetch ) {
		var qvBody = quickView.querySelector( '[data-qv-body]' );

		var qvMessage = function ( text ) {
			qvBody.innerHTML = '<p class="qv__loading"></p>';
			qvBody.firstChild.textContent = text;
		};

		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-quick-view]' );
			if ( ! btn ) {
				return;
			}
			e.preventDefault();
			qvMessage( quickView.getAttribute( 'data-loading' ) );
			quickView.showModal();

			var url = quickView.getAttribute( 'data-endpoint' );
			url += ( url.indexOf( '?' ) > -1 ? '&' : '?' ) + 'id=' + encodeURIComponent( btn.getAttribute( 'data-quick-view' ) );

			fetch( url, { credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json.success ) {
						throw new Error( json.data && json.data.message );
					}
					qvBody.innerHTML = json.data.html;
					quickView.setAttribute( 'aria-labelledby', 'qv-title' );
					var buyForm = qvBody.querySelector( '.pbuy__form' );
					if ( buyForm ) {
						initBuyForm( buyForm );
					}
				} )
				.catch( function ( err ) {
					qvMessage( err.message || quickView.getAttribute( 'data-error' ) );
				} );
		} );

		// Clear the content once the closing animation has finished, unless it was reopened.
		quickView.addEventListener( 'close', function () {
			setTimeout( function () {
				if ( ! quickView.open ) {
					qvBody.innerHTML = '';
					quickView.removeAttribute( 'aria-labelledby' );
				}
			}, 400 );
		} );
	}

	// Click-to-load YouTube embed.
	document.querySelectorAll( '.video-embed[data-video-id]' ).forEach( function ( box ) {
		var btn = box.querySelector( '.video-embed__play' );
		if ( ! btn ) {
			return;
		}
		btn.addEventListener( 'click', function () {
			var iframe = document.createElement( 'iframe' );
			iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent( box.getAttribute( 'data-video-id' ) ) + '?autoplay=1&rel=0';
			iframe.title = btn.getAttribute( 'aria-label' );
			iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen';
			iframe.allowFullscreen = true;
			box.innerHTML = '';
			box.appendChild( iframe );
		} );
	} );
} )();
