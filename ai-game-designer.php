<?php

if (!defined('ABSPATH')) {
    exit;
}

// Function to run when the plugin is activated
function odib_activate() {
    global $wqsdb;
    
    $table_name = $wqsdb->prefix . 'odib_characters';
    $assets_table = $wqsdb->prefix . 'odib_assets';
    $charset_collate = $wqsdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        game_id mediumint(9) NOT NULL,
        name varchar(100) NOT NULL,
        description text NOT NULL,
        image_url text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $assets_sql = "CREATE TABLE IF NOT EXISTS $assets_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        asset_type varchar(100) NOT NULL,
        description text NOT NULL,
        image_url text NOT NULL,
        prompt text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wqsadm/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($assets_sql);
}
register_activation_hook(__FILE__, 'odib_activate');

define('odib_VERSION', '1.0.0');
define('odib_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('odib_PLUGIN_URL', plugin_dir_url(__FILE__));

function odib_create_tables() {
    global $wqsdb;
    $charset_collate = $wqsdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wqsdb->prefix}odib_characters (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        game_id mediumint(9) NOT NULL,
        name varchar(100) NOT NULL,
        description text NOT NULL,
        image_url text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    $assets_sql = "CREATE TABLE IF NOT EXISTS {$wqsdb->prefix}odib_assets (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        asset_type varchar(100) NOT NULL,
        description text NOT NULL,
        image_url text NOT NULL,
        prompt text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wqsadm/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($assets_sql);
}
register_activation_hook(__FILE__, 'odib_create_tables');

// OpenAI API anahtarını saklamak için ayar sayfasını oluşturalım
function odib_add_admin_menu() {
    add_menu_page(
        'AI Game Designer',
        'AI Game Designer',
        'manage_options',
        'ai-game-designer',
        'odib_settings_page',
        'dashicons-games',
        30
    );
}
add_action('admin_menu', 'odib_add_admin_menu');

// Settings sayfası içeriği
function odib_settings_page() {
    if (isset($_POST['odib_save_settings'])) {
        if (isset($_POST['odib_openai_api_key'])) {
            update_option('odib_openai_api_key', sanitize_text_field($_POST['odib_openai_api_key']));
        }
    }

    $api_key = get_option('odib_openai_api_key', '');
    ?>
    <div class="wrap">
        <h1>AI Game Designer Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">OpenAI API Key</th>
                    <td>
                        <input type="text" name="odib_openai_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <p class="description">Enter your OpenAI API Key.</p>
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

// Karakter oluşturma shortcode
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

    <style>
    .odib-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    .odib-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .odib-logo {
        width: 40px;
        height: 40px;
    }
    .odib-tabs-wrapper {
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }
    .odib-tabs {
        display: flex;
        gap: 10px;
    }
    .odib-tab-card {
        padding: 10px 20px;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        width: 150px;
        margin: 0 10px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .odib-tab-card:hover {
        transform: translateY(-5px);
    }
    .odib-tab-card.active {
        background: #0073aa;
        color: white;
    }
    .odib-tab-card .tab-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }
    .odib-tab-card .tab-content i {
        font-size: 24px;
    }
    .odib-tab-card .neon-border {
        width: 100%;
        height: 2px;
        background: linear-gradient(to right, #0073aa, #005ea5);
        opacity: 0.2;
        border-radius: 10px;
    }
    .odib-tab-card.active .neon-border {
        opacity: 1;
    }
    .odib-form {
        margin-bottom: 20px;
    }
    .odib-input-group {
        margin-bottom: 15px;
    }
    .odib-input-group label {
        display: block;
        margin-bottom: 5px;
    }
    .odib-input-group input,
    .odib-input-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .prompt-container {
        position: relative;
        display: flex;
        gap: 10px;
    }
    .dice-button {
        padding: 8px 12px;
        font-size: 20px;
        background: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .dice-button:hover {
        transform: rotate(15deg);
    }
    .dice-button.spinning {
        animation: spin 2s linear infinite;
    }
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    .save-character {
        background: #46b450;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 15px;
        margin-top: 10px;
        cursor: pointer;
        width: 100%;
    }
    .save-character:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    .save-character.saved {
        background: #ccc;
        cursor: not-allowed;
    }
    .character-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .character-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .character-image {
        width: 100%;
        height: 150px;
        overflow: hidden;
    }
    .character-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .character-info {
        padding: 15px;
    }
    .character-info h4 {
        margin: 0 0 10px 0;
        color: #2271b1;
    }
    .character-info p {
        margin: 0;
        font-size: 14px;
        color: #666;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .select-character-btn {
        width: 100%;
        padding: 10px;
        border: none;
        background: #2271b1;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.3s ease;
    }
    .select-character-btn:hover {
        background: #135e96;
    }
    .selected-character-card {
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }
    .selected-character-card img {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }
    .selected-character-card span {
        font-size: 14px;
        color: #333;
    }
    .remove-character-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 4px;
        margin-left: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .remove-character-btn:hover {
        color: #bd2130;
    }
    .odib-result {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    .character-card {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
    }
    .character-card img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }
    .odib-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
    .concept-result {
        margin-top: 20px;
    }
    .concept-section {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .concept-section h3 {
        margin: 0 0 10px 0;
        color: #2271b1;
    }
    .concept-section .content {
        white-space: pre-wrap;
    }
    #generate-concept {
        background: #2271b1;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 10px 20px;
        cursor: pointer;
        width: 100%;
    }
    #generate-concept:disabled {
        background: #ccc;
    }

    .game-creator {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .game-creator__header {
        text-align: center;
        margin-bottom: 30px;
    }

    .game-creator__header h2 {
        color: #2271b1;
        font-size: 24px;
        margin: 0;
    }

    .game-creator__content {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .creation-step {
        background: #f9f9f9;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        padding: 20px;
    }

    .creation-step h3 {
        color: #2271b1;
        font-size: 18px;
        margin: 0 0 15px 0;
    }

    .character-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .selected-characters {
        background: #fff;
        border: 1px dashed #ccc;
        border-radius: 4px;
        padding: 15px;
        min-height: 60px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .no-characters-text {
        color: #666;
        font-style: italic;
        width: 100%;
        text-align: center;
    }

    .type-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
    }

    .type-btn {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .type-btn:hover {
        border-color: #2271b1;
        transform: translateY(-2px);
    }

    .type-btn.selected {
        background: #2271b1;
        color: white;
        border-color: #2271b1;
    }

    .type-btn i {
        font-size: 24px;
    }

    .prompt-input textarea {
        width: 100%;
        min-height: 120px;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
    }

    .game-creator__actions {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .create-game-btn {
        background: #2271b1;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 12px 24px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s ease;
    }

    .create-game-btn:hover {
        background: #135e96;
    }

    .create-game-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .create-game-btn i {
        font-size: 16px;
    }

    .coin-creator {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin: 20px;
        max-width: 800px;
    }

    .coin-creator__header {
        text-align: center;
        margin-bottom: 30px;
    }

    .coin-creator__header h2 {
        color: #2271b1;
        font-size: 24px;
        margin: 0;
    }

    .coin-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-weight: 500;
        color: #333;
    }

    .form-group input[type="text"],
    .form-group textarea,
    .form-group select {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .media-upload-container {
        border: 2px dashed #ddd;
        border-radius: 4px;
        padding: 20px;
        text-align: center;
    }

    .upload-btn {
        background: #f0f0f0;
        border: none;
        padding: 15px 25px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .upload-btn:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .media-preview {
        margin-top: 15px;
        max-width: 200px;
        margin: 15px auto 0;
    }

    .media-preview img,
    .media-preview video {
        max-width: 100%;
        border-radius: 4px;
    }

    .percentage-input {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .percentage-input input[type="range"] {
        flex: 1;
    }

    .percentage-input input[type="number"] {
        width: 80px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .help-text {
        color: #666;
        font-size: 12px;
        margin-top: 4px;
    }

    .create-coin-btn {
        background: #2271b1;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 12px 24px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .create-coin-btn:hover {
        background: #135e96;
    }

    .create-coin-btn i {
        font-size: 16px;
    }

    #percentageValue {
        color: #2271b1;
        font-weight: 500;
    }

    .character-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .character-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .character-image {
        width: 100%;
        height: 150px;
        overflow: hidden;
    }

    .character-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .character-info {
        padding: 15px;
    }

    .character-info h4 {
        margin: 0 0 10px 0;
        color: #2271b1;
    }

    .character-info p {
        margin: 0;
        font-size: 14px;
        color: #666;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .select-character-btn {
        width: 100%;
        padding: 10px;
        border: none;
        background: #2271b1;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.3s ease;
    }

    .select-character-btn:hover {
        background: #135e96;
    }

    .selected-character-card {
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .selected-character-card img {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }

    .selected-character-card span {
        font-size: 14px;
        color: #333;
    }

    .remove-character-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 4px;
        margin-left: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-character-btn:hover {
        color: #bd2130;
    }

    .asset-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .asset-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .asset-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .asset-image {
        width: 100%;
        height: 150px;
        overflow: hidden;
    }

    .asset-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .asset-info {
        padding: 15px;
    }

    .asset-info h4 {
        margin: 0 0 10px 0;
        color: #2271b1;
    }

    .asset-info p {
        margin: 0;
        font-size: 14px;
        color: #666;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .select-asset-btn {
        width: 100%;
        padding: 10px;
        border: none;
        background: #2271b1;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.3s ease;
    }

    .select-asset-btn:hover {
        background: #135e96;
    }

    .selected-assets {
        background: #fff;
        border: 1px dashed #ccc;
        border-radius: 4px;
        padding: 15px;
        min-height: 60px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .selected-asset-card {
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .selected-asset-card img {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }

    .selected-asset-card span {
        font-size: 14px;
        color: #333;
    }

    .remove-asset-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 4px;
        margin-left: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-asset-btn:hover {
        color: #bd2130;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        const ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        
        // Tab switching
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

        // Show concept tab by default
        $('.odib-tab-content').hide();
        $('#concept-tab').show();

        // Tab switching functionality
        $('.odib-tab-card').on('click', function() {
            const tabId = $(this).data('tab');
            
            // Remove active class from all tabs
            $('.odib-tab-card').removeClass('active');
            // Add active class to clicked tab
            $(this).addClass('active');
            
            // Hide all tab contents
            $('.odib-tab-content').hide();
            // Show selected tab content
            $(`#${tabId}-tab`).show();
        });
        
        // Generate prompt
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

        // Preview character
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
                // Generate 4 characters
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

                    if (response.success) {
                        const card = $('<div class="character-card">').html(`
                            <img src="${response.data.image_url}" alt="${response.data.name}">
                            <h3>${response.data.name}</h3>
                            <div class="character-actions">
                                <button class="save-character" data-name="${response.data.name}" data-image="${response.data.image_url}" data-prompt="${response.data.prompt}">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        `);
                        $result.append(card);
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

        // Save individual character
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

        // Delete character
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

        // Load gallery
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
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
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

        // Download character image
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

        // Generate game concept
        $('#generate-concept').on('click', function() {
            const $button = $(this);
            const gameIdea = $('#game-idea').val();
            
            if (!gameIdea) {
                alert('Please enter a game idea');
                return;
            }
            
            $button.prop('disabled', true).text('Generating Concept...');
            $('.concept-section').hide();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_generate_concept',
                    nonce: '<?php echo wqs_create_nonce("odib_nonce"); ?>',
                    game_idea: gameIdea
                },
                success: function(response) {
                    if (response.success) {
                        Object.keys(response.data).forEach(key => {
                            $(`.concept-section.${key}`)
                                .show()
                                .find('.content')
                                .html(response.data[key].replace(/\n/g, '<br>'));
                        });
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('An error occurred: ' + error);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Generate Concept');
                }
            });
        });

        // Coin Creation Functionality
        const percentageRange = $('#distributionPercentage');
        const percentageNumber = $('#percentageNumber');
        const percentageValue = $('#percentageValue');
        
        // Sync percentage inputs
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
        
        // Media Upload
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
        
        // Form Submission
        $('#coinForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'create_coin',
                name: $('#coinName').val(),
                ticker: $('#coinTicker').val(),
                description: $('#coinDescription').val(),
                mediaUrl: $('#mediaUrl').val(),
                gameId: $('#gameSelect').val(),
                distributionPercentage: $('#distributionPercentage').val()
            };
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#createCoinBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Coin created successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        $('#coinForm')[0].reset();
                        $('#mediaPreview').empty();
                        syncPercentageInputs(0);
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.data || 'An error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    $('#createCoinBtn').prop('disabled', false).html('<i class="fas fa-coins"></i> Create Coin');
                }
            });
        });
        
        // Load games for select
        function loadGames() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_games'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const select = $('#gameSelect');
                        select.empty().append('<option value="">Select a game</option>');
                        
                        response.data.forEach(game => {
                            select.append(`<option value="${game.id}">${game.name}</option>`);
                        });
                    }
                }
            });
        }
        
        loadGames();

        // Dice Button Animation
        $('.dice-button').on('click', function() {
            const $diceBtn = $(this);
            $diceBtn.addClass('spinning');
            
            // Simulated API call completion
            setTimeout(() => {
                $diceBtn.removeClass('spinning');
            }, 2000); // Adjust this time based on API call completion
        });

        // Sync percentage inputs
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
        
        // Load character list
        function loadCharacters() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_get_characters',
                    _ajax_nonce: '<?php echo wqs_create_nonce("odib_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const characterList = $('.character-list');
                        characterList.empty();
                        
                        response.data.forEach(character => {
                            const characterCard = $(`
                                <div class="character-card" data-id="${character.id}">
                                    <div class="character-image">
                                        <img src="${character.image_url}" alt="${character.character_name}">
                                    </div>
                                    <div class="character-info">
                                        <h4>${character.character_name}</h4>
                                        <p>${character.prompt}</p>
                                    </div>
                                    <button class="select-character-btn">
                                        <i class="fas fa-plus"></i> Select
                                    </button>
                                </div>
                            `);
                            
                            characterList.append(characterCard);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        // Character selection functionality
        $(document).on('click', '.select-character-btn', function(e) {
            e.preventDefault();
            const card = $(this).closest('.character-card');
            const characterId = card.data('id');
            const characterName = card.find('h4').text();
            const characterImage = card.find('img').attr('src');
            
            // Add to selected characters list
            const selectedCharacters = $('.selected-characters');
            const noCharactersText = $('.no-characters-text');
            
            // Remove "No characters selected yet" text
            noCharactersText.hide();
            
            // Check if character is already selected
            if (selectedCharacters.find(`[data-id="${characterId}"]`).length === 0) {
                const selectedCard = $(`
                    <div class="selected-character-card" data-id="${characterId}">
                        <img src="${characterImage}" alt="${characterName}">
                        <span>${characterName}</span>
                        <button class="remove-character-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);
                
                selectedCharacters.find('.selected-character-items').append(selectedCard);
                
                // Enable create game button
                $('#createGameBtn').prop('disabled', false);
            }
        });
        
        // Remove selected character functionality
        $(document).on('click', '.remove-character-btn', function() {
            $(this).closest('.selected-character-card').remove();
            
            // If no characters are selected
            if ($('.selected-character-card').length === 0) {
                $('.no-characters-text').show();
                $('#createGameBtn').prop('disabled', true);
            }
        });

        // Load characters when tab changes
        $('.odib-tab-card[data-tab="game"]').on('click', function() {
            loadCharacters();
        });
        
        // Load characters when page loads if active tab is game
        if ($('.odib-tab-card[data-tab="game"]').hasClass('active')) {
            loadCharacters();
        }
    });
    </script>

    <script>
    jQuery(document).ready(function($) {
        // Coin image upload handling
        const coinImageFile = $('#coinImageFile');
        const coinImagePreview = $('#coinImagePreview');
        const uploadContent = $('.upload-content');
        const generateBtn = $('#generateCoinImage');
        
        // File input change handler
        coinImageFile.on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) { // 2MB limit
                    alert('File size must be less than 2MB');
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
        
        // Drag and drop handling
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
        
        // AI Generate button click handler
        generateBtn.on('click', function() {
            // AI image generation function can be called here
            $(this).addClass('loading');
            // Simulated API call completion
            setTimeout(() => {
                $(this).removeClass('loading');
                // AI image generation function can be called here
            }, 2000); // Adjust this time based on API call completion
        });
    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('ai_game_designer', 'odib_character_creator_shortcode');

// Prompt generation AJAX handler
function odib_generate_prompt() {
    error_log('odib_generate_prompt called');
    
    check_ajax_referer('odib_nonce', 'nonce');
    
    error_log('Prompt generation request received');
    
    $api_key = get_option('odib_openai_api_key', '');
    if (empty($api_key)) {
        error_log('API key not found');
        wqs_send_json_error('OpenAI API key not set.');
        return;
    }

    error_log('OpenAI API request sent...');

    $response = wqs_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-4',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a DALL-E prompt generator. Generate a prompt for a 2D pixel art game character. The prompt should be detailed and creative, but not include character type or description. Only include visual style and details. Example format: "Detailed 2D character sprite in pixel art style, vibrant colors, front view, transparent background, 32x32 pixels, inspired by classic SNES RPGs"'
                ),
                array(
                    'role' => 'user',
                    'content' => 'Generate a DALL-E prompt for a 2D pixel art game character.'
                )
            ),
            'temperature' => 0.9,
            'max_tokens' => 150
        )),
        'timeout' => 60
    ));

    if (is_wqs_error($response)) {
        error_log('API Error: ' . $response->get_error_message());
        wqs_send_json_error('Failed to communicate with OpenAI API. Please check your internet connection and try again. Error: ' . $response->get_error_message());
        return;
    }

    $body = json_decode(wqs_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        error_log('API Error message: ' . $body['error']['message']);
        wqs_send_json_error($body['error']['message']);
        return;
    }

    if (!isset($body['choices'][0]['message']['content'])) {
        error_log('Invalid API response format');
        wqs_send_json_error('Invalid API response format.');
        return;
    }

    $prompt = $body['choices'][0]['message']['content'];
    error_log('Generated prompt: ' . $prompt);
    wqs_send_json_success($prompt);
}

// Preview generation AJAX handler
function odib_create_preview() {
    error_log('odib_create_preview called');
    
    check_ajax_referer('odib_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $prompt = sanitize_text_field($_POST['prompt']);
    
    if (empty($name) || empty($prompt)) {
        wqs_send_json_error('Character name and prompt are required.');
        return;
    }
    
    $api_key = get_option('odib_openai_api_key', '');
    if (empty($api_key)) {
        error_log('API key not found');
        wqs_send_json_error('OpenAI API key not set.');
        return;
    }

    error_log('OpenAI API request sent...');

    $response = wqs_remote_post('https://api.openai.com/v1/images/generations', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'prompt' => $prompt,
            'n' => 1,
            'size' => '256x256',
            'response_format' => 'url',
            'model' => 'dall-e-2',
            'quality' => 'standard'
        )),
        'timeout' => 60
    ));

    if (is_wqs_error($response)) {
        error_log('API Error: ' . $response->get_error_message());
        wqs_send_json_error('Failed to communicate with OpenAI API. Please check your internet connection and try again. Error: ' . $response->get_error_message());
        return;
    }

    $body = json_decode(wqs_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        error_log('API Error message: ' . $body['error']['message']);
        wqs_send_json_error($body['error']['message']);
        return;
    }

    if (!isset($body['data'][0]['url'])) {
        error_log('Invalid API response format');
        wqs_send_json_error('Invalid API response format.');
        return;
    }

    wqs_send_json_success(array(
        'name' => $name,
        'image_url' => $body['data'][0]['url'],
        'prompt' => $prompt
    ));
}

// Character save AJAX handler
function odib_save_character() {
    error_log('odib_save_character called');
    
    check_ajax_referer('odib_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $image_url = esc_url_raw($_POST['image_url']);
    $prompt = sanitize_text_field($_POST['prompt']);
    
    if (empty($name) || empty($image_url) || empty($prompt)) {
        wqs_send_json_error('All fields are required.');
        return;
    }

    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_characters';
    
    $result = $wqsdb->insert(
        $table_name,
        array(
            'character_name' => $name,
            'image_url' => $image_url,
            'prompt' => $prompt
        ),
        array('%s', '%s', '%s')
    );

    if ($wqsdb->last_error) {
        error_log('Database error: ' . $wqsdb->last_error);
        wqs_send_json_error('Database error: ' . $wqsdb->last_error);
        return;
    }

    wqs_send_json_success();
}

// Character deletion AJAX handler
function odib_delete_character() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    $character_id = intval($_POST['character_id']);
    
    if (!$character_id) {
        wqs_send_json_error('Invalid character ID.');
        return;
    }

    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_characters';
    
    $result = $wqsdb->delete(
        $table_name,
        array('id' => $character_id),
        array('%d')
    );

    if ($wqsdb->last_error) {
        error_log('Database error: ' . $wqsdb->last_error);
        wqs_send_json_error('Database error: ' . $wqsdb->last_error);
        return;
    }

    wqs_send_json_success();
}

// Character retrieval AJAX handler
function odib_get_characters() {
    error_log('odib_get_characters called');
    
    if (!check_ajax_referer('odib-nonce', 'nonce', false)) {
        error_log('Nonce check failed in odib_get_characters');
        wqs_send_json_error('Security check failed.');
        wqs_die();
    }
    
    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_characters';
    
    error_log('Querying table: ' . $table_name);
    
    $characters = $wqsdb->get_results(
        "SELECT id, character_name, image_url, prompt FROM $table_name ORDER BY character_name ASC",
        ARRAY_A
    );

    if ($wqsdb->last_error) {
        error_log('Database error: ' . $wqsdb->last_error);
        wqs_send_json_error('Database error: ' . $wqsdb->last_error);
        return;
    }

    error_log('Found ' . count($characters) . ' characters');
    wqs_send_json_success($characters);
}

// Game concept generation AJAX handler
function odib_generate_concept() {
    error_log('odib_generate_concept called');
    
    check_ajax_referer('odib_nonce', 'nonce');
    
    $game_idea = sanitize_text_field($_POST['game_idea']);
    
    if (empty($game_idea)) {
        wqs_send_json_error('Game idea is required.');
        return;
    }
    
    $api_key = get_option('odib_openai_api_key', '');
    if (empty($api_key)) {
        error_log('API key not found');
        wqs_send_json_error('OpenAI API key not set.');
        return;
    }

    $system_prompt = "You are a game designer. Analyze the given game idea and create a detailed game concept design.
    Organize your response under the following headings:
    1. Game Mechanics: Core gameplay, controls, and main mechanics
    2. Level Design: Structure of levels, obstacles, difficulty level, and variety
    3. Progression System: Player progression, rewards, and motivation elements
    4. Visual Style: Game's visual style, atmosphere, and art direction

    Write at least 3-4 sentences for each heading. Be creative but realistic and implementable.";

    $response = wqs_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-4',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_prompt
                ),
                array(
                    'role' => 'user',
                    'content' => $game_idea
                )
            ),
            'temperature' => 0.7,
            'max_tokens' => 1000
        )),
        'timeout' => 60
    ));

    if (is_wqs_error($response)) {
        error_log('API Error: ' . $response->get_error_message());
        wqs_send_json_error('Failed to communicate with OpenAI API. Please check your internet connection and try again. Error: ' . $response->get_error_message());
        return;
    }

    $body = json_decode(wqs_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        error_log('API Error message: ' . $body['error']['message']);
        wqs_send_json_error($body['error']['message']);
        return;
    }

    if (!isset($body['choices'][0]['message']['content'])) {
        error_log('Invalid API response format');
        wqs_send_json_error('Invalid API response format.');
        return;
    }

    $content = $body['choices'][0]['message']['content'];
    
    // Split response into sections
    preg_match_all('/(\d\. [^:]+):\s*([^1-4]+)(?=[1-4]\.|$)/s', $content, $matches);
    
    $sections = array();
    if (!empty($matches[1])) {
        $section_map = array(
            '1. Game Mechanics' => 'mechanics',
            '2. Level Design' => 'level-design',
            '3. Progression System' => 'progression',
            '4. Visual Style' => 'visuals'
        );
        
        for ($i = 0; $i < count($matches[1]); $i++) {
            $section_title = trim($matches[1][$i]);
            if (isset($section_map[$section_title])) {
                $sections[$section_map[$section_title]] = trim($matches[2][$i]);
            }
        }
    }

    wqs_send_json_success($sections);
}

// Coin creation AJAX handler
function odib_create_coin() {
    error_log('odib_create_coin called');
    
    check_ajax_referer('odib_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $ticker = sanitize_text_field($_POST['ticker']);
    $description = sanitize_text_field($_POST['description']);
    $media_url = esc_url_raw($_POST['mediaUrl']);
    $game_id = intval($_POST['gameId']);
    $distribution_percentage = floatval($_POST['distributionPercentage']);
    $twitter_handle = sanitize_text_field($_POST['twitterHandle']);
    $telegram_group = sanitize_text_field($_POST['telegramGroup']);
    $website = esc_url_raw($_POST['website']);
    $discord_server = sanitize_text_field($_POST['discordServer']);
    
    if (empty($name) || empty($ticker) || empty($description) || empty($media_url) || !$game_id || $distribution_percentage < 0 || $distribution_percentage > 20) {
        wqs_send_json_error('All fields are required.');
        return;
    }

    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_coins';
    
    $result = $wqsdb->insert(
        $table_name,
        array(
            'name' => $name,
            'ticker' => $ticker,
            'description' => $description,
            'media_url' => $media_url,
            'game_id' => $game_id,
            'distribution_percentage' => $distribution_percentage,
            'twitter_handle' => $twitter_handle,
            'telegram_group' => $telegram_group,
            'website' => $website,
            'discord_server' => $discord_server
        ),
        array('%s', '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%s', '%s')
    );

    if ($wqsdb->last_error) {
        error_log('Database error: ' . $wqsdb->last_error);
        wqs_send_json_error('Database error: ' . $wqsdb->last_error);
        return;
    }

    wqs_send_json_success();
}

// Game retrieval AJAX handler
function odib_get_games() {
    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_games';
    
    $games = $wqsdb->get_results(
        "SELECT * FROM $table_name ORDER BY name ASC",
        ARRAY_A
    );

    if ($wqsdb->last_error) {
        error_log('Database error: ' . $wqsdb->last_error);
        wqs_send_json_error('Database error: ' . $wqsdb->last_error);
        return;
    }

    wqs_send_json_success($games);
}

// Asset generation AJAX handler
function odib_generate_asset() {
    check_ajax_referer('odib_nonce', '_ajax_nonce');
    
    $asset_type = sanitize_text_field($_POST['asset_type']);
    $description = sanitize_text_field($_POST['description']);
    
    // Generate detailed prompt for 2D game asset
    $prompt = "Create a detailed pixel art game item in classic RPG style. ";
    $prompt .= "The item is a {$asset_type} with these features: {$description}. ";
    $prompt .= "Requirements: pixel art style, transparent background, clear pixel edges, ";
    $prompt .= "vibrant colors, proper shading, front view, game-ready asset, ";
    $prompt .= "suitable for a 2D fantasy RPG game interface. Make it detailed but readable at small sizes.";
    
    $api_key = get_option('odib_openai_api_key', '');
    
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
            'model' => 'dall-e-3',
            'quality' => 'standard'
        )),
        'timeout' => 60,
        'redirection' => 5,
        'blocking' => true,
        'sslverify' => false
    );
    
    $response = wqs_remote_post('https://api.openai.com/v1/images/generations', $args);
    
    if (is_wqs_error($response)) {
        wqs_send_json_error('API request failed: ' . $response->get_error_message());
        return;
    }
    
    $body = json_decode(wqs_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        wqs_send_json_error('API Error: ' . $body['error']['message']);
        return;
    }
    
    if (isset($body['data'][0]['url'])) {
        wqs_send_json_success(array(
            'image_url' => $body['data'][0]['url'],
            'asset_type' => $asset_type,
            'description' => $description,
            'prompt' => $prompt
        ));
    } else {
        wqs_send_json_error('Image generation failed');
    }
    
    wqs_die();
}
add_action('wqs_ajax_odib_generate_asset', 'odib_generate_asset');
add_action('wqs_ajax_nopriv_odib_generate_asset', 'odib_generate_asset');

// Asset save AJAX handler
function odib_save_asset() {
    if (!check_ajax_referer('odib-nonce', 'nonce', false)) {
        wqs_send_json_error('Security check failed.');
        wqs_die();
    }
    
    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_assets';
    
    $asset_type = sanitize_text_field($_POST['asset_type']);
    $description = sanitize_text_field($_POST['description']);
    $image_url = esc_url_raw($_POST['image_url']);
    $prompt = sanitize_text_field($_POST['prompt']);
    
    $result = $wqsdb->insert(
        $table_name,
        array(
            'asset_type' => $asset_type,
            'description' => $description,
            'image_url' => $image_url,
            'prompt' => $prompt
        ),
        array('%s', '%s', '%s', '%s')
    );
    
    if ($result === false) {
        wqs_send_json_error('Asset save failed');
    } else {
        wqs_send_json_success('Asset saved successfully');
    }
    
    wqs_die();
}
add_action('wqs_ajax_odib_save_asset', 'odib_save_asset');

// Asset retrieval AJAX handler
function odib_get_assets() {
    error_log('odib_get_assets called');
    
    if (!check_ajax_referer('odib-nonce', 'nonce', false)) {
        error_log('Nonce check failed in odib_get_assets');
        wqs_send_json_error('Security check failed.');
        wqs_die();
    }
    
    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_assets';
    
    error_log('Querying table: ' . $table_name);
    
    $assets = $wqsdb->get_results(
        "SELECT * FROM $table_name ORDER BY created_at DESC",
        ARRAY_A
    );

    if ($wqsdb->last_error) {
        error_log('Database error: ' . $wqsdb->last_error);
        wqs_send_json_error('Database error: ' . $wqsdb->last_error);
        return;
    }

    error_log('Found ' . count($assets) . ' assets');
    wqs_send_json_success($assets);
    wqs_die();
}
add_action('wqs_ajax_odib_get_assets', 'odib_get_assets');

// Asset deletion AJAX handler
function odib_delete_asset() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    global $wqsdb;
    $table_name = $wqsdb->prefix . 'odib_assets';
    
    $asset_id = intval($_POST['asset_id']);
    
    $result = $wqsdb->delete(
        $table_name,
        array('id' => $asset_id),
        array('%d')
    );
    
    if ($result === false) {
        wqs_send_json_error('Asset deletion failed');
    } else {
        wqs_send_json_success('Asset deleted successfully');
    }
    
    wqs_die();
}
add_action('wqs_ajax_odib_delete_asset', 'odib_delete_asset');

// Load necessary scripts and styles
function odib_enqueue_scripts() {
    // Font Awesome
    wqs_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    
    // SweetAlert2
    wqs_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
    
    // CSS file
    wqs_enqueue_style('odib-style', plugins_url('css/style.css', __FILE__), array(), time());
    
    // jQuery UI
    wqs_enqueue_script('jquery-ui-core');
    wqs_enqueue_script('jquery-ui-sortable');
    
    // JavaScript file
    wqs_enqueue_script('odib-main', plugins_url('js/main.js', __FILE__), array('jquery', 'sweetalert2'), time(), true);
    
    // Pass AJAX URL to JavaScript
    wqs_localize_script('odib-main', 'odibAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wqs_create_nonce('odib-nonce')
    ));
}
add_action('wqs_enqueue_scripts', 'odib_enqueue_scripts');

// AJAX handlers
add_action('wqs_ajax_odib_generate_prompt', 'odib_generate_prompt');
add_action('wqs_ajax_nopriv_odib_generate_prompt', 'odib_generate_prompt');

add_action('wqs_ajax_odib_create_preview', 'odib_create_preview');
add_action('wqs_ajax_nopriv_odib_create_preview', 'odib_create_preview');

add_action('wqs_ajax_odib_save_character', 'odib_save_character');
add_action('wqs_ajax_nopriv_odib_save_character', 'odib_save_character');

add_action('wqs_ajax_odib_delete_character', 'odib_delete_character');
add_action('wqs_ajax_nopriv_odib_delete_character', 'odib_delete_character');

add_action('wqs_ajax_odib_get_characters', 'odib_get_characters');
add_action('wqs_ajax_nopriv_odib_get_characters', 'odib_get_characters');

add_action('wqs_ajax_odib_generate_concept', 'odib_generate_concept');
add_action('wqs_ajax_nopriv_odib_generate_concept', 'odib_generate_concept');

add_action('wqs_ajax_odib_create_coin', 'odib_create_coin');
add_action('wqs_ajax_nopriv_odib_create_coin', 'odib_create_coin');

add_action('wqs_ajax_odib_get_games', 'odib_get_games');
add_action('wqs_ajax_nopriv_odib_get_games', 'odib_get_games');

add_action('wqs_ajax_odib_generate_asset', 'odib_generate_asset');
add_action('wqs_ajax_nopriv_odib_generate_asset', 'odib_generate_asset');

add_action('wqs_ajax_odib_save_asset', 'odib_save_asset');
add_action('wqs_ajax_nopriv_odib_save_asset', 'odib_save_asset');

add_action('wqs_ajax_odib_get_assets', 'odib_get_assets');
add_action('wqs_ajax_nopriv_odib_get_assets', 'odib_get_assets');

add_action('wqs_ajax_odib_delete_asset', 'odib_delete_asset');
add_action('wqs_ajax_nopriv_odib_delete_asset', 'odib_delete_asset');
