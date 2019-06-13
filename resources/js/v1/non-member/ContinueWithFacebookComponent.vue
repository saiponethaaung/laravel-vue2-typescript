<template>
    <div class="nonMemberComponent">
        <div class="nonMemberFormRoot">
            <form @submit.prevent>
                <figure>
                    <img src="/images/icons/logo.png" class="navIcon">
                </figure>
                <div class="form-group text-center">
                    <template v-if="loading">
                            Loading...
                    </template>
                    <template v-else>
                        <div id="fb-root" class="text-center" @click="fbLogin">
                            <div class="noclicking">
                                <div class="fb-login-button" data-width="" data-button-type="continue_with" data-size="large" data-auto-logout-link="false" data-use-continue-as="false"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </form>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch } from "vue-property-decorator";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import Axios from "axios";

@Component
export default class ContinueWithFacebookComponent extends Vue {
    private loading: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private permissions: Array<string> = [
        "public_profile",
        "email",
        "pages_messaging",
        "pages_messaging_subscriptions",
        "manage_pages",
        "pages_show_list",
    ];
    
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

        this.loading = true;

        await Axios({
            url: "/api/v1/user/facebook-linked",
            data: data,
            method: "POST"
        }).then((res: any) => {
            window.location.reload();
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(
                    err,
                    "Failed to access facebook!"
                );
                alert(mesg);
                this.loading = false;
                setTimeout(() => {
                    FB.XFBML.parse();
                }, 30);
            }
        });
    }
}
</script>

<style lang="scss">
</style>
