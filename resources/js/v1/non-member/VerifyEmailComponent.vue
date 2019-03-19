<template>
    <div class="nonMemberComponent">
        <div class="nonMemberFormRoot">
            <form @submit.prevent="registerNow()">
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
                    <label for="code">Verification Code</label>
                    <input
                        type="text"
                        id="code"
                        required
                        v-model="verifyData.code"
                        :disabled="loading"
                        placeholder="Verification code"
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
                        <button type="submit" class="defaultBtn">Verify</button>
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
export default class VerifyEmailComponent extends Vue {
    private loading: boolean = false;
    private errorEmail: string = "";
    private verifyData: any = {
        email: "",
        code: ""
    };
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    async registerNow() {
        this.loading = true;

        let data = new FormData();
        data.append("email", this.verifyData.email);
        data.append("code", this.verifyData.code);

        await Axios({
            url: "/api/user/verify",
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

