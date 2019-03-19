<template>
    <div class="nonMemberComponent">
        <div class="nonMemberFormRoot">
            <form @submit.prevent="registerNow()">
                <figure>
                    <img src="/images/icons/logo.png" class="navIcon">
                </figure>
                <div class="form-group">
                    <label for="name">Full name</label>
                    <input
                        type="text"
                        id="name"
                        required
                        v-model="registerData.name"
                        :disabled="loading"
                        placeholder="Full name"
                    >
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        required
                        v-model="registerData.email"
                        :disabled="loading"
                        placeholder="Email"
                    >
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        required
                        v-model="registerData.password"
                        :disabled="loading"
                        placeholder="Password"
                    >
                </div>
                <div class="form-group">
                    <p class="nonMemberFormNote">Already a member? Login
                        <router-link :to="{name: 'login'}">here</router-link>
                    </p>
                </div>
                <div class="form-group text-center">
                    <template v-if="loading">Loading...</template>
                    <template v-else>
                        <button type="submit" class="defaultBtn">Register</button>
                    </template>
                </div>
            </form>
            <template v-if="errorRegister!==''">
                <error-component :mesg="errorRegister" @closeError="errorRegister=''"></error-component>
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from "vue-property-decorator";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import Axios from "axios";

@Component
export default class RegisterComponent extends Vue {
    private loading: boolean = false;
    private errorRegister: string = "";
    private registerData: any = {
        name: "",
        email: "",
        password: ""
    };
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    async registerNow() {
        this.loading = true;

        let data = new FormData();
        data.append("name", this.registerData.name);
        data.append("email", this.registerData.email);
        data.append("password", this.registerData.password);

        await Axios({
            url: "/api/user/register",
            data: data,
            method: "POST"
        })
            .then(res => {
                this.$router.push({ name: "verify" });
            })
            .catch(err => {
                if (err.response) {
                    let mesg = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to register!"
                    );
                    this.errorRegister = mesg;
                }
            });

        this.loading = false;
    }
}
</script>

