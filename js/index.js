jQuery(document).ready(function($) {

  $('.js-add-icon').click(function(e) {
    var field = $(this).data('media-uploader-target');
    var $field = $(field);
    var $form_group = $field.closest('.js-form-group');
    // Prevents the default action from occuring.
    e.preventDefault();

    // Sets up the media library frame
    metaImageFrame = wp.media.frames.metaImageFrame = wp.media({
      title: testimonial_image.title,
      button: {
        text: 'Use this file'
      }
    });

    // Runs when an image is selected.
    metaImageFrame.on('select', function() {
      // Grabs the attachment selection and creates a JSON representation of the model.
      var media_attachment = metaImageFrame.state().get('selection').first().toJSON();
      console.log(field, media_attachment);

      // Sends the attachment URL to our custom image input field.
      $field.val(media_attachment.url);
      var $img = "<img src='"+media_attachment.url+"'/>";
      $form_group.find('.js-img-wrap').empty().append($img);
      $form_group.find('.js-add-icon').html('Modify Icon')
    });

    // Opens the media library frame.
    metaImageFrame.open();

  });

});
