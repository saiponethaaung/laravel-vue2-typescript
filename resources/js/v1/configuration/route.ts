import Vue from 'vue';
import VueRouter from 'vue-router';

import Example from '../Example.vue';

import ChatBotComponent from '../components/chatbot/ContentComponent.vue';
import ChatBotSidebar from '../components/chatbot/SidebarComponent.vue';

Vue.use(VueRouter);

export default new VueRouter({
    base: '/',
    mode: 'history',
    routes: [
        {
            path: "/",
            name: "home",
            meta: {
                sidebar: ChatBotSidebar
            },
            component: ChatBotComponent
        }
    ]
});