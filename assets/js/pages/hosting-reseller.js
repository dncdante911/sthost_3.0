/**
 * hosting-reseller.js - JavaScript для сторінки реселерського хостингу
 * Обробка калькулятора прибутку, замовлення планів та форм
 */

// Конфігурація комісій для різних планів
const commissionRates = {
    'start': 0.30,      // 30%
    'pro': 0.40,        // 40%
    'business': 0.45,   // 45%
    'enterprise': 0.50  // 50%
};

// Вартість додаткових послуг (на клієнта)
const additionalServices = {
    'ssl-sales': 50,
    'domain-sales': 100,
    'backup-sales': 30,
    'support-sales': 75
};

// Chart.js інстанс для графіка
let profitChart = null;

/**
 * Ініціалізація калькулятора при завантаженні сторінки
 */
document.addEventListener('DOMContentLoaded', function() {
    // Елементи форми
    const clientsSlider = document.getElementById('clients-slider');
    const priceSlider = document.getElementById('price-slider');
    const resellerPlan = document.getElementById('reseller-plan');

    // Чекбокси додаткових послуг
    const sslSales = document.getElementById('ssl-sales');
    const domainSales = document.getElementById('domain-sales');
    const backupSales = document.getElementById('backup-sales');
    const supportSales = document.getElementById('support-sales');

    if (!clientsSlider || !priceSlider || !resellerPlan) {
        console.warn('Calculator elements not found, skipping initialization');
        return;
    }

    // Ініціалізація графіка
    initProfitChart();

    // Початковий розрахунок
    calculateProfit();

    // Слухачі подій для слайдерів
    clientsSlider.addEventListener('input', function() {
        document.getElementById('clients-value').textContent = this.value;
        calculateProfit();
    });

    priceSlider.addEventListener('input', function() {
        document.getElementById('price-value').textContent = this.value;
        calculateProfit();
    });

    // Слухач для зміни плану
    resellerPlan.addEventListener('change', calculateProfit);

    // Слухачі для чекбоксів
    [sslSales, domainSales, backupSales, supportSales].forEach(checkbox => {
        if (checkbox) {
            checkbox.addEventListener('change', calculateProfit);
        }
    });
});

/**
 * Розрахунок прибутку на основі введених параметрів
 */
function calculateProfit() {
    // Отримання значень
    const clients = parseInt(document.getElementById('clients-slider').value);
    const avgPrice = parseInt(document.getElementById('price-slider').value);
    const plan = document.getElementById('reseller-plan').value;

    // Отримання комісії для обраного плану
    const commission = commissionRates[plan];

    // Розрахунок базового прибутку від хостингу
    const baseProfit = clients * avgPrice * commission;

    // Розрахунок додаткових послуг
    let additionalProfit = 0;

    if (document.getElementById('ssl-sales')?.checked) {
        additionalProfit += clients * additionalServices['ssl-sales'];
    }

    if (document.getElementById('domain-sales')?.checked) {
        additionalProfit += clients * additionalServices['domain-sales'];
    }

    if (document.getElementById('backup-sales')?.checked) {
        additionalProfit += clients * additionalServices['backup-sales'];
    }

    if (document.getElementById('support-sales')?.checked) {
        additionalProfit += clients * additionalServices['support-sales'];
    }

    // Загальний прибуток
    const monthlyProfit = baseProfit + additionalProfit;
    const yearlyProfit = monthlyProfit * 12;

    // Оновлення інтерфейсу
    updateProfitDisplay(clients, avgPrice, commission, additionalProfit, monthlyProfit, yearlyProfit);

    // Оновлення графіка
    updateProfitChart(baseProfit, additionalProfit);
}

/**
 * Оновлення відображення результатів розрахунку
 */
function updateProfitDisplay(clients, avgPrice, commission, additional, monthly, yearly) {
    // Оновлення summary
    document.getElementById('summary-clients').textContent = clients;
    document.getElementById('summary-price').textContent = avgPrice;
    document.getElementById('summary-commission').textContent = Math.round(commission * 100) + '%';
    document.getElementById('summary-additional').textContent = formatNumber(additional);

    // Оновлення прибутку з анімацією
    animateValue('monthly-profit', monthly);
    animateValue('yearly-profit', yearly);
}

/**
 * Анімація зміни числового значення
 */
function animateValue(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const currentValue = parseInt(element.textContent.replace(/\s/g, '')) || 0;
    const duration = 500; // мс
    const steps = 30;
    const increment = (targetValue - currentValue) / steps;
    const stepDuration = duration / steps;

    let step = 0;
    const timer = setInterval(() => {
        step++;
        const newValue = Math.round(currentValue + (increment * step));
        element.textContent = formatNumber(newValue);

        if (step >= steps) {
            element.textContent = formatNumber(targetValue);
            clearInterval(timer);
        }
    }, stepDuration);
}

/**
 * Форматування числа з розділювачем тисяч
 */
function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

/**
 * Ініціалізація графіка прибутку
 */
function initProfitChart() {
    const canvas = document.getElementById('profitChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // Знищення попереднього графіка якщо існує
    if (profitChart) {
        profitChart.destroy();
    }

    profitChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Базовий прибуток', 'Додаткові послуги'],
            datasets: [{
                data: [3000, 750],
                backgroundColor: [
                    'rgba(255, 255, 255, 0.9)',
                    'rgba(255, 255, 255, 0.5)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: 'white',
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return label + ': ₴' + formatNumber(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Оновлення даних графіка
 */
function updateProfitChart(baseProfit, additionalProfit) {
    if (!profitChart) return;

    profitChart.data.datasets[0].data = [
        Math.round(baseProfit),
        Math.round(additionalProfit)
    ];
    profitChart.update('none'); // Без анімації для швидкості
}

/**
 * Замовлення реселерського плану
 */
function orderReseller(planType) {
    const planNames = {
        'start': 'Reseller Start',
        'pro': 'Reseller Pro',
        'business': 'Reseller Business',
        'enterprise': 'Reseller Enterprise'
    };

    const planName = planNames[planType] || planType;

    // Показуємо модальне вікно з підтвердженням
    if (confirm(`Ви обрали план "${planName}". Бажаєте продовжити замовлення?`)) {
        // Відкриваємо форму партнерства з попередньо обраним планом
        showPartnerForm(planType);
    }
}

/**
 * Показати форму партнерства
 */
function showPartnerForm(preselectedPlan = null) {
    const modal = new bootstrap.Modal(document.getElementById('partnerModal'));

    // Якщо передано попередньо обраний план
    if (preselectedPlan) {
        const planSelect = document.querySelector('#partnerForm select[name="plan"]');
        if (planSelect) {
            planSelect.value = preselectedPlan;
        }
    }

    modal.show();
}

/**
 * Початок партнерства (з калькулятора)
 */
function startPartnership() {
    const selectedPlan = document.getElementById('reseller-plan').value;
    showPartnerForm(selectedPlan);
}

/**
 * Відправка форми партнерства
 */
function submitPartnerForm() {
    const form = document.getElementById('partnerForm');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Збір даних форми
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    // Показуємо індикатор завантаження
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Відправка...';

    // AJAX запит до сервера
    fetch('/api/partner-request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Показуємо повідомлення про успіх
            showNotification('success', 'Дякуємо за заявку!', 'Ми зв\'яжемося з вами найближчим часом.');

            // Закриваємо модальне вікно
            const modal = bootstrap.Modal.getInstance(document.getElementById('partnerModal'));
            modal.hide();

            // Очищаємо форму
            form.reset();
        } else {
            showNotification('error', 'Помилка', result.message || 'Не вдалося відправити заявку. Спробуйте пізніше.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Помилка', 'Не вдалося відправити заявку. Перевірте з\'єднання та спробуйте ще раз.');
    })
    .finally(() => {
        // Відновлюємо кнопку
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/**
 * Показати сповіщення
 */
function showNotification(type, title, message) {
    // Видаляємо попередні сповіщення
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());

    // Створюємо нове сповіщення
    const notification = document.createElement('div');
    notification.className = `notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    notification.innerHTML = `
        <strong>${title}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Автоматично видаляємо через 5 секунд
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

/**
 * Експорт функцій для глобального використання
 */
window.calculateProfit = calculateProfit;
window.orderReseller = orderReseller;
window.showPartnerForm = showPartnerForm;
window.startPartnership = startPartnership;
window.submitPartnerForm = submitPartnerForm;
