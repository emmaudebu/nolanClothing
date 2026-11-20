/**
 * Customizer live preview: logo sizes, hero picture sizes and slideshow options, and footer
 * layout/alignment (CSS variables, data attributes and classes)
 * update without reloading the preview. Text settings use a normal refresh.
 */
( function () {
	'use strict';

	var api = window.wp && window.wp.customize;
	if ( ! api ) {
		return;
	}

	var root = document.documentElement;

	var sizes = {
		nolasharon_logo_h: '--ns-logo-h',
		nolasharon_logo_w: '--ns-logo-w',
		nolasharon_logo_h_m: '--ns-logo-h-m',
		nolasharon_logo_w_m: '--ns-logo-w-m',
		nolasharon_footer_logo_h: '--ns-flogo-h',
		nolasharon_footer_logo_w: '--ns-flogo-w'
	};

	Object.keys( sizes ).forEach( function ( id ) {
		api( id, function ( setting ) {
			setting.bind( function ( value ) {
				var px = parseInt( value, 10 );
				root.style.setProperty( sizes[ id ], px > 0 ? px + 'px' : 'auto' );
			} );
		} );
	} );

	// Homepage hero pictures: size, corners, fit, position and speed (CSS variables).
	var flex = { top: 'flex-start', left: 'flex-start', center: 'center', bottom: 'flex-end', right: 'flex-end' };
	var hero = {
		nolasharon_hero_media_h: [ '--hs-h', 'px' ],
		nolasharon_hero_media_w: [ '--hs-w', 'px' ],
		nolasharon_hero_media_h_m: [ '--hs-h-m', 'px' ],
		nolasharon_hero_radius: [ '--hs-radius', 'px' ],
		nolasharon_hero_speed: [ '--hs-speed', 'ms' ],
		nolasharon_hero_fit: [ '--hs-fit' ],
		nolasharon_hero_valign: [ '--hs-valign', 'flex' ],
		nolasharon_hero_halign: [ '--hs-halign', 'flex' ]
	};

	Object.keys( hero ).forEach( function ( id ) {
		api( id, function ( setting ) {
			setting.bind( function ( value ) {
				var unit = hero[ id ][ 1 ];
				if ( 'flex' === unit ) {
					value = flex[ value ] || 'center';
				} else if ( unit ) {
					value = ( parseInt( value, 10 ) || 0 ) + unit;
				}
				root.style.setProperty( hero[ id ][ 0 ], value );
			} );
		} );
	} );

	// Slideshow options: data attributes and classes the slider reads on every change.
	function slider( change, demo ) {
		var el = document.querySelector( '.hslider' );
		if ( el ) {
			change( el );
			document.dispatchEvent( new CustomEvent( 'ns:hslider-update', { detail: { demo: demo } } ) );
		}
	}

	api( 'nolasharon_hero_effect', function ( setting ) {
		setting.bind( function ( value ) {
			slider( function ( el ) {
				el.dataset.effect = value;
			}, true );
		} );
	} );
	api( 'nolasharon_hero_interval', function ( setting ) {
		setting.bind( function ( value ) {
			slider( function ( el ) {
				el.dataset.interval = parseInt( value, 10 ) || 0;
			}, false );
		} );
	} );
	[ 'arrows', 'dots', 'captions' ].forEach( function ( name ) {
		api( 'nolasharon_hero_' + name, function ( setting ) {
			setting.bind( function ( value ) {
				slider( function ( el ) {
					el.classList.toggle( 'hslider--' + name, !! value );
				}, false );
			} );
		} );
	} );

	// Footer classes: site-footer--{layout}, --align-{x}, --bottom-{x}.
	var classes = {
		nolasharon_footer_layout: { prefix: 'site-footer--', options: [ 'side', 'side-reverse', 'stacked' ] },
		nolasharon_footer_align: { prefix: 'site-footer--align-', options: [ 'left', 'center', 'right' ] },
		nolasharon_footer_bottom_align: { prefix: 'site-footer--bottom-', options: [ 'spread', 'left', 'center', 'right' ] }
	};

	Object.keys( classes ).forEach( function ( id ) {
		api( id, function ( setting ) {
			setting.bind( function ( value ) {
				var footer = document.querySelector( '.site-footer' );
				if ( ! footer ) {
					return;
				}
				classes[ id ].options.forEach( function ( option ) {
					footer.classList.remove( classes[ id ].prefix + option );
				} );
				footer.classList.add( classes[ id ].prefix + value );
			} );
		} );
	} );
}() );
