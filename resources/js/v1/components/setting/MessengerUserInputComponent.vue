<template>
    <div class="inheritHFW ovAuto pageListRootCon">
        <h5>Messenger user input</h5>
        <template v-if="loading">
            Loading...
        </template>
        <template v-else>
            <p>By enabling this function user will no longer have an ability to use text input box in messenger and it could effect the chatflow if project use user input chat block.</p>
        </template>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import PersistentMenu from '../../models/PersistentMenu';
import Axios from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class MessengerUserInputComponent extends Vue {
    private loading: boolean = true;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    mounted() {
        // this.loadMenu();
    }

    // private async loadMenu() {
    //     await Axios({
    //         url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu`,
    //         method: 'get'
    //     }).then(res => {
    //         for(let i of res.data.data) {
    //             this.menu.push(new PersistentMenu(i, this.$store.state.projectInfo.id));
    //         }
    //     }).catch(err => {
    //         if(err.response) {
    //             let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load persistent menu!');
    //             alert(mesg);
    //         }
    //     });

    //     this.loading = false;
    // }

}
</script>

