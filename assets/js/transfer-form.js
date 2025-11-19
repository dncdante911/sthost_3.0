/**
 * Domain Transfer Form Handler
 * Handles form submission and validation
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('transferForm');
        const submitBtn = document.getElementById('submitBtn');
        const alertContainer = document.getElementById('transferAlert');
        const domainInput = document.getElementById('domain');
        const emailInput = document.getElementById('contact_email');

        if (!form) return;

        // Domain validation pattern
        const domainPattern = /^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i;

        // Email validation pattern
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        /**
         * Validate domain input in real-time
         */
        if (domainInput) {
            domainInput.addEventListener('input', function() {
                const value = this.value.toLowerCase().trim();

                if (value.length === 0) {
                    this.classList.remove('is-valid', 'is-invalid');
                    return;
                }

                if (domainPattern.test(value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });

            // Auto-lowercase on blur
            domainInput.addEventListener('blur', function() {
                this.value = this.value.toLowerCase().trim();
            });
        }

        /**
         * Validate email input in real-time
         */
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const value = this.value.trim();

                if (value.length === 0) {
                    this.classList.remove('is-valid', 'is-invalid');
                    return;
                }

                if (emailPattern.test(value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        }

        /**
         * Handle form submission
         */
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(form);
            const domain = formData.get('domain').toLowerCase().trim();
            const email = formData.get('contact_email').trim();
            const agreeTerms = formData.get('agree_terms');

            // Validate domain
            if (!domainPattern.test(domain)) {
                showAlert('error', 'Невірний формат домену. Приклад: example.com');
                domainInput.focus();
                return;
            }

            // Validate email
            if (!emailPattern.test(email)) {
                showAlert('error', 'Невірний формат email адреси');
                emailInput.focus();
                return;
            }

            // Check agreement
            if (!agreeTerms) {
                showAlert('error', 'Підтвердіть згоду з умовами надання послуг');
                return;
            }

            // Set loading state
            setLoadingState(true);

            try {
                // Submit to API
                const response = await fetch('/api/domains/transfer.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Parse response
                const text = await response.text();
                let data;

                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Сервер повернув некоректну відповідь');
                }

                // Handle response
                if (data.success) {
                    showSuccessMessage(data);
                    form.reset();
                    domainInput.classList.remove('is-valid', 'is-invalid');
                    emailInput.classList.remove('is-valid', 'is-invalid');
                } else if (data.error) {
                    showAlert('error', data.error);
                }

            } catch (error) {
                console.error('Transfer error:', error);
                showAlert('error', 'Помилка відправки форми: ' + error.message);
            } finally {
                setLoadingState(false);
            }
        });

        /**
         * Set loading state for submit button
         */
        function setLoadingState(loading) {
            if (loading) {
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.querySelector('span').textContent = 'Відправка...';
            } else {
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
                submitBtn.querySelector('span').textContent = 'Подати заявку на трансфер';
            }
        }

        /**
         * Show alert message
         */
        function showAlert(type, message) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const iconClass = type === 'error' ? 'bi-exclamation-triangle' : 'bi-info-circle';

            alertContainer.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="bi ${iconClass} me-2"></i>
                    <strong>${type === 'error' ? 'Помилка!' : 'Увага!'}</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            alertContainer.classList.add('show');
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        /**
         * Show success message with details
         */
        function showSuccessMessage(data) {
            const stepsHtml = data.next_steps
                ? data.next_steps.map(step => `<li>${step}</li>`).join('')
                : '';

            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-check-circle-fill fs-3 me-3 text-success"></i>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-2">
                                <i class="bi bi-party-popper"></i> Заявка успішно подана!
                            </h5>
                            <p class="mb-2">${data.message}</p>

                            <hr>

                            <div class="mb-3">
                                <strong><i class="bi bi-globe"></i> Домен:</strong>
                                <span class="badge bg-primary">${data.domain}</span>
                                <br>
                                <strong><i class="bi bi-cash-coin"></i> Вартість:</strong>
                                <span class="badge bg-success">${data.price} грн</span>
                                <small class="text-muted">(включає продовження на 1 рік)</small>
                            </div>

                            ${stepsHtml ? `
                                <div class="mt-3">
                                    <strong><i class="bi bi-list-check"></i> Наступні кроки:</strong>
                                    <ol class="mb-0 mt-2">${stepsHtml}</ol>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            alertContainer.classList.add('show');
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
})();
