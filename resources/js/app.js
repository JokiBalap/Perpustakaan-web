import { createApp } from 'vue';
import LoginApp from './components/LoginApp.vue';
import LibraryApp from './components/LibraryApp.vue';

// Mount LoginApp if its element exists
const loginEl = document.getElementById('login-app');
if (loginEl) {
    const errors = JSON.parse(loginEl.getAttribute('data-errors') || '[]');
    const oldEmail = loginEl.getAttribute('data-old-email') || '';
    createApp(LoginApp, {
        initialErrors: errors,
        oldEmail: oldEmail
    }).mount('#login-app');
}

// Mount LibraryApp if its element exists
const libraryEl = document.getElementById('library-app');
if (libraryEl) {
    createApp(LibraryApp).mount('#library-app');
}
