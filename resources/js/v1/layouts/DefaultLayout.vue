<template>
    <div id="loginContent">
        <section id="sideMenu">
            <ul id="sideNav">
                <li>
                    <router-link :to="{name: 'project.home', params: {projectid: $route.params.projectid}}">
                        <i class="material-icons">assistant</i>
                        <span class="icon-label">Chatbot</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'project.inbox', params: {projectid: $route.params.projectid}}">
                        <i class="material-icons">question_answer</i>
                        <span class="icon-label">Inbox</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'project.home', params: {projectid: $route.params.projectid}}">
                        <i class="material-icons">supervisor_account</i>
                        <span class="icon-label">Users</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'project.home', params: {projectid: $route.params.projectid}}">
                        <i class="material-icons">volume_up</i>
                        <span class="icon-label">Broadcast</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'project.home', params: {projectid: $route.params.projectid}}">
                        <i class="material-icons">stars</i>
                        <span class="icon-label">Star</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'project.configuration', params: {projectid: $route.params.projectid}}">
                        <i class="material-icons">settings</i>
                        <span class="icon-label">Settings</span>
                    </router-link>
                </li>
                <li>
                    <a href="javascript:void(0);" @click="$store.commit('logout')">
                        Logout
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
                        <div class="projectInfoContainer" @click="projectOptions=!projectOptions">
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
                        <button type="button" class="headerButtonTypeOne">
                            Activate Page
                        </button>
                    </template>
                    <template v-else>
                        <button v-if="$store.state.fbSdk" @click="fbLogin" type="button" class="headerButtonTypeOne">Connect facebook account</button>
                    </template>
                    <button v-if="!testNow" @click="testChatBot()" type="button" class="testChatBotBtn">
                        Test Now
                    </button>
                    <a v-else :href="'https://m.me/'+$store.state.projectInfo.pageId" target="_blank" class="headerButtonTypeOne">
                        View on Messenger
                    </a>
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
        'read_page_mailboxes'
    ];

    private projectOptions: boolean = false;
    private testNow: boolean = false;

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
}
</script>
