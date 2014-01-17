jQuery(document).ready( function($) {
	if( $('#wpfifc_taxonomies_edit_post_ID_id').length > 0 && $('#wpfifc_taxonomies_edit_term_ID_id').length > 0 ){					 
		$(".handlediv").click(function(){
			var p = $(this).parent('.postbox');
			
			p.toggleClass('closed');
		});
		
		WPSetThumbnailHTML = function(html){
			$('.inside', '#postimagediv').html(html);
		};
		
		WPRemoveThumbnail = function(nonce){
			var post_ID = 0;
			var term_ID = 0;
			
				if( $('#wpfifc_taxonomies_edit_post_ID_id').length > 0 ){
				post_ID = $('#wpfifc_taxonomies_edit_post_ID_id').val();
			}
			if( $('#wpfifc_taxonomies_edit_term_ID_id').length > 0 ){
				term_ID = $('#wpfifc_taxonomies_edit_term_ID_id').val();
			}
				if( post_ID < 1 || term_ID < 1 ){
					return;
				}
			$.post( ajaxurl, 
				{ action: "wpfifc-remove-image", 
				  post_id: post_ID, 
				  thumbnail_id: -1,
				  term_id: term_ID,
				  _ajax_nonce: nonce, 
				  cookie: encodeURIComponent(document.cookie)
				}, 
				function(str){
					if ( str.indexOf('ERROR') != '-1' ) {
						alert( "Remove featured image failed." );
					} else {
						WPSetThumbnailHTML(str);
					}
				}
			 );
		};
	}
	
});
