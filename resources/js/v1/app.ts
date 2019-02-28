
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
import FullScreenLoadingComponent from './components/common/FullScreenLoadingComponent.vue';
import BuilderComponent from './components/common/BuilderComponent.vue';
import ButtonComponent from './components/common/builder/ButtonComponent.vue';
import AttributeSelectorComponent from './components/common/AttributeSelectorComponent.vue';
import DropDownComponent from './components/common/SpinnerDropDownComponent.vue';
import TimeInputComponent from './components/common/TimeInputComponent.vue';

import { setupCalendar, Calendar, DatePicker} from 'v-calendar';

import Axios, { CancelTokenSource } from 'axios';
import AjaxErrorHandler from './utils/AjaxErrorHandler';

setupCalendar({
    firstDayOfWeek: 2,
});

let eventHub: any = new Vue();
let userLoadingToken: CancelTokenSource = Axios.CancelToken.source();
let ajaxHandler = new AjaxErrorHandler();
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
    let proceedNext = true;

    if(store.state.token!==null) {
        Axios.defaults.headers.common['Authorization'] = `Bearer ${store.state.token}`;

        userLoadingToken.cancel();
        userLoadingToken = Axios.CancelToken.source();

        store.state.autheticating = true;
        
        await Axios({
            url: '/api/v1/user',
            cancelToken: userLoadingToken.token
        }).then((res) => {
            if(to.name==='login' || to.name==='register' || to.name==='verify') {
                proceedNext = false;
                router.push({name: 'home'});
            } else {
                store.state.isLogin = true;
                store.state.user = res.data;
            }
        }).catch(err => {
            if(err.response) {
                let mesg = ajaxHandler.globalHandler(err, 'Failed to authenticate!');
                alert(mesg);
            }
        });

        if(proceedNext) {
            store.state.autheticating = false;
        }
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
Vue.component('v-calendar', Calendar);
Vue.component('v-date-picker', DatePicker);
Vue.component('dropdown-keybase-component', DropDownComponent);
Vue.component('time-input-component', TimeInputComponent);
Vue.component('fullscreen-loading-component', FullScreenLoadingComponent);

new Vue({
    router,
    store,
    el: '#app',
});