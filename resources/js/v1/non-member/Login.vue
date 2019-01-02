<template>
    <div>
        <form @submit.prevent="loginNow">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" required v-model="loginData.email" :disabled="loading"/>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" required v-model="loginData.password" :disabled="loading"/>
            </div>
            <template v-if="loading">
                Loading...
            </template>
            <template v-else>
                <button>Login</button>
            </template>
        </form>
    </div>    
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import AjaxErrorHandler from '../utils/AjaxErrorHandler';
import Axios from 'axios';

@Component
export default class Login extends Vue {

    private loading: boolean = false;
    private loginData: any = {
        email: "",
        password: ""
    };
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    async loginNow() {
        this.loading = true;

        let data = new FormData();
        data.append('email', this.loginData.email);
        data.append('password', this.loginData.password);

        await Axios({
            url: "/api/user/login",
            data: data,
            method: "POST"
        }).then((res) => {
            this.$store.commit('setToken', {
                token: res.data.token
            });
            window.location.reload();
        }).catch((err) => {
            let mesg = this.ajaxHandler.globalHandler(err, 'Failed to login!');
            alert(mesg);
        });

        this.loading = false;
    }
}
</script>

<style lang="scss">

</style>
