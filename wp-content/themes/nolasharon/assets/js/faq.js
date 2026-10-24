/**
 * FAQs (CONTENT-PAGES.md §3): accordion cards, live search with highlighted matches, and
 * topic chips. Search and chips also keep the address bar in step (?q= / ?topic=) so a
 * filtered view can be shared. Without JavaScript the same links reload the page.
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-faq]' );
	if ( ! root ) {
		return;
	}

	var l10n = window.nsFaq || {};
	var items = Array.prototype.slice.call( root.querySelectorAll( '[data-faq-item]' ) );
	var chips = Array.prototype.slice.call( root.querySelectorAll( '[data-faq-topic]' ) );
	var form = root.querySelector( '[data-faq-form]' );
	var input = root.querySelector( '[data-faq-q]' );
	var clear = root.querySelector( '[data-faq-clear]' );
	var result = root.querySelector( '[data-faq-result]' );
	var empty = root.querySelector( '[data-faq-empty]' );
	var reset = root.querySelector( '[data-faq-reset]' );
	var params = new URLSearchParams( window.location.search );
	var topic = params.get( 'topic' ) || '';

	/* Accordion ------------------------------------------------------------ */

	var setOpen = function ( item, open ) {
		var button = item.querySelector( '[data-faq-toggle]' );
		var panel = item.querySelector( '.faq-item__a' );
		item.classList.toggle( 'is-open', open );
		button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		panel.toggleAttribute( 'inert', ! open );
	};

	items.forEach( function ( item ) {
		setOpen( item, false );
		item.querySelector( '[data-faq-toggle]' ).addEventListener( 'click', function () {
			setOpen( item, ! item.classList.contains( 'is-open' ) );
		} );
	} );

	// A link to #faq-123 opens that answer.
	if ( window.location.hash ) {
		var target = document.getElementById( window.location.hash.slice( 1 ) );
		var owner = target && target.closest( '[data-faq-item]' );
		if ( owner ) {
			setOpen( owner, true );
		}
	}

	/* Highlighting --------------------------------------------------------- */

	var originals = new Map();
	items.forEach( function ( item ) {
		item.querySelectorAll( '[data-faq-text]' ).forEach( function ( el ) {
			originals.set( el, el.innerHTML );
		} );
	} );

	var escapeRe = function ( text ) {
		return text.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
	};

	var highlight = function ( el, re ) {
		el.innerHTML = originals.get( el );
		if ( ! re ) {
			return;
		}
		var walker = document.createTreeWalker( el, NodeFilter.SHOW_TEXT );
		var nodes = [];
		while ( walker.nextNode() ) {
			nodes.push( walker.currentNode );
		}
		nodes.forEach( function ( node ) {
			var text = node.nodeValue;
			re.lastIndex = 0;
			if ( ! re.test( text ) ) {
				return;
			}
			var frag = document.createDocumentFragment();
			var last = 0;
			re.lastIndex = 0;
			text.replace( re, function ( match, offset ) {
				frag.appendChild( document.createTextNode( text.slice( last, offset ) ) );
				var mark = document.createElement( 'mark' );
				mark.className = 'faq-hit';
				mark.textContent = match;
				frag.appendChild( mark );
				last = offset + match.length;
				return match;
			} );
			frag.appendChild( document.createTextNode( text.slice( last ) ) );
			node.parentNode.replaceChild( frag, node );
		} );
	};

	/* Filtering ------------------------------------------------------------ */

	var format = function ( template, count, query ) {
		return template.replace( '%1$d', count ).replace( '%d', count ).replace( '%2$s', query ).replace( '%s', query );
	};

	var apply = function () {
		var query = input ? input.value.trim() : '';
		var re = query ? new RegExp( escapeRe( query ), 'gi' ) : null;
		var needle = query.toLowerCase();
		var shown = 0;

		items.forEach( function ( item ) {
			var inTopic = ! topic || item.getAttribute( 'data-topic' ) === topic;
			var match = inTopic && ( ! needle || item.textContent.toLowerCase().indexOf( needle ) !== -1 );
			item.hidden = ! match;
			item.querySelectorAll( '[data-faq-text]' ).forEach( function ( el ) {
				highlight( el, match ? re : null );
			} );
			// Open answers that match the words, so the highlight is visible.
			if ( match && needle ) {
				setOpen( item, originals.get( item.querySelector( '.faq-item__body' ) ).toLowerCase().indexOf( needle ) !== -1 );
			}
			if ( match ) {
				shown++;
			}
		} );

		chips.forEach( function ( chip ) {
			if ( chip.getAttribute( 'data-faq-topic' ) === topic ) {
				chip.setAttribute( 'aria-current', 'true' );
			} else {
				chip.removeAttribute( 'aria-current' );
			}
		} );

		if ( query ) {
			result.textContent = format( shown === 1 ? l10n.matchOne : l10n.match, shown, query );
		} else {
			result.textContent = format( shown === 1 ? l10n.allOne : l10n.all, shown );
		}
		empty.hidden = shown > 0;
		if ( clear ) {
			clear.hidden = ! query;
		}

		var url = new URL( window.location.href );
		url.hash = '';
		[ [ 'q', query ], [ 'topic', topic ] ].forEach( function ( pair ) {
			if ( pair[ 1 ] ) {
				url.searchParams.set( pair[ 0 ], pair[ 1 ] );
			} else {
				url.searchParams.delete( pair[ 0 ] );
			}
		} );
		window.history.replaceState( null, '', url );
	};

	var timer;
	if ( input ) {
		input.addEventListener( 'input', function () {
			clearTimeout( timer );
			timer = setTimeout( apply, 150 );
		} );
		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			clearTimeout( timer );
			apply();
		} );
		// The topic is tracked here, not in the form.
		var hiddenTopic = form.querySelector( 'input[name="topic"]' );
		if ( hiddenTopic ) {
			hiddenTopic.remove();
		}
	}

	if ( clear ) {
		clear.addEventListener( 'click', function () {
			input.value = '';
			apply();
			input.focus();
		} );
	}

	chips.forEach( function ( chip ) {
		chip.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			topic = chip.getAttribute( 'data-faq-topic' );
			apply();
		} );
	} );

	if ( reset ) {
		reset.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			topic = '';
			if ( input ) {
				input.value = '';
				input.focus();
			}
			apply();
		} );
	}

	if ( input && input.value.trim() ) {
		apply();
	}
}() );
