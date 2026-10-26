/**
 * Coming soon page: drifting gold sparkles and the launch countdown. Without JavaScript the
 * page is complete, just without the countdown numbers moving.
 */
( function () {
	'use strict';

	var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Sparkles: a few dozen dots with random size, position, speed and drift.
	var field = document.querySelector( '[data-cs-sparkles]' );
	if ( field && ! reduced ) {
		var count = window.innerWidth < 600 ? 18 : 34;
		for ( var i = 0; i < count; i++ ) {
			var dot = document.createElement( 'span' );
			dot.style.left = ( Math.random() * 100 ).toFixed( 2 ) + '%';
			dot.style.setProperty( '--s', ( 1.5 + Math.random() * 3 ).toFixed( 1 ) + 'px' );
			dot.style.setProperty( '--t', ( 10 + Math.random() * 14 ).toFixed( 1 ) + 's' );
			dot.style.setProperty( '--delay', ( -Math.random() * 20 ).toFixed( 1 ) + 's' );
			dot.style.setProperty( '--x', ( Math.random() * 120 - 60 ).toFixed( 0 ) + 'px' );
			field.appendChild( dot );
		}
	}

	// Countdown to the launch time (seconds since 1970, from the server).
	var box = document.querySelector( '[data-cs-launch]' );
	if ( ! box ) {
		return;
	}
	var launch = parseInt( box.getAttribute( 'data-cs-launch' ), 10 ) * 1000;
	var parts = {
		d: box.querySelector( '[data-cs-d]' ),
		h: box.querySelector( '[data-cs-h]' ),
		m: box.querySelector( '[data-cs-m]' ),
		s: box.querySelector( '[data-cs-s]' )
	};
	var pad = function ( n ) {
		return n < 10 ? '0' + n : String( n );
	};
	var set = function ( el, value ) {
		if ( el.textContent === value ) {
			return;
		}
		el.textContent = value;
		if ( ! reduced ) {
			el.classList.remove( 'is-tick' );
			void el.offsetWidth; // Restart the tick animation.
			el.classList.add( 'is-tick' );
		}
	};
	var timer;
	var tick = function () {
		var left = Math.max( 0, Math.floor( ( launch - Date.now() ) / 1000 ) );
		set( parts.d, pad( Math.floor( left / 86400 ) ) );
		set( parts.h, pad( Math.floor( ( left % 86400 ) / 3600 ) ) );
		set( parts.m, pad( Math.floor( ( left % 3600 ) / 60 ) ) );
		set( parts.s, pad( left % 60 ) );
		if ( ! left ) {
			clearInterval( timer );
			// Launch time reached: reload once so the shop shows if the page has been switched off.
			setTimeout( function () {
				window.location.reload();
			}, 3000 );
		}
	};
	tick();
	timer = setInterval( tick, 1000 );
}() );
