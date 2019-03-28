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
        selectedSegment: 0,
        months: {
            1: 'Jan',
            2: 'Feb',
            3: 'Mar',
            4: 'Apr',
            5: 'May',
            6: 'Jun',
            7: 'Jul',
            8: 'Aug',
            9: 'Sep',
            10: 'Oct',
            11: 'Nov',
            12: 'Dec'
        },
        messageTags: [],
        loadingMessageTags: false,
        deleteTrigger: null,
        deleteSchedule: null,
        updateTrigger: null,
        updateSchedule: null,
        facebookReconnect: false,
        errorMesg: [],
        sessionIdentifier: localStorage.getItem('session_identifier'),
        passwordVerify: false,
        haveLiveChat: false,
        isError: false
    },
    mutations: {
        logout(state) {
            localStorage.removeItem('access_token');
            localStorage.removeItem('token_created');
            localStorage.removeItem('session_identifier');
            localStorage.removeItem('remember');
            state.passwordVerify = false;
            state.isLogin = false;
            state.token = null;
        },
        setToken(state, { token, sessionIdentifier, remember }) {
            state.sessionIdentifier = sessionIdentifier;
            localStorage.setItem('access_token', token);
            localStorage.setItem('token_created', new Date().getTime().toString());
            localStorage.setItem('session_identifier', sessionIdentifier);
            localStorage.setItem('remember', undefined !== remember && remember == true ? "true" : "false");
        },
        updateUserInfo(state, { user }) {
            state.user = user;
        },
        getToken() {
            return localStorage.getItem('access_token');
        },
        selectChatBot(state, { section, block }) {
            state.chatBot = {
                section: section,
                block: block
            };
        },
        deleteChatBot(state, { section, block }) {
            state.delBot = {
                section: section,
                block: block
            };
        },
        updateChatBot(state, { section, block, title }) {
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