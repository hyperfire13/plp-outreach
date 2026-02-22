import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'admin-lte/dist/css/adminlte.min.css'
import '@fortawesome/fontawesome-free/css/all.min.css'
import 'admin-lte/dist/js/adminlte.min.js'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')