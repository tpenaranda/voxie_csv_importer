import Vue from 'vue'
import App from './App.vue'
import BootstrapVue from 'bootstrap-vue'
import VueLodash from 'vue-lodash'

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(BootstrapVue)
Vue.use(VueLodash)

Vue.prototype.$axios = require('axios')
Vue.prototype.$axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

Vue.prototype.$nextStep = function () { this.$root.step++ }

new Vue({
    render: h => h(App),
    data: {
        step: 1
    }
}).$mount('#app')