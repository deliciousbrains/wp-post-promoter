(function( $ ) {

	$( '.don-king-share a' ).click( function( e ) {
		e.preventDefault( );

		var share_url = $( this ).prop( 'href' );
		var permalink = $( this ).parent().parent().find( 'input' ).val();
		var post_title = $( 'input[name=dk_post_title]' ).val();

		share_url = share_url.replace( '{url}', encodeURIComponent( permalink ) );
		share_url = share_url.replace( '{post_title}', encodeURIComponent( post_title ) );

		window.open( share_url, '', 'width=600,height=300' );
	} );

	$( '.change-campaign' ).each( function() {
		var $change_campaign = $( this );
		$( 'button', $change_campaign ).click( function( e ) {
			var new_campaign = $( 'input', $change_campaign ).val();
			$( '.don-king-share input' ).each( function() {
				var url = $( this ).val();
				url = url.replace( /utm_campaign=([^&]+)/, 'utm_campaign=' + new_campaign );
				$( this ).val( url );
			} );
		} );
	} );

})(jQuery);
