import Vue from 'vue'
import App from './App.vue'
import BootstrapVue from 'bootstrap-vue'

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(BootstrapVue)

Vue.prototype.$axios = require('axios')
Vue.prototype.$axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

new Vue({
    render: h => h(App),
}).$mount('#app')