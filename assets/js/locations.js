/**
 * Store Locations (LOCATIONS.md §4–§6): area tabs, opening-hours panels, two-way selection
 * between cards, map markers and the spotlight, hash deep links, the Leaflet map, and a live
 * "Open now" line computed in Lagos time (so cached pages stay correct).
 *
 * Vanilla JS. Without it every card shows, hours stay open and all links still work.
 */
( function () {
	'use strict';

	var finder = document.querySelector( '[data-loc-finder]' );
	if ( ! finder ) {
		return;
	}

	var cfg = window.nsLocations || { i18n: {} };
	var t = cfg.i18n || {};
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var mobile = window.matchMedia ? window.matchMedia( '(max-width: 820px)' ) : { matches: false };

	var branches = {};
	var order = [];
	finder.querySelectorAll( '.bcard[data-branch]' ).forEach( function ( card ) {
		var slug = card.getAttribute( 'data-branch' );
		var item = card.closest( '.loc-item' );
		branches[ slug ] = {
			slug: slug,
			card: card,
			item: item,
			area: item ? item.getAttribute( 'data-area' ) : '',
			pin: card.querySelector( '.bcard__pin' ),
			marker: null,
		};
		order.push( slug );
	} );

	var selected = '';
	var area = '';
	var map = null;
	// Room for the pin height and name bubble at the top, and the info bar at the bottom.
	var PAD_TL = [ 60, 110 ];
	var PAD_BR = [ 60, 120 ];
	var mapWrap = finder.querySelector( '[data-loc-map]' );

	// Open now, in Lagos time ---------------------------------------------------

	var lagosNow = function () {
		try {
			var parts = {};
			new Intl.DateTimeFormat( 'en-GB', {
				timeZone: 'Africa/Lagos',
				weekday: 'short',
				hour: '2-digit',
				minute: '2-digit',
				hourCycle: 'h23',
			} ).formatToParts( new Date() ).forEach( function ( p ) {
				parts[ p.type ] = p.value;
			} );
			var day = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ].indexOf( parts.weekday ) + 1;
			return day ? { day: day, time: parts.hour + ':' + parts.minute } : null;
		} catch ( e ) {
			return null;
		}
	};

	var fmt = function ( hm ) {
		var h = parseInt( hm.slice( 0, 2 ), 10 );
		return ( h % 12 || 12 ) + ':' + hm.slice( 3 ) + ( h < 12 ? 'am' : 'pm' );
	};

	var liveStatus = function ( hours, now ) {
		var today = hours[ now.day - 1 ];
		if ( today && ! today.closed && today.open && today.close && now.time >= today.open && now.time < today.close ) {
			return { tone: 'open', label: ( t.openNow || '%s' ).replace( '%s', fmt( today.close ) ) };
		}
		for ( var i = 0; i < 7; i++ ) {
			var row = hours[ ( now.day - 1 + i ) % 7 ];
			if ( ! row || row.closed || ! row.open || ( i === 0 && now.time >= row.open ) ) {
				continue;
			}
			var label = i === 0
				? ( t.opensToday || '%s' ).replace( '%s', fmt( row.open ) )
				: ( t.opensLater || '%1$s %2$s' ).replace( '%1$s', ( t.days || [] )[ row.day - 1 ] || '' ).replace( '%2$s', fmt( row.open ) );
			return { tone: 'closed', label: label };
		}
		return { tone: 'closed', label: t.closed || '' };
	};

	var refreshStatus = function () {
		var now = lagosNow();
		if ( ! now ) {
			return;
		}
		order.forEach( function ( slug ) {
			var card = branches[ slug ].card;
			var raw = card.getAttribute( 'data-hours' );
			if ( ! raw ) {
				return;
			}
			var status = liveStatus( JSON.parse( raw ), now );
			var line = card.querySelector( '[data-status-line]' );
			line.className = line.className.replace( /bcard__status--\w+/, 'bcard__status--' + status.tone );
			line.querySelector( '[data-status-text]' ).textContent = status.label;
		} );
		if ( selected ) {
			renderInfo();
		}
	};

	// Hours panels --------------------------------------------------------------

	finder.querySelectorAll( '[data-hours-toggle]' ).forEach( function ( btn ) {
		btn.setAttribute( 'aria-expanded', 'false' );
		btn.addEventListener( 'click', function () {
			btn.setAttribute( 'aria-expanded', String( btn.getAttribute( 'aria-expanded' ) !== 'true' ) );
		} );
	} );

	// Map info bar --------------------------------------------------------------

	var infoName = finder.querySelector( '[data-info-name]' );
	var infoStatus = finder.querySelector( '[data-info-status]' );
	var infoDirections = finder.querySelector( '[data-info-directions]' );
	var pins = {};

	var renderInfo = function () {
		if ( ! infoName || ! selected ) {
			return;
		}
		var b = branches[ selected ];
		var pin = pins[ selected ];
		var tag = b.card.querySelector( '.bcard__tag' );
		infoName.textContent = b.card.querySelector( '.bcard__name' ).textContent + ( tag ? ' · ' + tag.textContent : '' );
		infoStatus.textContent = b.card.querySelector( '[data-status-text]' ).textContent;
		if ( pin && pin.directions ) {
			infoDirections.href = pin.directions;
			infoDirections.hidden = false;
		} else {
			infoDirections.hidden = true;
		}
	};

	// Selection -----------------------------------------------------------------

	var isVisible = function ( slug ) {
		return ! area || branches[ slug ].area === area;
	};

	var select = function ( slug, opts ) {
		opts = opts || {};
		if ( ! branches[ slug ] ) {
			return;
		}
		if ( ! isVisible( slug ) ) {
			setArea( '', { keep: true } );
		}
		selected = slug;
		order.forEach( function ( s ) {
			var b = branches[ s ];
			var on = s === slug;
			b.card.classList.toggle( 'is-selected', on );
			if ( b.pin ) {
				b.pin.setAttribute( 'aria-pressed', String( on ) );
			}
			if ( b.marker ) {
				var el = b.marker.getElement();
				if ( el ) {
					el.classList.toggle( 'is-selected', on );
					el.setAttribute( 'aria-pressed', String( on ) );
				}
				b.marker.setZIndexOffset( on ? 1000 : 0 );
			}
		} );
		document.querySelectorAll( '[data-select-branch]:not(.bcard__pin)' ).forEach( function ( btn ) {
			btn.setAttribute( 'aria-pressed', String( btn.getAttribute( 'data-select-branch' ) === slug ) );
		} );
		renderInfo();

		if ( opts.hash !== false && window.history && history.replaceState ) {
			history.replaceState( null, '', '#' + branches[ slug ].card.id );
		}

		var marker = branches[ slug ].marker;
		if ( map && marker && ! opts.stay ) {
			if ( reduceMotion ) {
				map.setView( marker.getLatLng(), 15, { animate: false } );
			} else {
				map.flyTo( marker.getLatLng(), 15, { duration: 0.6 } );
			}
		}
		if ( opts.scrollMap && mapWrap && ! mapWrap.hidden ) {
			mapWrap.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: mobile.matches ? 'start' : 'center' } );
		}
	};

	// Area tabs -----------------------------------------------------------------

	var tabs = finder.querySelector( '[data-loc-tabs]' );

	var fitVisible = function () {
		if ( ! map ) {
			return;
		}
		var points = order.filter( isVisible ).map( function ( s ) {
			return branches[ s ].marker && branches[ s ].marker.getLatLng();
		} ).filter( Boolean );
		if ( points.length === 1 ) {
			map.setView( points[ 0 ], 15, { animate: ! reduceMotion } );
		} else if ( points.length ) {
			map.fitBounds( window.L.latLngBounds( points ), { paddingTopLeft: PAD_TL, paddingBottomRight: PAD_BR, maxZoom: 15, animate: ! reduceMotion } );
		}
	};

	var setArea = function ( next, opts ) {
		opts = opts || {};
		area = next;
		if ( tabs ) {
			var buttons = [].slice.call( tabs.querySelectorAll( '[data-area]' ) );
			buttons.forEach( function ( btn, i ) {
				var on = btn.getAttribute( 'data-area' ) === area;
				btn.setAttribute( 'aria-pressed', String( on ) );
				if ( on ) {
					tabs.style.setProperty( '--tab', String( i ) );
				}
			} );
		}
		order.forEach( function ( s ) {
			var b = branches[ s ];
			var on = isVisible( s );
			if ( b.item ) {
				b.item.classList.toggle( 'is-out', ! on );
				b.item.setAttribute( 'aria-hidden', String( ! on ) );
				b.item.inert = ! on;
			}
			if ( b.marker && b.marker.getElement() ) {
				b.marker.getElement().classList.toggle( 'is-out', ! on );
				b.marker.getElement().tabIndex = on ? 0 : -1;
			}
		} );
		if ( opts.keep ) {
			return;
		}
		if ( selected && ! isVisible( selected ) ) {
			var first = order.filter( isVisible )[ 0 ];
			if ( first ) {
				select( first, { hash: false } );
			}
		}
		if ( ! branches[ selected ] || ! branches[ selected ].marker ) {
			fitVisible();
		}
	};

	if ( tabs ) {
		tabs.hidden = false;
		tabs.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-area]' );
			if ( btn ) {
				setArea( btn.getAttribute( 'data-area' ) );
			}
		} );
	}

	// Map -----------------------------------------------------------------------

	var initMap = function () {
		var dataEl = document.getElementById( 'loc-map-data' );
		var canvas = document.getElementById( 'loc-map-canvas' );
		if ( ! mapWrap || ! dataEl || ! canvas || ! window.L || ! cfg.tiles ) {
			return;
		}
		var L = window.L;
		var data;
		try {
			data = JSON.parse( dataEl.textContent );
		} catch ( e ) {
			return;
		}
		mapWrap.hidden = false;
		var help = finder.querySelector( '[data-map-help]' );
		if ( help ) {
			help.hidden = false;
		}

		map = L.map( canvas, {
			scrollWheelZoom: false, // Until the map is clicked, so the page scrolls past it.
			zoomControl: true,
			fadeAnimation: ! reduceMotion,
			zoomAnimation: ! reduceMotion,
			markerZoomAnimation: ! reduceMotion,
		} );
		L.tileLayer( cfg.tiles.url, {
			attribution: cfg.tiles.attribution,
			subdomains: cfg.tiles.subdomains || 'abc',
			maxZoom: cfg.tiles.maxZoom || 19,
		} ).addTo( map );

		// Leaflet only creates marker elements once the map has a view.
		var all = data.map( function ( pin ) {
			return [ pin.lat, pin.lng ];
		} );
		map.fitBounds( L.latLngBounds( all ), { paddingTopLeft: PAD_TL, paddingBottomRight: PAD_BR, maxZoom: 15, animate: false } );

		var hint = mapWrap.querySelector( '[data-map-hint]' );
		map.once( 'click focus', function () {
			map.scrollWheelZoom.enable();
			if ( hint ) {
				hint.classList.add( 'is-hidden' );
			}
		} );
		if ( mobile.matches && hint ) {
			hint.hidden = true;
		}

		var icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M4 10l1.5-5h13L20 10M4 10v10h16V10M4 10h16"/></svg>';
		data.forEach( function ( pin ) {
			var b = branches[ pin.slug ];
			if ( ! b ) {
				return;
			}
			pins[ pin.slug ] = pin;
			var bubble = document.createElement( 'span' );
			bubble.textContent = pin.short;
			var marker = L.marker( [ pin.lat, pin.lng ], {
				icon: L.divIcon( {
					className: 'loc-marker',
					html: '<span class="loc-marker__pin"><span class="loc-marker__pulse" aria-hidden="true"></span><span class="loc-marker__head" aria-hidden="true">' + icon + '</span><span class="loc-marker__bubble" aria-hidden="true">' + bubble.innerHTML + '</span></span>',
					iconSize: [ 44, 44 ],
					iconAnchor: [ 22, 44 ],
				} ),
				keyboard: true,
				riseOnHover: true,
			} ).addTo( map );
			var el = marker.getElement();
			el.setAttribute( 'role', 'button' );
			el.setAttribute( 'aria-pressed', 'false' );
			el.setAttribute( 'aria-label', ( b.pin ? b.pin.getAttribute( 'aria-label' ) : pin.name ) );
			el.addEventListener( 'keydown', function ( e ) {
				if ( e.key === ' ' || e.key === 'Enter' ) {
					e.preventDefault();
					select( pin.slug );
				}
			} );
			marker.on( 'click', function () {
				select( pin.slug );
			} );
			b.marker = marker;
		} );
		fitVisible();
	};

	// Events and start ----------------------------------------------------------

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-select-branch]' );
		if ( ! btn ) {
			return;
		}
		e.preventDefault();
		var fromCard = !! btn.closest( '.bcard' );
		select( btn.getAttribute( 'data-select-branch' ), {
			scrollMap: btn.hasAttribute( 'data-scroll-map' ) || ( fromCard && mobile.matches ),
		} );
	} );

	var fromHash = function () {
		var id = decodeURIComponent( window.location.hash.slice( 1 ) );
		for ( var i = 0; i < order.length; i++ ) {
			if ( branches[ order[ i ] ].card.id === id ) {
				return order[ i ];
			}
		}
		return '';
	};

	window.addEventListener( 'hashchange', function () {
		var slug = fromHash();
		if ( slug ) {
			select( slug, { hash: false } );
		}
	} );

	initMap();
	refreshStatus();
	setInterval( refreshStatus, 60000 );
	setArea( '', { keep: true } );

	var start = fromHash();
	if ( ! start ) {
		var fresh = finder.querySelector( '.bcard[data-new]' );
		start = fresh ? fresh.getAttribute( 'data-branch' ) : order[ 0 ];
	}
	if ( start ) {
		// Highlight it, but keep the overview of every pin that fitVisible() set up.
		select( start, { hash: false, stay: ! fromHash() } );
	}
}() );
