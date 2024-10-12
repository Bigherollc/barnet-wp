jQuery(document).ready( function($) {
	function ct_media_upload(button_class) {
		var _custom_media = true,
		_orig_send_attachment = wp.media.editor.send.attachment;
		$('body').on('click', button_class, function(e) {
			var button_id = '#'+$(this).attr('id');
			var button = $(button_id);
			_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				console.log({attachment})
				if ( _custom_media ) {
					$('#taxImage').val(attachment.id);
					$('#taxImage-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
					var src = attachment.url;

					if (attachment.sizes.thumbnail) {
						src = attachment.sizes.thumbnail.url;
					}
					$('#taxImage-wrapper .custom_media_image').attr('src', src).css('display','block');
				}
				else {
					return _orig_send_attachment.apply( button_id, [props, attachment] );
				}
			}
			wp.media.editor.open(button);
			return false;
		});
	}
	ct_media_upload('.taxImage_button_add.button'); 

	$('body').on('click','.taxImage_button_remove',function(){
		$('#taxImage').val('');
		$('#taxImage-wrapper')
			.html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
	});
	// Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-writers-ajax-response
	$(document).ajaxComplete(function(event, xhr, settings) {
		var queryStringArr = settings.data.split('&');
		if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
			var xml = xhr.responseXML;
			$response = $(xml).find('term_id').text();
			if($response!=""){
				// Clear the thumb image
				$('#taxImage-wrapper').html('');
			}
		}
	});
});