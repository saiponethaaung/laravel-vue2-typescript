<template>
    <div class="inheritHFW ovAuto pageListRootCon">
        <h5>Profile</h5>
        <template v-if="loading">
            Loading...
        </template>
        <template v-else>
            <div>
                <div>
                    <figure>
                        <img :src="'/images/icons/default-user.jpg'"/>
                    </figure>
                </div>
                <div>
                    <label>
                        Name:
                    </label>
                    <div>
                        <input type="text" v-model="profile.name"/>
                    </div>
                </div>
                <div>
                    <label>
                        Email:
                    </label>
                    <div>
                        <input type="text" v-model="profile.email"/>
                    </div>
                </div>
                <div>
                    <button>
                        Show QR Code
                    </button>
                </div>
                <div>
                    <button>
                        Change Password
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import Axios from 'axios';

@Component
export default class ProfileComponent extends Vue {
    private profile: any = null;
    private staticProfile: any = null;
    private loading: boolean = true;

    async loadProfile() {
        this.loading = true;

        await Axios({
            url: '/api/v1/user',
            method: 'get'
        }).then(res => {
            this.profile = res.data.data;
            this.staticProfile = JSON.parse(JSON.stringify(res.data.data));
        }).catch(err => {
            if(err.response) {
                alert(err.response.data.mesg || "Failed to load user profile!");
            }
        });

        this.loading = false;
    }

    mounted() {
        this.loadProfile();
    }
}
</script>

