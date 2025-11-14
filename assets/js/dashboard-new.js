const DashboardComponent = {
    template: `
        <div class="card">
            <h3>Огляд</h3>
            <p>Тут буде відображатися основна інформація: баланс, активні послуги та інше.</p>
        </div>
    `
};

const ProfileComponent = {
    template: `
        <div class="card">
            <h3>Налаштування профілю</h3>
            <div class="alert" v-if="message" :class="message.type">{{ message.text }}</div>
            <form @submit.prevent="changePassword">
                <div class="form-group">
                    <label for="current_password">Поточний пароль</label>
                    <input type="password" id="current_password" v-model="passwords.current" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Новий пароль</label>
                    <input type="password" id="new_password" v-model="passwords.new" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Підтвердіть новий пароль</label>
                    <input type="password" id="confirm_password" v-model="passwords.confirm" required>
                </div>
                <button type="submit" class="btn-primary" :disabled="loading">
                    {{ loading ? 'Збереження...' : 'Змінити пароль' }}
                </button>
            </form>
        </div>
    `,
    setup() {
        const passwords = Vue.ref({
            current: '',
            new: '',
            confirm: ''
        });
        const message = Vue.ref(null);
        const loading = Vue.ref(false);

        const changePassword = async () => {
            message.value = null;
            if (passwords.value.new !== passwords.value.confirm) {
                message.value = { type: 'alert-danger', text: 'Нові паролі не співпадають.' };
                return;
            }

            loading.value = true;
            try {
                const response = await fetch('/api/panel/v1/user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'change_password',
                        current_password: passwords.value.current,
                        new_password: passwords.value.new
                    })
                });

                const result = await response.json();

                if (result.success) {
                    message.value = { type: 'alert-success', text: result.message };
                    passwords.value = { current: '', new: '', confirm: '' }; // Очистити поля
                } else {
                    message.value = { type: 'alert-danger', text: result.message || 'Сталася помилка.' };
                }
            } catch (error) {
                message.value = { type: 'alert-danger', text: 'Помилка мережі. Спробуйте пізніше.' };
            } finally {
                loading.value = false;
            }
        };

        return { passwords, message, loading, changePassword };
    }
};


// Основний додаток Vue
const app = Vue.createApp({
    setup() {
        const activeView = Vue.ref('DashboardComponent');
        
        const menuItems = Vue.ref([
            { title: 'Огляд', component: 'DashboardComponent', route: '/dashboard', icon: 'bi bi-grid-fill' },
            { title: 'Мій профіль', component: 'ProfileComponent', route: '/profile', icon: 'bi bi-person-circle' },
            // Інші пункти меню будуть додані тут
        ]);

        const currentTitle = Vue.computed(() => {
            const activeItem = menuItems.value.find(item => item.component === activeView.value);
            return activeItem ? activeItem.title : 'Панель керування';
        });

        const setView = (view) => {
            activeView.value = view;
        };
        
        return {
            activeView,
            menuItems,
            currentTitle,
            setView
        };
    }
});

app.component('DashboardComponent', DashboardComponent);
app.component('ProfileComponent', ProfileComponent);
app.mount('#app');