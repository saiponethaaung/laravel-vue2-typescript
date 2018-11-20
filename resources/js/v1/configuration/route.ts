import Vue from 'vue';
import VueRouter from 'vue-router';

import Example from '../Example.vue';

Vue.use(VueRouter);

export default new VueRouter({
    base: '/',
    mode: 'history',
    routes: [
        {
            path: "/",
            name: "home",
            meta: {},
            component: Example
        }
    ]
});