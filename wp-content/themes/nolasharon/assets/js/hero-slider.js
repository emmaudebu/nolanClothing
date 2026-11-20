/**
 * Homepage hero slideshow (template-parts/hero-slider.php).
 *
 * The effect, seconds per slide and transition length are read from the slider's data
 * attributes and --hs-speed on every change, so Customizer edits apply without a reload.
 * Autoplay pauses on hover, on keyboard focus, off screen, in a background tab and with the
 * pause button. Visitors who prefer reduced motion get a short fade and no autoplay.
 */
( function () {
	'use strict';

	var EFFECTS = [ 'fade', 'slide', 'slide-up', 'zoom-in', 'zoom-out', 'kenburns', 'flip', 'cube', 'blur', 'wipe', 'split', 'circle' ];
	var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function init( root ) {
		var slides = root.querySelectorAll( '.hslider__slide' );
		var dots = root.querySelectorAll( '[data-hslider-dot]' );
		var toggle = root.querySelector( '[data-hslider-toggle]' );
		var track = root.querySelector( '.hslider__track' );
		// Editorial design: one product card per slide, beside the slider.
		var cardBox = root.parentNode.querySelector( '.hcard' );
		var cards = root.parentNode.querySelectorAll( '[data-hcard-item]' );
		var current = 0;
		var leaving = null;
		var cleanup = null;
		var timer = null;
		var lastFx = root.dataset.fx;
		var paused = false;
		var hover = false;
		var focus = false;
		var onScreen = true;

		function speed() {
			var ms = parseFloat( window.getComputedStyle( root ).getPropertyValue( '--hs-speed' ) );
			return isNaN( ms ) ? 900 : ms;
		}

		function interval() {
			return reduce.matches ? 0 : ( parseInt( root.dataset.interval, 10 ) || 0 ) * 1000;
		}

		function effect() {
			var fx = root.dataset.effect;
			if ( reduce.matches ) {
				return 'fade';
			}
			if ( 'random' === fx ) {
				var pool = EFFECTS.filter( function ( name ) {
					return name !== lastFx;
				} );
				return pool[ Math.floor( Math.random() * pool.length ) ];
			}
			return -1 < EFFECTS.indexOf( fx ) ? fx : 'fade';
		}

		function finish() {
			window.clearTimeout( cleanup );
			if ( leaving ) {
				leaving.classList.remove( 'is-leaving' );
				leaving = null;
			}
		}

		function schedule() {
			var ms = interval();
			window.clearTimeout( timer );
			root.classList.toggle( 'hslider--auto', 0 < ms );
			if ( ms && ! paused && ! hover && ! focus && onScreen && ! document.hidden ) {
				timer = window.setTimeout( function () {
					go( current + 1, 'next' );
				}, ms );
			}
		}

		function go( index, dir ) {
			index = ( index + slides.length ) % slides.length;
			if ( index === current ) {
				return;
			}
			var from = slides[ current ];
			var to = slides[ index ];

			finish();
			lastFx = effect();
			root.dataset.fx = lastFx;
			root.dataset.dir = dir;

			// Put the incoming slide in its starting pose, then let both slides transition.
			to.classList.add( 'is-entering' );
			void to.offsetWidth; // eslint-disable-line no-void
			from.classList.remove( 'is-active' );
			from.classList.add( 'is-leaving' );
			to.classList.remove( 'is-entering' );
			to.classList.add( 'is-active' );

			leaving = from;
			cleanup = window.setTimeout( finish, speed() + 60 );
			current = index;
			if ( 1 < cards.length ) {
				cards.forEach( function ( card, i ) {
					card.classList.toggle( 'is-active', i === index );
				} );
				cardBox.classList.toggle( 'is-empty', ! cards[ index ].hasAttribute( 'href' ) );
			}
			dots.forEach( function ( dot, i ) {
				if ( i === index ) {
					dot.setAttribute( 'aria-current', 'true' );
				} else {
					dot.removeAttribute( 'aria-current' );
				}
			} );
			schedule();
		}

		root.querySelector( '[data-hslider-prev]' ).addEventListener( 'click', function () {
			go( current - 1, 'prev' );
		} );
		root.querySelector( '[data-hslider-next]' ).addEventListener( 'click', function () {
			go( current + 1, 'next' );
		} );
		dots.forEach( function ( dot ) {
			dot.addEventListener( 'click', function () {
				var index = parseInt( dot.dataset.hsliderDot, 10 );
				go( index, index < current ? 'prev' : 'next' );
			} );
		} );

		toggle.addEventListener( 'click', function () {
			paused = ! paused;
			root.classList.toggle( 'is-paused', paused );
			toggle.setAttribute( 'aria-label', paused ? toggle.dataset.labelPlay : toggle.dataset.labelPause );
			schedule();
		} );

		root.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key || 'ArrowRight' === event.key ) {
				event.preventDefault();
				go( current + ( 'ArrowLeft' === event.key ? -1 : 1 ), 'ArrowLeft' === event.key ? 'prev' : 'next' );
			}
		} );

		// Swipe on touch screens.
		var startX = 0;
		var startY = 0;
		track.addEventListener( 'touchstart', function ( event ) {
			startX = event.touches[ 0 ].clientX;
			startY = event.touches[ 0 ].clientY;
		}, { passive: true } );
		track.addEventListener( 'touchend', function ( event ) {
			var dx = event.changedTouches[ 0 ].clientX - startX;
			var dy = event.changedTouches[ 0 ].clientY - startY;
			if ( 40 < Math.abs( dx ) && Math.abs( dx ) > Math.abs( dy ) ) {
				go( current + ( 0 > dx ? 1 : -1 ), 0 > dx ? 'next' : 'prev' );
			}
		}, { passive: true } );

		// Hold autoplay while someone is looking closely or can't see it.
		root.addEventListener( 'mouseenter', function () {
			hover = true;
			schedule();
		} );
		root.addEventListener( 'mouseleave', function () {
			hover = false;
			schedule();
		} );
		root.addEventListener( 'focusin', function () {
			focus = true;
			schedule();
		} );
		root.addEventListener( 'focusout', function ( event ) {
			if ( ! root.contains( event.relatedTarget ) ) {
				focus = false;
				schedule();
			}
		} );
		document.addEventListener( 'visibilitychange', schedule );
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				onScreen = entries[ 0 ].isIntersecting;
				schedule();
			} ).observe( root );
		}
		if ( reduce.addEventListener ) {
			reduce.addEventListener( 'change', schedule );
		}

		// Customizer live preview (customize-preview.js): settings changed; detail.demo plays the new effect.
		document.addEventListener( 'ns:hslider-update', function ( event ) {
			if ( event.detail && event.detail.demo ) {
				go( current + 1, 'next' );
			} else {
				schedule();
			}
		} );

		root.classList.add( 'is-ready' );
		schedule();
	}

	document.querySelectorAll( '[data-hslider]' ).forEach( init );
}() );
