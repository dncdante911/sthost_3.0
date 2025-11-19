<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'transfer';
$page_title = 'Перенесення домену - StormHosting UA';
$meta_description = 'Перенесіть ваш домен до StormHosting UA безкоштовно. Простий процес трансферу доменів з будь-якого реєстратора. Продовження на 1 рік включено.';
$meta_keywords = 'трансфер доменів, перенесення доменів, домен transfer, зміна реєстратора';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/domains-transfer.css'
];

$additional_js = [
    '/assets/js/domains-transfer.js'
];

// Не требуется обработка формы в PHP - используем API

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// ========================================
// WHMCS INTEGRATION CONFIGURATION
// ========================================
$whmcs_config = [
    'billing_url' => 'https://bill.sthost.pro',
    'direct_checkout' => false
];

// Получаем поддерживаемые зоны для трансфера
try {
    if (defined('DB_AVAILABLE') && DB_AVAILABLE) {
        $transferable_zones = db_fetch_all(
            "SELECT zone, price_transfer, price_renewal 
             FROM domain_zones 
             WHERE is_active = 1 AND price_transfer > 0
             ORDER BY zone LIKE '%.ua' DESC, price_transfer ASC"
        );
    } else {
        throw new Exception('Database not available');
    }
} catch (Exception $e) {
    $transferable_zones = [
        ['zone' => '.ua', 'price_transfer' => 180, 'price_renewal' => 200],
        ['zone' => '.com.ua', 'price_transfer' => 130, 'price_renewal' => 150],
        ['zone' => '.kiev.ua', 'price_transfer' => 160, 'price_renewal' => 180],
        ['zone' => '.net.ua', 'price_transfer' => 160, 'price_renewal' => 180],
        ['zone' => '.org.ua', 'price_transfer' => 160, 'price_renewal' => 180],
        ['zone' => '.com', 'price_transfer' => 300, 'price_renewal' => 350],
        ['zone' => '.net', 'price_transfer' => 400, 'price_renewal' => 450],
        ['zone' => '.org', 'price_transfer' => 350, 'price_renewal' => 400]
    ];
}

// Обработка формы перенесена в API (/api/domains/transfer.php)

?>

<!-- Transfer Hero -->
<section class="transfer-hero py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Перенесення домену</h1>
                <p class="lead mb-4">Перенесіть ваш домен до StormHosting UA та отримайте кращий сервіс, захист та підтримку 24/7.</p>
                
                <div class="transfer-benefits">
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Безкоштовне перенесення</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Продовження на 1 рік включено</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Без втрати налаштувань DNS</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Захист від несанкціонованого трансферу</span>
                    </div>
                </div>
                
                <a href="#transfer-form" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right-circle"></i>
                    Почати трансфер
                </a>
            </div>
            
            <div class="col-lg-6">
                <div class="transfer-visual">
                    <div class="transfer-diagram">
                        <div class="old-registrar">
                            <div class="registrar-box">
                                <i class="bi bi-building"></i>
                                <span>Старий реєстратор</span>
                            </div>
                        </div>
                        
                        <div class="transfer-arrow">
                            <i class="bi bi-arrow-right"></i>
                            <span>Безкоштовний трансфер</span>
                        </div>
                        
                        <div class="new-registrar">
                            <div class="registrar-box stormhosting">
                                <i class="bi bi-shield-check"></i>
                                <span>StormHosting UA</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="domain-icon">
                        <i class="bi bi-globe"></i>
                        <span>your-domain.com</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Process -->
<section class="transfer-process py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Як проходить трансфер</h2>
                <p class="section-subtitle">Простий процес з 4 кроків</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="bi bi-key"></i>
                    </div>
                    <h4>Отримайте код авторизації</h4>
                    <p>Зверніться до поточного реєстратора для отримання EPP/Auth коду домену</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <h4>Подайте заявку</h4>
                    <p>Заповніть форму трансферу з кодом авторизації та контактними даними</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="bi bi-envelope-check"></i>
                    </div>
                    <h4>Підтвердьте трансфер</h4>
                    <p>Підтвердіть трансфер через email, який прийде на адресу власника домену</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h4>Готово!</h4>
                    <p>Домен буде перенесено протягом 5-7 днів з автоматичним продовженням на рік</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Form -->
<section id="transfer-form" class="transfer-form-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="transfer-form-card">
                    <div class="form-header text-center">
                        <h2 class="fw-bold">Форма трансферу домену</h2>
                        <p>Заповніть форму для подачі заявки на трансфер</p>
                    </div>

                    <!-- Результат отправки формы -->
                    <div id="transferResults"></div>

                    <form id="transferForm" class="transfer-form">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="domain" class="form-label">Домен для трансферу *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="bi bi-globe"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control"
                                           id="domain"
                                           name="domain"
                                           placeholder="example.com або example.ua"
                                           required>
                                </div>
                                <div class="form-text">Введіть повне ім'я домену включно з зоною</div>
                            </div>

                            <div class="col-md-12">
                                <label for="auth_code" class="form-label">Код авторизації (EPP/Auth код)</label>
                                <input type="text"
                                       class="form-control"
                                       id="auth_code"
                                       name="auth_code"
                                       placeholder="Отримайте у поточного реєстратора">
                                <div class="form-text">Необхідний для підтвердження трансферу</div>
                            </div>

                            <div class="col-md-6">
                                <label for="contact_email" class="form-label">Email для зв'язку *</label>
                                <input type="email"
                                       class="form-control"
                                       id="contact_email"
                                       name="contact_email"
                                       placeholder="email@example.com"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel"
                                       class="form-control"
                                       id="phone"
                                       name="phone"
                                       placeholder="+380 XX XXX XX XX">
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label">Примітки</label>
                                <textarea class="form-control"
                                          id="notes"
                                          name="notes"
                                          rows="3"
                                          placeholder="Додаткова інформація (необов'язково)"></textarea>
                            </div>

                            <div class="col-12">
                                <div class="transfer-note">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-lightbulb text-warning me-3" style="font-size: 24px;"></i>
                                        <div>
                                            <h6 class="mb-2">Важливо перед трансфером:</h6>
                                            <ul class="mb-0 small">
                                                <li>Переконайтеся що домен розблоковано для трансферу</li>
                                                <li>Отримайте EPP/Auth код від поточного реєстратора</li>
                                                <li>Домен повинен бути зареєстрований більше 60 днів тому</li>
                                                <li>Email адреса власника домену має бути актуальною</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        Я підтверджую, що маю права на трансфер цього домену та погоджуюсь з <a href="/info/rules" target="_blank">умовами послуг</a> *
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="bi bi-check-circle"></i>
                                    Подати заявку на трансфер
                                </button>
                                <p class="text-muted mt-3 small">
                                    <i class="bi bi-shield-lock"></i> Безпечна обробка даних
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Pricing -->
<section class="transfer-pricing py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Ціни на трансфер доменів</h2>
                <p class="section-subtitle">Прозорі ціни без прихованих платежів</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach (array_chunk($transferable_zones, ceil(count($transferable_zones) / 2)) as $chunk): ?>
            <div class="col-lg-6">
                <div class="pricing-table">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Доменна зона</th>
                                    <th>Трансфер</th>
                                    <th>Продовження</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chunk as $zone): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo escapeOutput($zone['zone']); ?></strong>
                                        <?php if (strpos($zone['zone'], '.ua') !== false): ?>
                                        <span class="badge bg-primary ms-2">UA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="price-highlight"><?php echo formatPrice($zone['price_transfer']); ?></span>
                                        <small class="text-muted d-block">+ 1 рік продовження</small>
                                    </td>
                                    <td><?php echo formatPrice($zone['price_renewal']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="quickTransfer('<?php echo escapeOutput($zone['zone']); ?>')">
                                            <i class="bi bi-arrow-right-circle"></i> Трансфер
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="pricing-note">
                    <i class="bi bi-info-circle"></i>
                    <strong>Важливо:</strong> Ціна трансферу включає продовження домену на 1 рік. 
                    DNS налаштування зберігаються без змін.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="transfer-faq py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Часто задавані питання</h2>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="transferFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Скільки часу займає трансфер домену?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Трансфер домену зазвичай займає від 5 до 7 днів. Це залежить від доменної зони та швидкості підтвердження з боку поточного реєстратора. Українські домени (.ua, .com.ua) можуть трансферитися швидше - протягом 2-3 днів.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Що таке код авторизації (EPP код)?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                EPP код (також Auth код) - це унікальний код, який підтверджує ваші права на домен. Його можна отримати в панелі управління поточного реєстратора або звернувшись до їхньої підтримки. Код зазвичай складається з 8-16 символів.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Чи втрачу я налаштування DNS при трансфері?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Ні, всі DNS налаштування зберігаються під час трансферу. Ваш сайт та email продовжать працювати без перебоїв. Після трансферу ви зможете керувати DNS через нашу панель управління.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Чи можу я скасувати трансфер?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Так, ви можете скасувати трансфер до його завершення. Також поточний реєстратор може відхилити трансфер протягом 5 днів. У такому випадку кошти будуть повернені на ваш рахунок.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Які домени можна трансферити?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Можна трансферити більшість доменів, включаючи .com, .net, .org, .ua, .com.ua та інші. Домен повинен бути зареєстрований більше 60 днів тому та не мати блокування на трансфер. Деякі домени (.gov, .edu) не можна трансферити.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Що робити, якщо я не можу отримати код авторизації?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Зверніться до поточного реєстратора домену. Вони зобов'язані надати код авторизації власнику домену. Якщо у вас виникли проблеми, наша підтримка допоможе вам з процедурою отримання коду.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Benefits -->
<section class="transfer-benefits-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-4">Чому варто перенести домен до нас?</h2>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <h5>Надійність та безпека</h5>
                                <p>Захист від несанкціонованого трансферу, блокування та зламу</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-headset"></i>
                            <div>
                                <h5>Підтримка 24/7</h5>
                                <p>Технічна підтримка українською мовою цілодобово</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-gear"></i>
                            <div>
                                <h5>Зручне керування</h5>
                                <p>Інтуїтивна панель управління з усіма необхідними функціями</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-cash-coin"></i>
                            <div>
                                <h5>Конкурентні ціни</h5>
                                <p>Найкращі ціни на ринку України для продовження доменів</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 text-center">
                <div class="cta-box">
                    <h3>Готові перенести домен?</h3>
                    <p>Почніть зараз та отримайте безкоштовний трансфер</p>
                    <a href="#transfer-form" class="btn btn-light btn-lg">
                        <i class="bi bi-arrow-up-circle"></i>
                        Заповнити форму
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Support -->
<section class="transfer-support py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-4">Потрібна допомога з трансфером?</h2>
                <p class="lead mb-4">Наша команда експертів готова допомогти вам з будь-якими питаннями щодо трансферу доменів.</p>
                
                <div class="contact-options">
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <div class="contact-method">
                                <i class="bi bi-chat-dots text-primary"></i>
                                <h5>Онлайн чат</h5>
                                <p>Миттєва відповідь від наших спеціалістів</p>
                                <button class="btn btn-outline-primary" onclick="openChat()">Почати чат</button>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="contact-method">
                                <i class="bi bi-telephone text-primary"></i>
                                <h5>Телефон</h5>
                                <p>Зателефонуйте нам для консультації</p>
                                <a href="tel:+380XXXXXXXXX" class="btn btn-outline-primary">+380 XX XXX XX XX</a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="contact-method">
                                <i class="bi bi-envelope text-primary"></i>
                                <h5>Email</h5>
                                <p>Надішліть нам детальний запит</p>
                                <a href="mailto:domains@sthost.pro" class="btn btn-outline-primary">domains@sthost.pro</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.transfer-note {
    background: linear-gradient(to right, rgba(255, 193, 7, 0.1), transparent);
    border-left: 4px solid #ffc107;
    padding: 20px;
    border-radius: 8px;
    margin: 15px 0;
}

.transfer-note h6 {
    color: #856404;
    font-weight: 600;
}

.transfer-note ul {
    list-style: none;
    padding-left: 0;
}

.transfer-note ul li {
    padding: 5px 0;
    color: #666;
}

.transfer-note ul li:before {
    content: "✓ ";
    color: #28a745;
    font-weight: bold;
    margin-right: 8px;
}

.price-highlight {
    color: var(--premium-primary, #667eea);
    font-weight: 700;
    font-size: 18px;
}
</style>

<script>
// ========================================
// WHMCS Transfer Configuration
// ========================================
window.transferConfig = {
    whmcs: {
        billingUrl: '<?php echo $whmcs_config['billing_url']; ?>',
        directCheckout: <?php echo $whmcs_config['direct_checkout'] ? 'true' : 'false'; ?>
    },
    supportedZones: <?php echo json_encode(array_column($transferable_zones, 'zone')); ?>,
    translations: {
        invalidDomain: 'Невірний формат домену',
        unsupportedZone: 'Ця доменна зона не підтримується для трансферу',
        enterDomain: 'Будь ласка, введіть домен',
        agreeTerms: 'Будь ласка, погодьтеся з умовами'
    }
};

// ========================================
// Transfer Form Handler (WHMCS Integration)
// ========================================
function handleTransferSubmit(event) {
    event.preventDefault();

    const domainInput = document.getElementById('domain');
    const agreeTerms = document.getElementById('agree_terms');

    const domain = domainInput.value.trim().toLowerCase();

    // Validation
    if (!domain) {
        alert(window.transferConfig.translations.enterDomain);
        domainInput.focus();
        return false;
    }

    if (!validateDomainFormat(domain)) {
        alert(window.transferConfig.translations.invalidDomain);
        domainInput.focus();
        return false;
    }

    if (!agreeTerms.checked) {
        alert(window.transferConfig.translations.agreeTerms);
        return false;
    }

    // Redirect to WHMCS transfer
    transferDomainToWHMCS(domain);
    return false;
}

// ========================================
// Quick Transfer from Price Table
// ========================================
function quickTransfer(zone) {
    const domain = prompt(`Введіть назву домену для трансферу (без ${zone}):\n\nНаприклад: mycompany`);

    if (!domain || domain.trim() === '') {
        return;
    }

    const cleanDomain = domain.trim().toLowerCase();

    // Validate domain name part
    if (!/^[a-z0-9][a-z0-9-]*[a-z0-9]$|^[a-z0-9]$/.test(cleanDomain)) {
        alert('Невірний формат імені домену. Використовуйте лише літери, цифри та дефіси.');
        return;
    }

    const fullDomain = cleanDomain + zone;
    transferDomainToWHMCS(fullDomain);
}

// ========================================
// WHMCS Transfer Redirect
// ========================================
function transferDomainToWHMCS(domain) {
    const billingUrl = window.transferConfig.whmcs.billingUrl;
    const transferUrl = `${billingUrl}/cart.php?a=add&domain=transfer&query=${encodeURIComponent(domain)}`;

    // Open in new tab
    window.open(transferUrl, '_blank');

    // Show success message
    setTimeout(() => {
        alert(`✓ Перехід до оформлення трансферу домену: ${domain}\n\nВи будете перенаправлені в систему біллінгу для введення коду авторизації та оплати.`);
    }, 500);
}

// ========================================
// Domain Validation
// ========================================
function validateDomainFormat(domain) {
    // Check basic domain format
    const domainPattern = /^[a-z0-9][a-z0-9-]*[a-z0-9]\.[a-z]{2,}$|^[a-z0-9]\.[a-z]{2,}$/;
    return domainPattern.test(domain);
}

function openChat() {
    alert('Онлайн чат буде доступний незабаром.\n\nНаразі ви можете зв\'язатись з нами:\n• Email: domains@sthost.pro\n• Telegram: @sthost_support');
}

// ========================================
// Initialize
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const domainInput = document.getElementById('domain');

    if (domainInput) {
        // Real-time validation feedback
        domainInput.addEventListener('input', function(e) {
            const domain = e.target.value.toLowerCase().trim();

            if (domain.length === 0) {
                e.target.classList.remove('is-invalid', 'is-valid');
                return;
            }

            const isValid = validateDomainFormat(domain);
            e.target.classList.toggle('is-invalid', !isValid);
            e.target.classList.toggle('is-valid', isValid);
        });

        // Auto-lowercase
        domainInput.addEventListener('blur', function(e) {
            e.target.value = e.target.value.toLowerCase().trim();
        });
    }

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    }
});
</script>

 <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>