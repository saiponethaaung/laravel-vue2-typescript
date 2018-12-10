import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        welcome: "welcome",
        isLogin: false,
        user: {},
        autheticating: false,
        chatBot: {
            block: -1,
            section: -1
        },
        token: localStorage.getItem('access_token')
    },
    mutations: {
        logout(state) {
            localStorage.removeItem('access_token');
            localStorage.removeItem('token_created');
            localStorage.removeItem('remember');
            state.isLogin = false;
            state.token = '';
        },
        setToken(state, {token, remember}) {
            localStorage.setItem('access_token', token);
            localStorage.setItem('token_created', new Date().getTime().toString());
            localStorage.setItem('remember', undefined!==remember && remember==true ? "true" : "false");
        },
        updateUserInfo(state, {user}) {
            state.user = user;
        },
        getToken() {
            return localStorage.getItem('access_token');
        },
        selectChatBot(state, {section, block}) {
            state.chatBot = {
                section: section,
                block: block
            };
        }
    }
} as StoreOptions<any>);