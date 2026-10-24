/**
 * Shop, category and search pages (SHOP-PRODUCT.md §2.5): filters apply as soon as they
 * change, pills, chips and pagination load in place, and the browser's back and forward
 * buttons work. Every control is still a normal link or GET form, so the page works without
 * JavaScript and filtered URLs can be shared. Also the grid density toggle, the collapsible
 * sidebar and the bottom sheet on phones.
 */
( function () {
	'use strict';

	var ui = window.nsUI;
	var root = document.querySelector( '[data-shop-root]' );
	if ( ! root ) {
		return;
	}
	var mobile = window.matchMedia( '(max-width: 860px)' );
	var COLS_KEY = 'ns-shop-cols';

	function $( sel, scope ) {
		return ( scope || document ).querySelector( sel );
	}

	/* Grid density (desktop): 3 or 4 per row, remembered in this browser. ---- */

	function applyCols( cols ) {
		var layout = $( '[data-shop-layout]' );
		var density = $( '[data-density]' );
		if ( ! layout || ! density ) {
			return;
		}
		if ( cols ) {
			layout.setAttribute( 'data-cols', cols );
		}
		var current = layout.getAttribute( 'data-cols' ) || ( layout.classList.contains( 'is-collapsed' ) ? '4' : '3' );
		density.querySelectorAll( 'button' ).forEach( function ( btn ) {
			btn.setAttribute( 'aria-pressed', btn.getAttribute( 'data-cols' ) === current ? 'true' : 'false' );
		} );
	}

	try {
		applyCols( localStorage.getItem( COLS_KEY ) );
	} catch ( e ) {
		applyCols();
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-density] button' );
		if ( ! btn ) {
			return;
		}
		applyCols( btn.getAttribute( 'data-cols' ) );
		try {
			localStorage.setItem( COLS_KEY, btn.getAttribute( 'data-cols' ) );
		} catch ( err ) {}
	} );

	/* Filters: collapsible sidebar on desktop, bottom sheet on phones. ------- */

	var toggle = $( '[data-filters-toggle]' );
	var sheetOpen = false;

	function sheet() {
		return $( '#shop-filters' );
	}

	function scrim() {
		return $( '[data-sheet-scrim]' );
	}

	function focusables() {
		return Array.prototype.filter.call( sheet().querySelectorAll( 'a[href], button, input, select' ), function ( el ) {
			return ! el.disabled && el.offsetParent !== null && ! el.closest( '[inert]' );
		} );
	}

	function openSheet() {
		sheetOpen = true;
		var s = scrim();
		s.hidden = false;
		sheet().classList.add( 'is-open' );
		sheet().setAttribute( 'role', 'dialog' );
		sheet().setAttribute( 'aria-modal', 'true' );
		document.body.classList.add( 'sheet-open' );
		toggle.setAttribute( 'aria-expanded', 'true' );
		requestAnimationFrame( function () {
			s.classList.add( 'is-open' );
		} );
		setTimeout( function () {
			var close = $( '[data-sheet-close]', sheet() );
			if ( close ) {
				close.focus();
			}
		}, ui.reduced ? 0 : 120 );
	}

	function closeSheet( returnFocus ) {
		if ( ! sheetOpen ) {
			return;
		}
		sheetOpen = false;
		var s = scrim();
		sheet().classList.remove( 'is-open' );
		sheet().removeAttribute( 'role' );
		sheet().removeAttribute( 'aria-modal' );
		s.classList.remove( 'is-open' );
		document.body.classList.remove( 'sheet-open' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		setTimeout( function () {
			s.hidden = true;
		}, ui.reduced ? 0 : 350 );
		if ( returnFocus !== false ) {
			toggle.focus();
		}
	}

	function syncToggle() {
		if ( mobile.matches ) {
			toggle.setAttribute( 'aria-expanded', sheetOpen ? 'true' : 'false' );
		} else {
			closeSheet( false );
			var collapsed = $( '[data-shop-layout]' ).classList.contains( 'is-collapsed' );
			toggle.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
		}
	}

	if ( toggle ) {
		syncToggle();
		( mobile.addEventListener ? mobile.addEventListener.bind( mobile, 'change' ) : mobile.addListener.bind( mobile ) )( syncToggle );

		toggle.addEventListener( 'click', function () {
			if ( mobile.matches ) {
				openSheet();
				return;
			}
			var layout = $( '[data-shop-layout]' );
			var collapsed = layout.classList.toggle( 'is-collapsed' );
			sheet().toggleAttribute( 'inert', collapsed );
			toggle.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
			applyCols();
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( '[data-sheet-close]' ) || e.target.matches( '[data-sheet-scrim]' ) ) {
				closeSheet();
			} else if ( e.target.closest( '[data-sheet-submit]' ) && sheetOpen ) {
				e.preventDefault();
				closeSheet();
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( ! sheetOpen ) {
				return;
			}
			if ( e.key === 'Escape' ) {
				closeSheet();
			} else if ( e.key === 'Tab' ) {
				var list = focusables();
				var first = list[ 0 ];
				var last = list[ list.length - 1 ];
				if ( e.shiftKey && document.activeElement === first ) {
					e.preventDefault();
					last.focus();
				} else if ( ! e.shiftKey && document.activeElement === last ) {
					e.preventDefault();
					first.focus();
				}
			}
		} );
	}

	/* Filter sections fold open and shut. ---------------------------------- */

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-fsec-toggle]' );
		if ( ! btn ) {
			return;
		}
		var open = btn.getAttribute( 'aria-expanded' ) !== 'true';
		btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		ui.collapse( document.getElementById( btn.getAttribute( 'aria-controls' ) ), open );
	} );

	/* Building URLs from the controls. ------------------------------------ */

	function buildUrl() {
		var form = $( '[data-filters-form]' );
		var params = new URLSearchParams();
		var cats = [];
		var base = form.action;
		var data = new FormData( form );

		data.forEach( function ( value, key ) {
			if ( key === 'product_cat[]' ) {
				cats.push( value );
			} else if ( /\[\]$/.test( key ) ) {
				var name = key.slice( 0, -2 );
				params.set( name, params.get( name ) ? params.get( name ) + ',' + value : value );
			} else if ( value !== '' && key !== 'orderby' ) {
				params.set( key, value );
			}
		} );

		if ( cats.length === 1 && ! params.has( 's' ) ) {
			base = form.querySelector( 'input[name="product_cat[]"][value="' + cats[ 0 ] + '"]' ).getAttribute( 'data-url' );
		} else if ( cats.length ) {
			params.set( 'product_cat', cats.join( ',' ) );
		}

		var sort = $( '[data-sort]' );
		if ( sort && sort.value !== sort.getAttribute( 'data-default' ) ) {
			params.set( 'orderby', sort.value );
		}

		var url = new URL( base, window.location.href );
		params.forEach( function ( value, key ) {
			url.searchParams.set( key, value );
		} );
		return url.toString();
	}

	/* Loading a listing in place. ----------------------------------------- */

	var controller = null;

	function sectionStates() {
		var states = {};
		document.querySelectorAll( '[data-fsec]' ).forEach( function ( sec ) {
			var btn = $( '[data-fsec-toggle]', sec );
			states[ sec.getAttribute( 'data-fsec' ) ] = btn.getAttribute( 'aria-expanded' ) === 'true';
		} );
		return states;
	}

	function swapFilters( next, keepFocused ) {
		var states = sectionStates();
		var scroll = $( '.shop-filters__scroll' );
		var nextScroll = $( '.shop-filters__scroll', next );
		if ( ! scroll || ! nextScroll ) {
			return;
		}
		var focused = document.activeElement;
		var keep = keepFocused && focused ? focused.closest( '[data-fsec]' ) : null;
		var top = scroll.scrollTop;

		Array.prototype.forEach.call( nextScroll.children, function ( child ) {
			var key = child.getAttribute && child.getAttribute( 'data-fsec' );
			if ( key && keep && keep.getAttribute( 'data-fsec' ) === key ) {
				child.replaceWith( keep.cloneNode( false ) ); // Placeholder, swapped back below.
			}
		} );
		var placeholder = keep ? nextScroll.querySelector( '[data-fsec="' + keep.getAttribute( 'data-fsec' ) + '"]' ) : null;
		scroll.innerHTML = '';
		while ( nextScroll.firstChild ) {
			scroll.appendChild( nextScroll.firstChild );
		}
		if ( keep && placeholder ) {
			placeholder.replaceWith( keep );
			focused.focus( { preventScroll: true } );
		}
		// Keep each section open or closed as the shopper left it.
		scroll.querySelectorAll( '[data-fsec]' ).forEach( function ( sec ) {
			var key = sec.getAttribute( 'data-fsec' );
			if ( key in states ) {
				$( '[data-fsec-toggle]', sec ).setAttribute( 'aria-expanded', states[ key ] ? 'true' : 'false' );
				ui.collapse( $( '.fsec__body', sec ), states[ key ] );
			}
		} );
		scroll.scrollTop = top;

		var foot = $( '[data-sheet-submit]' );
		var nextFoot = $( '[data-sheet-submit]', next );
		if ( foot && nextFoot ) {
			foot.textContent = nextFoot.textContent;
		}
		var form = $( '[data-filters-form]' );
		form.action = $( '[data-filters-form]', next ).action;
	}

	function swap( doc, fromControl ) {
		var nextRoot = $( '[data-shop-root]', doc );
		if ( ! nextRoot ) {
			throw new Error( 'shop' );
		}
		// The head band (title, intro, pills) changes when the category does.
		var head = $( '.shop-head' );
		var nextHead = $( '.shop-head', doc );
		if ( head && nextHead && head.innerHTML !== nextHead.innerHTML ) {
			var focusedHref = document.activeElement && document.activeElement.closest( '.shop-pill' ) ? document.activeElement.getAttribute( 'href' ) : null;
			head.replaceWith( nextHead );
			if ( focusedHref ) {
				var pill = document.querySelector( '.shop-pill[href="' + focusedHref + '"]' );
				if ( pill ) {
					pill.focus( { preventScroll: true } );
				}
			}
		}
		[ 'count', 'badge' ].forEach( function ( key ) {
			var el = $( '[data-shop="' + key + '"]' );
			var fresh = $( '[data-shop="' + key + '"]', doc );
			if ( el && fresh ) {
				el.replaceWith( fresh );
			}
		} );

		var chips = $( '[data-shop="chips"]' );
		var nextChips = $( '[data-shop="chips"]', doc );
		if ( chips && nextChips ) {
			chips.firstElementChild.innerHTML = nextChips.firstElementChild.innerHTML;
			ui.collapse( chips, nextChips.classList.contains( 'is-open' ) );
		}

		var sort = $( '[data-sort-form]' );
		var nextSort = $( '[data-sort-form]', doc );
		if ( sort && nextSort ) {
			sort.action = nextSort.action;
			$( '[data-sort]', sort ).value = $( '[data-sort]', nextSort ).value;
		}

		swapFilters( doc, fromControl );
		root.setAttribute( 'data-base', nextRoot.getAttribute( 'data-base' ) );
		if ( doc.title ) {
			document.title = doc.title;
		}

		var results = $( '[data-shop="results"]' );
		results.innerHTML = $( '[data-shop="results"]', doc ).innerHTML;
		results.querySelectorAll( '.pcard' ).forEach( function ( card, i ) {
			card.style.setProperty( '--i', Math.min( i, 12 ) );
			card.classList.add( 'is-entering' );
		} );
		results.classList.remove( 'is-leaving', 'is-loading' );
	}

	function load( url, options ) {
		options = options || {};
		var results = $( '[data-shop="results"]' );
		if ( controller ) {
			controller.abort();
		}
		controller = window.AbortController ? new AbortController() : null;
		results.classList.add( 'is-leaving' );
		var slow = setTimeout( function () {
			results.classList.add( 'is-loading' );
		}, 400 );
		var started = Date.now();

		fetch( url, { credentials: 'same-origin', signal: controller ? controller.signal : undefined } )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( response.status );
				}
				return response.text();
			} )
			.then( function ( html ) {
				var doc = new DOMParser().parseFromString( html, 'text/html' );
				// Let the fade-out finish before the swap.
				var wait = Math.max( 0, ( ui.reduced ? 0 : 200 ) - ( Date.now() - started ) );
				setTimeout( function () {
					clearTimeout( slow );
					swap( doc, options.fromControl );
					if ( options.push !== false ) {
						history.pushState( { nsShop: true }, '', url );
					}
					if ( options.scroll ) {
						var top = root.getBoundingClientRect().top + window.scrollY - 90;
						window.scrollTo( { top: top, behavior: ui.reduced ? 'auto' : 'smooth' } );
					}
				}, wait );
			} )
			.catch( function ( err ) {
				clearTimeout( slow );
				if ( err && err.name === 'AbortError' ) {
					return;
				}
				window.location.href = url;
			} );
	}

	document.addEventListener( 'change', function ( e ) {
		if ( e.target.closest( '[data-filters-form]' ) || e.target.matches( '[data-sort]' ) ) {
			load( buildUrl(), { fromControl: true } );
		}
	} );

	document.addEventListener( 'submit', function ( e ) {
		if ( e.target.matches( '[data-filters-form], [data-sort-form]' ) ) {
			e.preventDefault();
			if ( ! sheetOpen ) {
				load( buildUrl() );
			}
		}
	} );

	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( '[data-shop-link], .shop-pager a' );
		if ( ! link || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0 ) {
			return;
		}
		e.preventDefault();
		load( link.href, { scroll: !! link.closest( '.shop-pager' ) } );
	} );

	window.addEventListener( 'popstate', function () {
		load( window.location.href, { push: false } );
	} );
	history.replaceState( { nsShop: true }, '', window.location.href );
}() );
