/**
 * Product page (SHOP-PRODUCT.md §3.2–§3.3): the gallery (thumbnails, cross-fade, hover
 * zoom, swipe carousel with dots, PhotoSwipe lightbox, the chosen variation's photo) and
 * the accordions. The buy form, size guide and sticky bar live in theme.js, shared with
 * the quick view.
 */
( function () {
	'use strict';

	var ui = window.nsUI;

	/* Gallery ---------------------------------------------------------------- */

	var gallery = document.querySelector( '[data-gallery]' );
	if ( gallery ) {
		var items = JSON.parse( gallery.getAttribute( 'data-items' ) || '[]' );
		var slides = Array.prototype.slice.call( gallery.querySelectorAll( '.pg__slide' ) );
		var thumbs = Array.prototype.slice.call( gallery.querySelectorAll( '.pg__thumb' ) );
		var track = gallery.querySelector( '[data-track]' );
		var cells = Array.prototype.slice.call( gallery.querySelectorAll( '.pg__cell img' ) );
		var dots = Array.prototype.slice.call( gallery.querySelectorAll( '.pg__dot' ) );
		var main = gallery.querySelector( '[data-zoom]' );
		var active = 0;
		var original = null; // The first slide's own photo, restored when a variation is cleared.

		var show = function ( index ) {
			active = Math.max( 0, Math.min( slides.length - 1, index ) );
			slides.forEach( function ( slide, i ) {
				slide.classList.toggle( 'is-active', i === active );
				slide.toggleAttribute( 'aria-hidden', i !== active );
				slide.style.transform = '';
			} );
			thumbs.forEach( function ( thumb, i ) {
				thumb.setAttribute( 'aria-pressed', i === active ? 'true' : 'false' );
			} );
		};

		thumbs.forEach( function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				show( parseInt( thumb.getAttribute( 'data-show' ), 10 ) );
			} );
		} );

		// Hover zoom: about 1.9x, centred on the cursor.
		if ( main && window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches && ! ui.reduced ) {
			main.addEventListener( 'mousemove', function ( e ) {
				var box = main.getBoundingClientRect();
				var slide = slides[ active ];
				slide.style.transformOrigin = ( ( e.clientX - box.left ) / box.width * 100 ) + '% ' + ( ( e.clientY - box.top ) / box.height * 100 ) + '%';
				slide.style.transform = 'scale(1.9)';
				main.classList.add( 'is-zooming' );
			} );
			main.addEventListener( 'mouseleave', function () {
				slides[ active ].style.transform = '';
				main.classList.remove( 'is-zooming' );
			} );
		}

		// Carousel dots follow the scroll position.
		if ( track && dots.length ) {
			var raf;
			track.addEventListener( 'scroll', function () {
				cancelAnimationFrame( raf );
				raf = requestAnimationFrame( function () {
					var index = Math.round( track.scrollLeft / track.clientWidth );
					dots.forEach( function ( dot, i ) {
						dot.classList.toggle( 'is-active', i === index );
					} );
				} );
			} );
		}

		// Full-screen lightbox (WooCommerce's bundled PhotoSwipe).
		var openLightbox = function ( index ) {
			var element = document.querySelector( '.pswp' );
			if ( ! element || typeof window.PhotoSwipe === 'undefined' ) {
				window.open( items[ index ].src, '_blank', 'noopener' );
				return;
			}
			var opener = document.activeElement;
			var pswp = new window.PhotoSwipe( element, window.PhotoSwipeUI_Default, items, {
				index: index,
				shareEl: false,
				history: false,
				bgOpacity: 0.92,
				showHideOpacity: true
			} );
			pswp.listen( 'close', function () {
				if ( opener && opener.focus ) {
					opener.focus();
				}
			} );
			pswp.init();
		};

		gallery.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-open-lightbox]' );
			if ( btn ) {
				var value = btn.getAttribute( 'data-open-lightbox' );
				openLightbox( value === '' ? active : parseInt( value, 10 ) );
			}
		} );

		// The chosen variation's photo: jump to it if it is in the gallery, otherwise show it
		// in place of the first photo.
		document.addEventListener( 'ns:selection', function ( e ) {
			if ( e.target.id !== 'ns-form' ) {
				return;
			}
			var image = e.detail.image;
			if ( ! original ) {
				original = { src: slides[ 0 ].src, srcset: slides[ 0 ].getAttribute( 'srcset' ) || '', item: items[ 0 ] };
			}
			var setFirst = function ( src, srcset, item ) {
				[ slides[ 0 ], cells[ 0 ] ].forEach( function ( img ) {
					if ( img ) {
						img.src = src;
						if ( srcset ) {
							img.setAttribute( 'srcset', srcset );
						} else {
							img.removeAttribute( 'srcset' );
						}
					}
				} );
				if ( thumbs[ 0 ] ) {
					thumbs[ 0 ].querySelector( 'img' ).src = item.thumb || src;
				}
				items[ 0 ] = { src: item.src, w: item.w, h: item.h, title: item.title };
			};
			if ( ! image ) {
				if ( slides[ 0 ].src !== original.src ) {
					setFirst( original.src, original.srcset, { src: original.item.src, w: original.item.w, h: original.item.h, title: original.item.title, thumb: '' } );
				}
				return;
			}
			var at = -1;
			items.forEach( function ( item, i ) {
				if ( item.src === image.full ) {
					at = i;
				}
			} );
			if ( at < 0 ) {
				setFirst( image.src, image.srcset, { src: image.full, w: image.w, h: image.h, title: image.alt, thumb: image.thumb } );
				at = 0;
			}
			show( at );
			if ( track && track.clientWidth ) {
				track.scrollTo( { left: at * track.clientWidth, behavior: ui.reduced ? 'auto' : 'smooth' } );
			}
		} );
	}

	/* Accordions: "+" turns to "×"; Description starts open. ----------------- */

	document.querySelectorAll( '[data-acc-toggle]' ).forEach( function ( btn ) {
		var body = document.getElementById( btn.getAttribute( 'aria-controls' ) );
		if ( body.hasAttribute( 'data-start-closed' ) ) {
			btn.setAttribute( 'aria-expanded', 'false' );
			ui.collapse( body, false );
		}
		btn.addEventListener( 'click', function () {
			var open = btn.getAttribute( 'aria-expanded' ) !== 'true';
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			ui.collapse( body, open );
		} );
	} );
}() );
