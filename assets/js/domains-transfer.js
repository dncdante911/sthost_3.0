/**
 * Domain Transfer Page JavaScript
 * /assets/css/pages/domains-transfer.js
 */

class TransferManager {
    constructor() {
        this.form = document.getElementById('transferForm');
        this.domainInput = document.getElementById('domain');
        this.authCodeInput = document.getElementById('auth_code');
        this.emailInput = document.getElementById('contact_email');
        this.phoneInput = document.getElementById('phone');
        this.notesInput = document.getElementById('notes');
        this.agreeCheckbox = document.getElementById('agree_terms');
        this.submitBtn = document.querySelector('.btn-submit-transfer');
        
        this.init();
    }

// Additional helper functions
window.openChat = function() {
    // Integration with live chat widget
    if (window.tidioChatApi) {
        window.tidioChatApi.open();
    } else {
        alert('Чат тимчасово недоступний. Будь ласка, напишіть нам на info@sthost.pro');
    }
};

window.copyToClipboard = function(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Скопійовано';
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize transfer manager
    window.transferManager = new TransferManager();
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add animation classes on scroll
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(element => {
            const rect = element.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
            
            if (isVisible) {
                element.classList.add('animated');
            }
        });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Initial check
    
    // FAQ Accordion enhancements
    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Add animation to chevron icon
            const icon = this.querySelector('.accordion-icon');
            if (icon) {
                icon.style.transform = this.classList.contains('collapsed') ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    });
});

// Export for use in other scripts
window.TransferManager = TransferManager;

// Add toast notification styles dynamically
const toastStyles = `
    <style>
        .toast-notification {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 16px 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .toast-notification.show {
            transform: translateX(0);
        }
        
        .toast-notification.success {
            border-left: 4px solid #10b981;
        }
        
        .toast-notification.error {
            border-left: 4px solid #ef4444;
        }
        
        .toast-notification.info {
            border-left: 4px solid #3b82f6;
        }
        
        .toast-notification.warning {
            border-left: 4px solid #f59e0b;
        }
        
        .toast-notification i {
            font-size: 1.25rem;
        }
        
        .toast-notification.success i {
            color: #10b981;
        }
        
        .toast-notification.error i {
            color: #ef4444;
        }
        
        .toast-notification.info i {
            color: #3b82f6;
        }
        
        .toast-notification.warning i {
            color: #f59e0b;
        }
        
        /* Animation classes */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Form loading overlay */
        .form-loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Process step animations */
        @keyframes stepPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
            }
        }
        
        .process-step:hover .step-number {
            animation: stepPulse 1.5s infinite;
        }
        
        /* Success modal animations */
        @keyframes successCheckmark {
            0% {
                transform: scale(0) rotate(45deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.2) rotate(45deg);
            }
            100% {
                transform: scale(1) rotate(45deg);
                opacity: 1;
            }
        }
        
        .modal.show .bi-check-circle {
            animation: successCheckmark 0.6s ease;
        }
    </style>
`;

// Add styles to document
document.head.insertAdjacentHTML('beforeend', toastStyles);
    
    init() {
        this.bindEvents();
        this.initValidation();
        this.initTooltips();
        this.initAnimations();
        this.loadSavedData();
    }
    
    bindEvents() {
        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        // Real-time validation
        if (this.domainInput) {
            this.domainInput.addEventListener('blur', () => this.validateDomain());
            this.domainInput.addEventListener('input', () => this.formatDomain());
        }
        
        if (this.authCodeInput) {
            this.authCodeInput.addEventListener('input', () => this.formatAuthCode());
            this.authCodeInput.addEventListener('blur', () => this.validateAuthCode());
        }
        
        if (this.emailInput) {
            this.emailInput.addEventListener('blur', () => this.validateEmail());
        }
        
        if (this.phoneInput) {
            this.phoneInput.addEventListener('input', () => this.formatPhone());
        }
        
        // Checkbox state
        if (this.agreeCheckbox) {
            this.agreeCheckbox.addEventListener('change', () => this.toggleSubmitButton());
        }
        
        // Auto-save form data
        this.form?.addEventListener('input', () => this.saveFormData());
        
        // Clear form button
        const clearBtn = document.querySelector('.btn-clear-form');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => this.clearForm());
        }
        
        // Check domain availability
        const checkBtn = document.querySelector('.btn-check-domain');
        if (checkBtn) {
            checkBtn.addEventListener('click', () => this.checkDomainAvailability());
        }
    }
    
    initValidation() {
        // Bootstrap validation
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }
    
    initTooltips() {
        // Initialize Bootstrap tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
        
        // Custom tooltips for form fields
        this.addCustomTooltips();
    }
    
    addCustomTooltips() {
        if (this.authCodeInput) {
            const tooltip = document.createElement('span');
            tooltip.className = 'auth-code-tooltip';
            tooltip.innerHTML = '<i class="bi bi-info-circle"></i> Код авторизації можна отримати у поточного реєстратора';
            this.authCodeInput.parentElement.appendChild(tooltip);
        }
    }
    
    initAnimations() {
        // Animate elements on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.process-step, .feature-box').forEach(el => {
            observer.observe(el);
        });
    }
    
    formatDomain() {
        if (!this.domainInput) return;
        
        let value = this.domainInput.value.toLowerCase();
        // Remove protocol if present
        value = value.replace(/^https?:\/\//, '');
        // Remove www if present
        value = value.replace(/^www\./, '');
        // Remove trailing slash
        value = value.replace(/\/$/, '');
        // Remove spaces
        value = value.replace(/\s/g, '');
        
        this.domainInput.value = value;
    }
    
    formatAuthCode() {
        if (!this.authCodeInput) return;
        
        // Format auth code to uppercase and remove spaces
        let value = this.authCodeInput.value.toUpperCase();
        value = value.replace(/[^A-Z0-9-]/g, '');
        
        // Add visual separators every 4 characters (display only)
        if (value.length > 4) {
            const formatted = value.match(/.{1,4}/g).join('-');
            this.authCodeInput.value = formatted;
        } else {
            this.authCodeInput.value = value;
        }
    }
    
    formatPhone() {
        if (!this.phoneInput) return;
        
        let value = this.phoneInput.value.replace(/\D/g, '');
        
        // Format as Ukrainian phone number
        if (value.startsWith('380')) {
            if (value.length > 3) value = '+380 ' + value.slice(3);
            if (value.length > 7) value = value.slice(0, 7) + ' ' + value.slice(7);
            if (value.length > 11) value = value.slice(0, 11) + ' ' + value.slice(11);
            if (value.length > 14) value = value.slice(0, 14) + ' ' + value.slice(14, 16);
        } else if (value.startsWith('0')) {
            if (value.length > 3) value = value.slice(0, 3) + ' ' + value.slice(3);
            if (value.length > 7) value = value.slice(0, 7) + ' ' + value.slice(7);
            if (value.length > 10) value = value.slice(0, 10) + ' ' + value.slice(10, 12);
        }
        
        this.phoneInput.value = value;
    }
    
    validateDomain() {
        if (!this.domainInput) return true;
        
        const domain = this.domainInput.value;
        const domainRegex = /^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,10}$/i;
        
        if (!domain) {
            this.showFieldError(this.domainInput, 'Введіть доменне ім\'я');
            return false;
        }
        
        if (!domainRegex.test(domain)) {
            this.showFieldError(this.domainInput, 'Невірний формат домену');
            return false;
        }
        
        // Check if domain is transferable
        const nonTransferableTLDs = ['.pp.ua', '.edu.ua', '.gov.ua', '.mil.ua'];
        const isNonTransferable = nonTransferableTLDs.some(tld => domain.endsWith(tld));
        
        if (isNonTransferable) {
            this.showFieldError(this.domainInput, 'Цей домен не може бути перенесений');
            return false;
        }
        
        this.clearFieldError(this.domainInput);
        return true;
    }
    
    validateAuthCode() {
        if (!this.authCodeInput) return true;
        
        const authCode = this.authCodeInput.value.replace(/[^A-Z0-9]/g, '');
        
        if (!authCode) {
            this.showFieldError(this.authCodeInput, 'Введіть код авторизації');
            return false;
        }
        
        if (authCode.length < 6) {
            this.showFieldError(this.authCodeInput, 'Код авторизації занадто короткий');
            return false;
        }
        
        this.clearFieldError(this.authCodeInput);
        return true;
    }
    
    validateEmail() {
        if (!this.emailInput) return true;
        
        const email = this.emailInput.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!email) {
            this.showFieldError(this.emailInput, 'Введіть email');
            return false;
        }
        
        if (!emailRegex.test(email)) {
            this.showFieldError(this.emailInput, 'Невірний формат email');
            return false;
        }
        
        this.clearFieldError(this.emailInput);
        return true;
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        let feedback = field.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentElement.appendChild(feedback);
        }
        feedback.textContent = message;
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
    
    toggleSubmitButton() {
        if (!this.submitBtn || !this.agreeCheckbox) return;
        
        if (this.agreeCheckbox.checked) {
            this.submitBtn.disabled = false;
            this.submitBtn.classList.remove('disabled');
        } else {
            this.submitBtn.disabled = true;
            this.submitBtn.classList.add('disabled');
        }
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        // Validate all fields
        const isDomainValid = this.validateDomain();
        const isAuthCodeValid = this.validateAuthCode();
        const isEmailValid = this.validateEmail();
        
        if (!isDomainValid || !isAuthCodeValid || !isEmailValid) {
            this.showToast('Будь ласка, виправте помилки у формі', 'error');
            return;
        }
        
        // Show loading state
        this.setLoadingState(true);
        
        // Prepare form data
        const formData = new FormData(this.form);
        formData.append('action', 'start_transfer');
        
        try {
            // Submit form via AJAX
            const response = await fetch('/api/domain-transfer', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast('Заявка на трансфер успішно подана!', 'success');
                this.clearForm();
                this.showSuccessModal(result.data);
            } else {
                this.showToast(result.message || 'Помилка при поданні заявки', 'error');
            }
        } catch (error) {
            console.error('Transfer submission error:', error);
            this.showToast('Помилка з\'єднання. Спробуйте пізніше.', 'error');
        } finally {
            this.setLoadingState(false);
        }
    }
    
    setLoadingState(loading) {
        if (loading) {
            this.form?.classList.add('form-loading');
            if (this.submitBtn) {
                this.submitBtn.disabled = true;
                this.submitBtn.innerHTML = '<span class="loading-spinner"></span> Обробка...';
            }
        } else {
            this.form?.classList.remove('form-loading');
            if (this.submitBtn) {
                this.submitBtn.disabled = false;
                this.submitBtn.innerHTML = '<i class="bi bi-arrow-right-circle"></i> Почати трансфер';
            }
        }
    }
    
    async checkDomainAvailability() {
        if (!this.domainInput) return;
        
        const domain = this.domainInput.value;
        if (!domain) {
            this.showToast('Введіть доменне ім\'я', 'warning');
            return;
        }
        
        // Show checking state
        const checkBtn = document.querySelector('.btn-check-domain');
        const originalText = checkBtn.innerHTML;
        checkBtn.innerHTML = '<span class="loading-spinner"></span> Перевірка...';
        checkBtn.disabled = true;
        
        try {
            const response = await fetch(`/api/check-domain?domain=${encodeURIComponent(domain)}`);
            const result = await response.json();
            
            if (result.available === false) {
                // Domain is registered (good for transfer)
                this.showDomainStatus('registered', domain);
            } else {
                // Domain is available (cannot transfer)
                this.showDomainStatus('available', domain);
            }
        } catch (error) {
            console.error('Domain check error:', error);
            this.showToast('Помилка перевірки домену', 'error');
        } finally {
            checkBtn.innerHTML = originalText;
            checkBtn.disabled = false;
        }
    }
    
    showDomainStatus(status, domain) {
        const statusContainer = document.getElementById('domainStatus');
        if (!statusContainer) return;

        statusContainer.innerHTML = '';

        const alert = document.createElement('div');
        if (status === 'registered') {
            alert.className = 'alert alert-success';

            const icon = document.createElement('i');
            icon.className = 'bi bi-check-circle-fill';

            const div = document.createElement('div');

            const title = document.createElement('strong');
            title.textContent = 'Домен ' + domain + ' зареєстрований';

            const br = document.createElement('br');

            const text = document.createElement('span');
            text.textContent = 'Ви можете розпочати процес трансферу';

            div.appendChild(title);
            div.appendChild(br);
            div.appendChild(text);

            alert.appendChild(icon);
            alert.appendChild(div);
        } else {
            alert.className = 'alert alert-info';

            const icon = document.createElement('i');
            icon.className = 'bi bi-info-circle-fill';

            const div = document.createElement('div');

            const title = document.createElement('strong');
            title.textContent = 'Домен ' + domain + ' вільний';

            const br = document.createElement('br');

            const text = document.createElement('span');
            text.textContent = 'Цей домен не зареєстрований і не може бути перенесений. ';

            const link = document.createElement('a');
            link.href = '/domains/register?domain=' + encodeURIComponent(domain);
            link.textContent = 'Зареєструвати домен';

            div.appendChild(title);
            div.appendChild(br);
            div.appendChild(text);
            div.appendChild(link);

            alert.appendChild(icon);
            alert.appendChild(div);
        }

        statusContainer.appendChild(alert);
        statusContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    clearForm() {
        if (!this.form) return;
        
        this.form.reset();
        this.form.classList.remove('was-validated');
        
        // Clear validation states
        this.form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid');
        });
        
        // Clear saved data
        localStorage.removeItem('transferFormData');
        
        // Clear status messages
        const statusContainer = document.getElementById('domainStatus');
        if (statusContainer) {
            statusContainer.innerHTML = '';
        }
    }
    
    saveFormData() {
        if (!this.form) return;
        
        const formData = {
            domain: this.domainInput?.value || '',
            auth_code: this.authCodeInput?.value || '',
            email: this.emailInput?.value || '',
            phone: this.phoneInput?.value || '',
            notes: this.notesInput?.value || ''
        };
        
        localStorage.setItem('transferFormData', JSON.stringify(formData));
    }
    
    loadSavedData() {
        const savedData = localStorage.getItem('transferFormData');
        if (!savedData) return;
        
        try {
            const data = JSON.parse(savedData);
            
            if (this.domainInput && data.domain) {
                this.domainInput.value = data.domain;
            }
            if (this.authCodeInput && data.auth_code) {
                this.authCodeInput.value = data.auth_code;
            }
            if (this.emailInput && data.email) {
                this.emailInput.value = data.email;
            }
            if (this.phoneInput && data.phone) {
                this.phoneInput.value = data.phone;
            }
            if (this.notesInput && data.notes) {
                this.notesInput.value = data.notes;
            }
            
            this.showToast('Дані форми відновлено', 'info');
        } catch (error) {
            console.error('Error loading saved form data:', error);
        }
    }
    
    showSuccessModal(data) {
        const modalDiv = document.createElement('div');
        modalDiv.className = 'modal fade';
        modalDiv.id = 'transferSuccessModal';
        modalDiv.setAttribute('tabindex', '-1');

        const dialog = document.createElement('div');
        dialog.className = 'modal-dialog modal-dialog-centered';

        const content = document.createElement('div');
        content.className = 'modal-content';

        // Header
        const header = document.createElement('div');
        header.className = 'modal-header bg-success text-white';

        const headerIcon = document.createElement('i');
        headerIcon.className = 'bi bi-check-circle-fill';

        const title = document.createElement('h5');
        title.className = 'modal-title';
        title.appendChild(headerIcon);
        const titleText = document.createElement('span');
        titleText.textContent = ' Заявка прийнята!';
        title.appendChild(titleText);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close btn-close-white';
        closeBtn.setAttribute('data-bs-dismiss', 'modal');

        header.appendChild(title);
        header.appendChild(closeBtn);

        // Body
        const body = document.createElement('div');
        body.className = 'modal-body';

        const centerDiv = document.createElement('div');
        centerDiv.className = 'text-center py-3';

        const bodyIcon = document.createElement('i');
        bodyIcon.className = 'bi bi-check-circle text-success';
        bodyIcon.style.fontSize = '4rem';

        const bodyTitle = document.createElement('h4');
        bodyTitle.className = 'mt-3';
        bodyTitle.textContent = 'Трансфер розпочато';

        const bodyP = document.createElement('p');
        bodyP.className = 'text-muted';
        const pText = document.createElement('span');
        pText.textContent = 'Заявка на трансфер домену ';
        const pDomain = document.createElement('strong');
        pDomain.textContent = data.domain;
        const pEnd = document.createElement('span');
        pEnd.textContent = ' успішно подана.';
        bodyP.appendChild(pText);
        bodyP.appendChild(pDomain);
        bodyP.appendChild(pEnd);

        centerDiv.appendChild(bodyIcon);
        centerDiv.appendChild(bodyTitle);
        centerDiv.appendChild(bodyP);

        const alert = document.createElement('div');
        alert.className = 'alert alert-info';

        const alertIcon = document.createElement('i');
        alertIcon.className = 'bi bi-info-circle';

        const alertTitle = document.createElement('strong');
        alertTitle.textContent = 'Що далі?';

        const ol = document.createElement('ol');
        ol.className = 'mb-0 mt-2';

        const li1 = document.createElement('li');
        li1.textContent = 'Ми надішлемо інструкції на ' + data.email;

        const li2 = document.createElement('li');
        li2.textContent = 'Підтвердіть трансфер у поточного реєстратора';

        const li3 = document.createElement('li');
        li3.textContent = 'Очікуйте завершення (5-7 днів)';

        ol.appendChild(li1);
        ol.appendChild(li2);
        ol.appendChild(li3);

        alert.appendChild(alertIcon);
        alert.appendChild(alertTitle);
        alert.appendChild(ol);

        body.appendChild(centerDiv);
        body.appendChild(alert);

        // Footer
        const footer = document.createElement('div');
        footer.className = 'modal-footer';

        const closeFooterBtn = document.createElement('button');
        closeFooterBtn.type = 'button';
        closeFooterBtn.className = 'btn btn-secondary';
        closeFooterBtn.setAttribute('data-bs-dismiss', 'modal');
        closeFooterBtn.textContent = 'Закрити';

        const domainLink = document.createElement('a');
        domainLink.href = '/domains';
        domainLink.className = 'btn btn-primary';
        const linkIcon = document.createElement('i');
        linkIcon.className = 'bi bi-arrow-left';
        const linkText = document.createElement('span');
        linkText.textContent = ' До доменів';
        domainLink.appendChild(linkIcon);
        domainLink.appendChild(linkText);

        footer.appendChild(closeFooterBtn);
        footer.appendChild(domainLink);

        content.appendChild(header);
        content.appendChild(body);
        content.appendChild(footer);

        dialog.appendChild(content);
        modalDiv.appendChild(dialog);

        // Add modal to page
        document.body.appendChild(modalDiv);

        // Show modal
        const modal = new bootstrap.Modal(modalDiv);
        modal.show();

        // Remove modal after hidden
        modalDiv.addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer') || this.createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;

        const icon = document.createElement('i');
        const iconMap = {
            success: 'check-circle',
            error: 'x-circle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        icon.className = `bi bi-${iconMap[type] || 'info-circle'}`;

        const span = document.createElement('span');
        span.textContent = message;

        toast.appendChild(icon);
        toast.appendChild(span);
        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    
    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(container);
        return container;
    }