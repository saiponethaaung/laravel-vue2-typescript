<template>
    <div class="">
        <template v-if="$store.state.isLogin">
            <template v-if="$route.name!=='home'">
                <default-layout></default-layout>
            </template>
            <template v-else>
                <router-view></router-view>
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

    @Component({
        components: {
            DefaultLayout,
            Login
        }
    })
    export default class App extends Vue {
        
        @Watch('$route.name')
        validateRouter() {
            console.log("Route", this.$route.name);
        }

        mounted() {
            console.log(this.$store.state.isLogin);
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
    }
</script>