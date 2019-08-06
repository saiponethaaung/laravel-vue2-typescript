<template>
    <div class="inheritHFW ovAuto pageListRootCon">
        <h5>Messenger user input</h5>
        <label>
            <input type="checkbox" v-model="$store.state.projectInfo.inputDisabled"/>
            Disable user input
        </label>
        <p>By enabling this function user will no longer have an ability to use text input box in messenger and it could effect the chatflow if project use user input chat block.</p>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Watch } from 'vue-property-decorator';
import PersistentMenu from '../../models/PersistentMenu';
import Axios from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class MessengerUserInputComponent extends Vue {
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private updating: boolean = false;

    mounted() {
        // this.loadMenu();
    }

    @Watch('$store.state.projectInfo.inputDisabled')
    public async updateUserInput() {
        if(this.updating) return;
        this.updating = true;

        let data = new FormData();
        data.append('status', this.$store.state.projectInfo.inputDisabled);

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/user-input`,
            data: data,
            method: 'post'
        }).then(res => {
            
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to update input status');
                this.$store.state.projectInfo.inputDisabled = !this.$store.state.projectInfo.inputDisabled;
                alert(mesg);
            }
        });

        this.updating = false;
    }
}
</script>

