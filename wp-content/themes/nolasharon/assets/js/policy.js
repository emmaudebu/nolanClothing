/**
 * Policy pages (CONTENT-PAGES.md §4): the "On this page" list follows the section being
 * read (a brand bar slides to it), folds into a dropdown on phones, the print button, and a
 * short fade when moving between policy tabs. Without JavaScript the list is plain links.
 */
( function () {
	'use strict';

	var ui = window.nsUI;
	var toc = document.querySelector( '[data-pol-toc]' );

	if ( toc ) {
		var links = Array.prototype.slice.call( toc.querySelectorAll( '.pol-toc__list a' ) );
		var bar = toc.querySelector( '.pol-toc__bar' );
		var heads = links.map( function ( link ) {
			return document.getElementById( link.getAttribute( 'href' ).slice( 1 ) );
		} );
		var current = -1;

		var setActive = function ( index ) {
			if ( index === current || ! links[ index ] ) {
				return;
			}
			current = index;
			links.forEach( function ( link, i ) {
				if ( i === index ) {
					link.setAttribute( 'aria-current', 'true' );
				} else {
					link.removeAttribute( 'aria-current' );
				}
			} );
			bar.style.transform = 'translateY(' + links[ index ].offsetTop + 'px)';
			bar.style.height = links[ index ].offsetHeight + 'px';
		};

		var frame;
		var spy = function () {
			cancelAnimationFrame( frame );
			frame = requestAnimationFrame( function () {
				var index = 0;
				heads.forEach( function ( head, i ) {
					if ( head && head.getBoundingClientRect().top < 160 ) {
						index = i;
					}
				} );
				setActive( index );
			} );
		};
		window.addEventListener( 'scroll', spy, { passive: true } );
		window.addEventListener( 'resize', spy );
		spy();

		// Phones: the list folds into an "On this page" dropdown.
		var toggle = toc.querySelector( '[data-pol-toc-toggle]' );
		var wrap = toc.querySelector( '.pol-toc__wrap' );
		var narrow = window.matchMedia( '(max-width: 900px)' );
		var syncNarrow = function () {
			toggle.hidden = ! narrow.matches;
			wrap.classList.toggle( 'ns-collapse', narrow.matches );
			if ( narrow.matches ) {
				ui.collapse( wrap, toggle.getAttribute( 'aria-expanded' ) === 'true' );
			} else {
				wrap.removeAttribute( 'inert' );
				wrap.classList.remove( 'is-open' );
			}
		};
		syncNarrow();
		( narrow.addEventListener ? narrow.addEventListener.bind( narrow, 'change' ) : narrow.addListener.bind( narrow ) )( syncNarrow );
		toggle.addEventListener( 'click', function () {
			var open = toggle.getAttribute( 'aria-expanded' ) !== 'true';
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			ui.collapse( wrap, open );
		} );
		links.forEach( function ( link ) {
			link.addEventListener( 'click', function () {
				if ( narrow.matches ) {
					toggle.setAttribute( 'aria-expanded', 'false' );
					ui.collapse( wrap, false );
				}
			} );
		} );

		var print = toc.querySelector( '[data-pol-print]' );
		if ( print && window.print ) {
			print.hidden = false;
			print.addEventListener( 'click', function () {
				window.print();
			} );
		}
	}

	// Policy tabs are links to separate pages; fade the content out before leaving.
	if ( ! ui.reduced ) {
		document.addEventListener( 'click', function ( e ) {
			var tab = e.target.closest( '[data-pol-tab]' );
			if ( ! tab || tab.getAttribute( 'aria-current' ) || e.metaKey || e.ctrlKey || e.shiftKey ) {
				return;
			}
			e.preventDefault();
			document.querySelectorAll( '[data-pol-fade]' ).forEach( function ( el ) {
				el.classList.add( 'is-leaving' );
			} );
			setTimeout( function () {
				window.location.href = tab.href;
			}, 220 );
		} );
	}
}() );
