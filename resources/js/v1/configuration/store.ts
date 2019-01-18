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
        delBot: {
            block: -1,
            section: -1
        },
        updateBot: {
            block: -1,
            section: -1,
            title: ''
        },
        token: localStorage.getItem('access_token'),
        validatingProject: false,
        projectInfo: {},
        projectList: [],
        fbSdk: false,
        selectedInbox: -1,
        inboxList: [],
        chatFilter: 0,
        userFilter: [],
        prevUserFilter: '',
        segments: [],
        selectedSegment: -1
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
        },
        deleteChatBot(state, {section, block}) {
            state.delBot = {
                section: section,
                block: block
            };
        },
        updateChatBot(state, {section, block, title}) {
            state.updateBot = {
                section: section,
                block: block,
                title: title
            };
        },
        setProjectStatus(state, { status }) {
            state.validatingProject = status;
        },
        setProjectInfo(state, { project }) {
            state.projectInfo = project;
        },
        updateFBSdk(state, { status }) {
            state.fbSdk = status;
        },
        updateProjectList(state, { projects }) {
            state.projectList = projects;
        },
        setProjectPublishStatus(state, { status }) {
            state.projectInfo.publish = status;
        },
        updateSelectedInbox(state, { selected }) {
            state.selectedInbox = selected;
        },
        updateInboxList(state, { inbox }) {
            state.inboxList = inbox;
        },
        updateInboxChatStatus(state, { index, status }) {
            state.inboxList[index].live_chat = status;
        },
        updateInboxUrgentStatus(state, { index, status }) {
            state.inboxList[index].urgent = status;
        }
    }
} as StoreOptions<any>);