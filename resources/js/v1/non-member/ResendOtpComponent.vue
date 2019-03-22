<template>
    <div class="nonMemberComponent">
        <div class="nonMemberFormRoot">
            <form @submit.prevent="sendNow()">
                <figure>
                    <img src="/images/icons/logo.png" class="navIcon">
                </figure>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        required
                        v-model="verifyData.email"
                        :disabled="loading"
                        placeholder="Email"
                    >
                </div>
                <div class="form-group">
                    <p class="nonMemberFormNote">Not a member? Register
                        <router-link :to="{name: 'register'}">here</router-link>
                    </p>
                </div>
                <div class="form-group text-center">
                    <template v-if="loading">Loading...</template>
                    <template v-else>
                        <button type="submit" class="defaultBtn">Send OTP</button>
                    </template>
                </div>
            </form>
            <template v-if="errorEmail!==''">
                <error-component :mesg="errorEmail" @closeError="errorEmail=''"></error-component>
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from "vue-property-decorator";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import Axios from "axios";

@Component
export default class ResendOtpComponent extends Vue {
    private loading: boolean = false;
    private errorEmail: string = "";
    private verifyData: any = {
        email: "",
        code: ""
    };
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    async sendNow() {
        this.loading = true;

        let data = new FormData();
        data.append("email", this.verifyData.email);

        await Axios({
            url: "/api/user/otp-resend",
            data: data,
            method: "POST"
        })
            .then(res => {
                this.$router.push({ name: "login" });
            })
            .catch(err => {
                if (err.response) {
                    let mesg = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to verify!"
                    );
                    this.errorEmail = mesg;
                }
            });

        this.loading = false;
    }
}
</script>

