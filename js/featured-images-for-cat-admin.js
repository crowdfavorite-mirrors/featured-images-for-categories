jQuery(document).ready( function($) {
								 
	$(".handlediv").click(function(){
		var p = $(this).parent('.postbox');
		
		p.toggleClass('closed');
	});
	
	WPSetThumbnailHTML = function(html){
		$('.inside', '#postimagediv').html(html);
	};
	
	WPRemoveThumbnail = function(nonce){
		
		var post_ID = $('#wpfifc_taxonomies_post_ID_id').val();
		if ( !post_ID ){
			post_ID = $('#wpfifc_taxonomies_edit_post_ID_id').val();
		}
		$.post( ajaxurl, 
			{ action: "wpfifc-remove-image", 
			  post_id: post_ID, 
			  thumbnail_id: -1, 
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
	
	if( $("#wpfifc_taxonomies_post_ID_id").length > 0 ){
		$(".submit").html( '<input type="button" name="wpfifc_add_tag_submit" id="wpfifc_add_tag_submit_id" class="button button-primary" value="Save"  />' );
		
		$("#wpfifc_add_tag_submit_id").click(function(){
			$("#addtag").submit();
		});
	}
	
	if( $("#wpfifc_taxonomies_edit_post_ID_id").length > 0 ){
		$(".submit").html( '<input type="button" name="wpfifc_edit_tag_submit" id="wpfifc_edit_tag_submit_id" class="button button-primary" value="Save"  />' );
		
		$("#wpfifc_edit_tag_submit_id").click(function(){
			$("#edittag").submit();
		});
		
		
	}
	
});
