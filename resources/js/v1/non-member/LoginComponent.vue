<template>
    <div class="nonMemberComponent">
        <div class="nonMemberFormRoot">
            <template v-if="$store.state.token===null">
                <form @submit.prevent="loginNow">
                    <figure>
                        <img src="/images/icons/logo.png" class="navIcon">
                    </figure>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            required
                            v-model="loginData.email"
                            :disabled="loading"
                            placeholder="Email"
                        >
                    </div>
                    <div class="form-group">
                        <label for="otp">OTP</label>
                        <input
                            type="password"
                            id="otp"
                            required
                            v-model="loginData.otp"
                            :disabled="loading"
                            placeholder="otp"
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
                            <button type="submit" class="defaultBtn loginBtn">Login</button>
                        </template>
                    </div>
                    <template v-if="errorLogin!==''">
                        <error-component :mesg="errorLogin" @closeError="errorLogin=''"></error-component>
                    </template>
                </form>
            </template>
            <template v-else>
                <form @submit.prevent="verifyPassword">
                    <figure>
                        <img src="/images/icons/logo.png" class="navIcon">
                    </figure>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            required
                            v-model="loginData.password"
                            :disabled="loading"
                            placeholder="Password"
                        >
                    </div>
                    <div class="form-group">
                        <p class="nonMemberFormNote">
                            Use different account?
                            <a
                                href="javascript:void(0);"
                                @click="$store.commit('logout')"
                            >Logout</a>
                        </p>
                    </div>
                    <div class="form-group text-center">
                        <template v-if="loading">Loading...</template>
                        <template v-else>
                            <button type="submit" class="defaultBtn loginBtn">Submit</button>
                        </template>
                    </div>
                    <template v-if="errorLogin!==''">
                        <error-component :mesg="errorLogin" @closeError="errorLogin=''"></error-component>
                    </template>
                </form>
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import Axios from "axios";

@Component
export default class LoginComponent extends Vue {
    private loading: boolean = false;
    private errorLogin: string = "";
    private loginData: any = {
        email: "",
        password: "",
        otp: ""
    };
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    async loginNow() {
        this.loading = true;

        let data = new FormData();
        data.append("email", this.loginData.email);
        data.append("otp", this.loginData.otp);

        await Axios({
            url: "/api/user/login",
            data: data,
            method: "POST"
        })
            .then(res => {
                if (res.data.isVerify) {
                    this.$store.commit("setToken", {
                        token: res.data.token,
                        sessionIdentifier: res.data.sessionIdentifier
                    });
                    if (this.$route.name === "login") {
                        window.location.assign("/");
                    } else {
                        window.location.reload();
                    }
                } else {
                    this.$router.push({ name: "verify" });
                }
            })
            .catch(err => {
                let mesg = this.ajaxHandler.globalHandler(
                    err,
                    "Failed to login!"
                );
                this.errorLogin = mesg;
            });

        this.loading = false;
    }

    private async verifyPassword() {
        if (this.loginData.password === "") {
            alert("Password is required!");
            return false;
        }

        let data = new FormData();
        data.append("password", this.loginData.password);

        this.loading = true;

        await Axios({
            url: `/api/user/verify-password`,
            data: data,
            method: "post"
        })
            .then(res => {
                this.$store.state.passwordVerify = true;
            })
            .catch(err => {
                if (err.response) {
                    this.errorLogin = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to verify password!"
                    );
                }
            });

        this.loading = false;
    }
}
</script>

<style lang="scss">
</style>
