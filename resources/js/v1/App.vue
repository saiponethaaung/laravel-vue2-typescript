<template>
    <div class>
        <template v-if="$store.state.isLogin && $store.state.passwordVerify">
            <template v-if="$store.state.user.facebook_connected">
                <template v-if="$route.name!=='home'">
                    <default-layout></default-layout>
                </template>
                <template v-else>
                    <router-view :loading="loading"></router-view>
                </template>
            </template>
            <template v-else>
                <div id="fb-root" @click="fbLogin">
                    <div class="noclicking">
                        <div class="fb-login-button" data-width="" data-size="medium" data-auto-logout-link="false" data-use-continue-as="false"></div>
                    </div>
                </div>
            </template>
        </template>
        <template v-else>
            <template v-if="!$store.state.autheticating">
                <template v-if="$route.name==='register' || $route.name==='verify' || $route.name==='qrcode.resend'">
                    <router-view></router-view>
                </template>
                <template v-else>
                    <login-component></login-component>
                </template>
            </template>
        </template>
        <template v-if="$store.state.autheticating">
            <div class="floatingLoading">Loading...</div>
        </template>
        <template v-if="$store.state.errorMesg.length>0">
            <error-component
                :mesg="$store.state.errorMesg[0]"
                @closeError="$store.state.errorMesg.splice(0, 1)"
            ></error-component>
        </template>
    </div>
</template>


<script lang="ts">
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import LoginComponent from "./non-member/LoginComponent.vue";
import Axios, { CancelTokenSource } from "axios";
import AjaxErrorHandler from "./utils/AjaxErrorHandler";

@Component({
    components: {
        DefaultLayout,
        LoginComponent
    }
})
export default class App extends Vue {
    private loading: boolean = true;
    private loadToken: CancelTokenSource = Axios.CancelToken.source();
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private permissions: Array<string> = [
        "public_profile",
        "email",
        "pages_messaging",
        "pages_messaging_subscriptions",
        "manage_pages",
        "pages_show_list",
    ];

    mounted() {
        this.loadProject();

        window.fbAsyncInit = () => {
            FB.init({
                appId: "1155102521322007",
                cookie: true,
                xfbml: true,
                version: "v3.2"
            });

            this.$store.commit("updateFBSdk", {
                status: true
            });

            // FB.AppEvents.logPageView();
        };
    }

    @Watch("$route.name")
    validateRouter() {
        console.log("Route", this.$route.name);
    }

    @Watch("$store.state.isLogin")
    async loadProject() {
        if (this.$store.state.isLogin) {
            this.loading = true;
            this.loadToken.cancel();
            this.loadToken = Axios.CancelToken.source();

            await Axios({
                url: `/api/v1/project/list`,
                method: "get"
            })
                .then((res: any) => {
                    this.$store.commit("updateProjectList", {
                        projects: res.data.data
                    });
                })
                .catch((err: any) => {
                    if (err.response) {
                        let mesg = this.ajaxHandler.globalHandler(
                            err,
                            "Failed to load project list!"
                        );
                        alert(mesg);
                    }
                });

            this.loading = false;
        }
    }

    @Watch("$store.state.user.facebook_connected", { immediate: true, deep: true })
    @Watch("$store.state.fbSdk", { immediate: true, deep: true })
    initSendToMessenger() {
        if (!this.$store.state.fbSdk) return;
        setTimeout(() => {
            setTimeout(() => {
                FB.XFBML.parse();
            }, 30);
        }, 30);
    }

    fbLogin() {
        FB.login(
            (res: any) => {
                console.log("fb response", res);
                if (res.status === "connected") {
                    let valid = true;

                    for (var i in this.permissions) {
                        FB.api(
                            `/me/permissions/${this.permissions[i]}`,
                            (pres: any) => {
                                if (pres.data[0].status !== "granted") {
                                    valid = false;

                                    let mesg = `Login with facebook and allow ${
                                        this.permissions[i]
                                    } permissions`;
                                    alert(mesg);
                                }
                            }
                        );
                    }

                    if (valid) {
                        this.updateFBToken(res.authResponse);
                    }
                }
            },
            {
                auth_type: "rerequest",
                scope: this.permissions.join(","),
                returnScope: true
            }
        );
    }

    async updateFBToken(res: any) {
        let data = new FormData();
        data.append("access_token", res.accessToken);
        data.append("userID", res.userID);

        await Axios({
            url: "/api/v1/user/facebook-linked",
            data: data,
            method: "POST"
        })
            .then((res: any) => {
                this.$store.state.user.facebookReconnect = false;
                this.$store.commit("updateUserInfo", {
                    user: res.data.user
                });
            })
            .catch((err: any) => {
                let mesg = this.ajaxHandler.globalHandler(
                    err,
                    "Failed to access facebook!"
                );
                alert(mesg);
            });
    }

    beforeDestory() {
        this.loadToken.cancel();
    }
}
</script>