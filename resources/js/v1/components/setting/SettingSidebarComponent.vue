<template>
    <div class="bcs">
        <div class="chatSbHeaderOption broadcastHeading">
            <div class="chatFilterList float-left">
                <div class="inboxOptionTitle">Setting</div>
            </div>
        </div>

        <div class="contentRoot">
            <div class="broadcastHeading">
                <span>Project Setting</span>
            </div>
        </div>
        <div class="btnClick configSidebarLink" v-if="$store.state.projectInfo.isOwner">
            <router-link :to="{name: 'project.configuration'}" :class="{'activeSetting': $route.meta.sidebarSection==='connected-page'}">
                Facebook Pages
            </router-link>
        </div>
        <div class="btnClick configSidebarLink">
            <router-link :to="{name: 'project.configuration.persistent-menu'}" :class="{'activeSetting': $route.meta.sidebarSection==='persistent-menu'}">
                Persistent Menu
            </router-link>
        </div>
        <div class="btnClick configSidebarLink">
            <router-link :to="{name: 'project.configuration.messenger-user-input'}" :class="{'activeSetting': $route.meta.sidebarSection==='messenger-user-input'}">
                Messenger user input
            </router-link>
        </div>
        <div class="btnClick configSidebarLink" v-if="$store.state.projectInfo.role<2">
            <router-link :to="{name: 'project.configuration.admins'}" :class="{'activeSetting': $route.meta.sidebarSection==='admins'}">
                Admins
            </router-link>
        </div>
        
        <div class="divider20"></div>
        
        <div class="contentRoot">
            <div class="broadcastHeading">
                <span>Account Settings</span>
            </div>
        </div>
        <div class="btnClick configSidebarLink">
            <router-link :to="{name: 'project.configuration.profile'}" :class="{'activeSetting': $route.meta.sidebarSection==='profile'}">
                Account Info
            </router-link>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

@Component
export default class SettingSidebarComponent extends Vue {

}
</script>