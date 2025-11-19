/**
 * Logger Wrapper
 * Замена console.log для безопасности в production
 */

const isDevelopment = window.location.hostname === 'localhost' || 
                      window.location.hostname === '127.0.0.1' ||
                      window.location.hostname.includes('local') ||
                      window.location.hostname.includes('dev');

export const logger = {
    log: (...args) => {
        if (isDevelopment) {
            console.log(...args);
        }
    },
    
    error: (...args) => {
        if (isDevelopment) {
            console.error(...args);
        } else {
            // В production можно отправлять в Sentry или другой сервис
        }
    },
    
    warn: (...args) => {
        if (isDevelopment) {
            console.warn(...args);
        }
    },
    
    info: (...args) => {
        if (isDevelopment) {
            console.info(...args);
        }
    },
    
    debug: (...args) => {
        if (isDevelopment) {
            console.debug(...args);
        }
    },
    
    table: (data) => {
        if (isDevelopment && console.table) {
            console.table(data);
        }
    }
};

// Глобальный доступ (для совместимости со старым кодом)
if (typeof window !== 'undefined') {
    window.logger = logger;
}

// CommonJS экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = logger;
}
