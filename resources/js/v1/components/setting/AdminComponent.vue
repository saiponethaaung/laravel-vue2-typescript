<template>
    <div>
        <div>
            <h5>Admins</h5>
            <div>
                <button type="buton">Activate</button>
                <button type="buton">Deactivate</button>
                <button type="button" @click="openAdminInvite=true">Invite</button>
            </div>
        </div>
        <div>
            <ul>
                <li>Members</li>
                <li>Pending invite</li>
            </ul>
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
}
</script>
