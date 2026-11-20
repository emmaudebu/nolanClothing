/**
 * Customizer controls for the homepage hero slideshow: the image gallery (media library,
 * drag to reorder) and the product picker (tick order = play order, with a search box).
 * Both write a comma-separated list of IDs to their setting.
 */
( function () {
	'use strict';

	var api = window.wp && window.wp.customize;
	if ( ! api ) {
		return;
	}

	function save( root, ids ) {
		api( root.dataset.setting, function ( setting ) {
			setting.set( ids.join( ',' ) );
		} );
	}

	// Gallery ---------------------------------------------------------------

	function initGallery( root ) {
		var list = root.querySelector( '.ns-gallery__list' );
		var frame = null;
		var dragged = null;

		function ids() {
			return Array.prototype.map.call( list.children, function ( item ) {
				return item.dataset.id;
			} );
		}

		function item( id, src ) {
			var li = document.createElement( 'li' );
			li.className = 'ns-gallery__item';
			li.draggable = true;
			li.dataset.id = id;
			var img = document.createElement( 'img' );
			img.src = src;
			img.alt = '';
			var remove = document.createElement( 'button' );
			remove.type = 'button';
			remove.className = 'ns-gallery__remove';
			remove.setAttribute( 'aria-label', root.dataset.remove );
			remove.innerHTML = '&times;';
			li.appendChild( img );
			li.appendChild( remove );
			return li;
		}

		root.querySelector( '.ns-gallery__add' ).addEventListener( 'click', function () {
			if ( ! window.wp.media ) {
				return;
			}
			if ( ! frame ) {
				frame = window.wp.media( {
					title: root.dataset.title,
					button: { text: root.dataset.button },
					library: { type: 'image' },
					multiple: 'add'
				} );
				// Open with the current images already ticked.
				frame.on( 'open', function () {
					var selection = frame.state().get( 'selection' );
					selection.reset( [] );
					ids().forEach( function ( id ) {
						var attachment = window.wp.media.attachment( id );
						attachment.fetch();
						selection.add( attachment );
					} );
				} );
				frame.on( 'select', function () {
					var keep = ids();
					var chosen = frame.state().get( 'selection' ).toJSON();
					var byId = {};
					chosen.forEach( function ( a ) {
						byId[ a.id ] = a;
					} );
					// Keep the existing order for images still ticked, then add the new ones.
					list.innerHTML = '';
					keep.concat( chosen.map( function ( a ) {
						return String( a.id );
					} ) ).forEach( function ( id ) {
						var a = byId[ id ];
						if ( ! a || list.querySelector( '[data-id="' + id + '"]' ) ) {
							return;
						}
						var src = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
						list.appendChild( item( id, src ) );
					} );
					save( root, ids() );
				} );
			}
			frame.open();
		} );

		root.querySelector( '.ns-gallery__clear' ).addEventListener( 'click', function () {
			list.innerHTML = '';
			save( root, [] );
		} );

		list.addEventListener( 'click', function ( event ) {
			var remove = event.target.closest( '.ns-gallery__remove' );
			if ( remove ) {
				remove.parentNode.remove();
				save( root, ids() );
			}
		} );

		// Drag to reorder.
		list.addEventListener( 'dragstart', function ( event ) {
			dragged = event.target.closest( '.ns-gallery__item' );
			if ( dragged ) {
				dragged.classList.add( 'is-dragging' );
				event.dataTransfer.effectAllowed = 'move';
			}
		} );
		list.addEventListener( 'dragover', function ( event ) {
			var over = event.target.closest( '.ns-gallery__item' );
			event.preventDefault();
			if ( ! dragged || ! over || over === dragged ) {
				return;
			}
			var box = over.getBoundingClientRect();
			var after = event.clientX > box.left + box.width / 2;
			list.insertBefore( dragged, after ? over.nextSibling : over );
		} );
		list.addEventListener( 'dragend', function () {
			if ( dragged ) {
				dragged.classList.remove( 'is-dragging' );
				dragged = null;
				save( root, ids() );
			}
		} );
	}

	// Product picker --------------------------------------------------------

	function initPicker( root ) {
		var boxes = root.querySelectorAll( 'input[type="checkbox"]' );
		var order = Array.prototype.filter.call( boxes, function ( box ) {
			return box.checked;
		} ).sort( function ( a, b ) {
			return parseInt( a.closest( 'label' ).querySelector( '.ns-pick__n' ).textContent, 10 ) -
				parseInt( b.closest( 'label' ).querySelector( '.ns-pick__n' ).textContent, 10 );
		} ).map( function ( box ) {
			return box.value;
		} );

		function numbers() {
			Array.prototype.forEach.call( boxes, function ( box ) {
				var position = order.indexOf( box.value );
				box.closest( 'label' ).querySelector( '.ns-pick__n' ).textContent = position > -1 ? position + 1 : '';
			} );
		}

		root.addEventListener( 'change', function ( event ) {
			var box = event.target;
			if ( 'checkbox' !== box.type ) {
				return;
			}
			order = order.filter( function ( id ) {
				return id !== box.value;
			} );
			if ( box.checked ) {
				order.push( box.value );
			}
			numbers();
			save( root, order );
		} );

		root.querySelector( '.ns-pick__search' ).addEventListener( 'input', function ( event ) {
			var term = event.target.value.trim().toLowerCase();
			root.querySelectorAll( '.ns-pick__item' ).forEach( function ( item ) {
				item.hidden = term && -1 === item.dataset.name.indexOf( term );
			} );
		} );
	}

	// Controls are added to the page when their section first opens.
	var inits = { nolasharon_gallery: [ '.ns-gallery', initGallery ], nolasharon_products: [ '.ns-pick', initPicker ] };
	api.control.bind( 'add', function ( control ) {
		var init = inits[ control.params.type ];
		if ( init ) {
			control.deferred.embedded.done( function () {
				var root = control.container[ 0 ].querySelector( init[ 0 ] );
				if ( root && ! root.dataset.ready ) {
					root.dataset.ready = '1';
					init[ 1 ]( root );
				}
			} );
		}
	} );
}() );
