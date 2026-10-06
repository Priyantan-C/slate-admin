/* Slate Admin: appearance toggle in the toolbar (light, dark, system). */
( function () {
	var cfg = window.slateAdmin;
	var node = document.getElementById( 'wp-admin-bar-slate-scheme' );
	if ( ! cfg || ! node ) {
		return;
	}
	var order = [ 'light', 'dark', 'auto' ];
	var current = cfg.scheme;

	node.querySelector( 'a' ).addEventListener( 'click', function ( e ) {
		e.preventDefault();
		var next = order[ ( order.indexOf( current ) + 1 ) % order.length ];
		document.body.classList.remove( 'sa-scheme-' + current );
		document.body.classList.add( 'sa-scheme-' + next );
		current = next;

		var label = node.querySelector( '.ab-label' );
		if ( label ) {
			label.textContent = cfg.labels[ next ];
		}

		var body = new URLSearchParams();
		body.set( 'action', 'slate_admin_scheme' );
		body.set( 'nonce', cfg.nonce );
		body.set( 'scheme', next );
		fetch( cfg.ajax, { method: 'POST', credentials: 'same-origin', body: body } );
	} );
} )();
