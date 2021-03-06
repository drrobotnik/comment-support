(function ($) {
	"use strict";
	$(function () {

		if(is_singular) {
		// Uploading files
		var file_frame;
		var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
	 
		jQuery('.form-submit .button').live('click', function( event ){
	 
			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				// Set the post ID to what we want
				file_frame.uploader.uploader.param( 'post_id', set_to_post_id );

				// Open frame
				file_frame.open();
				return;
			} else {
				// Set the wp.media post id so the uploader grabs the ID we want when initialised
				wp.media.model.settings.post.id = set_to_post_id;
				var this_uploader = $(this).parent().parent().find('.uploader');
				console.log(this_uploader);

				wp.media.model.settings.form_elem = this_uploader;
				wp.media.model.settings.thumb_elem = this_uploader.find('.cs_thumbnails');
				wp.media.model.settings.title_elem = this_uploader.find('.image_title');
				wp.media.model.settings.url_elem = this_uploader.find('.image_url');
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
					title: jQuery( this ).data( 'uploader_title' ),
				button: {
					text: jQuery( this ).data( 'uploader_button_text' ),
				},
				multiple: true  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function( event ) {
				// We set multiple to false so only get one image from the uploader
				var attachments = file_frame.state().get('selection').toJSON();

				// Do something with attachment.id and/or attachment.url here
				var fields = '';
				var thumbnails = '';
				var image_mime = Array('image/gif','image/png','image/jpeg');

				for (var x=0; x < attachments.length; x++) {
					var attachment = attachments[x];
					console.log(attachment.mime);
					console.log(attachment.icon);
					fields += '<input type="hidden" name="attachments['+x+'][id]" value="'+attachment.id+'">';
					fields += '<input type="hidden" name="attachments['+x+'][url]" value="'+attachment.url+'">';

					
					var url = ( jQuery.inArray(attachment.mime, image_mime) != -1 ) ? attachment.url : attachment.icon;
					
					var sizes = ( typeof attachment.sizes != 'undefined' && typeof attachment.sizes.thumbnail != 'undefined' ) ? 'width="'+attachment.sizes.thumbnail.width+'" height="'+attachment.sizes.thumbnail.height+'"' : '';
					
					thumbnails += '<a href="'+attachment.url+'"><img src="'+url+'" '+sizes+' /></a>';

				}
				$( wp.media.model.settings.form_elem ).append( fields );
				$( wp.media.model.settings.thumb_elem ).append( thumbnails );
				// Restore the main post ID
				wp.media.model.settings.post.id = wp_media_post_id;
			});

			// Finally, open the modal
			file_frame.open();
		});
	  
		// Restore the main ID when the add media button is pressed
		jQuery('a.add_media').on('click', function() {
			wp.media.model.settings.post.id = wp_media_post_id;
		});
	}
	});
}(jQuery));