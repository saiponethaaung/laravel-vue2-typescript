<template>
    <div class="inheritHFW ovAuto pageListRootCon">
        <h5>Profile</h5>
        <template v-if="loading">
            Loading...
        </template>
        <template v-else>
            <div>
                <div>
                    <figure class="profileImageCon">
                        <img :src="profile.image ? profile.image : '/images/icons/default-user.jpg'"/>
                        <template v-if="edit && !updating && !uploading">
                            <label>
                                <i class="material-icons">camera_alt</i>
                                <input type="file" @change="uploadProfileImage"/>
                            </label>
                        </template>
                    </figure>
                </div>
                <div class="profileInputCon">
                    <label>
                        Name:
                    </label>
                    <div class="profileInput">
                        <input type="text" v-model="profile.name" :disabled="!edit || updating"/>
                    </div>
                </div>
                <div class="profileInputCon">
                    <label>
                        Email:
                    </label>
                    <div class="profileInput">
                        <input type="text" v-model="profile.email" :disabled="!edit || updating"/>
                    </div>
                </div>
                <div>
                    <template v-if="edit">
                        <template v-if="updating">
                            <div>Updating...</div>
                        </template>
                        <template v-else>
                            <div class="profileInputCon">
                                <button class="btnTypeTwo" type="button" @click="resetData()">
                                    Cancel
                                </button>
                                <button class="btnTypeOne" type="button" @click="updateProfile()">
                                    Update
                                </button>
                            </div>
                        </template>
                    </template>
                    <template v-else>
                        <button class="btnTypeOne" type="button" @click="edit=true">
                            Edit Profile
                        </button>
                    </template>
                </div>
                <div v-if="edit && !updating">
                    <template v-if="changePassword">
                        <template v-if="otppassvalid">
                            <div class="profileInputCon">
                                <label>
                                    Enter new password:
                                </label>
                                <template v-if="updatingPassword">
                                    <div>Updating password...</div>
                                </template>
                                <template v-else>
                                    <div class="profileInput">
                                        <input type="password" v-model="newpassword" placeholder="New Password"/>
                                    </div>
                                </template>
                            </div>
                            <template v-if="!updatingPassword">
                                <button @click="updatePassword" class="btnTypeOne" type="button">Update Password</button>
                            </template>
                        </template>
                        <template v-else>
                            <div class="profileInputCon">
                                <label>
                                    Enter otp code:
                                </label>
                                <template v-if="validatingOTP">
                                    <div>Validating otp...</div>
                                </template>
                                <template v-else>
                                    <div class="profileInput">
                                        <input type="password" v-model="otpCode" placeholder="OTP Code"/>
                                    </div>
                                </template>
                            </div>
                            <template v-if="!validatingOTP">
                                <button @click="validateOtp" class="btnTypeOne" type="button">Submit</button>
                            </template>
                        </template>
                    </template>
                    <template v-else>
                        <div class="profileInputCon">
                            <button class="btnTypeOne" type="button" @click="changePassword=true">
                                Change Password
                            </button>
                        </div>
                    </template>
                </div>
                <hr/>
                <div>
                    <template v-if="qrpassvalid">
                        <img :src="qrCodeImage"/>
                    </template>
                    <template v-else>
                        <template v-if="showQrCode">
                            <div class="profileInputCon">
                                <label>
                                    Enter password to see qr code:
                                </label>
                                <template v-if="validatingPassword">
                                    <div>Validating password...</div>
                                </template>
                                <template v-else>
                                    <div class="profileInput profileInputCon">
                                        <input type="password" v-model="qrpassword" placeholder="Password..."/>
                                    </div>
                                    <button class="btnTypeOne" type="button" @click="validatePassword()">Submit</button>
                                </template>
                            </div>
                        </template>
                        <template v-else>
                            <button class="btnTypeOne" type="button" @click="showQrCode=true">
                                Show QR Code
                            </button>
                        </template>
                    </template>
                </div>
                <template v-if="$store.state.user.facebook_connected">
                    <hr/>
                    <div id="fb-root" @click="logoutFB">
                        <div class="noclicking">
                            <div class="fb-login-button" data-width="" data-size="medium" data-auto-logout-link="true" data-use-continue-as="false"></div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import Axios from 'axios';

@Component
export default class ProfileComponent extends Vue {
    private edit: boolean = false;
    private profile: any = null;
    private staticProfile: any = null;
    private qrpassword: string = '';
    private otpCode: string = '';
    private newpassword: string = '';
    private showQrCode: boolean = false;
    private loading: boolean = true;
    private changePassword: boolean = false;
    private validatingPassword: boolean = false;
    private validatingOTP: boolean = false;
    private qrpassvalid: boolean = false;
    private otppassvalid: boolean = false;
    private updatingPassword: boolean = false;
    private updating: boolean = false;
    private qrCodeImage: string = '';
    private uploading: boolean = false;

    @Watch("$store.state.fbSdk", { immediate: true, deep: true })
    initSendToMessenger() {
        if (!this.$store.state.fbSdk) return;
        setTimeout(() => {
            FB.XFBML.parse();
        }, 30);
    }

    async logoutFB() {
        FB.getLoginStatus(async (response: any) => {
            console.log('res', response);
            setTimeout(() => {
                console.log('logout');
                FB.logout(async (response: any) => {
                    console.log("Am i log out");
                    await Axios({
                        url: '/api/v1/user/remove-facebook',
                        method: 'post',
                    }).then(res => {
                        this.$store.state.user.facebook_connected = false;
                    });
                });
            }, 30);
            console.log("end");
        });
    }

    async loadProfile() {
        this.loading = true;

        await Axios({
            url: '/api/v1/user',
            method: 'get'
        }).then(res => {
            this.profile = res.data.data.profile;
            this.staticProfile = JSON.parse(JSON.stringify(res.data.data.profile));
            setTimeout(() => {
                FB.XFBML.parse();
            }, 30);
        }).catch(err => {
            if(err.response) {
                alert(err.response.data.mesg || "Failed to load user profile!");
            }
        });

        this.loading = false;
    }

    async logout() {

    }

    async updateProfile() {
        this.updating = true;

        let data =  new FormData();
        data.append('name', this.profile.name);
        data.append('email', this.profile.email);

        await Axios({
            url: '/api/v1/user',
            data: data,
            method: 'post'
        }).then(res => {
            this.staticProfile = JSON.parse(JSON.stringify(this.profile));
            this.edit = false;
            this.changePassword = false;
        }).catch(err => {
            if(err.response) {
                alert(err.response.data.mesg || "Failed to update user profile!");
            }
        });

        this.updating = false;
    }

    async validatePassword() {
        if(this.qrpassword=='') {
            alert("Password is required!");
            return;
        }

        this.validatingPassword = true;

        let data = new FormData();
        data.append('password', this.qrpassword);

        await Axios({
            url: '/api/v1/user/get-qrcode',
            data: data,
            method: 'post'
        }).then(res => {
            this.qrpassvalid = true;
            this.qrCodeImage = res.data.data.url;
        }).catch(err => {
            if(err.response) {
                alert(err.response.data.mesg || 'Failed to validate password!');
            }
        });

        this.validatingPassword = false;
    }

    async validateOtp() {
        if(this.otpCode=='') {
            alert("OTP is required!");
            return;
        }

        this.validatingOTP = true;

        let data = new FormData();
        data.append('otp', this.otpCode);

        await Axios({
            url: '/api/v1/user/validate-otp',
            data: data,
            method: 'post'
        }).then(res => {
            this.otppassvalid = true;
        }).catch(err => {
            if(err.response) {
                alert(err.response.data.mesg || 'Failed to validate otp!');
            }
        });

        this.validatingOTP = false;
    }

    async updatePassword() {
        if(this.newpassword=='') {
            alert("Password is required!");
            return;
        }

        this.updatingPassword = true;

        let data = new FormData();
        data.append('password', this.newpassword);

        await Axios({
            url: '/api/v1/user/update-password',
            data: data,
            method: 'post'
        }).then(res => {
            this.changePassword = false;
            this.updatingPassword = false;
            this.otppassvalid = false;
        }).catch(err => {
            if(err.response) {
                alert(err.response.data.mesg || 'Failed to update otp!');
            }
        });

        this.updatingPassword = false;
    }

    async uploadProfileImage(e: any) {
        let data = new FormData();
        data.append('image', e.target.files[0]);

        Axios({
            url: '/api/v1/user/upload-image',
            data: data,
            method: 'post'
        }).then(res => {
            this.profile.image = res.data.data.image;
            this.staticProfile.image = res.data.data.image;
        }).catch(err => {
            if(err.response) {
                alert(err.response.data.mesg || 'Failed to upload user profile image!');
            }
        });
    }

    resetData() {
        this.profile = JSON.parse(JSON.stringify(this.staticProfile));
        this.edit = false;
    }

    mounted() {
        this.loadProfile();
    }
}
</script>

