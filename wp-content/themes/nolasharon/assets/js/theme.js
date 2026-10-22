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
