/**
 * hosting-cloud.js - JavaScript для сторінки хмарного хостингу
 * Калькулятор вартості, вибір конфігурацій, замовлення
 */

// Ціни за ресурси (базові ставки на одиницю)
const resourcePrices = {
    cpu: 100,        // ₴100 за 1 vCPU
    ram: 50,         // ₴50 за 1 ГБ RAM
    storage: 5,      // ₴5 за 1 ГБ SSD
    bandwidth: 0.1   // ₴0.1 за 1 ГБ трафіку
};

// Готові конфігурації
const presetConfigs = {
    'start': {
        cpu: 1,
        ram: 2,
        storage: 25,
        bandwidth: 500,
        price: 399
    },
    'business': {
        cpu: 2,
        ram: 4,
        storage: 50,
        bandwidth: 1000,
        price: 799
    },
    'pro': {
        cpu: 4,
        ram: 8,
        storage: 100,
        bandwidth: 2000,
        price: 1499
    },
    'enterprise': {
        cpu: 8,
        ram: 16,
        storage: 200,
        bandwidth: 5000,
        price: 2999
    }
};

// Поточна конфігурація
let currentConfig = {
    cpu: 2,
    ram: 4,
    storage: 50,
    bandwidth: 1000,
    options: {}
};

/**
 * Ініціалізація калькулятора при завантаженні сторінки
 */
document.addEventListener('DOMContentLoaded', function() {
    initCalculator();
    initBillingPeriod();
});

/**
 * Ініціалізація калькулятора
 */
function initCalculator() {
    // CPU Slider
    const cpuSlider = document.getElementById('cpu-slider');
    if (cpuSlider) {
        cpuSlider.addEventListener('input', function() {
            currentConfig.cpu = parseInt(this.value);
            document.getElementById('cpu-value').textContent = this.value;
            document.getElementById('summary-cpu').textContent = this.value;
            calculatePrice();
        });
    }

    // RAM Slider
    const ramSlider = document.getElementById('ram-slider');
    if (ramSlider) {
        ramSlider.addEventListener('input', function() {
            currentConfig.ram = parseInt(this.value);
            document.getElementById('ram-value').textContent = this.value;
            document.getElementById('summary-ram').textContent = this.value;
            calculatePrice();
        });
    }

    // Storage Slider
    const storageSlider = document.getElementById('storage-slider');
    if (storageSlider) {
        storageSlider.addEventListener('input', function() {
            currentConfig.storage = parseInt(this.value);
            document.getElementById('storage-value').textContent = this.value;
            document.getElementById('summary-storage').textContent = this.value;
            calculatePrice();
        });
    }

    // Bandwidth Slider
    const bandwidthSlider = document.getElementById('bandwidth-slider');
    if (bandwidthSlider) {
        bandwidthSlider.addEventListener('input', function() {
            currentConfig.bandwidth = parseInt(this.value);
            document.getElementById('bandwidth-value').textContent = this.value;
            document.getElementById('summary-bandwidth').textContent = this.value;
            calculatePrice();
        });
    }

    // Додаткові опції (чекбокси)
    const optionCheckboxes = document.querySelectorAll('.option-check input[type="checkbox"]');
    optionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const price = parseInt(this.dataset.price) || 0;
            if (this.checked) {
                currentConfig.options[this.id] = price;
            } else {
                delete currentConfig.options[this.id];
            }
            updateSelectedOptions();
            calculatePrice();
        });
    });

    // Початковий розрахунок
    calculatePrice();
}

/**
 * Ініціалізація перемикача періоду оплати
 */
function initBillingPeriod() {
    const monthlyRadio = document.getElementById('monthly');
    const yearlyRadio = document.getElementById('yearly');

    if (monthlyRadio) {
        monthlyRadio.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('yearly-info').classList.add('d-none');
                calculatePrice();
            }
        });
    }

    if (yearlyRadio) {
        yearlyRadio.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('yearly-info').classList.remove('d-none');
                calculatePrice();
            }
        });
    }
}

/**
 * Розрахунок вартості
 */
function calculatePrice() {
    // Базова вартість за ресурси
    let basePrice = 0;
    basePrice += currentConfig.cpu * resourcePrices.cpu;
    basePrice += currentConfig.ram * resourcePrices.ram;
    basePrice += currentConfig.storage * resourcePrices.storage;
    basePrice += currentConfig.bandwidth * resourcePrices.bandwidth;

    // Додаткові опції
    let optionsPrice = 0;
    Object.values(currentConfig.options).forEach(price => {
        optionsPrice += price;
    });

    // Загальна вартість за місяць
    let monthlyPrice = basePrice + optionsPrice;

    // Якщо обрано річний план - знижка 15%
    const isYearly = document.getElementById('yearly')?.checked;
    let displayPrice = monthlyPrice;

    if (isYearly) {
        const yearlyTotal = monthlyPrice * 12;
        const discountedYearlyTotal = yearlyTotal * 0.85; // 15% знижка
        displayPrice = Math.round(discountedYearlyTotal / 12);

        // Показуємо економію
        const savings = yearlyTotal - discountedYearlyTotal;
        const savingsElement = document.getElementById('yearly-savings');
        if (savingsElement) {
            savingsElement.textContent = formatNumber(Math.round(savings));
        }
    }

    // Оновлюємо відображення ціни з анімацією
    animatePrice('monthly-price', Math.round(displayPrice));
}

/**
 * Анімація зміни ціни
 */
function animatePrice(elementId, targetPrice) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const currentPrice = parseInt(element.textContent.replace(/\s/g, '')) || 0;
    const duration = 300;
    const steps = 15;
    const increment = (targetPrice - currentPrice) / steps;
    const stepDuration = duration / steps;

    let step = 0;
    const timer = setInterval(() => {
        step++;
        const newPrice = Math.round(currentPrice + (increment * step));
        element.textContent = formatNumber(newPrice);

        if (step >= steps) {
            element.textContent = formatNumber(targetPrice);
            clearInterval(timer);
        }
    }, stepDuration);
}

/**
 * Оновлення відображення обраних опцій
 */
function updateSelectedOptions() {
    const container = document.getElementById('selected-options');
    if (!container) return;

    const optionNames = {
        'backup': 'Щоденні бекапи',
        'monitoring': 'Розширений моніторинг',
        'ssl': 'SSL Wildcard',
        'cdn': 'Premium CDN'
    };

    if (Object.keys(currentConfig.options).length === 0) {
        container.innerHTML = '';
        return;
    }

    let html = '<div class="small text-muted mb-2">Додаткові опції:</div>';
    Object.keys(currentConfig.options).forEach(optionId => {
        const price = currentConfig.options[optionId];
        html += `
            <div class="selected-option">
                <i class="bi bi-check-circle-fill text-success me-1"></i>
                <span>${optionNames[optionId]}</span>
                <span class="text-muted ms-auto">+${price} ₴</span>
            </div>
        `;
    });

    container.innerHTML = html;
}

/**
 * Вибір готової конфігурації
 */
function selectConfig(configName) {
    const config = presetConfigs[configName];
    if (!config) return;

    // Встановлюємо значення слайдерів
    const cpuSlider = document.getElementById('cpu-slider');
    const ramSlider = document.getElementById('ram-slider');
    const storageSlider = document.getElementById('storage-slider');
    const bandwidthSlider = document.getElementById('bandwidth-slider');

    if (cpuSlider) {
        cpuSlider.value = config.cpu;
        currentConfig.cpu = config.cpu;
        document.getElementById('cpu-value').textContent = config.cpu;
        document.getElementById('summary-cpu').textContent = config.cpu;
    }

    if (ramSlider) {
        ramSlider.value = config.ram;
        currentConfig.ram = config.ram;
        document.getElementById('ram-value').textContent = config.ram;
        document.getElementById('summary-ram').textContent = config.ram;
    }

    if (storageSlider) {
        storageSlider.value = config.storage;
        currentConfig.storage = config.storage;
        document.getElementById('storage-value').textContent = config.storage;
        document.getElementById('summary-storage').textContent = config.storage;
    }

    if (bandwidthSlider) {
        bandwidthSlider.value = config.bandwidth;
        currentConfig.bandwidth = config.bandwidth;
        document.getElementById('bandwidth-value').textContent = config.bandwidth;
        document.getElementById('summary-bandwidth').textContent = config.bandwidth;
    }

    // Скролимо до калькулятора
    const calculator = document.getElementById('calculator');
    if (calculator) {
        calculator.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Перераховуємо ціну
    calculatePrice();
}

/**
 * Замовлення хмарного хостингу
 */
function orderCloud() {
    // WHMCS інтеграція
    const whmcsUrl = 'https://bill.sthost.pro';

    // Формуємо опис конфігурації
    const configDescription = `Cloud Hosting: ${currentConfig.cpu} vCPU, ${currentConfig.ram}GB RAM, ${currentConfig.storage}GB SSD, ${currentConfig.bandwidth}GB Traffic`;

    // Відкриваємо WHMCS для замовлення
    const orderUrl = `${whmcsUrl}/cart.php?a=add&pid=cloud&configoption[cpu]=${currentConfig.cpu}&configoption[ram]=${currentConfig.ram}&configoption[storage]=${currentConfig.storage}&configoption[bandwidth]=${currentConfig.bandwidth}`;

    // Можна також показати модальне вікно для деталей
    showOrderModal();
}

/**
 * Показати модальне вікно замовлення
 */
function showOrderModal() {
    const modal = new bootstrap.Modal(document.getElementById('orderModal'));

    // Заповнюємо деталі конфігурації
    const configDetails = document.getElementById('order-config-details');
    if (configDetails) {
        let html = '<div class="config-summary">';
        html += `<div><strong>vCPU:</strong> ${currentConfig.cpu} ядер</div>`;
        html += `<div><strong>RAM:</strong> ${currentConfig.ram} ГБ</div>`;
        html += `<div><strong>SSD:</strong> ${currentConfig.storage} ГБ</div>`;
        html += `<div><strong>Трафік:</strong> ${currentConfig.bandwidth} ГБ/міс</div>`;

        if (Object.keys(currentConfig.options).length > 0) {
            html += '<div class="mt-2"><strong>Опції:</strong></div>';
            const optionNames = {
                'backup': 'Щоденні бекапи',
                'monitoring': 'Розширений моніторинг',
                'ssl': 'SSL Wildcard',
                'cdn': 'Premium CDN'
            };
            Object.keys(currentConfig.options).forEach(optionId => {
                html += `<div class="ms-3">• ${optionNames[optionId]}</div>`;
            });
        }

        const monthlyPrice = document.getElementById('monthly-price').textContent;
        html += `<div class="mt-3"><strong>Вартість:</strong> ₴${monthlyPrice}/міс</div>`;
        html += '</div>';

        configDetails.innerHTML = html;
    }

    modal.show();
}

/**
 * Відправка замовлення
 */
function submitOrder() {
    const form = document.getElementById('orderForm');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const data = {
        config: currentConfig,
        name: formData.get('name'),
        surname: formData.get('surname'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        domain: formData.get('domain'),
        comment: formData.get('comment')
    };

    // Показуємо індикатор завантаження
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Обробка...';

    // AJAX запит
    fetch('/api/cloud-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('success', 'Замовлення прийнято!', 'Ми зв\'яжемося з вами найближчим часом.');

            const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
            modal.hide();

            form.reset();

            // Перенаправлення на WHMCS для оплати
            if (result.whmcs_url) {
                setTimeout(() => {
                    window.location.href = result.whmcs_url;
                }, 2000);
            }
        } else {
            showNotification('error', 'Помилка', result.message || 'Не вдалося створити замовлення.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Помилка', 'Не вдалося відправити замовлення. Спробуйте пізніше.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/**
 * Збереження конфігурації
 */
function saveConfiguration() {
    const configJson = JSON.stringify(currentConfig, null, 2);

    // Створюємо blob і завантажуємо файл
    const blob = new Blob([configJson], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'cloud-hosting-config.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    showNotification('success', 'Конфігурацію збережено', 'Файл завантажено на ваш комп\'ютер.');
}

/**
 * Запит міграції
 */
function requestMigration() {
    // Перенаправлення на сторінку контактів з параметром
    window.location.href = '/pages/contacts.php?subject=migration';
}

/**
 * Показати сповіщення
 */
function showNotification(type, title, message) {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());

    const notification = document.createElement('div');
    notification.className = `notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <strong>${title}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

/**
 * Форматування числа з розділювачем тисяч
 */
function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

/**
 * Експорт функцій для глобального використання
 */
window.selectConfig = selectConfig;
window.orderCloud = orderCloud;
window.submitOrder = submitOrder;
window.saveConfiguration = saveConfiguration;
window.requestMigration = requestMigration;
