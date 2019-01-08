<template>
    <div class="">
        <template v-if="$store.state.isLogin">
            <template v-if="$route.name!=='home'">
                <default-layout></default-layout>
            </template>
            <template v-else>
                <router-view :loading="loading"></router-view>
            </template>
        </template>
        <template v-else>
            <login></login>
        </template>
    </div>
</template>


<script lang="ts">
import Vue from 'vue';
import { Component, Watch } from 'vue-property-decorator';
import DefaultLayout from './layouts/DefaultLayout.vue';
import Login from './non-member/Login.vue';
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from './utils/AjaxErrorHandler';

@Component({
    components: {
        DefaultLayout,
        Login
    }
})
export default class App extends Vue {
    
    private loading: boolean = true;
    private loadToken: CancelTokenSource = Axios.CancelToken.source();
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler(); 
    
    mounted() {
        this.loadProject();

        window.fbAsyncInit = () => {
            FB.init({
                appId      : '1155102521322007',
                cookie     : true,
                xfbml      : true,
                version    : 'v3.2'
            });
            
            this.$store.commit('updateFBSdk', {
                status: true
            });
            
            FB.AppEvents.logPageView();
        };
        
        (function(d, s, id){
            var js: any, fjs: any = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement('script');
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }

    @Watch('$route.name')
    validateRouter() {
        console.log("Route", this.$route.name);
    }

    @Watch('$store.state.isLogin')
    async loadProject() {
        if(this.$store.state.isLogin) {
            this.loading = true;
            this.loadToken.cancel();
            this.loadToken = Axios.CancelToken.source();

            await Axios({
                url: `/api/v1/project/list`,
                method: 'get'
            }).then((res: any) => {
                this.$store.commit('updateProjectList', {
                    projects: res.data.data
                });
            }).catch((err:any) => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load project list!');
                    alert(mesg);
                }
            });

            this.loading = false;
        }
    }

    beforeDestory() {
        this.loadToken.cancel();
    }
}
</script>