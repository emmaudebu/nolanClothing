/**
 * Accounts (STORE-PAGES.md §2–§3): sign-in tabs and #register deep link, inline
 * validation, loading buttons, the forgot-password email carry-over, the resend
 * countdown and the reset checklist. Every form still submits normally to WooCommerce.
 */
( function () {
	'use strict';

	var t = window.nsAccount || {};
	var ui = window.nsUI;
	var EMAIL_KEY = 'ns-auth-email';

	function store( value ) {
		try {
			if ( value ) {
				sessionStorage.setItem( EMAIL_KEY, value );
			}
		} catch ( e ) {}
	}

	function stored() {
		try {
			return sessionStorage.getItem( EMAIL_KEY ) || '';
		} catch ( e ) {
			return '';
		}
	}

	/* Sign in | Create account tabs ---------------------------------------- */

	var tabsWrap = document.querySelector( '[data-auth-tabs]' );
	var panels = document.querySelector( '.ns-auth__panels' );

	if ( tabsWrap && panels ) {
		var seg = tabsWrap.querySelector( '.ns-seg' );
		var tabs = Array.prototype.slice.call( seg.querySelectorAll( '[role="tab"]' ) );
		var order = [ 'login', 'register' ];

		tabsWrap.hidden = false;
		order.forEach( function ( id, i ) {
			var panel = document.getElementById( id );
			panel.setAttribute( 'role', 'tabpanel' );
			panel.setAttribute( 'aria-labelledby', tabs[ i ].id );
			panel.removeAttribute( 'id' ); // The hash no longer jumps the page; tabs handle it.
			panel.setAttribute( 'data-panel', id );
			tabs[ i ].setAttribute( 'aria-controls', 'ns-panel-' + id );
			panel.id = 'ns-panel-' + id;
		} );

		var show = function ( name, focus ) {
			var index = Math.max( 0, order.indexOf( name ) );
			ui.seg( seg, index );
			order.forEach( function ( id, i ) {
				var panel = panels.querySelector( '[data-panel="' + id + '"]' );
				var on = i === index;
				panel.classList.toggle( 'is-active', on );
				panel.toggleAttribute( 'inert', ! on );
				panel.style.setProperty( '--from', ( i < index ? -28 : 28 ) + 'px' );
			} );
			if ( focus === 'tab' ) {
				tabs[ index ].focus();
			} else if ( focus === 'field' ) {
				var first = panels.querySelector( '[data-panel="' + order[ index ] + '"] input:not([type="hidden"])' );
				if ( first ) {
					first.focus( { preventScroll: true } );
				}
			}
		};

		var fromHash = function () {
			var hash = window.location.hash.replace( '#', '' );
			return order.indexOf( hash ) > -1 ? hash : null;
		};

		show( fromHash() || panels.getAttribute( 'data-start' ) );

		tabs.forEach( function ( tab, i ) {
			tab.addEventListener( 'click', function () {
				show( order[ i ] );
				history.replaceState( null, '', '#' + order[ i ] );
			} );
			tab.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'ArrowRight' || e.key === 'ArrowLeft' ) {
					e.preventDefault();
					var next = ( i + ( e.key === 'ArrowRight' ? 1 : -1 ) + order.length ) % order.length;
					show( order[ next ], 'tab' );
					history.replaceState( null, '', '#' + order[ next ] );
				}
			} );
		} );

		document.addEventListener( 'click', function ( e ) {
			var go = e.target.closest( '[data-auth-go]' );
			if ( go ) {
				e.preventDefault();
				show( go.getAttribute( 'data-auth-go' ), 'field' );
				history.replaceState( null, '', '#' + go.getAttribute( 'data-auth-go' ) );
			}
		} );

		window.addEventListener( 'hashchange', function () {
			if ( fromHash() ) {
				show( fromHash(), 'field' );
			}
		} );
	}

	/* Inline validation and loading buttons -------------------------------- */

	var checks = {
		'login-email': function ( v ) {
			return v ? '' : t.emailInvalid;
		},
		email: function ( v ) {
			return /^\S+@\S+\.\S+$/.test( v ) ? '' : t.emailInvalid;
		},
		password: function ( v ) {
			return v ? '' : t.passwordEmpty;
		},
		first: function ( v ) {
			return v ? '' : t.firstEmpty;
		}
	};

	function setError( input, message ) {
		var field = input.closest( '.ns-field' ) || input.parentElement;
		var id = input.id + '-error';
		var el = document.getElementById( id );
		var described = ( input.getAttribute( 'aria-describedby' ) || '' ).split( ' ' ).filter( function ( d ) {
			return d && d !== id;
		} );
		if ( ! message ) {
			if ( el ) {
				el.remove();
			}
			input.removeAttribute( 'aria-invalid' );
			field.classList.remove( 'is-invalid' );
		} else {
			if ( ! el ) {
				el = document.createElement( 'span' );
				el.className = 'ns-field__error';
				el.id = id;
				field.appendChild( el );
			}
			el.textContent = message;
			input.setAttribute( 'aria-invalid', 'true' );
			field.classList.add( 'is-invalid' );
			described.push( id );
		}
		if ( described.length ) {
			input.setAttribute( 'aria-describedby', described.join( ' ' ) );
		} else {
			input.removeAttribute( 'aria-describedby' );
		}
	}

	function validate( form ) {
		var firstBad = null;
		form.querySelectorAll( '[data-required], [data-phone]' ).forEach( function ( input ) {
			var value = input.value.trim();
			var message = '';
			if ( input.hasAttribute( 'data-phone' ) ) {
				var digits = value.replace( /\D+/g, '' );
				message = value && ! /^(?:234|0)?[789][01]\d{8}$/.test( digits ) ? t.phoneInvalid : '';
			} else {
				var check = checks[ input.getAttribute( 'data-required' ) ];
				message = check ? check( value ) : ( value ? '' : t.required );
			}
			setError( input, message );
			if ( message && ! firstBad ) {
				firstBad = input;
			}
		} );
		if ( firstBad ) {
			firstBad.focus();
		}
		return ! firstBad;
	}

	function loading( form ) {
		var btn = form.querySelector( '[data-loading]' );
		if ( ! btn ) {
			return;
		}
		btn.classList.add( 'is-loading' );
		btn.setAttribute( 'aria-busy', 'true' );
		btn.setAttribute( 'aria-disabled', 'true' );
		var label = t[ btn.getAttribute( 'data-loading' ) ];
		if ( label ) {
			btn.textContent = label;
		}
	}

	document.querySelectorAll( 'form[data-auth-form], form[data-account-form]' ).forEach( function ( form ) {
		form.addEventListener( 'input', function ( e ) {
			if ( e.target.getAttribute( 'aria-invalid' ) === 'true' ) {
				setError( e.target, '' );
			}
		} );
		form.addEventListener( 'submit', function ( e ) {
			if ( ! validate( form ) ) {
				e.preventDefault();
				return;
			}
			var remember = form.querySelector( '[data-remember-email], #username' );
			if ( remember ) {
				store( remember.value.trim() );
			}
			loading( form );
		} );
	} );

	/* Forgot password: carry the typed email over, never in the URL --------- */

	document.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '[data-forgot]' ) ) {
			var login = document.getElementById( 'username' );
			if ( login && /@/.test( login.value ) ) {
				store( login.value.trim() );
			}
		}
	} );

	document.querySelectorAll( '[data-remember-email]' ).forEach( function ( input ) {
		if ( ! input.value ) {
			input.value = stored();
		}
	} );

	/* Check your email: show the address and allow a resend after 30s ------- */

	var sent = document.querySelector( '[data-sent-email]' );
	if ( sent && stored() ) {
		sent.textContent = stored();
	}

	var resend = document.querySelector( '[data-resend]' );
	var resendForm = document.querySelector( '[data-resend-form]' );
	if ( resend && resendForm && stored() ) {
		var left = 30;
		var label = t.resend || resend.textContent;
		var tick = function () {
			if ( left > 0 ) {
				resend.textContent = ( t.resendIn || 'Resend in %ds' ).replace( '%d', left );
				resend.setAttribute( 'aria-disabled', 'true' );
				resend.classList.add( 'is-wait' );
				left--;
				setTimeout( tick, 1000 );
			} else {
				resend.textContent = label;
				resend.removeAttribute( 'aria-disabled' );
				resend.classList.remove( 'is-wait' );
			}
		};
		tick();
		resendForm.querySelector( '[data-resend-email]' ).value = stored();
		resend.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( resend.getAttribute( 'aria-disabled' ) === 'true' ) {
				return;
			}
			resend.setAttribute( 'aria-disabled', 'true' );
			resend.textContent = t.sending || label;
			resendForm.submit();
		} );
	}

	/* Set a new password: live checklist and match line --------------------- */

	var rules = {
		length: function ( v ) {
			return v.length >= 8;
		},
		case: function ( v ) {
			return /[A-Z]/.test( v ) && /[a-z]/.test( v );
		},
		number: function ( v ) {
			return /\d/.test( v );
		}
	};

	document.querySelectorAll( '[data-rules-for]' ).forEach( function ( list ) {
		var input = document.getElementById( list.getAttribute( 'data-rules-for' ) );
		input.addEventListener( 'input', function () {
			list.querySelectorAll( '[data-rule]' ).forEach( function ( li ) {
				var met = rules[ li.getAttribute( 'data-rule' ) ]( input.value );
				li.classList.toggle( 'is-met', met );
				li.querySelector( '[data-rule-state]' ).textContent = met ? t.met : t.notMet;
			} );
		} );
	} );

	document.querySelectorAll( '[data-match-for]' ).forEach( function ( line ) {
		var first = document.getElementById( line.getAttribute( 'data-match-for' ) );
		var second = line.parentElement.querySelector( 'input' );
		var update = function () {
			if ( ! second.value ) {
				line.textContent = '';
				line.classList.remove( 'is-match' );
				return;
			}
			var same = second.value === first.value;
			line.textContent = same ? t.match : t.noMatch;
			line.classList.toggle( 'is-match', same );
		};
		first.addEventListener( 'input', update );
		second.addEventListener( 'input', update );
	} );
	/* Order tracker: the line fills the first time it comes into view -------- */

	var trackers = document.querySelectorAll( '[data-tracker]' );
	if ( trackers.length ) {
		if ( 'IntersectionObserver' in window && ! ui.reduced ) {
			var seen = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-shown' );
						seen.unobserve( entry.target );
					}
				} );
			}, { threshold: 0.4 } );
			trackers.forEach( function ( el ) {
				seen.observe( el );
			} );
		} else {
			trackers.forEach( function ( el ) {
				el.classList.add( 'is-shown' );
			} );
		}
	}

	/* Log out: confirm in a dialog; without JavaScript the link just works ---- */

	var logoutDialog = document.getElementById( 'ns-logout' );
	if ( logoutDialog ) {
		document.addEventListener( 'click', function ( e ) {
			var link = e.target.closest( '[data-logout]' );
			if ( link && ui.modal( logoutDialog, link ) ) {
				e.preventDefault();
			}
		} );
	}

	/* Orders: switch the status filter without a reload ----------------------- */

	var filters = document.querySelector( '[data-order-filters]' );
	var list = document.querySelector( '[data-orders-list]' );
	if ( filters && list && window.fetch && window.DOMParser ) {
		var swapping = false;

		var rows = function ( scope ) {
			return Array.prototype.slice.call( scope.querySelectorAll( '.ns-orow-wrap' ) );
		};

		var press = function ( value ) {
			filters.querySelectorAll( '.ns-chip' ).forEach( function ( chip ) {
				chip.setAttribute( 'aria-pressed', chip.value === value ? 'true' : 'false' );
			} );
		};

		filters.addEventListener( 'submit', function ( e ) {
			var chip = e.submitter;
			if ( ! chip || swapping ) {
				return;
			}
			e.preventDefault();
			var value = chip.value;
			var url = new URL( filters.action, window.location.href );
			if ( value ) {
				url.searchParams.set( 'status', value );
			}
			swapping = true;
			press( value );
			list.classList.add( 'is-busy' );

			fetch( url.toString(), { credentials: 'same-origin' } )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( response.status );
					}
					return response.text();
				} )
				.then( function ( html ) {
					var next = new DOMParser().parseFromString( html, 'text/html' ).querySelector( '[data-orders-list]' );
					if ( ! next ) {
						throw new Error( 'list' );
					}
					// Fold the old rows away, then fold the new ones in.
					rows( list ).forEach( function ( row ) {
						ui.collapse( row, false );
					} );
					setTimeout( function () {
						rows( next ).forEach( function ( row ) {
							ui.collapse( row, false );
						} );
						list.innerHTML = next.innerHTML;
						list.classList.remove( 'is-busy' );
						requestAnimationFrame( function () {
							requestAnimationFrame( function () {
								rows( list ).forEach( function ( row ) {
									ui.collapse( row, true );
								} );
							} );
						} );
						history.replaceState( null, '', url.toString() );
						swapping = false;
					}, ui.reduced ? 0 : 320 );
				} )
				.catch( function () {
					window.location.href = url.toString();
				} );
		} );
	}
}() );
