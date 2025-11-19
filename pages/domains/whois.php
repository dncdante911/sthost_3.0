<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'whois';
$page_title = 'WHOIS Lookup - StormHosting UA';
$meta_description = 'WHOIS сервіс для перевірки інформації про домени .ua, .com.ua та інші. Дізнайтесь хто власник домену, коли закінчується реєстрація.';
$meta_keywords = 'whois домен, інформація про домен, власник домену, дата реєстрації домену';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/domains2.css'
];

$additional_js = [
    '/assets/js/domains.js'
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Получаем WHOIS серверы из БД
try {
    if (defined('DB_AVAILABLE') && DB_AVAILABLE) {
        $whois_servers = db_fetch_all(
            "SELECT zone, whois_server FROM domain_whois_servers WHERE is_active = 1 ORDER BY zone"
        );
    } else {
        throw new Exception('Database not available');
    }
} catch (Exception $e) {
    // Fallback данные
    $whois_servers = [
        ['zone' => '.ua', 'whois_server' => 'whois.ua'],
        ['zone' => '.com.ua', 'whois_server' => 'whois.ua'],
        ['zone' => '.com', 'whois_server' => 'whois.verisign-grs.com'],
        ['zone' => '.net', 'whois_server' => 'whois.verisign-grs.com'],
        ['zone' => '.org', 'whois_server' => 'whois.pir.org']
    ];
}

?>

<!-- WHOIS Hero -->
<section class="whois-hero py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-5 fw-bold mb-4">WHOIS lookup</h1>
                <p class="lead mb-5">Перевірте інформацію про власника домену, дати реєстрації та закінчення, DNS сервери та інші дані.</p>
                
                <!-- WHOIS Search Form -->
                <div class="whois-search-form">
                    <form id="whoisForm" class="row g-3 justify-content-center">
                        <input type="hidden" id="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="col-md-8">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       id="whoisDomain" 
                                       class="form-control" 
                                       placeholder="example.com або example.com.ua"
                                       pattern="[a-zA-Z0-9.-]+"
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-info-circle"></i>
                                Перевірити WHOIS
                            </button>
                        </div>
                    </form>
                    
                    <!-- Search Results -->
                    <div id="whoisResults" class="mt-5"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- WHOIS Info -->
<section class="whois-info py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Що таке WHOIS?</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="info-card h-100">
                    <div class="info-icon">
                        <i class="bi bi-database"></i>
                    </div>
                    <h4>База даних доменів</h4>
                    <p>WHOIS - це протокол і база даних, що містить інформацію про зареєстровані домени, включаючи дані про власників, реєстраторів та технічні деталі.</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="info-card h-100">
                    <div class="info-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h4>Інформація про власника</h4>
                    <p>Через WHOIS можна дізнатись хто є власником домену, контактну інформацію (якщо не приховано), дати реєстрації та закінчення терміну дії.</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="info-card h-100">
                    <div class="info-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Перевірка доменів</h4>
                    <p>WHOIS допомагає перевірити статус домену, визначити чи доступний він для реєстрації, а також отримати технічну інформацію про DNS сервери.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Supported Zones -->
<section class="supported-zones py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Підтримувані доменні зони</h2>
                <p class="section-subtitle">Наш WHOIS сервіс працює з наступними доменними зонами</p>
            </div>
        </div>
        
        <div class="row g-3">
            <?php foreach ($whois_servers as $server): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="zone-card text-center">
                    <div class="zone-name"><?php echo escapeOutput($server['zone']); ?></div>
                    <div class="zone-server"><?php echo escapeOutput($server['whois_server']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Privacy Protection -->
<section class="privacy-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold">Захист приватності WHOIS</h2>
                <p class="lead">Стурбовані приватністю ваших даних в WHOIS базі?</p>
                
                <div class="privacy-benefits">
                    <div class="benefit-item">
                        <i class="bi bi-eye-slash text-primary"></i>
                        <span>Приховання особистих даних</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-shield-lock text-primary"></i>
                        <span>Захист від спаму та небажаних дзвінків</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-incognito text-primary"></i>
                        <span>Анонімна реєстрація доменів</span>
                    </div>
                </div>
                
                <p>При реєстрації домену у нас ви автоматично отримуєте безкоштовний захист приватності WHOIS.</p>
                
                <a href="/domains/register" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i>
                    Зареєструвати домен
                </a>
            </div>
            
            <div class="col-lg-6">
                <div class="privacy-visual">
                    <div class="before-after">
                        <div class="before">
                            <h5>Без захисту:</h5>
                            <div class="whois-data">
                                <div class="data-line">Name: Іван Петренко</div>
                                <div class="data-line">Email: ivan@example.com</div>
                                <div class="data-line">Phone: +380501234567</div>
                                <div class="data-line">Address: вул. Хрещатик 1, Київ</div>
                            </div>
                        </div>
                        
                        <div class="after">
                            <h5>З захистом:</h5>
                            <div class="whois-data protected">
                                <div class="data-line">Name: REDACTED FOR PRIVACY</div>
                                <div class="data-line">Email: REDACTED FOR PRIVACY</div>
                                <div class="data-line">Phone: REDACTED FOR PRIVACY</div>
                                <div class="data-line">Address: REDACTED FOR PRIVACY</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- WHOIS Tools -->
<section class="whois-tools py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Додаткові інструменти</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-dns"></i>
                    </div>
                    <h4>DNS Lookup</h4>
                    <p>Перевірте DNS записи домену</p>
                    <a href="/pages/domains/dns.php" class="btn btn-outline-primary">Перевірити DNS</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h4>Пошук доменів</h4>
                    <p>Знайдіть доступні домени</p>
                    <a href="/pages/domains/register.php" class="btn btn-outline-primary">Знайти домен</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-arrow-right-circle"></i>
                    </div>
                    <h4>Перенесення доменів</h4>
                    <p>Перенесіть домен до нас</p>
                    <a href="/pages/domains/transfer.php" class="btn btn-outline-primary">Перенести</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.zone-card {
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
    cursor: pointer;
}

.zone-card:hover {
    border-color: var(--premium-primary, #667eea);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.zone-name {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 5px;
}

.zone-server {
    font-size: 12px;
    color: #999;
}

.whois-result-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
}

.whois-result-card.available {
    border-left: 4px solid #28a745;
}

.whois-result-card.registered {
    border-left: 4px solid #dc3545;
}

.whois-data-table {
    width: 100%;
    margin-top: 20px;
}

.whois-data-table tr {
    border-bottom: 1px solid #f0f0f0;
}

.whois-data-table td {
    padding: 12px 8px;
}

.whois-data-table td:first-child {
    font-weight: 600;
    color: #666;
    width: 200px;
}

.raw-data {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    font-family: monospace;
    font-size: 13px;
    white-space: pre-wrap;
    margin-top: 20px;
    max-height: 400px;
    overflow-y: auto;
}
</style>

<script>
// ========================================
// WHOIS Configuration
// ========================================
window.whoisConfig = {
    lookupUrl: '?ajax=1',
    csrfToken: '<?php echo generateCSRFToken(); ?>',
    servers: <?php echo json_encode($whois_servers); ?>,
    translations: {
        searching: 'Виконуємо WHOIS запит...',
        error: 'Помилка запиту',
        notFound: 'Домен не знайдено',
        available: 'Домен доступний для реєстрації'
    }
};

// ========================================
// WHOIS Form Handler
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const whoisForm = document.getElementById('whoisForm');
    const resultsDiv = document.getElementById('whoisResults');

    if (whoisForm) {
        whoisForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const domainInput = document.getElementById('whoisDomain');
            const domain = domainInput.value.trim().toLowerCase();

            if (!domain) {
                alert('Введіть домен для перевірки');
                return;
            }

            // Validate domain format
            if (!/^[a-z0-9][a-z0-9-]*[a-z0-9]\.[a-z]{2,}$|^[a-z0-9]\.[a-z]{2,}$/.test(domain)) {
                alert('Невірний формат домену');
                return;
            }

            // Show loading
            resultsDiv.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Завантаження...</span>
                    </div>
                    <p class="mt-3 text-muted">${window.whoisConfig.translations.searching}</p>
                </div>
            `;

            // Perform WHOIS lookup
            fetch(window.whoisConfig.lookupUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'whois_lookup',
                    domain: domain,
                    csrf_token: window.whoisConfig.csrfToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showError(data.error);
                    return;
                }

                displayWhoisResults(data);
            })
            .catch(error => {
                showError(window.whoisConfig.translations.error);
                console.error('WHOIS Error:', error);
            });
        });
    }

    // Quick lookup from zone cards
    document.querySelectorAll('.zone-card').forEach(card => {
        card.addEventListener('click', function() {
            const zone = this.querySelector('.zone-name').textContent;
            const domainInput = document.getElementById('whoisDomain');
            domainInput.value = 'example' + zone;
            domainInput.focus();
        });
    });
});

// ========================================
// Display WHOIS Results
// ========================================
function displayWhoisResults(data) {
    const resultsDiv = document.getElementById('whoisResults');

    if (data.data.status === 'available') {
        resultsDiv.innerHTML = `
            <div class="whois-result-card available">
                <div class="d-flex align-items-center mb-4">
                    <div class="me-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 48px;"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">${escapeHtml(data.domain)}</h3>
                        <p class="text-success mb-0 fw-bold">Домен доступний для реєстрації!</p>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Цей домен не зареєстрований та доступний для придбання.
                </div>

                <div class="text-center mt-4">
                    <a href="/pages/domains/register.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-cart-plus"></i>
                        Зареєструвати цей домен
                    </a>
                </div>
            </div>
        `;
    } else {
        // Registered domain
        const whoisData = data.data;
        resultsDiv.innerHTML = `
            <div class="whois-result-card registered">
                <div class="d-flex align-items-center mb-4">
                    <div class="me-3">
                        <i class="bi bi-x-circle text-danger" style="font-size: 48px;"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">${escapeHtml(data.domain)}</h3>
                        <p class="text-danger mb-0 fw-bold">Домен зареєстрований</p>
                    </div>
                </div>

                <table class="whois-data-table">
                    <tr>
                        <td>Реєстратор:</td>
                        <td>${escapeHtml(whoisData.registrar || 'N/A')}</td>
                    </tr>
                    <tr>
                        <td>Дата реєстрації:</td>
                        <td>${escapeHtml(whoisData.creation_date || 'N/A')}</td>
                    </tr>
                    <tr>
                        <td>Дата закінчення:</td>
                        <td>${escapeHtml(whoisData.expiration_date || 'N/A')}</td>
                    </tr>
                    <tr>
                        <td>Останнє оновлення:</td>
                        <td>${escapeHtml(whoisData.updated_date || 'N/A')}</td>
                    </tr>
                    <tr>
                        <td>Статус:</td>
                        <td>${escapeHtml(whoisData.status || 'N/A')}</td>
                    </tr>
                    ${whoisData.name_servers && whoisData.name_servers.length > 0 ? `
                    <tr>
                        <td>Name Servers:</td>
                        <td>${whoisData.name_servers.map(ns => escapeHtml(ns)).join('<br>')}</td>
                    </tr>
                    ` : ''}
                </table>

                <div class="mt-4">
                    <button class="btn btn-outline-secondary" onclick="toggleRawData()">
                        <i class="bi bi-code-slash"></i>
                        Показати raw WHOIS дані
                    </button>
                </div>

                <div id="rawWhoisData" class="raw-data" style="display: none;">
${escapeHtml(whoisData.raw_data || 'No raw data available')}
                </div>
            </div>
        `;
    }

    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function toggleRawData() {
    const rawData = document.getElementById('rawWhoisData');
    if (rawData) {
        rawData.style.display = rawData.style.display === 'none' ? 'block' : 'none';
    }
}

function showError(message) {
    const resultsDiv = document.getElementById('whoisResults');
    resultsDiv.innerHTML = `
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Помилка:</strong> ${escapeHtml(message)}
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>