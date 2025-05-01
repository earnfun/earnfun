jQuery(document).ready(function ($) {

    if ($('#asset-tab').is(':visible')) {
        loadAssets();
    }

    $('.odib-tab-card').on('click', function () {
        var tab = $(this).data('tab');
        $('.odib-tab-card').removeClass('active');
        $(this).addClass('active');
        $('.odib-tab-content').hide();
        $('#' + tab + '-tab').show();

        if (tab === 'asset') {
            loadAssets();
        }
    });

    $('.type-btn').on('click', function () {
        $('.type-btn').removeClass('selected');
        $(this).addClass('selected');

        $('.creation-step').removeClass('active');
        $('#characters-step').addClass('active');

        const selectedType = $(this).data('type');
        $('#selected-game-type').val(selectedType);

    });

    $('.distribution-bar input[type="range"]').on('input', function () {
        const value = $(this).val();
        const valueDisplay = $(this).siblings('.value-display');

        valueDisplay.text(parseFloat(value).toFixed(1) + '%');

        const percent = (value - $(this).attr('min')) / ($(this).attr('max') - $(this).attr('min'));
        const thumbOffset = 18;
        const trackWidth = $(this).width() - thumbOffset;
        const newqsosition = (trackWidth * percent) + (thumbOffset / 2);

        valueDisplay.css('left', newqsosition + 'px');
    }).trigger('input');

    $('.distribution-bar input[type="range"]').on('mouseup touchend', function () {
        $(this).siblings('.value-display').removeClass('active');
    });

    $('.asset-type-card').on('click', function () {
        const selectedValue = $(this).data('value');
        $('#asset-type').val(selectedValue);

        $('.asset-type-card').removeClass('selected');
        $(this).addClass('selected');
    });

    $('#create-asset-form').on('submit', function (e) {
        e.preventDefault();

        const assetType = $('#asset-type').val();
        const description = $('#asset-description').val();
        const nonce = $('#_ajax_nonce').val();

        if (!assetType || !description) {
            Swal.fire({
                title: 'Error!',
                text: 'Please fill in all fields',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Asset...');
    });

    $(document).on('click', '.delete-asset', function (e) {
        e.preventDefault();

        const assetId = $(this).closest('.saved-item').data('asset-id');
        const nonce = $('#_ajax_nonce').val();

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_delete_asset',
                asset_id: assetId,
                _ajax_nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    $(`[data-asset-id="${assetId}"]`).closest('.saved-item').remove();
                } else {
                    alert('Error deleting asset: ' + response.data);
                }
            },
            error: function (xhr, status, error) {
                alert('Server communication error');
                console.error('AJAX Error:', status, error);
            }
        });
    });

    $('.odib-tab-card[data-tab="game"]').on('click', function () {
        loadGameAssets();
    });

    if ($('#game-tab').is(':visible')) {
        loadGameAssets();
    }

    $('.odib-tab-card[data-tab="coin"]').on('click', function () {
        loadGames();
    });

    loadAssets();

    $('#creator-tab').show();

    $(document).on('click', '.game-creator .form-actions button[type="submit"]', function (e) {
        e.preventDefault();

        const selectedGame = $('#gameSelect').val();

        if (!selectedGame) {
            alert('Please select a game first!');
            return;
        }
    });

    document.querySelectorAll('.game-type-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.game-type-card').forEach(c => {
                c.classList.remove('selected');
            });

            this.classList.add('selected');

            document.getElementById('gameType').value = this.dataset.value;

            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'translateY(-2px)';
            }, 100);
        });
    });
});
