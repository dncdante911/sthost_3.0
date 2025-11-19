/**
 * Domain Transfer JavaScript
 */

class DomainTransfer {
    constructor() {
        this.form = document.getElementById('transferForm');
        this.domainInput = document.getElementById('domain');
        this.resultsContainer = document.getElementById('transferResults');
        
        if (this.form) {
            this.init();
        }
    }

    init() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        if (this.domainInput) {
            this.domainInput.addEventListener('input', (e) => this.validateDomain(e.target));
            this.domainInput.addEventListener('blur', (e) => {
                e.target.value = e.target.value.toLowerCase().trim();
            });
        }
    }

    validateDomain(input) {
        const value = input.value.toLowerCase().trim();
        const pattern = /^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i;
        
        if (value.length === 0) {
            input.classList.remove('is-invalid', 'is-valid');
            return false;
        }

        const isValid = pattern.test(value);
        input.classList.toggle('is-invalid', !isValid);
        input.classList.toggle('is-valid', isValid);
        
        return isValid;
    }

    async handleSubmit(e) {
        e.preventDefault();

        const formData = new FormData(this.form);
        const domain = formData.get('domain');
        const contactEmail = formData.get('contact_email');
        const agreeTerms = formData.get('agree_terms');

        if (!domain || !this.validateDomain(this.domainInput)) {
            this.showError('Невірний формат домену');
            return;
        }

        if (!contactEmail) {
            this.showError('Введіть email для зв\'язку');
            return;
        }

        if (!agreeTerms) {
            this.showError('Підтвердіть згоду з умовами послуг');
            return;
        }

        this.showLoading();

        try {
            const response = await fetch('/api/domains/transfer.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                }
            });

            const text = await response.text();
            let data;

            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON:', text);
                throw new Error('Сервер повернув некоректну відповідь');
            }

            if (data.error) {
                this.showError(data.error);
            } else if (data.success) {
                this.showSuccess(data);
            }

        } catch (error) {
            this.showError('Помилка: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    showLoading() {
        const submitBtn = this.form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Обробка...';
        }
    }

    hideLoading() {
        const submitBtn = this.form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Подати заявку';
        }
    }

    showError(message) {
        if (!this.resultsContainer) return;

        this.resultsContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Помилка!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        this.resultsContainer.scrollIntoView({ behavior: 'smooth' });
    }

    showSuccess(data) {
        if (!this.resultsContainer) return;

        const stepsHtml = data.next_steps.map(step => `<li>${step}</li>`).join('');

        this.resultsContainer.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>
                <h5>Заявка успішно подана!</h5>
                <p>${data.message}</p>
                
                <hr>
                
                <div class="mb-3">
                    <strong>Домен:</strong> ${data.domain}<br>
                    <strong>Вартість:</strong> ${data.price} грн
                </div>

                <h6><i class="bi bi-list-check"></i> Наступні кроки:</h6>
                <ol>${stepsHtml}</ol>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        this.form.reset();
        this.domainInput.classList.remove('is-valid', 'is-invalid');
        this.resultsContainer.scrollIntoView({ behavior: 'smooth' });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new DomainTransfer();
});
