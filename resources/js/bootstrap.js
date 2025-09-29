import 'bootstrap';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// ------------------- ENV CONFIG -------------------
window._env_ = {
    APP_NAME: "Laravel",
    APP_ENV: "local",
    APP_DEBUG: true,
    APP_URL: "http://localhost",
    APP_LOCALE: "en",
    APP_FALLBACK_LOCALE: "en",
    APP_FAKER_LOCALE: "en_US",
    APP_MAINTENANCE_DRIVER: "file"
};

console.log("Environment loaded:", window._env_);

// ------------------- AXIOS SETUP -------------------
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ------------------- ECHO & PUSHER SETUP -------------------
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Optional: you can now access env values anywhere in JS
// Example: console.log("App URL:", window._env_.APP_URL);
