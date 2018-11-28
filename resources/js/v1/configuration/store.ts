import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        welcome: "welcome",
        isLogin: true,
        user: {},
        autheticating: false,
        chatBot: {
            block: -1,
            section: -1
        }
    },
    mutations: {
        logout(state) {
            localStorage.removeItem('user-token');
            localStorage.removeItem('login');
            localStorage.removeItem('remember');
            state.islogin = false;
            state.token = '';
        },
        setToken(state, {token, remember}) {
            localStorage.setItem('access_token', token);
            localStorage.setItem('token_created', new Date().getTime().toString());
            localStorage.setItem('remember', undefined!==remember && remember==true ? "true" : "false");
        },
        getToken() {
            return localStorage.getItem('access_token');
        },
        selectChatBot(state, {section, block}) {
            state.chatBot = {
                section: section,
                block: block
            }
        }
    }
} as StoreOptions<any>);