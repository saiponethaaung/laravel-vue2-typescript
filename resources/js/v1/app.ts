
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
import LoadingComponent from './components/common/LoadingComponent.vue';
import BuilderComponent from './components/common/BuilderComponent.vue';
import ButtonComponent from './components/common/builder/ButtonComponent.vue';
import AttributeSelectorComponent from './components/common/AttributeSelectorComponent.vue';

import Axios from 'axios';

let eventHub: any = new Vue();
window.fbSdkLoaded = false;

function logoutResponseHandler(error: any) {
    // if has response show the error
    if (error.response.status===401) {
        store.state.commit('logout');
        return;
    } else {
        return Promise.reject(error);
    }
}


Axios.interceptors.response.use(
    response => response,
    logoutResponseHandler
);

router.beforeEach(async (to, from, next) => {
    if(store.state.token!==null) {
        Axios.defaults.headers.common['Authorization'] = `Bearer ${store.state.token}`;

        await Axios({
            url: '/api/v1/user'
        }).then((res) => {
            console.log('res', res);
            store.state.isLogin = true;
            store.state.user = res.data;
        });
    }

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
Vue.component('loading-component', LoadingComponent);
Vue.component('builder-component', BuilderComponent);
Vue.component('button-component', ButtonComponent);
Vue.component('attribute-selector-component', AttributeSelectorComponent);

new Vue({
    router,
    store,
    el: '#app',
});