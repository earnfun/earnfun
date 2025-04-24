jQuery(document).ready(function ($) {
    console.log('Document ready...');

    // Tab değiştirme işlevi
    $('.odib-tab-card').on('click', function () {
        var tab = $(this).data('tab');
        $('.odib-tab-card').removeClass('active');
        $(this).addClass('active');
        $('.odib-tab-content').hide();
        $('#' + tab + '-tab').show();
    });

    // Seçilen karakterleri tutacak dizi
    let selectedCharacters = [];

    // Karakter listesini yükle
    function loadCharacters() {
        console.log('Loading characters...');
        console.log('AJAX URL:', odibAjax.ajaxurl);
        console.log('AJAX Nonce:', odibAjax.nonce);

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_get_characters',
                _ajax_nonce: odibAjax.nonce
            },
            success: function (response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    const characters = response.data;
                    const characterList = $('.character-list');
                    characterList.empty();

                    console.log('Found characters:', characters.length);

                    characters.forEach(character => {
                        console.log('Adding character:', character);
                        const characterCard = $(`
                            <div class="character-card" data-id="${character.id}">
                                <img src="${character.image_url}" alt="${character.character_name}">
                                <h4>${character.character_name}</h4>
                            </div>
                        `);
                        characterList.append(characterCard);
                    });

                    // Karakter kartlarına tıklama olayı ekle
                    $('.character-card').on('click', function () {
                        const characterId = parseInt($(this).data('id'));
                        const characterName = $(this).find('h4').text();
                        const characterImage = $(this).find('img').attr('src');

                        console.log('Clicked character:', characterId, characterName);
                        console.log('Current element:', $(this));

                        // Seçim durumunu değiştir
                        $(this).toggleClass('selected');

                        if ($(this).hasClass('selected')) {
                            // Karakter seçildi
                            selectedCharacters.push({
                                id: characterId,
                                name: characterName,
                                image: characterImage
                            });
                            console.log('Character selected:', characterId);
                        } else {
                            // Karakter seçimden kaldırıldı
                            selectedCharacters = selectedCharacters.filter(char => char.id !== characterId);
                            console.log('Character deselected:', characterId);
                        }

                        console.log('Selected characters:', selectedCharacters);
                        updateSelectedCharacters();
                    });

                    console.log('Character cards initialized');
                } else {
                    console.error('Failed to load characters:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Seçili karakterleri güncelle
    function updateSelectedCharacters() {
        console.log('Updating selected characters view');
        const selectedCharactersContainer = $('.selected-characters');
        selectedCharactersContainer.empty();

        if (selectedCharacters.length === 0) {
            selectedCharactersContainer.html('<p>Henüz karakter seçilmedi</p>');
            return;
        }

        const selectedCharactersList = $('<div class="selected-character-list"></div>');

        selectedCharacters.forEach(character => {
            console.log('Adding selected character to view:', character);
            const selectedCharacterElement = $(`
                <div class="selected-character">
                    <img src="${character.image}" alt="${character.name}">
                    <span>${character.name}</span>
                    <button class="remove-character" data-id="${character.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);

            selectedCharacterElement.find('.remove-character').on('click', function (e) {
                e.stopPropagation();
                const idToRemove = parseInt($(this).data('id'));

                console.log('Removing character:', idToRemove);

                // Seçili karakterlerden kaldır
                selectedCharacters = selectedCharacters.filter(char => char.id !== idToRemove);

                // Karakter kartından selected sınıfını kaldır
                $(`.character-card[data-id="${idToRemove}"]`).removeClass('selected');

                console.log('Character removed:', idToRemove);
                console.log('Remaining characters:', selectedCharacters);

                updateSelectedCharacters();
            });

            selectedCharactersList.append(selectedCharacterElement);
        });

        selectedCharactersContainer.append(selectedCharactersList);
        console.log('Selected characters view updated');
    }

    // Oyun türü seçimi
    $('.type-btn').on('click', function () {
        $('.type-btn').removeClass('selected');
        $(this).addClass('selected');

        // Sonraki adıma geçiş
        $('.creation-step').removeClass('active');
        $('#characters-step').addClass('active');

        // Seçilen oyun türünü sakla
        const selectedType = $(this).data('type');
        $('#selected-game-type').val(selectedType);

        console.log('Selected game type:', selectedType);
    });

    // Prompt oluşturma
    $('#generate-prompt').on('click', function () {
        const promptText = $('#character-prompt').val();

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_generate_prompt',
                prompt: promptText,
                _ajax_nonce: odibAjax.nonce
            },
            beforeSend: function () {
                $('#generate-prompt').prop('disabled', true).text('Oluşturuluyor...');
            },
            success: function (response) {
                if (response.success) {
                    $('#character-prompt').val(response.data);
                } else {
                    alert('Prompt oluşturulurken bir hata oluştu: ' + response.data);
                }
            },
            complete: function () {
                $('#generate-prompt').prop('disabled', false).text('Prompt Oluştur');
            }
        });
    });

    // Önizleme oluşturma
    $('#create-preview').on('click', function () {
        const characterName = $('#character-name').val();
        const promptText = $('#character-prompt').val();

        if (!characterName || !promptText) {
            alert('Lütfen karakter adı ve prompt alanlarını doldurun.');
            return;
        }

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_create_preview',
                character_name: characterName,
                prompt: promptText,
                _ajax_nonce: odibAjax.nonce
            },
            beforeSend: function () {
                $('#create-preview').prop('disabled', true).text('Oluşturuluyor...');
                $('#preview-image').html('<div class="loading">Yükleniyor...</div>');
            },
            success: function (response) {
                if (response.success) {
                    $('#preview-image').html(`<img src="${response.data}" alt="${characterName}">`);
                } else {
                    alert('Önizleme oluşturulurken bir hata oluştu: ' + response.data);
                    $('#preview-image').empty();
                }
            },
            complete: function () {
                $('#create-preview').prop('disabled', false).text('Önizleme Oluştur');
            }
        });
    });

    // Karakteri kaydetme
    $('#save-character').on('click', function () {
        const characterName = $('#character-name').val();
        const promptText = $('#character-prompt').val();
        const previewImage = $('#preview-image img').attr('src');

        if (!characterName || !promptText || !previewImage) {
            alert('Lütfen tüm alanları doldurun ve bir önizleme oluşturun.');
            return;
        }

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_save_character',
                character_name: characterName,
                prompt: promptText,
                image_url: previewImage,
                _ajax_nonce: odibAjax.nonce
            },
            beforeSend: function () {
                $('#save-character').prop('disabled', true).text('Kaydediliyor...');
            },
            success: function (response) {
                if (response.success) {
                    alert('Karakter başarıyla kaydedildi!');
                    $('#character-name').val('');
                    $('#character-prompt').val('');
                    $('#preview-image').empty();
                    loadCharacters(); // Karakter listesini güncelle
                } else {
                    alert('Karakter kaydedilirken bir hata oluştu: ' + response.data);
                }
            },
            complete: function () {
                $('#save-character').prop('disabled', false).text('Karakteri Kaydet');
            }
        });
    });

    // Oyun konsepti oluşturma
    $('#generate-concept').on('click', function () {
        const gameIdea = $('#game-idea').val();

        if (!gameIdea) {
            alert('Lütfen bir oyun fikri girin.');
            return;
        }

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_generate_concept',
                idea: gameIdea,
                _ajax_nonce: odibAjax.nonce
            },
            beforeSend: function () {
                $('#generate-concept').prop('disabled', true).text('Oluşturuluyor...');
                $('.concept-section').hide().find('.content').empty();
            },
            success: function (response) {
                if (response.success) {
                    const concept = response.data;

                    // Her bölümü doldur ve göster
                    $('.mechanics .content').html(concept.mechanics);
                    $('.level-design .content').html(concept.level_design);
                    $('.progression .content').html(concept.progression);
                    $('.visuals .content').html(concept.visuals);

                    $('.concept-section').fadeIn();
                } else {
                    alert('Konsept oluşturulurken bir hata oluştu: ' + response.data);
                }
            },
            complete: function () {
                $('#generate-concept').prop('disabled', false).text('Konsept Oluştur');
            }
        });
    });

    // Dağıtım yüzdesi barı için event listener
    $('.distribution-bar input[type="range"]').on('input', function () {
        const value = $(this).val();
        const valueDisplay = $(this).siblings('.value-display');

        // Değer göstergesini güncelle
        valueDisplay.text(parseFloat(value).toFixed(1) + '%');

        // Değer göstergesinin pozisyonunu ayarla
        const percent = (value - $(this).attr('min')) / ($(this).attr('max') - $(this).attr('min'));
        const thumbOffset = 18; // thumb genişliği
        const trackWidth = $(this).width() - thumbOffset;
        const newqsosition = (trackWidth * percent) + (thumbOffset / 2);

        valueDisplay.css('left', newqsosition + 'px');
    }).trigger('input'); // Sayfa yüklendiğinde değeri göster

    // Mouse bırakıldığında vurgulamayı kaldır
    $('.distribution-bar input[type="range"]').on('mouseup touchend', function () {
        $(this).siblings('.value-display').removeClass('active');
    });

    // Asset listesini yükle
    function loadAssets() {
        console.log('Loading assets...');
        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_get_assets',
                nonce: odibAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    const assets = response.data;
                    const assetList = $('.saved-grid');
                    assetList.empty();

                    if (assets.length === 0) {
                        assetList.html('<p class="no-items">Henüz kayıtlı eşya bulunmuyor</p>');
                        return;
                    }

                    assets.forEach(asset => {
                        const assetCard = $(`
                            <div class="saved-item">
                                <img src="${asset.image_url}" alt="${asset.description}">
                                <h4>${asset.asset_type}</h4>
                                <p>${asset.description}</p>
                                <button class="delete-asset" data-id="${asset.id}">
                                    <i class="fas fa-trash"></i> Sil
                                </button>
                            </div>
                        `);
                        assetList.append(assetCard);
                    });
                } else {
                    console.error('Failed to load assets:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    // Asset type selection
    $('.asset-type-card').on('click', function () {
        const selectedValue = $(this).data('value');
        $('#asset-type').val(selectedValue);

        // Update visual selection
        $('.asset-type-card').removeClass('selected');
        $(this).addClass('selected');
    });

    // Asset oluşturma formu gönderimi
    $('#create-asset-form').on('submit', function (e) {
        e.preventDefault();

        const assetType = $('#asset-type').val();
        const description = $('#asset-description').val();
        const nonce = $('#_ajax_nonce').val();

        if (!assetType || !description) {
            Swal.fire({
                title: 'Hata!',
                text: 'Lütfen tüm alanları doldurun',
                icon: 'error',
                confirmButtonText: 'Tamam'
            });
            return;
        }

        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eşya Oluşturuluyor...');

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_generate_asset',
                asset_type: assetType,
                description: description,
                _ajax_nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    $('.preview-section').show();
                    $('.preview-image').html(`<img src="${response.data.image_url}" alt="Eşya önizleme">`);

                    // Asset verilerini kaydet
                    const assetData = {
                        asset_type: assetType,
                        description: description,
                        image_url: response.data.image_url,
                        prompt: response.data.prompt
                    };

                    // Kaydet butonuna tıklandığında
                    $('.save-asset').off('click').on('click', function () {
                        saveAsset(assetData);
                    });

                    // Yeniden oluştur butonuna tıklandığında
                    $('.regenerate-asset').off('click').on('click', function () {
                        $('#create-asset-form').submit();
                    });

                    // Başarılı mesajı göster
                    Swal.fire({
                        title: 'Başarılı!',
                        text: 'Eşya başarıyla oluşturuldu',
                        icon: 'success',
                        confirmButtonText: 'Tamam'
                    });
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Eşya oluşturulurken bir hata oluştu: ' + (response.data || 'Bilinmeyen hata'),
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    title: 'Hata!',
                    text: 'Bir hata oluştu: ' + error,
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            },
            complete: function () {
                submitButton.prop('disabled', false).html('<i class="fas fa-magic"></i> Eşya Oluştur');
            }
        });
    });

    // Asset kaydetme fonksiyonu
    function saveAsset(assetData) {
        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_save_asset',
                asset_type: assetData.asset_type,
                description: assetData.description,
                image_url: assetData.image_url,
                prompt: assetData.prompt,
                nonce: odibAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Başarılı!',
                        text: 'Eşya başarıyla kaydedildi.',
                        icon: 'success',
                        confirmButtonText: 'Tamam'
                    });
                    $('.preview-section').hide();
                    loadAssets();
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Eşya kaydedilirken bir hata oluştu',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    title: 'Hata!',
                    text: 'Bir hata oluştu: ' + error,
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        });
    }

    // Kaydedilen asseti silme
    $(document).on('click', '.delete-asset', function () {
        const assetId = $(this).data('id');

        Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu eşyayı silmek istediğinizden emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: odibAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'odib_delete_asset',
                        asset_id: assetId,
                        nonce: odibAjax.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire(
                                'Silindi!',
                                'Eşya başarıyla silindi.',
                                'success'
                            );
                            loadAssets();
                        } else {
                            Swal.fire(
                                'Hata!',
                                'Eşya silinirken bir hata oluştu.',
                                'error'
                            );
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', error);
                        Swal.fire(
                            'Hata!',
                            'Sunucu ile iletişim kurulamadı.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Sayfa yüklendiğinde karakter listesini yükle
    console.log('Initializing character loading...');
    loadCharacters();
    loadCoins();
    loadGames();

    // İlk yüklemede asset listesini yükle
    loadAssets();

    // İlk sekmeyi göster
    $('#creator-tab').show();
});
