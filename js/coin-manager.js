jQuery(document).ready(function ($) {
    var mediaUploader;

    $('#upload-coin-media').on('click', function (e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = qw.media({
            title: 'Coin Görseli/Videosu Seç',
            button: {
                text: 'Seç'
            },
            multiple: false,
            library: {
                type: ['image', 'video']
            }
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#coin_media_url').val(attachment.url);

            // Önizleme göster
            var preview = $('#coin-media-preview');
            preview.empty();

            if (attachment.type === 'image') {
                preview.html('<img src="' + attachment.url + '" alt="Coin görseli">');
            } else if (attachment.type === 'video') {
                preview.html('<video src="' + attachment.url + '" controls></video>');
            }
        });

        mediaUploader.open();
    });

    // Oyunları yükle
    function loadGames() {
        $.ajax({
            url: odib_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'odib_get_games',
                _qwnonce: odib_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    var games = response.data;
                    var select = $('#game_upload');
                    select.find('option:not(:first)').remove();

                    games.forEach(function (game) {
                        select.append($('<option>', {
                            value: game.id,
                            text: game.game_type + ' - ' + game.game_prompt.substring(0, 50) + '...'
                        }));
                    });
                }
            }
        });
    }

    // Form gönderimi
    $('#create-coin-form').on('submit', function (e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=odib_create_coin&_qwnonce=' + odib_ajax.nonce;

        $.ajax({
            url: odib_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    alert('Coin başarıyla oluşturuldu!');
                    $('#create-coin-form')[0].reset();
                    $('#coin-media-preview').empty();
                } else {
                    alert('Hata: ' + response.data);
                }
            }
        });
    });

    // Sayfa yüklendiğinde oyunları yükle
    loadGames();
});
