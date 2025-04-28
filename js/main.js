jQuery(document).ready(function ($) {
    console.log('Document ready...');

    // Initial load of assets if assets tab is active
    if ($('#asset-tab').is(':visible')) {
        loadAssets();
    }

    // Tab switching function
    $('.odib-tab-card').on('click', function () {
        var tab = $(this).data('tab');
        $('.odib-tab-card').removeClass('active');
        $(this).addClass('active');
        $('.odib-tab-content').hide();
        $('#' + tab + '-tab').show();

        // Load assets when switching to assets tab
        if (tab === 'asset') {
            loadAssets();
        }
    });

    // Array to hold selected characters
    let selectedCharacters = [];

    // Load character list
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

                    // Character card click event
                    $('.character-card').on('click', function () {
                        const characterId = parseInt($(this).data('id'));
                        const characterName = $(this).find('h4').text();
                        const characterImage = $(this).find('img').attr('src');

                        console.log('Clicked character:', characterId, characterName);
                        console.log('Current element:', $(this));

                        // Toggle selection state
                        $(this).toggleClass('selected');

                        if ($(this).hasClass('selected')) {
                            // Character selected
                            selectedCharacters.push({
                                id: characterId,
                                name: characterName,
                                image: characterImage
                            });
                            console.log('Character selected:', characterId);
                        } else {
                            // Character deselected
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

    // Update selected characters view
    function updateSelectedCharacters() {
        console.log('Updating selected characters view');
        const selectedCharactersContainer = $('.selected-characters');
        selectedCharactersContainer.empty();

        if (selectedCharacters.length === 0) {
            selectedCharactersContainer.html('<p>No characters selected yet</p>');
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

                // Remove character from selected characters
                selectedCharacters = selectedCharacters.filter(char => char.id !== idToRemove);

                // Remove selected class from character card
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

    // Game type selection
    $('.type-btn').on('click', function () {
        $('.type-btn').removeClass('selected');
        $(this).addClass('selected');

        // Proceed to next step
        $('.creation-step').removeClass('active');
        $('#characters-step').addClass('active');

        // Save selected game type
        const selectedType = $(this).data('type');
        $('#selected-game-type').val(selectedType);

        console.log('Selected game type:', selectedType);
    });

    // Generate prompt
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
                $('#generate-prompt').prop('disabled', true).text('Generating...');
            },
            success: function (response) {
                if (response.success) {
                    $('#character-prompt').val(response.data);
                } else {
                    alert('Error while generating prompt: ' + response.data);
                }
            },
            complete: function () {
                $('#generate-prompt').prop('disabled', false).text('Created');
            }
        });
    });

    // Create preview
    $('#create-preview').on('click', function () {
        const characterName = $('#character-name').val();
        const promptText = $('#character-prompt').val();

        if (!characterName || !promptText) {
            alert('Please enter character name and prompt');
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
                $('#create-preview').prop('disabled', true).text('Generating...');
                $('#preview-image').html('<div class="loading">Loading...</div>');
            },
            success: function (response) {
                if (response.success) {
                    $('#preview-image').html(`<img src="${response.data}" alt="${characterName}">`);
                } else {
                    alert('Error while generating preview: ' + response.data);
                    $('#preview-image').empty();
                }
            },
            complete: function () {
                $('#create-preview').prop('disabled', false).text('Generate Preview');
            }
        });
    });

    // Save character
    $('#save-character').on('click', function () {
        const characterName = $('#character-name').val();
        const promptText = $('#character-prompt').val();
        const previewImage = $('#preview-image img').attr('src');

        if (!characterName || !promptText || !previewImage) {
            alert('Please fill in all fields and generate a preview');
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
                $('#save-character').prop('disabled', true).text('Saving...');
            },
            success: function (response) {
                if (response.success) {
                    alert('Character saved successfully');
                    $('#character-name').val('');
                    $('#character-prompt').val('');
                    $('#preview-image').empty();
                    loadCharacters(); // Update character list
                } else {
                    alert('Error while saving character: ' + response.data);
                }
            },
            complete: function () {
                $('#save-character').prop('disabled', false).text('Save Character');
            }
        });
    });

    // Generate game concept
    $('#generate-concept').on('click', function () {
        const gameIdea = $('#game-idea').val();

        if (!gameIdea) {
            alert('Please enter a game idea');
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
                $('#generate-concept').prop('disabled', true).text('Generating...');
                $('.concept-section').hide().find('.content').empty();
            },
            success: function (response) {
                if (response.success) {
                    const concept = response.data;

                    // Fill in each section and show
                    $('.mechanics .content').html(concept.mechanics);
                    $('.level-design .content').html(concept.level_design);
                    $('.progression .content').html(concept.progression);
                    $('.visuals .content').html(concept.visuals);

                    $('.concept-section').fadeIn();
                } else {
                    alert('Error while generating game concept: ' + response.data);
                }
            },
            complete: function () {
                $('#generate-concept').prop('disabled', false).text('Generate Concept');
            }
        });
    });

    // Distribution percentage bar event listener
    $('.distribution-bar input[type="range"]').on('input', function () {
        const value = $(this).val();
        const valueDisplay = $(this).siblings('.value-display');

        // Update value display
        valueDisplay.text(parseFloat(value).toFixed(1) + '%');

        // Update value display position
        const percent = (value - $(this).attr('min')) / ($(this).attr('max') - $(this).attr('min'));
        const thumbOffset = 18; // thumb width
        const trackWidth = $(this).width() - thumbOffset;
        const newqsosition = (trackWidth * percent) + (thumbOffset / 2);

        valueDisplay.css('left', newqsosition + 'px');
    }).trigger('input'); // Show value on page load

    // Remove highlight on mouse up
    $('.distribution-bar input[type="range"]').on('mouseup touchend', function () {
        $(this).siblings('.value-display').removeClass('active');
    });

    // Load asset list
    function loadAssets() {
        console.log('Loading assets...');
        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_get_assets',
                _ajax_nonce: odibAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    const assets = response.data;
                    const assetList = $('.saved-grid');
                    assetList.empty();

                    if (assets.length === 0) {
                        assetList.html('<p class="no-items">No saved assets yet</p>');
                        return;
                    }

                    assets.forEach(asset => {
                        const assetCard = $(`
                            <div class="saved-item" data-asset-id="${asset.id}">
                                <img src="${asset.image_url}" alt="${asset.description}">
                                <h4>${asset.asset_type}</h4>
                                <p>${asset.description}</p>
                                <button class="delete-asset">
                                    <i class="fas fa-trash"></i> Delete
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

    // Asset creation form submission
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
                    $('.preview-image').html(`<img src="${response.data.image_url}" alt="Asset preview">`);

                    // Save asset data
                    const assetData = {
                        asset_type: assetType,
                        description: description,
                        image_url: response.data.image_url,
                        prompt: response.data.prompt
                    };

                    // Save asset button click event
                    $('.save-asset').off('click').on('click', function () {
                        saveAsset(assetData);
                    });

                    // Regenerate asset button click event
                    $('.regenerate-asset').off('click').on('click', function () {
                        $('#create-asset-form').submit();
                    });

                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Asset created successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error creating asset: ' + (response.data || 'Unknown error'),
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred: ' + error,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            },
            complete: function () {
                submitButton.prop('disabled', false).html('<i class="fas fa-magic"></i> Create Asset');
            }
        });
    });

    // Save asset function
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
                        title: 'Success!',
                        text: 'Asset saved successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    $('.preview-section').hide();
                    loadAssets();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error saving asset',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred: ' + error,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // Delete saved asset
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
                    // Remove the asset element from DOM
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

    // Array to hold selected game assets
    let selectedGameAssets = [];

    // Load game asset list
    function loadGameAssets() {
        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_get_assets',
                _ajax_nonce: odibAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    const assetList = $('.asset-list');
                    assetList.empty();

                    response.data.forEach(asset => {
                        const assetCard = $(`
                            <div class="asset-card" data-id="${asset.id}">
                                <div class="asset-image">
                                    <img src="${asset.image_url}" alt="${asset.asset_type}">
                                </div>
                                <div class="asset-info">
                                    <h4>${asset.asset_type}</h4>
                                    <p>${asset.description}</p>
                                </div>
                            </div>
                        `);
                        assetList.append(assetCard);
                    });

                    // Game asset selection event
                    $(document).off('click', '.asset-card').on('click', '.asset-card', function (e) {
                        e.preventDefault();
                        const card = $(this);
                        card.toggleClass('selected');

                        // Enable create game button
                        const selectedCount = $('.asset-card.selected').length;
                        $('#createGameBtn').prop('disabled', selectedCount === 0);
                    });
                }
            }
        });
    }

    // Update selected game assets
    function updateSelectedGameAssets() {
        const selectedAssetsContainer = $('.selected-assets');
        selectedAssetsContainer.empty();

        if (selectedGameAssets.length === 0) {
            selectedAssetsContainer.html('<p>No assets selected yet</p>');
            return;
        }

        selectedGameAssets.forEach(asset => {
            const selectedAssetCard = $(`
                <div class="selected-asset-card" data-id="${asset.id}">
                    <img src="${asset.image}" alt="${asset.description}">
                    <span>${asset.type}</span>
                    <button class="remove-asset-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            selectedAssetsContainer.append(selectedAssetCard);
        });

        // Remove asset button click event
        $('.remove-asset-btn').on('click', function () {
            const assetId = $(this).closest('.selected-asset-card').data('id');
            selectedGameAssets = selectedGameAssets.filter(a => a.id !== assetId);
            updateSelectedGameAssets();
        });
    }

    // Load game assets when switching to game tab
    $('.odib-tab-card[data-tab="game"]').on('click', function () {
        loadGameAssets();
    });

    // Load game assets on page load if game tab is active
    if ($('#game-tab').is(':visible')) {
        loadGameAssets();
    }

    // Load character list on page load
    console.log('Initializing character loading...');
    loadCharacters();

    // Load games
    function loadGames() {
        console.log('Loading games...');
        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_get_games',
                _ajax_nonce: odibAjax.nonce
            },
            success: function (response) {
                console.log('Games response:', response);
                if (response.success) {
                    const games = response.data;
                    const gameSelect = $('#gameSelect');
                    gameSelect.empty();
                    gameSelect.append('<option value="">Select a game</option>');

                    games.forEach(game => {
                        gameSelect.append(`<option value="${game.id}">${game.name}</option>`);
                    });
                } else {
                    console.error('Failed to load games:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Load games when switching to coin tab
    $('.odib-tab-card[data-tab="coin"]').on('click', function () {
        loadGames();
    });

    // Load asset list on page load
    loadAssets();

    // Show first tab
    $('#creator-tab').show();

    // Coin creation button
    $(document).on('click', '.game-creator .form-actions button[type="submit"]', function (e) {
        e.preventDefault();
        console.log('Coin creation button clicked');

        // Game selection check
        const selectedGame = $('#gameSelect').val();
        console.log('Selected game:', selectedGame);

        if (!selectedGame) {
            alert('Please select a game first!');
            return;
        }

        const coinData = {
            name: $('#coinName').val(),
            ticker: $('#coinTicker').val(),
            description: $('#coinDescription').val(),
            image_url: $('#coinImageUrl').val(),
            game_id: selectedGame,
            distribution_percentage: $('#distributionPercentage').val()
        };

        console.log('Coin data:', coinData);

        $.ajax({
            url: odibAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'odib_create_coin',
                coin_data: coinData,
                _ajax_nonce: odibAjax.nonce
            },
            beforeSend: function () {
                $(e.target).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
            },
            success: function (response) {
                if (response.success) {
                    alert('Coin created successfully!');
                    // Clear form fields
                    $('#coinName, #coinTicker, #coinDescription, #coinImageUrl').val('');
                    $('#coinImagePreview').html('<div class="preview-placeholder"><i class="fas fa-image"></i><span>Image Preview</span></div>');
                    $('#distributionPercentage').val(0);
                    $('.value-display').text('0%');
                    $('#gameSelect').val('');
                } else {
                    alert('Error creating coin: ' + response.data);
                }
            },
            error: function (xhr, status, error) {
                alert('An error occurred: ' + error);
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            },
            complete: function () {
                $(e.target).prop('disabled', false).html('<i class="fas fa-coins"></i> Create Coin');
            }
        });
    });

    // Game Type Selection
    document.querySelectorAll('.game-type-card').forEach(card => {
        card.addEventListener('click', function () {
            // Remove selected class from all cards
            document.querySelectorAll('.game-type-card').forEach(c => {
                c.classList.remove('selected');
            });

            // Add selected class to clicked card
            this.classList.add('selected');

            // Update hidden input
            document.getElementById('gameType').value = this.dataset.value;

            // Add selection animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'translateY(-2px)';
            }, 100);
        });
    });
});
