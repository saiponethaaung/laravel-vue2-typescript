<template>
    <div class="inheritHFW ovAuto pageListRootCon">
        <h5>Persistent menu</h5>
        <template v-if="loading">
            Loading...
        </template>
        <template v-else>
            <div>
                <div>
                    <div class="menuHeading">
                        <h5>Send a message...</h5>
                    </div>
                    <template v-for="(firstMenu, index) in menu">
                        <first-menu-component :key="index" :menu="firstMenu"></first-menu-component>
                    </template>
                    <template v-if="menu.length<3">
                        <template v-if="adding">
                            Loading...
                        </template>
                        <template v-else>
                            <button @click="createMenu()">Add Menu Item</button>
                        </template>
                    </template>
                </div>
                <div>Second Menu</div>
                <div>Third Menu</div>
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import PersistentMenu from '../../models/PersistentMenu';
import Axios from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';
import FirstMenuComponent from './FirstMenuComponent.vue';

@Component({
    components: {
        FirstMenuComponent
    }
})
export default class PersistentMenuComponent extends Vue {
    private menu: PersistentMenu[] = [];
    private loading: boolean = true;
    private adding: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    mounted() {
        this.loadMenu();
    }

    private async loadMenu() {
        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu`,
            method: 'get'
        }).then(res => {
            for(let i of res.data.data) {
                this.menu.push(new PersistentMenu(i));
            }
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load persistent menu!');
                alert(mesg);
            }
        });

        this.loading = false;
    }

    private async createMenu() {
        this.adding = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu`,
            method: 'post'
        }).then(res => {
            this.menu.push(new PersistentMenu(res.data.data));
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new persistent menu!');
                alert(mesg);
            }
        });

        this.adding = false;
    }
}
</script>

