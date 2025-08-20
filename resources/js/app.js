import { createApp } from 'vue';
import { createPinia } from 'pinia';
import axios from 'axios';
import App from './App.vue';
import router from './router';

const app = createApp(App);
const pinia = createPinia();

// Set base URL globally
axios.defaults.baseURL = "http://127.0.0.1:8000";

// Make Axios globally available
app.config.globalProperties.$axios = axios;
app.use(router);
app.use(pinia);

window.axios = axios;

app.mount('#app');

// Initialize auth store from localStorage
import { useAuthStore } from './stores/authStore';
const authStore = useAuthStore();
authStore.fetchUser();
