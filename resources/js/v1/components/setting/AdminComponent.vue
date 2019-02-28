<template>
    <div class="userListRoot">
        <div class="adminHeadingCon">
            <h5>Admins</h5>
            <div class="adminHeadingAction">
                <button type="button" class="inviteMemberButton" @click="openAdminInvite=true">
                    <i class="material-icons">add</i>
                    <span>Invite</span>
                </button>
            </div>
        </div>
        <div>
            <ul class="sectionList">
                <li @click="showSection=1" :class="{'activeMSection': showSection===1}">Members ({{ adminList.length }})</li>
                <li class="sectionSeparator">|</li>
                <li @click="showSection=2" :class="{'activeMSection': showSection===2}">Pending invite ({{ inviteList.length }})</li>
            </ul>
            <div class="memberListCon" v-if="showSection==1">
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
                                    <!-- <i class="material-icons">arrow_drop_down</i> -->
                                </div>
                            </th>
                            <th colspan="1" class="editColumn"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="memberLoading">
                            <tr>
                                <td colspan="6">Loading...</td>
                            </tr>
                        </template>
                        <template v-else>
                            <template v-if="adminList.length>0">
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
                                    <!-- <td class="utlIconColumn">
                                        <div class="ultWrapper iconCenter">
                                            <i class="material-icons ultEditIcon">create</i>
                                        </div>
                                    </td> -->
                                    <td class="utlIconColumn">
                                        <div class="ultWrapper iconCenter">
                                            <i class="material-icons ultEditIcon" @click="deleteMember(index)">delete_forever</i>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="6">There is no member.</td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="memberListCon" v-if="showSection==2">
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
                            <th class="editColumn"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="inviteLoading">
                            <tr>
                                <td colspan="4">Loading...</td>
                            </tr>
                        </template>
                        <template v-else>
                            <template v-if="inviteList.length>0">
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
                                            <i class="material-icons ultEditIcon" @click="cancelInvite(index)">delete_forever</i>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="4">There is no pending invite.</td>
                                </tr>
                            </template>
                        </template>
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
    private inviteLoading: boolean = false;
    private memberLoading: boolean = false;

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
            if(res.data.data.type==1) {
                this.inviteList.push(res.data.data.info);
            } else {
                this.adminList.push(res.data.data.info);
            }
            this.openAdminInvite = false;
            this.memberInfo.email = '';
            this.memberInfo.role = 0;
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
        this.memberLoading = true;

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

        this.memberLoading = false;
    }

    private async getInviets() {
        this.inviteLoading = true;

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

        this.inviteLoading = false;
    }

    private async cancelInvite(index: any) {
        if(confirm("Are you sure you want to cancel this invitation?")) {
            await Axios({
                url: `/api/v1/project/${this.$route.params.projectid}/member/invite/${this.inviteList[index].id}`,
                method: 'delete'
            }).then(res => {
                this.inviteList.splice(index, 1);
            }).catch(err => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to cancel an invitation!');
                    alert(mesg);
                }
            });
        }
    }

    private async deleteMember(index: any) {
        if(confirm("Are you sure you want to delete this member?")) {
            await Axios({
                url: `/api/v1/project/${this.$route.params.projectid}/member/${this.adminList[index].id}`,
                method: 'delete'
            }).then(res => {
                this.adminList.splice(index, 1);
            }).catch(err => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to delete a member!');
                    alert(mesg);
                }
            });
        }
    }
}
</script>
