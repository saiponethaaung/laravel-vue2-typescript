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
                    Action
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

@Component
export default class DefaultLayout extends Vue {

    get dynamicSidebar() {
        if(this.$route.meta === undefined || this.$route.meta.sidebar === undefined) {
            return null;
        }
        return this.$route.meta.sidebar;
    }

}
</script>
