/**
 * NolaSharon theme interactions: light/dark toggle, mobile menu,
 * Resources dropdown, mobile search and the click-to-load video.
 */
( function () {
	'use strict';

	var root = document.documentElement;

	// Light / dark theme.
	function currentTheme() {
		return root.getAttribute( 'data-theme' ) === 'dark' ? 'dark' : 'light';
	}

	function syncThemeButtons() {
		var dark = currentTheme() === 'dark';
		document.querySelectorAll( '.theme-toggle' ).forEach( function ( btn ) {
			btn.setAttribute( 'aria-label', btn.getAttribute( dark ? 'data-label-light' : 'data-label-dark' ) );
		} );
	}

	document.querySelectorAll( '.theme-toggle' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var next = currentTheme() === 'dark' ? 'light' : 'dark';
			root.setAttribute( 'data-theme', next );
			try {
				localStorage.setItem( 'ns-theme', next );
			} catch ( e ) {}
			syncThemeButtons();
		} );
	} );
	syncThemeButtons();

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

	// Resources dropdown.
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

	// "Load More" on product listings: fetch the next page and append its cards.
	document.querySelectorAll( '.load-more' ).forEach( function ( link ) {
		var grid = document.getElementById( link.getAttribute( 'data-grid' ) );
		if ( ! grid || ! window.fetch || ! window.DOMParser ) {
			return;
		}
		link.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( link.classList.contains( 'is-loading' ) ) {
				return;
			}
			link.classList.add( 'is-loading' );
			link.setAttribute( 'aria-busy', 'true' );

			fetch( link.href, { credentials: 'same-origin' } )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( response.status );
					}
					return response.text();
				} )
				.then( function ( html ) {
					var doc = new DOMParser().parseFromString( html, 'text/html' );
					var cards = doc.querySelectorAll( '#' + grid.id + ' > .product-card' );
					var next = doc.querySelector( '.load-more' );
					var first = null;

					cards.forEach( function ( card ) {
						var node = document.importNode( card, true );
						grid.appendChild( node );
						first = first || node;
					} );
					if ( first ) {
						first.focus( { preventScroll: true } );
					}

					if ( next ) {
						link.href = next.getAttribute( 'href' );
						link.classList.remove( 'is-loading' );
						link.removeAttribute( 'aria-busy' );
					} else {
						link.parentElement.remove();
					}
				} )
				.catch( function () {
					window.location.href = link.href;
				} );
		} );
	} );

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
