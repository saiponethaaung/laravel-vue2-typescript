<template>
    <div id="loginContent">
        <section id="sideMenu">
            <ul id="sideNav">
                <li>
                    <router-link 
                        :to="{name: 'project.home', params: {projectid: $route.params.projectid}}"
                        :class="{'activeSection': this.$route.meta.section==='chatbot'}"    
                    >
                        <i class="material-icons">assistant</i>
                        <span class="icon-label">Chatbot</span>
                    </router-link>
                </li>
                <li>
                    <router-link 
                        :to="{name: 'project.inbox', params: {projectid: $route.params.projectid}}"
                        :class="{'activeSection': this.$route.meta.section==='inbox'}"    
                    >
                        <i class="material-icons">question_answer</i>
                        <span class="icon-label">Inbox</span>
                    </router-link>
                </li>
                <li>
                    <router-link 
                        :to="{name: 'project.users', params: {projectid: $route.params.projectid}}"
                        :class="{'activeSection': this.$route.meta.section==='user'}"    
                    >
                        <i class="material-icons">supervisor_account</i>
                        <span class="icon-label">Users</span>
                    </router-link>
                </li>
                <li>
                    <router-link 
                        :to="{name: 'project.broadcast', params: {projectid: $route.params.projectid}}"
                        :class="{'activeSection': this.$route.meta.section==='broadcast'}"    
                    >
                        <figure>
                            <img class="imgIcon" src="/images/icons/sidebar/broadcast.png"/>
                            <img class="activeIcon" src="/images/icons/sidebar/broadcast_active.png"/>
                        </figure>
                        <span class="icon-label">Broadcast</span>
                    </router-link>
                </li>
                <li>
                    <router-link 
                        :to="{name: 'project.home', params: {projectid: $route.params.projectid}}"
                        :class="{'activeSection': this.$route.meta.section==='plugin'}"    
                    >
                        <figure>
                            <img class="imgIcon" src="/images/icons/sidebar/plugin.png"/>
                            <img class="activeIcon" src="/images/icons/sidebar/plugin_active.png"/>
                        </figure>
                        <span class="icon-label">Plugin</span>
                    </router-link>
                </li>
                <li>
                    <router-link 
                        :to="{name: 'project.configuration', params: {projectid: $route.params.projectid}}"
                        :class="{'activeSection': this.$route.meta.section==='setting'}"    
                    >
                        <i class="material-icons">settings</i>
                        <span class="icon-label">Settings</span>
                    </router-link>
                </li>
                <li>
                    <a href="javascript:void(0);" @click="$store.commit('logout')">
                        <i class="material-icons">exit_to_app</i>
                        <span class="icon-label">Logout</span>
                    </a>
                </li>
            </ul>
            <div>
            </div>
        </section>
        <div id="mainContent">
            <section id="headerContent">
                <div class="sidebar headerSidebar">
                    <div class="projectList">
                        <div class="projectInfoContainer" ref="projectListDropDown" @click="projectOptions=!projectOptions">
                            <figure class="projectIconWrapper">
                                <img :src="$store.state.projectInfo.image ? $store.state.projectInfo.image : '/images/sample/logo.png'" class="projectIcon"/>
                            </figure>
                            <div class="projectInfo">
                                <h4 class="projectName">{{ $store.state.projectInfo.name ? $store.state.projectInfo.name : '-' }}</h4>
                                <h6 class="projectVendor">powered by <a href="javascript:void(0);">Pixybot</a></h6>
                            </div>
                            <div class="projectNavControl">
                                <i class="material-icons" v-if="projectOptions">arrow_drop_up</i>
                                <i class="material-icons" v-else>arrow_drop_down</i>
                            </div>
                        </div>
                        <template v-if="projectOptions">
                            <div class="popProjectList">
                                <router-link :to="{name: 'project.home', params: {projectid: project.id}}" class="projectInfoContainer" v-for="(project, index) in $store.state.projectList" v-bind:key="index">
                                    <figure class="projectIconWrapper">
                                        <img :src="project.image ? project.image : '/images/sample/logo.png'" class="projectIcon"/>
                                    </figure>
                                    <div class="projectInfo">
                                        <h4 class="projectName">{{ project.name ? project.name : '-' }}</h4>
                                    </div>
                                </router-link>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="headerConent">
                    <template v-if="$store.state.user.facebook_connected">
                        <template v-if="undefined!==$store.state.projectInfo.pageConnected">
                            <template v-if="$store.state.projectInfo.pageConnected">
                                <button type="button" class="headerButtonTypeOne" v-if="updatingStatus">
                                    Changing status
                                </button>
                                <button type="button" class="headerButtonTypeOne" @click="changePublishStatus()" v-else>
                                    <template v-if="$store.state.projectInfo.publish">
                                        Deactivate Page
                                    </template>
                                    <template v-else>
                                        Activate Page
                                    </template>
                                </button>
                            </template>
                            <router-link :to="{name: 'project.configuration', params: {projectid: $route.params.projectid}}" class="headerButtonTypeOne" v-else>
                                Connect a page
                            </router-link>
                        </template>
                    </template>
                    <template v-else>
                        <button v-if="$store.state.fbSdk" @click="fbLogin" type="button" class="headerButtonTypeOne">Connect facebook account</button>
                    </template>
                    <template v-if="undefined!==$store.state.projectInfo.id">
                        <div v-if="!testNow && canTest" type="button" class="testChatBotBtn" :class="{'hideTest': hideTest}">
                            Test this bot
                            <div class="fb-send-to-messenger" 
                                messenger_app_id="1155102521322007" 
                                :page_id="$store.state.projectInfo.pageConnected && $store.state.projectInfo.publish ? $store.state.projectInfo.pageId : $store.state.projectInfo.testingPageId" 
                                :data-ref="`${$store.state.projectInfo.id}-${$store.state.projectInfo.pageConnected && $store.state.projectInfo.publish ? $store.state.projectInfo.pageId : $store.state.projectInfo.testingPageId}-${$store.state.user.facebook}`"
                                color="blue" 
                                size="standard">
                                Send to messenger
                            </div>
                        </div>
                        <a v-if="testNow" :href="'https://m.me/'+($store.state.projectInfo.pageConnected && $store.state.projectInfo.publish ? $store.state.projectInfo.pageId : $store.state.projectInfo.testingPageId)" target="_blank" class="headerButtonTypeOne">
                            View on Messenger
                        </a>
                    </template>
                </div>
            </section>
            <section id="innnerContent">
                <div class="sidebar bodySidebar">
                    <template v-if="!$store.state.validatingProject">
                        <component :is="dynamicSidebar"></component>
                    </template>
                </div>
                <div class="bodyContent">
                    <router-view></router-view>
                </div>
            </section>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Watch } from 'vue-property-decorator';
import Axios from 'axios';
import AjaxErrorHandler from '../utils/AjaxErrorHandler';

@Component
export default class DefaultLayout extends Vue {

    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    private permissions: Array<string> = [
        'public_profile',
        'email',
        'pages_messaging',
        'pages_messaging_subscriptions',
        'manage_pages',
        'pages_show_list',
        'publish_pages',
        'read_page_mailboxes',
        // 'groups_access_member_info',
        // 'publish_to_groups',
        // 'user_age_range',
        // 'user_birthday',
        // 'user_events',
        // 'user_friends',
        // 'user_gender',
        // 'user_hometown',
        // 'user_likes',
        // 'user_link',
        // 'user_location',
        // 'user_photos',
        // 'user_posts',
        // 'user_tagged_places',
        // 'user_videos',
        // 'ads_management',
        // 'ads_read',
        // 'business_management',
        // 'leads_retrieval',
        // 'pages_manage_cta',
        // 'pages_manage_instant_articles',
        // 'read_audience_network_insights',
        // 'read_insights'
    ];

    private projectOptions: boolean = false;
    private canTest: boolean = true;
    private testNow: boolean = false;
    private hideTest: boolean = true;
    private updatingStatus: boolean = false;

    mounted() {
        this.initSendToMessenger();
    }

    beforeDestory() {
        FB.Event.unsubscribe('send_to_messenger');
    }

    get dynamicSidebar() {
        if(this.$route.meta === undefined || this.$route.meta.sidebar === undefined) {
            return null;    
        }
        return this.$route.meta.sidebar;
    }

    async testChatBot() {
        this.testNow = true;
        setTimeout(() => {
            this.testNow = false;
        }, 30000);
    }

    @Watch('$store.state.projectInfo', { immediate: true, deep: true })
    projectInfoChange() {
        this.canTest = false;
        setTimeout(() => {
            this.canTest = true;
            setTimeout(() => {
                this.initSendToMessenger();
            }, 500);
        }, 500);
    }

    @Watch('testNow')
    testNowChange() {
        setTimeout(() => {
            this.initSendToMessenger();
        }, 500);
    }

    @Watch('$store.state.fbSdk', { immediate: true, deep: true })
    initSendToMessenger() {
        if(!this.$store.state.fbSdk) return;
        FB.XFBML.parse();
    }

    @Watch('$store.state.fbSdk', { immediate: true, deep: true })
    sendToMessengerEvent() {
        if(!this.$store.state.fbSdk) return;
        FB.Event.subscribe('send_to_messenger', (e: any) => {
            switch(e.event) {
                case('rendered'):
                    this.hideTest = false;
                    break;

                case('clicked'):
                    this.hideTest = true;
                    this.testChatBot();
                    break;
            }
        });
    }

    fbLogin() {
        FB.login((res: any) => {
            console.log('fb response', res);
            if(res.status==='connected') {

                let valid = true;

                for(var i in this.permissions) {
                    FB.api(`/me/permissions/${this.permissions[i]}`, (pres: any) => {
                        if(pres.data[0].status!=='granted') {
                            valid = false;

                            let mesg = `Login with facebook and allow ${this.permissions[i]} permissions`;
                            alert(mesg);
                        }
                    });
                }

                if(valid) {
                    this.updateFBToken(res.authResponse);
                }
            }
        }, {
            auth_type: 'rerequest',
            scope: this.permissions.join(","),
            returnScope: true
        });
    }

    async updateFBToken(res: any) {
        let data = new FormData();
        data.append('access_token', res.accessToken);
        data.append('userID', res.userID);

        await Axios({
            url: "/api/v1/user/facebook-linked",
            data: data,
            method: "POST"
        }).then((res: any) => {
            this.$store.commit('updateUserInfo', {
                user: res.data.user
            });
        }).catch((err: any) => {
            let mesg = this.ajaxHandler.globalHandler(err, 'Failed to access facebook!');
            alert(mesg);
        });
    }

    async changePublishStatus() {
        this.updatingStatus = true;
        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/pages/change-publish-status`,
            method: 'post'
        }).then((res) => {
            this.$store.commit('setProjectPublishStatus', { status: res.data.publishStatus});
        }).catch((err) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to change project publish status!');
                alert(mesg);
            }
        });
        this.updatingStatus = false;
    }

    documentClick(e: any){
        let el: any = this.$refs.projectListDropDown;
        let target = e.target;
        if (( el !== target) && !el.contains(target)) {
            this.projectOptions = false;
        }
    }

    created() {
      document.addEventListener('click', this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener('click', this.documentClick);
    }
}
</script>
