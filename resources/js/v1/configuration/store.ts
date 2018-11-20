import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        welcome: "welcome",
    },
} as StoreOptions<any>);