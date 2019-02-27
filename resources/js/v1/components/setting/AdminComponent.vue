<template>
    <div>
        <div>
            <h5>Admins</h5>
            <div>
                <button type="button" @click="openAdminInvite=true">Invite</button>
            </div>
        </div>
        <div>
            <ul>
                <li @click="showSection=1">Members</li>
                <li @click="showSection=2">Pending invite</li>
            </ul>
            <div v-if="showSection==1">
                admin list
                <table class="userListTable">
                    <thead>
                        <tr>
                            <th>
                                <div class="ultWrapper">
                                    Name
                                </div>
                            </th>
                            <th class="utlGenderColumn">
                                <div class="ultWrapper">
                                    Email
                                </div>
                            </th>
                            <th>
                                <div class="ultWrapper">
                                    Role
                                </div>
                            </th>
                            <th class="ultDateColumn">
                                <div class="ultWrapper">
                                    Invited on
                                    <i class="material-icons">arrow_drop_down</i>
                                </div>
                            </th>
                            <th colspan="2" class="editColumn"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- <template v-if="userLoading"> -->
                            <!-- <tr>
                                <td colspan="7">Loading...</td>
                            </tr>
                        </template>
                        <template v-else> -->
                            <tr v-for="(admin, index) in adminList" :key="index">
                                <td>
                                    <div class="ultWrapper">
                                        <figure>
                                            <img src="/images/sample/logo.png"/>
                                        </figure>
                                        {{ admin.name }}
                                    </div>
                                </td>
                                <td class="utlGenderColumn">
                                    <div class="ultWrapper">
                                        {{ admin.email }}
                                    </div>
                                </td>
                                <td>
                                    <div class="ultWrapper">
                                        {{ getRole(admin.role) }}
                                    </div>
                                </td>
                                <td class="ultDateColumn">
                                    <div class="ultWrapper">
                                        {{ admin.invited_on }}
                                    </div>
                                </td>
                                <td class="utlIconColumn">
                                    <div class="ultWrapper iconCenter">
                                        <i class="material-icons ultEditIcon">create</i>
                                    </div>
                                </td>
                                <td class="utlIconColumn">
                                    <div class="ultWrapper iconCenter">
                                        <i class="material-icons ultEditIcon">delete_forever</i>
                                    </div>
                                </td>
                            </tr>
                        <!-- </template> -->
                    </tbody>
                </table>
            </div>
            <div v-if="showSection==2">
                Invite list
                <table class="userListTable">
                    <thead>
                        <tr>
                            <th class="utlGenderColumn">
                                <div class="ultWrapper">
                                    Email
                                </div>
                            </th>
                            <th>
                                <div class="ultWrapper">
                                    Role
                                </div>
                            </th>
                            <th class="ultDateColumn">
                                <div class="ultWrapper">
                                    Invited on
                                    <i class="material-icons">arrow_drop_down</i>
                                </div>
                            </th>
                            <th colspan="2" class="editColumn"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- <template v-if="userLoading"> -->
                            <!-- <tr>
                                <td colspan="7">Loading...</td>
                            </tr>
                        </template>
                        <template v-else> -->
                            <tr v-for="(invite, index) in inviteList" :key="index">
                                <td class="utlGenderColumn">
                                    <div class="ultWrapper">
                                        {{ invite.email }}
                                    </div>
                                </td>
                                <td>
                                    <div class="ultWrapper">
                                        {{ getRole(invite.role) }}
                                    </div>
                                </td>
                                <td class="ultDateColumn">
                                    <div class="ultWrapper">
                                        {{ invite.invited_on }}
                                    </div>
                                </td>
                                <td class="utlIconColumn">
                                    <div class="ultWrapper iconCenter">
                                        <i class="material-icons ultEditIcon">create</i>
                                    </div>
                                </td>
                                <td class="utlIconColumn">
                                    <div class="ultWrapper iconCenter">
                                        <i class="material-icons ultEditIcon">delete_forever</i>
                                    </div>
                                </td>
                            </tr>
                        <!-- </template> -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="popFixedContainer popFixedCenter popContainer" v-if="openAdminInvite">
            <div class="adminInviteForm">
                <form @submit.prevent="sendAnInvite()">
                    <div class="formHeading">
                        <h5>Invite new member</h5>
                        <button type="button" @click="openAdminInvite=false" :disabled="inviting">
                            <i class="material-icons">close</i>
                        </button>
                    </div>
                    <div class="">
                        <label>
                            <span>Email</span>
                            <input type="email" v-model="memberInfo.email" placeholder="Enter an email address" required/>
                        </label>
                    </div>
                    <div class="">
                        <label>
                            <span>Role</span>
                            <select v-model="memberInfo.role" required>
                                <option :value="0">Select a role</option>
                                <option :value="1">Admin</option>
                                <option :value="2">Manager</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <button type="submit">Invite</button>
                        <button type="button" @click="openAdminInvite=false" :disabled="inviting">Cancel</button>
                    </div>
                </form>
                <div class="popProcessing" v-if="inviting">Sending an invite...</div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import Axios from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class AdminComponent extends Vue {
    private openAdminInvite: boolean = false;
    private inviting: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private memberInfo: any = {
        email: "",
        role: 0
    };
    private adminList: Array<any> = [];
    private inviteList: Array<any> = [];
    private showSection: number = 1;

    mounted() {
        this.getMembers();
        this.getInviets();
    }

    private async sendAnInvite() {
        if(this.memberInfo.email==="") {
            alert("Email is required!");
            return;
        }

        if(this.memberInfo.email===this.$store.state.user.email) {
            alert("You cannot invite yourself!");
            return;
        }

        if(this.memberInfo.role===0) {
            alert("Role is required!");
            return;
        }

        this.inviting = true;

        let data = new FormData();
        data.append('email', this.memberInfo.email);
        data.append('role', this.memberInfo.role);

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/member`,
            data: data,
            method: 'post'
        }).then(res => {
            
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to invite new member!');
                alert(mesg);
            }
        });

        this.inviting = false;
    }

    private getRole(role: number) {
        let roleName = '';
        
        switch (role) {
            case 1:
                roleName = 'Admin';
                break;
        
            default:
                roleName = 'Manager';
                break;
        }

        return roleName;
    }

    private async getMembers() {
        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/member`,
            method: 'get'
        }).then(res => {
            this.adminList = res.data.data;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to invite new member!');
                alert(mesg);
            }
        });

        this.inviting = false;
    }

    private async getInviets() {
        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/member/invite`,
            method: 'get'
        }).then(res => {
            this.inviteList = res.data.data;
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to invite new member!');
                alert(mesg);
            }
        });

        this.inviting = false;
    }
}
</script>
