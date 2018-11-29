
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './configuration/bootstrap';
import Vue from 'vue';
import router from './configuration/route';
import store from './configuration/store';
// import { VueMasonryPlugin } from 'vue-masonry';
import App from './App.vue';
import PopupComponent from './components/common/PopupComponent.vue';
import BuilderComponent from './components/common/BudilerComponent.vue';
import axios from 'axios';

let eventHub: any = new Vue();

function logoutResponseHandler(error: any) {
    // if has response show the error
    if (error.response.status===401) {
        // default handle for status 401
        return;
    } else {
        return Promise.reject(error);
    }
}


axios.interceptors.response.use(
    response => response,
    logoutResponseHandler
);

router.beforeEach((to, from, next) => {
    next();
});

/*
 Vue.use(VueMasonryPlugin); 
 */

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
// Vue.prototype.$eventHub = new Vue();
Vue.component('app', App);
Vue.component('popup-component', PopupComponent);
Vue.component('builder-component', BuilderComponent);

new Vue({
    router,
    store,
    el: '#app'
});