<?php

if (!defined('ABSPATH')) {
    exit;
}

// Eklenti aktifleştirildiğinde çalışacak fonksiyon
function odib_activate() {
    global $wqdb;
    
    $table_name = $wqdb->prefix . 'odib_characters';
    $charset_collate = $wqdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        character_name varchar(100) NOT NULL,
        image_url text NOT NULL,
        prompt text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wqad/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'odib_activate');

// Gerekli sabitleri tanımlayalım
define('odib_VERSION', '1.0.0');
define('odib_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('odib_PLUGIN_URL', plugin_dir_url(__FILE__));

// Veritabanı tablosunu oluştur
function odib_create_tables() {
    global $wqdb;
    $charset_collate = $wqdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wqdb->prefix}odib_characters (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        character_name varchar(100) NOT NULL,
        image_url text NOT NULL,
        prompt text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wqad/includes/upgrade.php');
    dbDelta($sql);
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

// Ayarlar sayfası içeriği
function odib_settings_page() {
    if (isset($_POST['odib_save_settings'])) {
        if (isset($_POST['odib_openai_api_key'])) {
            update_option('odib_openai_api_key', sanitize_text_field($_POST['odib_openai_api_key']));
        }
    }

    $api_key = get_option('odib_openai_api_key', '');
    ?>
    <div class="wrap">
        <h1>AI Game Designer Ayarları</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">OpenAI API Anahtarı</th>
                    <td>
                        <input type="text" name="odib_openai_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <p class="description">OpenAI API anahtarınızı buraya girin.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="odib_save_settings" class="button-primary" value="Ayarları Kaydet">
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
            <img src="<?php echo plugins_url('assets/dragon-logo.svg', __FILE__); ?>" alt="AI Game Designer Logo" class="odib-logo">
            <h1>AI Game Designer</h1>
        </div>

        <div class="odib-tabs-wrapper">
            <div class="odib-tabs">
                <button class="odib-tab-card" data-tab="concept">
                    <div class="tab-content">
                        <i class="fas fa-lightbulb"></i>
                        <span>Oyun Konsepti</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card active" data-tab="creator">
                    <div class="tab-content">
                        <i class="fas fa-user-astronaut"></i>
                        <span>Karakter Oluştur</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card" data-tab="gallery">
                    <div class="tab-content">
                        <i class="fas fa-images"></i>
                        <span>Galeri</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card" data-tab="game">
                    <div class="tab-content">
                        <i class="fas fa-gamepad"></i>
                        <span>Oyun Oluştur</span>
                    </div>
                    <div class="neon-border"></div>
                </button>

                <button class="odib-tab-card" data-tab="coin">
                    <div class="tab-content">
                        <i class="fas fa-coins"></i>
                        <span>Coin Oluştur</span>
                    </div>
                    <div class="neon-border"></div>
                </button>
            </div>
        </div>

        <div id="concept-tab" class="odib-tab-content" style="display: none;">
            <div class="odib-form">
                <div class="odib-input-group">
                    <label for="game-idea">Oyun Fikriniz:</label>
                    <textarea id="game-idea" rows="3" placeholder="Örnek: Kullanıcı engelleri aşan bir tavşan oyunu yapalım"></textarea>
                </div>
                <button id="generate-concept">Konsept Oluştur</button>
            </div>
            <div id="concept-result" class="concept-result">
                <div class="concept-section mechanics" style="display: none;">
                    <h3>🎮 Oyun Mekanikleri</h3>
                    <div class="content"></div>
                </div>
                <div class="concept-section level-design" style="display: none;">
                    <h3>🏗️ Bölüm Tasarımı</h3>
                    <div class="content"></div>
                </div>
                <div class="concept-section progression" style="display: none;">
                    <h3>📈 İlerleme Sistemi</h3>
                    <div class="content"></div>
                </div>
                <div class="concept-section visuals" style="display: none;">
                    <h3>🎨 Görsel Stil</h3>
                    <div class="content"></div>
                </div>
            </div>
        </div>

        <div id="creator-tab" class="odib-tab-content">
            <div class="odib-form">
                <div class="odib-input-group">
                    <label for="character-name">Karakter Adı:</label>
                    <input type="text" id="character-name" required>
                </div>
                
                <div class="odib-input-group">
                    <label for="character-prompt">Karakter Prompt:</label>
                    <div class="prompt-container">
                        <textarea id="character-prompt" rows="4" required></textarea>
                        <button id="generate-prompt" class="dice-button" title="Random Prompt Oluştur">🎲</button>
                    </div>
                    <small>Kendi promptunuzu yazabilir veya zar butonuna tıklayarak rastgele bir prompt oluşturabilirsiniz.</small>
                </div>

                <button id="preview-character">Karakterleri Oluştur</button>
            </div>

            <div id="result" class="odib-result"></div>
        </div>

        <div id="gallery-tab" class="odib-tab-content" style="display: none;">
            <div id="gallery" class="odib-gallery"></div>
        </div>

        <div id="game-tab" class="odib-tab-content" style="display: none;">
            <div class="game-creator">
                <div class="game-creator__header">
                    <h2>Oyun Oluştur</h2>
                </div>

                <div class="game-creator__content">
                    <div class="game-creator__main">
                        <div class="creation-step">
                            <h3>1. Karakterleri Seç</h3>
                            <div class="character-list"></div>
                            <div class="selected-characters">
                                <span class="no-characters-text">Henüz karakter seçilmedi</span>
                                <div class="selected-character-items"></div>
                            </div>
                        </div>

                        <div class="creation-step">
                            <h3>2. Oyun Türü</h3>
                            <div class="game-type-select">
                                <div class="type-buttons">
                                    <button class="type-btn" data-type="action">
                                        <i class="fas fa-gamepad"></i>
                                        <span>Aksiyon</span>
                                    </button>
                                    <button class="type-btn" data-type="puzzle">
                                        <i class="fas fa-puzzle-piece"></i>
                                        <span>Bulmaca</span>
                                    </button>
                                    <button class="type-btn" data-type="adventure">
                                        <i class="fas fa-map"></i>
                                        <span>Macera</span>
                                    </button>
                                    <button class="type-btn" data-type="strategy">
                                        <i class="fas fa-chess"></i>
                                        <span>Strateji</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="creation-step">
                            <h3>3. Oyun Detayları</h3>
                            <div class="prompt-input">
                                <textarea id="game-prompt" placeholder="Oyununuzu nasıl hayal ediyorsunuz? Detayları buraya yazın..."></textarea>
                            </div>
                        </div>

                        <div class="game-creator__actions">
                            <button id="createGameBtn" class="create-game-btn" disabled>
                                <i class="fas fa-plus"></i> Oyun Oluştur
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="coin-tab" class="odib-tab-content" style="display: none;">
            <div class="coin-creator">
                <div class="coin-creator__header">
                    <h2>Coin Oluştur</h2>
                </div>
                <div class="coin-creator__content">
                    <form id="coinForm" class="coin-form">
                        <div class="form-group">
                            <label for="coinName">Coin Adı:</label>
                            <input type="text" id="coinName" name="coinName" required placeholder="Coin adını girin">
                        </div>
                        
                        <div class="form-group">
                            <label for="coinTicker">Ticker Sembolü:</label>
                            <input type="text" id="coinTicker" name="coinTicker" required placeholder="Örn: BTC, ETH">
                        </div>
                        
                        <div class="form-group">
                            <label for="coinDescription">Açıklama:</label>
                            <textarea id="coinDescription" name="coinDescription" required placeholder="Coin hakkında detaylı açıklama"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="coinMedia">Coin Görseli:</label>
                            <div class="media-upload-container">
                                <button type="button" id="uploadMediaBtn" class="upload-btn">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    Görsel veya Video Yükle
                                </button>
                                <div id="mediaPreview" class="media-preview"></div>
                                <input type="hidden" id="mediaUrl" name="mediaUrl">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="gameSelect">Oyun Seç:</label>
                            <select id="gameSelect" name="gameSelect" required>
                                <option value="">Oyun seçin</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="distributionPercentage">Dağıtım Yüzdesi: <span id="percentageValue">0%</span></label>
                            <div class="percentage-input">
                                <input type="range" id="distributionPercentage" name="distributionPercentage" 
                                       min="0" max="20" step="0.1" value="0">
                                <input type="number" id="percentageNumber" 
                                       min="0" max="20" step="0.1" value="0">
                            </div>
                            <small class="help-text">Maximum %20'ye kadar dağıtım yapılabilir</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" id="createCoinBtn" class="create-coin-btn">
                                <i class="fas fa-coins"></i> Coin Oluştur
                            </button>
                        </div>
                    </form>
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

        // Generate prompt
        $('#generate-prompt').on('click', function() {
            const $button = $(this);
            const $promptDisplay = $('#character-prompt');
            
            console.log('Prompt oluşturma başlatılıyor...');
            $button.prop('disabled', true);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_generate_prompt',
                    nonce: '<?php echo wq_create_nonce("odib_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $promptDisplay.val(response.data);
                    } else {
                        alert('Hata: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX hatası:', error);
                    alert('Bir hata oluştu: ' + error);
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
                alert('Lütfen karakter adı ve prompt giriniz.');
                return;
            }
            
            $button.prop('disabled', true).text('Karakterler Oluşturuluyor...');
            $result.empty();

            try {
                // 4 karakter oluştur
                for (let i = 0; i < 4; i++) {
                    const response = await $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'odib_create_preview',
                            nonce: '<?php echo wq_create_nonce("odib_nonce"); ?>',
                            name: `${name}_${i + 1}`,
                            prompt: prompt
                        }
                    });

                    if (response.success) {
                        const card = $('<div class="character-card">').html(`
                            <img src="${response.data.image_url}" alt="${response.data.name}">
                            <h3>${response.data.name}</h3>
                            <div class="character-actions">
                                <button class="save-character" data-name="${response.data.name}" data-image="${response.data.image_url}" data-prompt="${response.data.prompt}">Kaydet</button>
                                <button class="download-character" onclick="downloadImage('${response.data.image_url}', '${response.data.name}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                    </svg>
                                    İndir
                                </button>
                            </div>
                        `);
                        $result.append(card);
                    } else {
                        alert('Hata: ' + response.data);
                    }
                }
            } catch (error) {
                console.error('AJAX hatası:', error);
                alert('Bir hata oluştu: ' + error);
            } finally {
                $button.prop('disabled', false).text('Karakterleri Oluştur');
            }
        });

        // Save individual character
        $(document).on('click', '.save-character', function() {
            const $button = $(this);
            if ($button.hasClass('saved')) return;

            const name = $button.data('name');
            const image_url = $button.data('image');
            const prompt = $button.data('prompt');

            $button.prop('disabled', true).text('Kaydediliyor...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_save_character',
                    nonce: '<?php echo wq_create_nonce("odib_nonce"); ?>',
                    name: name,
                    image_url: image_url,
                    prompt: prompt
                },
                success: function(response) {
                    if (response.success) {
                        $button.addClass('saved').text('Kaydedildi').prop('disabled', true);
                    } else {
                        alert('Hata: ' + response.data);
                        $button.prop('disabled', false).text('Kaydet');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX hatası:', error);
                    alert('Bir hata oluştu: ' + error);
                    $button.prop('disabled', false).text('Kaydet');
                }
            });
        });

        // Delete character
        $(document).on('click', '.delete-character', function() {
            const $card = $(this).closest('.character-card');
            const characterId = $(this).data('id');

            if (confirm('Bu karakteri silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'odib_delete_character',
                        nonce: '<?php echo wq_create_nonce("odib_nonce"); ?>',
                        character_id: characterId
                    },
                    success: function(response) {
                        if (response.success) {
                            $card.fadeOut(400, function() {
                                $(this).remove();
                            });
                        } else {
                            alert('Hata: ' + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX hatası:', error);
                        alert('Bir hata oluştu: ' + error);
                    }
                });
            }
        });

        // Load gallery
        function loadGallery() {
            const $gallery = $('#gallery');
            $gallery.html('Yükleniyor...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_get_characters',
                    nonce: '<?php echo wq_create_nonce("odib_nonce"); ?>'
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
                                        İndir
                                    </button>
                                </div>
                            `);
                            $gallery.append(card);
                        });
                    } else {
                        $gallery.html('Hata: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX hatası:', error);
                    $gallery.html('Bir hata oluştu: ' + error);
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
                console.error('İndirme hatası:', error);
                alert('Görsel indirilirken bir hata oluştu: ' + error);
            }
        }

        // Generate game concept
        $('#generate-concept').on('click', function() {
            const $button = $(this);
            const gameIdea = $('#game-idea').val();
            
            if (!gameIdea) {
                alert('Lütfen bir oyun fikri girin.');
                return;
            }
            
            $button.prop('disabled', true).text('Konsept Oluşturuluyor...');
            $('.concept-section').hide().find('.content').empty();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_generate_concept',
                    nonce: '<?php echo wq_create_nonce("odib_nonce"); ?>',
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
                        alert('Hata: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX hatası:', error);
                    alert('Bir hata oluştu: ' + error);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Konsept Oluştur');
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
            
            const mediaUploader = wq.media({
                title: 'Coin Görseli Seç',
                button: {
                    text: 'Seç'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#mediaUrl').val(attachment.url);
                
                const preview = $('#mediaPreview');
                preview.empty();
                
                if (attachment.type === 'image') {
                    preview.html(`<img src="${attachment.url}" alt="Coin görseli">`);
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
                    $('#createCoinBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Oluşturuluyor...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Başarılı!',
                            text: 'Coin başarıyla oluşturuldu.',
                            icon: 'success',
                            confirmButtonText: 'Tamam'
                        });
                        $('#coinForm')[0].reset();
                        $('#mediaPreview').empty();
                        syncPercentageInputs(0);
                    } else {
                        Swal.fire({
                            title: 'Hata!',
                            text: response.data || 'Bir hata oluştu.',
                            icon: 'error',
                            confirmButtonText: 'Tamam'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Bir hata oluştu. Lütfen tekrar deneyin.',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                },
                complete: function() {
                    $('#createCoinBtn').prop('disabled', false).html('<i class="fas fa-coins"></i> Coin Oluştur');
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
                        select.empty().append('<option value="">Oyun seçin</option>');
                        
                        response.data.forEach(game => {
                            select.append(`<option value="${game.id}">${game.name}</option>`);
                        });
                    }
                }
            });
        }
        
        loadGames();

        // Karakter listesini yükle
        function loadCharacters() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'odib_get_characters',
                    _ajax_nonce: '<?php echo wq_create_nonce("odib_nonce"); ?>'
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
                                        <i class="fas fa-plus"></i> Seç
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

        // Karakter seçme işlevi
        $(document).on('click', '.select-character-btn', function(e) {
            e.preventDefault();
            const card = $(this).closest('.character-card');
            const characterId = card.data('id');
            const characterName = card.find('h4').text();
            const characterImage = card.find('img').attr('src');
            
            // Seçili karakterler listesine ekle
            const selectedCharacters = $('.selected-characters');
            const noCharactersText = $('.no-characters-text');
            
            // "Henüz karakter seçilmedi" yazısını kaldır
            noCharactersText.hide();
            
            // Karakter zaten seçili mi kontrol et
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
                
                // Oyun oluştur butonunu aktif et
                $('#createGameBtn').prop('disabled', false);
            }
        });
        
        // Seçili karakteri kaldırma işlevi
        $(document).on('click', '.remove-character-btn', function() {
            $(this).closest('.selected-character-card').remove();
            
            // Eğer hiç seçili karakter kalmadıysa
            if ($('.selected-character-card').length === 0) {
                $('.no-characters-text').show();
                $('#createGameBtn').prop('disabled', true);
            }
        });

        // Tab değiştiğinde karakterleri yükle
        $('.odib-tab-card[data-tab="game"]').on('click', function() {
            loadCharacters();
        });
        
        // Sayfa yüklendiğinde aktif tab game ise karakterleri yükle
        if ($('.odib-tab-card[data-tab="game"]').hasClass('active')) {
            loadCharacters();
        }
    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('ai_game_designer', 'odib_character_creator_shortcode');

// Prompt oluşturma AJAX handler
function odib_generate_prompt() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    error_log('Prompt oluşturma isteği alındı');
    
    $api_key = get_option('odib_openai_api_key', '');
    if (empty($api_key)) {
        error_log('API anahtarı bulunamadı');
        wq_send_json_error('OpenAI API anahtarı ayarlanmamış.');
        return;
    }

    error_log('OpenAI API isteği gönderiliyor...');

    $response = wq_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-4',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'Sen bir DALL-E prompt üreticisin. 2D pixel art tarzında oyun karakterleri için prompt üretmelisin. Promptlar detaylı ve yaratıcı olmalı, ancak karakter türü veya açıklaması içermemeli. Sadece görsel stil ve detayları içermeli. Örnek format: "Detailed 2D character sprite in pixel art style, vibrant colors, front view, transparent background, 32x32 pixels, inspired by classic SNES RPGs"'
                ),
                array(
                    'role' => 'user',
                    'content' => 'Pixel art tarzında bir oyun karakteri için DALL-E prompt üret.'
                )
            ),
            'temperature' => 0.9,
            'max_tokens' => 150
        )),
        'timeout' => 60
    ));

    if (is_wq_error($response)) {
        error_log('API hatası: ' . $response->get_error_message());
        wq_send_json_error('OpenAI API ile iletişim kurulamadı. Lütfen internet bağlantınızı kontrol edin ve tekrar deneyin. Hata: ' . $response->get_error_message());
        return;
    }

    $body = json_decode(wq_remote_retrieve_body($response), true);
    error_log('API yanıtı: ' . print_r($body, true));
    
    if (isset($body['error'])) {
        error_log('API hata mesajı: ' . $body['error']['message']);
        wq_send_json_error($body['error']['message']);
        return;
    }

    if (!isset($body['choices'][0]['message']['content'])) {
        error_log('Beklenmeyen API yanıt formatı');
        wq_send_json_error('API yanıtı beklenmeyen formatta.');
        return;
    }

    $prompt = $body['choices'][0]['message']['content'];
    error_log('Oluşturulan prompt: ' . $prompt);
    wq_send_json_success($prompt);
}

// Önizleme oluşturma AJAX handler
function odib_create_preview() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $prompt = sanitize_text_field($_POST['prompt']);
    
    if (empty($name) || empty($prompt)) {
        wq_send_json_error('Karakter adı ve prompt gereklidir.');
        return;
    }
    
    $api_key = get_option('odib_openai_api_key', '');
    if (empty($api_key)) {
        error_log('API anahtarı bulunamadı');
        wq_send_json_error('OpenAI API anahtarı ayarlanmamış.');
        return;
    }

    error_log('OpenAI API isteği gönderiliyor...');

    $response = wq_remote_post('https://api.openai.com/v1/images/generations', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'prompt' => $prompt,
            'n' => 1,
            'size' => '256x256',
            'model' => 'dall-e-2',
            'response_format' => 'url'
        )),
        'timeout' => 60
    ));

    if (is_wq_error($response)) {
        error_log('API hatası: ' . $response->get_error_message());
        wq_send_json_error('OpenAI API ile iletişim kurulamadı. Lütfen internet bağlantınızı kontrol edin ve tekrar deneyin. Hata: ' . $response->get_error_message());
        return;
    }

    $body = json_decode(wq_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        error_log('API hata mesajı: ' . $body['error']['message']);
        wq_send_json_error($body['error']['message']);
        return;
    }

    if (!isset($body['data'][0]['url'])) {
        error_log('Beklenmeyen API yanıt formatı');
        wq_send_json_error('API yanıtı beklenmeyen formatta.');
        return;
    }

    wq_send_json_success(array(
        'name' => $name,
        'image_url' => $body['data'][0]['url'],
        'prompt' => $prompt
    ));
}

// Karakter kaydetme AJAX handler
function odib_save_character() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $image_url = esc_url_raw($_POST['image_url']);
    $prompt = sanitize_text_field($_POST['prompt']);
    
    if (empty($name) || empty($image_url) || empty($prompt)) {
        wq_send_json_error('Tüm alanlar gereklidir.');
        return;
    }

    global $wqdb;
    $table_name = $wqdb->prefix . 'odib_characters';
    
    $result = $wqdb->insert(
        $table_name,
        array(
            'character_name' => $name,
            'image_url' => $image_url,
            'prompt' => $prompt
        ),
        array('%s', '%s', '%s')
    );

    if ($wqdb->last_error) {
        error_log('Veritabanı hatası: ' . $wqdb->last_error);
        wq_send_json_error('Veritabanı hatası: ' . $wqdb->last_error);
        return;
    }

    wq_send_json_success();
}

// Karakter silme AJAX handler
function odib_delete_character() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    $character_id = intval($_POST['character_id']);
    
    if (!$character_id) {
        wq_send_json_error('Geçersiz karakter ID.');
        return;
    }

    global $wqdb;
    $table_name = $wqdb->prefix . 'odib_characters';
    
    $result = $wqdb->delete(
        $table_name,
        array('id' => $character_id),
        array('%d')
    );

    if ($wqdb->last_error) {
        error_log('Veritabanı hatası: ' . $wqdb->last_error);
        wq_send_json_error('Veritabanı hatası: ' . $wqdb->last_error);
        return;
    }

    wq_send_json_success();
}

// Karakterleri getirme AJAX handler
function odib_get_characters() {
    check_ajax_referer('odib_nonce', '_ajax_nonce');
    
    global $wqdb;
    $table_name = $wqdb->prefix . 'odib_characters';
    
    $characters = $wqdb->get_results(
        "SELECT id, character_name, image_url, prompt FROM $table_name ORDER BY character_name ASC",
        ARRAY_A
    );

    if ($wqdb->last_error) {
        wq_send_json_error('Veritabanı hatası: ' . $wqdb->last_error);
        return;
    }

    wq_send_json_success($characters);
}

// Oyun konsepti oluşturma AJAX handler
function odib_generate_concept() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    $game_idea = sanitize_text_field($_POST['game_idea']);
    
    if (empty($game_idea)) {
        wq_send_json_error('Oyun fikri gereklidir.');
        return;
    }
    
    $api_key = get_option('odib_openai_api_key', '');
    if (empty($api_key)) {
        error_log('API anahtarı bulunamadı');
        wq_send_json_error('OpenAI API anahtarı ayarlanmamış.');
        return;
    }

    $system_prompt = "Sen bir oyun tasarımcısısın. Verilen oyun fikrini analiz edip detaylı bir konsept tasarımı oluşturmalısın.
    Yanıtını şu başlıklar altında organize et:
    1. Oyun Mekanikleri: Temel oynanış, kontroller ve ana mekanikler
    2. Bölüm Tasarımı: Bölümlerin yapısı, engeller, zorluk seviyesi ve çeşitlilik
    3. İlerleme Sistemi: Oyuncunun ilerlemesi, ödüller ve motivasyon unsurları
    4. Görsel Stil: Oyunun görsel tarzı, atmosferi ve sanat yönü

    Her başlık için en az 3-4 cümle yaz. Yaratıcı ol ama gerçekçi ve uygulanabilir fikirler üret.";

    $response = wq_remote_post('https://api.openai.com/v1/chat/completions', array(
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

    if (is_wq_error($response)) {
        error_log('API hatası: ' . $response->get_error_message());
        wq_send_json_error('OpenAI API ile iletişim kurulamadı. Lütfen internet bağlantınızı kontrol edin ve tekrar deneyin. Hata: ' . $response->get_error_message());
        return;
    }

    $body = json_decode(wq_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        error_log('API hata mesajı: ' . $body['error']['message']);
        wq_send_json_error($body['error']['message']);
        return;
    }

    if (!isset($body['choices'][0]['message']['content'])) {
        error_log('Beklenmeyen API yanıt formatı');
        wq_send_json_error('API yanıtı beklenmeyen formatta.');
        return;
    }

    $content = $body['choices'][0]['message']['content'];
    
    // Yanıtı bölümlere ayır
    preg_match_all('/(\d\. [^:]+):\s*([^1-4]+)(?=[1-4]\.|$)/s', $content, $matches);
    
    $sections = array();
    if (!empty($matches[1])) {
        $section_map = array(
            '1. Oyun Mekanikleri' => 'mechanics',
            '2. Bölüm Tasarımı' => 'level-design',
            '3. İlerleme Sistemi' => 'progression',
            '4. Görsel Stil' => 'visuals'
        );
        
        for ($i = 0; $i < count($matches[1]); $i++) {
            $section_title = trim($matches[1][$i]);
            if (isset($section_map[$section_title])) {
                $sections[$section_map[$section_title]] = trim($matches[2][$i]);
            }
        }
    }

    wq_send_json_success($sections);
}

// Coin oluşturma AJAX handler
function odib_create_coin() {
    check_ajax_referer('odib_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $ticker = sanitize_text_field($_POST['ticker']);
    $description = sanitize_text_field($_POST['description']);
    $media_url = esc_url_raw($_POST['mediaUrl']);
    $game_id = intval($_POST['gameId']);
    $distribution_percentage = floatval($_POST['distributionPercentage']);
    
    if (empty($name) || empty($ticker) || empty($description) || empty($media_url) || !$game_id || $distribution_percentage < 0 || $distribution_percentage > 20) {
        wq_send_json_error('Tüm alanlar gereklidir.');
        return;
    }

    global $wqdb;
    $table_name = $wqdb->prefix . 'odib_coins';
    
    $result = $wqdb->insert(
        $table_name,
        array(
            'name' => $name,
            'ticker' => $ticker,
            'description' => $description,
            'media_url' => $media_url,
            'game_id' => $game_id,
            'distribution_percentage' => $distribution_percentage
        ),
        array('%s', '%s', '%s', '%s', '%d', '%f')
    );

    if ($wqdb->last_error) {
        error_log('Veritabanı hatası: ' . $wqdb->last_error);
        wq_send_json_error('Veritabanı hatası: ' . $wqdb->last_error);
        return;
    }

    wq_send_json_success();
}

// Oyunları getirme AJAX handler
function odib_get_games() {
    global $wqdb;
    $table_name = $wqdb->prefix . 'odib_games';
    
    $games = $wqdb->get_results(
        "SELECT * FROM $table_name ORDER BY name ASC",
        ARRAY_A
    );

    if ($wqdb->last_error) {
        error_log('Veritabanı hatası: ' . $wqdb->last_error);
        wq_send_json_error('Veritabanı hatası: ' . $wqdb->last_error);
        return;
    }

    wq_send_json_success($games);
}

// Gerekli script ve stilleri ekleyelim
function odib_enqueue_scripts() {
    // CSS dosyasını yükle
    wq_enqueue_style('odib-styles', plugins_url('assets/css/style.css', __FILE__), array(), time());
    
    // JavaScript dosyasını yükle
    wq_enqueue_script('odib-main', plugins_url('assets/js/main.js', __FILE__), array('jquery'), time(), true);
    
    // AJAX URL'sini JavaScript'e aktar
    wq_localize_script('odib-main', 'odibAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wq_create_nonce('odib-nonce')
    ));
}
add_action('wq_enqueue_scripts', 'odib_enqueue_scripts');

// AJAX handlers
add_action('wq_ajax_odib_generate_prompt', 'odib_generate_prompt');
add_action('wq_ajax_nopriv_odib_generate_prompt', 'odib_generate_prompt');

add_action('wq_ajax_odib_create_preview', 'odib_create_preview');
add_action('wq_ajax_nopriv_odib_create_preview', 'odib_create_preview');

add_action('wq_ajax_odib_save_character', 'odib_save_character');
add_action('wq_ajax_nopriv_odib_save_character', 'odib_save_character');

add_action('wq_ajax_odib_delete_character', 'odib_delete_character');
add_action('wq_ajax_nopriv_odib_delete_character', 'odib_delete_character');

add_action('wq_ajax_odib_get_characters', 'odib_get_characters');
add_action('wq_ajax_nopriv_odib_get_characters', 'odib_get_characters');

add_action('wq_ajax_odib_generate_concept', 'odib_generate_concept');
add_action('wq_ajax_nopriv_odib_generate_concept', 'odib_generate_concept');

add_action('wq_ajax_odib_create_coin', 'odib_create_coin');
add_action('wq_ajax_nopriv_odib_create_coin', 'odib_create_coin');

add_action('wq_ajax_odib_get_games', 'odib_get_games');
add_action('wq_ajax_nopriv_odib_get_games', 'odib_get_games');
