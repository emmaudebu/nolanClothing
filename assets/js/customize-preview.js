/**
 * Customizer live preview: logo sizes (CSS variables) and footer layout/alignment (classes)
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
