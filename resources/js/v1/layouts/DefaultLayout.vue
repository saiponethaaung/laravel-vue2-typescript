<template>
    <div id="loginContent">
        <section id="sideMenu">
            <ul id="sideNav">
                <li>
                    <router-link :to="{name: 'home'}">
                        <i class="material-icons">assistant</i>
                        <span class="icon-label">Chatbot</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'home'}">
                        <i class="material-icons">question_answer</i>
                        <span class="icon-label">Inbox</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'home'}">
                        <i class="material-icons">supervisor_account</i>
                        <span class="icon-label">Users</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'home'}">
                        <i class="material-icons">volume_up</i>
                        <span class="icon-label">Broadcast</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'home'}">
                        <i class="material-icons">stars</i>
                        <span class="icon-label">Star</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'home'}">
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
                        <div class="projectInfoContainer">
                            <figure class="projectIconWrapper">
                                <img src="/images/sample/logo.png" class="projectIcon"/>
                            </figure>
                            <div class="projectInfo">
                                <h4 class="projectName">Tech Hub Myanmar</h4>
                                <h6 class="projectVendor">powered by <a href="javascript:void(0);">Pixybot</a></h6>
                            </div>
                            <div class="projectNavControl">
                                <i class="material-icons">arrow_drop_down</i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="headerConent">
                    <template v-if="$store.state.user.facebook_connected">
                        Action
                    </template>
                    <template v-else>
                        <button v-if="fbSdkLoaded" @click="fbLogin">Link a facebook account</button>
                    </template>
                </div>
            </section>
            <section id="innnerContent">
                <div class="sidebar bodySidebar">
                    <component :is="dynamicSidebar"></component>
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
import { Component } from 'vue-property-decorator';
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

    get fbSdkLoaded() {
        return window.fbSdkLoaded;
    }

    get dynamicSidebar() {
        if(this.$route.meta === undefined || this.$route.meta.sidebar === undefined) {
            return null;
        }
        return this.$route.meta.sidebar;
    }

    fbLogin() {
        FB.login((res: any) => {
            console.log('fb response', res);
            if(res.status==='connected') {
                FB.api('/me/permissions', (pres: any) => {
                    let reqRequest = [];
                    for(let i in pres.data) {
                        let index = this.permissions.indexOf(pres.data[i].permission);
                        if(index>-1 && pres.data[i].status=='granted') {
                            continue;
                        }
                        reqRequest.push(pres.data[i].permission);
                    }

                    if(reqRequest.length>0) {
                        let mesg = `Login with facebook and allow following permissions\n${reqRequest.join(', ')}`;
                        alert(mesg);
                    } else {
                        this.updateFBToken(res.authResponse);
                    }
                });
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
