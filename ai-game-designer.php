<?php


if (!defined('ABSPATH')) {
    exit;
}

function odib_settings_page() {

    ?>
    <div class="wrap">
        <h1>AI Game Designer Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <td>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="odib_save_settings" class="button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}


function odib_character_creator_shortcode() {
    ob_start();
    ?>
    <div class="odib-container">
        <div class="odib-header">
            <div class="odib-logo">
                <i class="fas fa-dragon"></i>
            </div>
            <div class="odib-header-content">
                <div class="title-wrapper">
                    <h1>AI Game Designer</h1>
                    <div class="created-by">by <span class="brand-text"><span class="earn">EARN</span><span class="fun">.fun</span></span></div>
                </div>
                <p>Create your dream game easily with an AI-powered game design tool. Get inspired by EARN.fun's AI for characters, stories, and game mechanics.</p>
            </div>
        </div>

        <div class="odib-tabs-wrapper">
            <div class="odib-tabs">
                <button class="odib-tab-card active" data-tab="concept">
                    <div class="tab-content">
                        <i class="fas fa-lightbulb"></i>
                        <span>Game Concept</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card" data-tab="creator">
                    <div class="tab-content">
                        <i class="fas fa-user-astronaut"></i>
                        <span>Create Character</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card" data-tab="asset">
                    <div class="tab-content">
                        <i class="fas fa-box"></i>
                        <span>Create Asset</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card" data-tab="game">
                    <div class="tab-content">
                        <i class="fas fa-gamepad"></i>
                        <span>Create Game</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card" data-tab="coin">
                    <div class="tab-content">
                        <i class="fas fa-coins"></i>
                        <span>Create Coin</span>
                    </div>
                    <div class="neon-border"></div>
                </button>
            </div>
        </div>

        <div id="concept-tab" class="odib-tab-content">
            <div class="odib-form">
                <div class="odib-input-group">
                    <label for="game-idea">Game Idea:</label>
                    <textarea id="game-idea" rows="3" placeholder="Example: A platformer game with a rabbit character"></textarea>
                </div>
                <button id="generate-concept">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <span>Generate Concept</span>
                    <i class="fas fa-lightbulb"></i>
                </button>
            </div>
            <div id="concept-result" class="concept-result">
                <div class="concept-section mechanics" style="display: none;">
                    <div class="concept-card">
                        <div class="concept-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <h3>Game Mechanics</h3>
                        <div class="content"></div>
                    </div>
                </div>
                <div class="concept-section level-design" style="display: none;">
                    <div class="concept-card">
                        <div class="concept-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h3>Level Design</h3>
                        <div class="content"></div>
                    </div>
                </div>
                <div class="concept-section progression" style="display: none;">
                    <div class="concept-card">
                        <div class="concept-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Progression System</h3>
                        <div class="content"></div>
                    </div>
                </div>
                <div class="concept-section visuals" style="display: none;">
                    <div class="concept-card">
                        <div class="concept-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3>Visual Style</h3>
                        <div class="content"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="creator-tab" class="odib-tab-content" style="display: none;">
            <div class="odib-form">
                <div class="odib-input-group">
                    <label for="character-name">Character Name:</label>
                    <input type="text" id="character-name" required>
                </div>
                
                <div class="odib-input-group">
                    <label for="character-prompt">Character Description:</label>
                    <div class="prompt-container">
                        <textarea id="character-prompt" rows="4" required></textarea>
                        <button id="generate-prompt" class="dice-button" title="Generate Random Description">🎲</button>
                    </div>
                    <small>Write your own description or click the dice button to generate a random one.</small>
                </div>

                <div class="button-container">
                    <button id="preview-character"><i class="fas fa-robot"></i> Generate Characters</button>
                </div>

            </div>

            <div id="result" class="odib-result"></div>
        </div>

        <div id="asset-tab" class="odib-tab-content" style="display: none;">
            <div class="asset-creator">
                <div class="creator-header">
                    <h2>2D Game Asset Creator</h2>
                </div>

                <div class="creator-content">
                    <form id="create-asset-form">
                        <?php wqs_nonce_field('odib_nonce', '_ajax_nonce'); ?>
                        
                        <div class="form-group">
                            <label for="asset-type">Asset Type:</label>
                            <div class="asset-type-grid">
                                <div class="asset-type-card" data-value="sword">
                                    <i class="fas fa-khanda"></i>
                                    <span>Sword</span>
                                </div>
                                <div class="asset-type-card" data-value="shield">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Shield</span>
                                </div>
                                <div class="asset-type-card" data-value="potion">
                                    <i class="fas fa-flask"></i>
                                    <span>Potion</span>
                                </div>
                                <div class="asset-type-card" data-value="bow">
                                    <i class="fas fa-bullseye"></i>
                                    <span>Bow</span>
                                </div>
                                <div class="asset-type-card" data-value="staff">
                                    <i class="fas fa-magic"></i>
                                    <span>Staff</span>
                                </div>
                                <div class="asset-type-card" data-value="armor">
                                    <i class="fas fa-tshirt"></i>
                                    <span>Armor</span>
                                </div>
                                <div class="asset-type-card" data-value="ring">
                                    <i class="fas fa-ring"></i>
                                    <span>Ring</span>
                                </div>
                                <div class="asset-type-card" data-value="amulet">
                                    <i class="fas fa-gem"></i>
                                    <span>Amulet</span>
                                </div>
                                <div class="asset-type-card" data-value="gem">
                                    <i class="fas fa-dice-d20"></i>
                                    <span>Gem</span>
                                </div>
                                <div class="asset-type-card" data-value="scroll">
                                    <i class="fas fa-scroll"></i>
                                    <span>Scroll</span>
                                </div>
                            </div>
                            <input type="hidden" id="asset-type" name="asset_type" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="asset-description">Properties:</label>
                            <textarea id="asset-description" name="description" required placeholder="Describe the asset's properties in detail... Example: Flaming, frozen, poisonous, ancient, magical etc."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="button button-primary">
                                <i class="fas fa-magic"></i> Create Asset
                            </button>
                        </div>
                    </form>
                </div>

                <div class="preview-section" style="display: none;">
                    <div class="preview-header">
                        <h3>Preview</h3>
                    </div>
                    <div class="preview-content">
                        <div class="preview-image">
                            <!-- Preview image will be placed here -->
                        </div>
                        <div class="preview-actions">
                            <button class="button button-primary save-asset">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <button class="button regenerate-asset">
                                <i class="fas fa-redo"></i> Regenerate
                            </button>
                        </div>
                    </div>
                </div>

                <div class="saved-section">
                    <div class="saved-header">
                        <h3>Saved Assets</h3>
                    </div>
                    <div class="saved-content">
                        <div class="saved-grid"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="game-tab" class="odib-tab-content" style="display: none;">
            <div class="game-creator">
                <div class="creation-step">
                    <h3>1. Game Type</h3>
                    <div class="game-type-container">
                        <div class="game-type-card" data-value="action">
                            <i class="fas fa-fist-raised"></i>
                            <span>Action</span>
                            <small>Fast-paced combat and challenges</small>
                            <i class="fas fa-check check-icon"></i>
                        </div>
                        <div class="game-type-card" data-value="rpg">
                            <i class="fas fa-hat-wizard"></i>
                            <span>RPG</span>
                            <small>Character development and story</small>
                            <i class="fas fa-check check-icon"></i>
                        </div>
                        <div class="game-type-card" data-value="strategy">
                            <i class="fas fa-chess"></i>
                            <span>Strategy</span>
                            <small>Tactical planning and resource management</small>
                            <i class="fas fa-check check-icon"></i>
                        </div>
                        <div class="game-type-card" data-value="adventure">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Adventure</span>
                            <small>Exploration and discovery</small>
                            <i class="fas fa-check check-icon"></i>
                        </div>
                        <div class="game-type-card" data-value="puzzle">
                            <i class="fas fa-puzzle-piece"></i>
                            <span>Puzzle</span>
                            <small>Brain teasers and logic challenges</small>
                            <i class="fas fa-check check-icon"></i>
                        </div>
                        <div class="game-type-card" data-value="simulation">
                            <i class="fas fa-city"></i>
                            <span>Simulation</span>
                            <small>Life and world simulation</small>
                            <i class="fas fa-check check-icon"></i>
                        </div>
                    </div>
                    <input type="hidden" id="gameType" name="gameType" required>
                </div>

                <div class="creation-step">
                    <h3>2. Assets</h3>
                    <div class="asset-list">
                        <!-- Assets will be placed here -->
                    </div>
                </div>

                <div class="creation-step">
                    <h3>3. Characters</h3>
                    <div class="character-list"></div>
                    <div class="selected-characters">
                        <p>No characters selected yet</p>
                    </div>
                </div>

                <div class="creation-step">
                    <h3>4. Game Details</h3>
                    <div class="game-details">
                        <textarea id="gameDetails" placeholder="Provide detailed information about your game..."></textarea>
                    </div>
                </div>

                <div class="form-actions-container">
                    <div class="form-actions">
                        <button type="button" class="button button-primary disabled" disabled>
                            <i class="fas fa-gamepad"></i> Create Game
                            <span class="coming-soon-badge">Coming Soon</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="coin-tab" class="odib-tab-content" style="display: none;">
            <div class="game-creator">
                <div class="creation-step">
                    <h3>1. Coin Information</h3>
                    <div class="form-group">
                        <input type="text" id="coinName" name="coinName" required placeholder="Enter coin name">
                    </div>
                        
                        <div class="form-group">
                            <input type="text" id="coinTicker" name="coinTicker" required placeholder="Ticker symbol (Ex: BTC, ETH)">
                        </div>
                    </div>

                    <div class="creation-step">
                        <h3>2. Coin Description</h3>
                        <div class="prompt-container">
                            <textarea id="coinDescription" name="coinDescription" required placeholder="Write a detailed description about the coin..."></textarea>
                            <button type="button" class="dice-button" title="Generate Random Description">
                                <i class="fas fa-dice"></i>
                            </button>
                        </div>
                    </div>

                    <div class="creation-step">
                        <h3>3. Coin Image</h3>
                        <div class="media-upload-container">
                            <div class="upload-options">
                                <label class="upload-option">
                                    <input type="file" id="coinImageFile" accept="image/*" class="file-input">
                                    <div class="upload-content">
                                        <i class="fas fa-upload"></i>
                                        <span>Upload from Computer</span>
                                        <small>PNG, JPG or GIF (Max 2MB)</small>
                                    </div>
                                </label>
                                <div class="upload-divider">
                                    <span>or</span>
                                </div>
                                <button type="button" id="generateCoinImage" class="ai-generate-btn">
                                    <i class="fas fa-magic"></i>
                                    <span>Generate with AI</span>
                                </button>
                            </div>
                            <div id="coinImagePreview" class="image-preview">
                                <div class="preview-placeholder">
                                    <i class="fas fa-image"></i>
                                    <span>Image Preview</span>
                                </div>
                            </div>
                            <input type="hidden" id="coinImageUrl" name="coinImageUrl">
                        </div>
                    </div>

                    <div class="creation-step">
                        <h3>4. Game Selection</h3>
                        <div class="game-select-container">
                            <select id="gameSelect" name="gameSelect" required>
                                <option value="">Select a game</option>
                            </select>
                        </div>
                    </div>

                    <div class="creation-step">
                        <h3>5. Distribution Percentage</h3>
                        <div class="distribution-bar">
                            <div class="range-wrapper">
                                <input type="range" id="distributionPercentage" name="distributionPercentage" min="0" max="20" value="0" step="0.1">
                                <div class="value-display">0%</div>
                            </div>
                            <div class="range-labels">
                                <span>0%</span>
                                <span>20%</span>
                            </div>
                            <small class="help-text">Percentage of coins to be allocated for players (maximum 20%)</small>
                        </div>
                    </div>

                    <div class="creation-step">
                        <h3>6. Social Media & Website</h3>
                        <div class="social-links-container">
                            <div class="input-with-icon">
                                <i class="fab fa-twitter"></i>
                                <input type="text" id="twitterHandle" name="twitterHandle" placeholder="Twitter handle (optional)">
                            </div>
                            <div class="input-with-icon">
                                <i class="fab fa-telegram-plane"></i>
                                <input type="text" id="telegramGroup" name="telegramGroup" placeholder="Telegram group link (optional)">
                            </div>
                            <div class="input-with-icon">
                                <i class="fas fa-globe"></i>
                                <input type="url" id="website" name="website" placeholder="Website URL (optional)">
                            </div>
                            <div class="input-with-icon">
                                <i class="fab fa-discord"></i>
                                <input type="text" id="discordServer" name="discordServer" placeholder="Discord server invite (optional)">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions-container">
                        <div class="form-actions">
                            <button type="submit" class="button button-primary">
                                <i class="fas fa-coins"></i> Create Coin
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        
        
        $('.odib-tab-card').on('click', function() {
            $('.odib-tab-card').removeClass('active');
            $(this).addClass('active');
            
            const tabId = $(this).data('tab');
            $('.odib-tab-content').hide();
            $(`#${tabId}-tab`).show();

            if (tabId === 'gallery') {
                loadGallery();
            }
        });

        
        $('.odib-tab-content').hide();
        $('#concept-tab').show();

        
        $('.odib-tab-card').on('click', function() {
            const tabId = $(this).data('tab');
            
            
            $('.odib-tab-card').removeClass('active');
            
            $(this).addClass('active');
            
            
            $('.odib-tab-content').hide();
            
            $(`#${tabId}-tab`).show();
        });
        
        
        $('#generate-prompt').on('click', function() {
            const $button = $(this);
            const $promptDisplay = $('#character-prompt');
            
            console.log('Prompt creation started...');
            $button.prop('disabled', true);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_generate_prompt',
                    nonce: '<?php echo wqs_create_nonce("odib_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $promptDisplay.val(response.data);
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('An error occurred: ' + error);
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        
        $('#preview-character').on('click', async function() {
            const $button = $(this);
            const $result = $('#result');
            const name = $('#character-name').val();
            const prompt = $('#character-prompt').val();
            
            if (!name || !prompt) {
                alert('Please enter character name and prompt');
                return;
            }
            
            $button.prop('disabled', true).text('Generating Characters...');
            $result.empty();

            try {
                
                for (let i = 0; i < 4; i++) {
                    const response = await $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'odib_create_preview',
                            nonce: '<?php echo wqs_create_nonce("odib_nonce"); ?>',
                            name: `${name}_${i + 1}`,
                            prompt: prompt
                        }
                    });

                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                alert('An error occurred: ' + error);
            } finally {
                $button.prop('disabled', false).text('Generate Characters');
            }
        });

        
        $(document).on('click', '.save-character', function() {
            const $button = $(this);
            if ($button.hasClass('saved')) return;

            const name = $button.data('name');
            const image_url = $button.data('image');
            const prompt = $button.data('prompt');

            $button.prop('disabled', true).text('Saving...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_save_character',
                    nonce: '<?php echo wqs_create_nonce("odib_nonce"); ?>',
                    name: name,
                    image_url: image_url,
                    prompt: prompt
                },
                success: function(response) {
                    if (response.success) {
                        $button.addClass('saved').text('Saved').prop('disabled', true);
                    } else {
                        alert('Error: ' + response.data);
                        $button.prop('disabled', false).text('Save');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('An error occurred: ' + error);
                    $button.prop('disabled', false).text('Save');
                }
            });
        });

        
        $(document).on('click', '.delete-character', function() {
            const $card = $(this).closest('.character-card');
            const characterId = $(this).data('id');

            if (confirm('Are you sure you want to delete this character?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'odib_delete_character',
                        nonce: '<?php echo wqs_create_nonce("odib_nonce"); ?>',
                        character_id: characterId
                    },
                    success: function(response) {
                        if (response.success) {
                            $card.fadeOut(400, function() {
                                $(this).remove();
                            });
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }
        });

        
        function loadGallery() {
            const $gallery = $('#gallery');
            $gallery.html('Loading...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_get_characters',
                    nonce: '<?php echo wqs_create_nonce("odib_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $gallery.empty();
                        response.data.forEach(function(character) {
                            const card = $('<div class="character-card">').html(`
                                <button class="delete-character" data-id="${character.id}">×</button>
                                <img src="${character.image_url}" alt="${character.character_name}">
                                <h3>${character.character_name}</h3>
                                <p><small>${character.prompt}</small></p>
                                <div class="character-actions">
                                    <button class="download-character" onclick="downloadImage('${character.image_url}', '${character.character_name}')">
                                        <svg xmlns="http:
                                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                        </svg>
                                        Download
                                    </button>
                                </div>
                            `);
                            $gallery.append(card);
                        });
                    } else {
                        $gallery.html('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    $gallery.html('An error occurred: ' + error);
                }
            });
        }

        
        async function downloadImage(url, name) {
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                const blobUrl = window.URL.createObjectURL(blob);
                
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = `${name}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(blobUrl);
            } catch (error) {
                console.error('Download error:', error);
                alert('An error occurred while downloading the image: ' + error);
            }
        }

        
        $('#generate-concept').on('click', function() {
            const $button = $(this);
            const gameIdea = $('#game-idea').val();
            
            if (!gameIdea) {
                alert('Please enter a game idea');
                return;
            }
            
        });

        
        const percentageRange = $('#distributionPercentage');
        const percentageNumber = $('#percentageNumber');
        const percentageValue = $('#percentageValue');
        
        
        function syncPercentageInputs(value) {
            percentageRange.val(value);
            percentageNumber.val(value);
            percentageValue.text(value + '%');
        }
        
        percentageRange.on('input', function() {
            syncPercentageInputs($(this).val());
        });
        
        percentageNumber.on('input', function() {
            let value = $(this).val();
            if (value > 20) value = 20;
            if (value < 0) value = 0;
            syncPercentageInputs(value);
        });
        
        
        $('#uploadMediaBtn').on('click', function(e) {
            e.preventDefault();
            
            const mediaUploader = wqs.media({
                title: 'Select Coin Image',
                button: {
                    text: 'Select'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#mediaUrl').val(attachment.url);
                
                const preview = $('#mediaPreview');
                preview.empty();
                
                if (attachment.type === 'image') {
                    preview.html(`<img src="${attachment.url}" alt="Coin image">`);
                } else if (attachment.type === 'video') {
                    preview.html(`<video src="${attachment.url}" controls></video>`);
                }
            });
            
            mediaUploader.open();
        });
    </script>

    <script>
    jQuery(document).ready(function($) {
        
        const coinImageFile = $('#coinImageFile');
        const coinImagePreview = $('#coinImagePreview');
        const uploadContent = $('.upload-content');
        const generateBtn = $('#generateCoinImage');
        
        
        coinImageFile.on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) { 
                    return;
                }
                
                if (!file.type.startsWith('image/')) {
                    alert('Please select a valid image file');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    coinImagePreview.html(`<img src="${e.target.result}" alt="Coin image preview">`);
                    $('#coinImageUrl').val(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
        
        
        uploadContent.on('dragenter dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        uploadContent.on('dragleave drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });
        
        uploadContent.on('drop', function(e) {
            e.preventDefault();
            const file = e.originalEvent.dataTransfer.files[0];
            if (file) {
                coinImageFile[0].files = e.originalEvent.dataTransfer.files;
                coinImageFile.trigger('change');
            }
        });
        
        
        generateBtn.on('click', function() {
            
            $(this).addClass('loading');
            
            setTimeout(() => {
                $(this).removeClass('loading');
                
            }, 2000); 
        });
    });
    </script>

    <?php
    return ob_get_clean();
}

function odib_get_characters() {
    
    if (!check_ajax_referer('odib-nonce', 'nonce', false)) {
        wqs_die();
    }
    
    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_characters';
    
    error_log('Querying table: ' . $table_name);
    
    $characters = [];
    if ($wqsdb->last_error) {
        return;
    }
    wqs_send_json_success($characters);
}