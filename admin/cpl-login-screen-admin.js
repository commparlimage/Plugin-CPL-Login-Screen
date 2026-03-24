jQuery(document).ready(function($){
    var mediaUploader;

    $('#cpl-login-select-logo').click(function(e){
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choisir un logo',
            button: { text: 'Choisir logo' },
            multiple: false
        });

        mediaUploader.on('select', function(){
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#cpl-login-logo-url').val(attachment.url);
            $('#cpl-login-logo-preview').html('<img src="' + attachment.url + '" style="max-width:200px;">');
        });

        mediaUploader.open();
    });
});