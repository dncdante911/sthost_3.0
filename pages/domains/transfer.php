<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'transfer';
$page_title = 'Трансфер домену до StormHosting UA';
$meta_description = 'Перенесіть домен до StormHosting UA швидко та безпечно. Безкоштовний трансфер + 1 рік продовження включено.';
$meta_keywords = 'трансфер домену, перенесення домену, domain transfer, зміна реєстратора';

// Додаткові CSS та JS файли
$additional_css = [
    '/assets/css/pages/transfer-form.css'
];

$additional_js = [
    '/assets/js/transfer-form.js'
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Ціни на трансфер
$transfer_prices = [
    '.ua' => ['price' => 180, 'currency' => 'грн'],
    '.com.ua' => ['price' => 130, 'currency' => 'грн'],
    '.kiev.ua' => ['price' => 160, 'currency' => 'грн'],
    '.net.ua' => ['price' => 160, 'currency' => 'грн'],
    '.org.ua' => ['price' => 160, 'currency' => 'грн'],
    '.com' => ['price' => 300, 'currency' => 'грн'],
    '.net' => ['price' => 400, 'currency' => 'грн'],
    '.org' => ['price' => 350, 'currency' => 'грн'],
    '.info' => ['price' => 300, 'currency' => 'грн'],
    '.biz' => ['price' => 300, 'currency' => 'грн']
];
?>

<!-- Transfer Hero Section -->
<section class="transfer-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="bi bi-arrow-left-right"></i>
                <span>Domain Transfer</span>
            </div>
            <h1 class="hero-title">Перенесіть домен до нас</h1>
            <p class="hero-subtitle">Швидкий та безпечний трансфер доменів з будь-якого реєстратора.<br>Продовження на 1 рік вже включено у вартість!</p>

            <div class="hero-benefits">
                <div class="benefit">
                    <div class="benefit-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="benefit-text">
                        <strong>Безпечно</strong>
                        <span>Захист даних</span>
                    </div>
                </div>
                <div class="benefit">
                    <div class="benefit-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <div class="benefit-text">
                        <strong>Швидко</strong>
                        <span>3-7 днів</span>
                    </div>
                </div>
                <div class="benefit">
                    <div class="benefit-icon">
                        <i class="bi bi-gift"></i>
                    </div>
                    <div class="benefit-text">
                        <strong>+1 рік</strong>
                        <span>Безкоштовно</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Form Section -->
<section class="transfer-form-section">
    <div class="container">
        <div class="form-wrapper">
            <div class="form-header">
                <h2>Заповніть форму трансферу</h2>
                <p>Вкажіть домен та контактні дані для початку процесу переносу</p>
            </div>

            <!-- Alert Container -->
            <div id="transferAlert"></div>

            <form id="transferForm" class="transfer-form">
                <!-- Domain Input -->
                <div class="form-group">
                    <label for="domain" class="form-label">
                        <i class="bi bi-globe"></i>
                        Домен для трансферу
                        <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="domain"
                        name="domain"
                        class="form-control"
                        placeholder="example.com"
                        required
                        autocomplete="off">
                    <div class="form-hint">Введіть повне ім'я домену з зоною</div>
                </div>

                <!-- Auth Code -->
                <div class="form-group">
                    <label for="auth_code" class="form-label">
                        <i class="bi bi-key"></i>
                        Код авторизації (EPP/Auth код)
                    </label>
                    <input
                        type="text"
                        id="auth_code"
                        name="auth_code"
                        class="form-control"
                        placeholder="Отримайте у поточного реєстратора"
                        autocomplete="off">
                    <div class="form-hint">Якщо у вас ще немає коду - вкажіть пізніше</div>
                </div>

                <!-- Contact Email -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_email" class="form-label">
                            <i class="bi bi-envelope"></i>
                            Email для зв'язку
                            <span class="required">*</span>
                        </label>
                        <input
                            type="email"
                            id="contact_email"
                            name="contact_email"
                            class="form-control"
                            placeholder="your@email.com"
                            required
                            autocomplete="email">
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone"></i>
                            Телефон
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="form-control"
                            placeholder="+380 XX XXX XX XX"
                            autocomplete="tel">
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label for="notes" class="form-label">
                        <i class="bi bi-chat-text"></i>
                        Додаткові примітки
                    </label>
                    <textarea
                        id="notes"
                        name="notes"
                        class="form-control"
                        rows="3"
                        placeholder="Будь-яка додаткова інформація (необов'язково)"></textarea>
                </div>

                <!-- Agreement -->
                <div class="form-group">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            id="agree_terms"
                            name="agree_terms"
                            class="form-check-input"
                            required>
                        <label for="agree_terms" class="form-check-label">
                            Я підтверджую, що маю права на трансфер цього домену та погоджуюсь з
                            <a href="/info/rules" target="_blank">умовами надання послуг</a>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="bi bi-check-circle"></i>
                        <span>Подати заявку на трансфер</span>
                    </button>
                </div>
            </form>

            <!-- Info Block -->
            <div class="transfer-info">
                <div class="info-item">
                    <i class="bi bi-info-circle"></i>
                    <span>Трансфер займає від 3 до 7 днів в залежності від доменної зони</span>
                </div>
                <div class="info-item">
                    <i class="bi bi-shield-lock"></i>
                    <span>Ваші дані захищені та не передаються третім особам</span>
                </div>
            </div>
        </div>

        <!-- Pricing Table -->
        <div class="pricing-sidebar">
            <div class="pricing-card">
                <h3>Ціни на трансфер</h3>
                <div class="price-list">
                    <?php foreach ($transfer_prices as $zone => $data): ?>
                    <div class="price-item">
                        <span class="zone"><?php echo escapeOutput($zone); ?></span>
                        <span class="price"><?php echo $data['price']; ?> <?php echo $data['currency']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pricing-note">
                    <i class="bi bi-star"></i>
                    <span>Ціна включає продовження на 1 рік</span>
                </div>
            </div>

            <!-- Steps Card -->
            <div class="steps-card">
                <h3>Як це працює</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-text">
                        <strong>Заповніть форму</strong>
                        <span>Вкажіть домен та контакти</span>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">
                        <strong>Отримайте код</strong>
                        <span>EPP код від реєстратора</span>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">
                        <strong>Підтвердіть email</strong>
                        <span>Підтвердження трансферу</span>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-text">
                        <strong>Готово!</strong>
                        <span>Домен у вашому акаунті</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <h2 class="section-title">Питання та відповіді</h2>
        <div class="faq-grid">
            <div class="faq-item">
                <h4><i class="bi bi-question-circle"></i> Скільки коштує трансфер?</h4>
                <p>Трансфер включає продовження домену на 1 рік. Ціни вказані в таблиці праворуч.</p>
            </div>
            <div class="faq-item">
                <h4><i class="bi bi-clock-history"></i> Скільки часу займає трансфер?</h4>
                <p>Зазвичай від 3 до 7 днів. Українські домени (.ua) - 2-3 дні.</p>
            </div>
            <div class="faq-item">
                <h4><i class="bi bi-key"></i> Що таке EPP код?</h4>
                <p>Це код авторизації, який підтверджує ваше право на трансфер. Запитайте у поточного реєстратора.</p>
            </div>
            <div class="faq-item">
                <h4><i class="bi bi-shield-check"></i> Чи безпечний трансфер?</h4>
                <p>Так, процес повністю безпечний. DNS налаштування зберігаються, сайт продовжує працювати.</p>
            </div>
        </div>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
