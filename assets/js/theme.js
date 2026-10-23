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

	// Dialogs (size guide, lightbox, quick view, drawer): delegated so content loaded later works too.
	document.addEventListener( 'click', function ( e ) {
		var opener = e.target.closest( '[data-open-dialog]' );
		if ( opener ) {
			var target = document.getElementById( opener.getAttribute( 'data-open-dialog' ) );
			if ( target && target.showModal ) {
				target.showModal();
			}
			return;
		}
		var closer = e.target.closest( '[data-close-dialog]' );
		if ( closer && closer.closest( 'dialog' ) ) {
			closer.closest( 'dialog' ).close();
			return;
		}
		// A click on the dialog element itself is a click on its backdrop.
		if ( e.target.tagName === 'DIALOG' ) {
			e.target.close();
		}
	} );

	// Product image lightbox.
	var lightbox = document.getElementById( 'lightbox' );
	var galleryItems = document.querySelectorAll( '.pgallery__item' );
	if ( lightbox && galleryItems.length ) {
		var lbImg = lightbox.querySelector( '.lightbox__img' );
		var lbCount = lightbox.querySelector( '.lightbox__count' );
		var lbIndex = 0;

		var showImage = function ( index ) {
			lbIndex = ( index + galleryItems.length ) % galleryItems.length;
			lbImg.src = galleryItems[ lbIndex ].getAttribute( 'data-full' );
			if ( lbCount ) {
				lbCount.textContent = ( lbIndex + 1 ) + ' / ' + galleryItems.length;
			}
		};

		galleryItems.forEach( function ( item ) {
			item.addEventListener( 'click', function () {
				showImage( parseInt( item.getAttribute( 'data-index' ), 10 ) );
				lightbox.showModal();
			} );
		} );
		lightbox.querySelectorAll( '.lightbox__nav' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				showImage( lbIndex + parseInt( btn.getAttribute( 'data-dir' ), 10 ) );
			} );
		} );
		lightbox.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' || e.key === 'ArrowRight' ) {
				showImage( lbIndex + ( e.key === 'ArrowLeft' ? -1 : 1 ) );
			}
		} );
	}

	// Buy form: colour and size choices, sold-out options, price/stock updates and Add to Cart.
	// Used on the product page and for forms loaded into the quick view.
	function initBuyForm( form ) {
		// The quick view's submit button sits in its footer, linked by the form attribute.
		var submit = form.querySelector( '.pbuy__submit' ) || document.querySelector( '.pbuy__submit[form="' + form.id + '"]' );
		var hint = form.querySelector( '.pbuy__hint' );
		var qty = form.querySelector( '.qty__input' );
		var variations = form.hasAttribute( 'data-variations' ) ? JSON.parse( form.getAttribute( 'data-variations' ) ) : null;
		var groups = Array.prototype.slice.call( form.querySelectorAll( '[data-attribute]' ) );
		var scope = form.closest( '.pbuy, .qv__inner' ) || document;
		var priceBox = scope.querySelector( '[data-price]' );
		var stickyPrice = form.id === 'ns-form' ? document.querySelector( '[data-sticky-price]' ) : null;
		var defaultPrice = priceBox ? priceBox.innerHTML : '';
		var stock = form.querySelector( '[data-stock]' );
		var variationInput = form.querySelector( 'input[name="variation_id"]' );
		var resetBtn = form.querySelector( '[data-reset]' );
		var current = null;

		var selection = function () {
			var sel = {};
			groups.forEach( function ( group ) {
				var checked = group.querySelector( 'input:checked' );
				sel[ group.getAttribute( 'data-attribute' ) ] = checked ? checked.value : '';
			} );
			return sel;
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

		var refresh = function () {
			if ( variations ) {
				var sel = selection();
				groups.forEach( function ( group ) {
					var name = group.getAttribute( 'data-attribute' );
					group.querySelectorAll( 'input' ).forEach( function ( input ) {
						var test = Object.assign( {}, sel );
						test[ name ] = input.value;
						var available = variations.some( function ( v ) {
							return v.ok && fits( v, test );
						} );
						input.disabled = ! available;
						input.closest( 'label' ).classList.toggle( 'is-soldout', ! available );
						input.closest( 'label' ).querySelector( '[data-opt-status]' ).textContent = available ? '' : ', ' + hint.getAttribute( 'data-sold-out' );
					} );
					var chosen = group.querySelector( 'input:checked' );
					group.querySelector( '[data-chosen]' ).textContent = chosen ? ': ' + chosen.getAttribute( 'data-label' ) : '';
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
				stock.hidden = ! current;
				if ( current ) {
					stock.querySelector( '[data-stock-text]' ).textContent = current.stock;
					stock.querySelector( '.stock-dot' ).className = 'stock-dot stock-dot--' + current.state;
				}
				if ( current && current.max ) {
					qty.max = current.max;
					qty.value = Math.min( parseInt( qty.value, 10 ) || 1, current.max );
				} else {
					qty.removeAttribute( 'max' );
				}
				resetBtn.hidden = ! groups.some( function ( group ) {
					return group.querySelector( 'input:checked' );
				} );
			}

			submit.classList.toggle( 'is-disabled', ! ready() );
			submit.setAttribute( 'aria-disabled', ready() ? 'false' : 'true' );
			if ( ready() ) {
				hint.textContent = '';
			}
		};

		// Quick view: add without leaving the page, then show the cart panel.
		var addInPlace = function () {
			var dialog = form.closest( 'dialog' );
			var label = submit.innerHTML;
			submit.disabled = true;
			submit.textContent = dialog.getAttribute( 'data-adding' );

			fetch( dialog.getAttribute( 'data-add' ), { method: 'POST', body: new FormData( form ), credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json.success ) {
						throw new Error( json.data && json.data.message );
					}
					dialog.close();
					window.nsUI.cartCount( json.data.count );
					if ( typeof openDrawer === 'function' ) {
						openDrawer();
					} else {
						window.location.href = document.querySelector( '.header-cart' ).href;
					}
				} )
				.catch( function ( err ) {
					hint.textContent = err.message || dialog.getAttribute( 'data-error' );
				} )
				.then( function () {
					submit.disabled = false;
					submit.innerHTML = label;
				} );
		};

		form.addEventListener( 'change', refresh );

		form.addEventListener( 'submit', function ( e ) {
			if ( ! ready() ) {
				e.preventDefault();
				var sel = selection();
				var empty = groups.filter( function ( group ) {
					return ! sel[ group.getAttribute( 'data-attribute' ) ];
				} );
				hint.textContent = hint.getAttribute( empty.length ? 'data-msg-choose' : 'data-msg-soldout' );
				var focus = ( empty[ 0 ] || groups[ 0 ] ).querySelector( 'input:not(:disabled)' );
				if ( focus ) {
					focus.focus();
				}
			} else if ( form.hasAttribute( 'data-ajax' ) ) {
				e.preventDefault();
				addInPlace();
			}
		} );

		if ( resetBtn ) {
			resetBtn.addEventListener( 'click', function () {
				form.querySelectorAll( '[data-attribute] input:checked' ).forEach( function ( input ) {
					input.checked = false;
				} );
				refresh();
			} );
		}

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

	// Sticky Add to Cart bar on phones, shown while the main button is off screen.
	var sticky = document.querySelector( '[data-sticky-buy]' );
	if ( sticky && 'IntersectionObserver' in window ) {
		var stickyForm = document.getElementById( sticky.querySelector( '[data-sticky-submit]' ).getAttribute( 'data-sticky-submit' ) );
		var mainActions = stickyForm && stickyForm.querySelector( '.pbuy__actions' );
		if ( mainActions ) {
			new IntersectionObserver( function ( entries ) {
				sticky.hidden = entries[ 0 ].isIntersecting;
			} ).observe( mainActions );

			sticky.querySelector( '[data-sticky-submit]' ).addEventListener( 'click', function () {
				var mainSubmit = stickyForm.querySelector( '.pbuy__submit' );
				if ( mainSubmit.getAttribute( 'aria-disabled' ) === 'true' ) {
					stickyForm.scrollIntoView( { behavior: 'smooth', block: 'center' } );
				}
				stickyForm.requestSubmit( mainSubmit );
			} );
		}
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

		var render = function ( data ) {
			drawerBody.innerHTML = data.body;
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

		openDrawer = function () {
			var date = drawer.querySelector( '[data-drawer-date]' );
			var now = new Date();
			date.textContent = date.getAttribute( 'data-today' ) + ' ' +
				now.toLocaleDateString( undefined, { month: 'short', day: 'numeric', year: 'numeric' } ) + ' ' +
				now.toLocaleTimeString( undefined, { hour: '2-digit', minute: '2-digit' } );
			drawer.showModal();
			root.classList.add( 'drawer-open' );
			send( 'load' );
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

	// Announcement bar: dismissed per message, remembered in this browser.
	var announce = document.getElementById( 'announce' );
	if ( announce ) {
		announce.querySelector( '.announce__close' ).addEventListener( 'click', function () {
			announce.hidden = true;
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
					if ( json.data.sizeGuide && ! document.getElementById( 'size-guide' ) ) {
						document.body.insertAdjacentHTML( 'beforeend', json.data.sizeGuide );
					}
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
