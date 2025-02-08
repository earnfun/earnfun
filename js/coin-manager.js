jQuery(document).ready(function ($) {

    var mediaUploader;

    $('#upload-coin-media').on('click', function (e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
    });

    $('#create-coin-form').on('submit', function (e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=odib_create_coin&_wqsnonce=' + odib_ajax.nonce;
    });

    loadGames();
});
