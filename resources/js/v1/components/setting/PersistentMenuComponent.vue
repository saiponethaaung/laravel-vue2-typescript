<template>
    <div class="inheritHFW ovAuto pageListRootCon">
        <h5>Persistent menu</h5>
        <template v-if="loading">
            Loading...
        </template>
        <template v-else>
            <div class="persistentMenuRoot">
                <div class="persistentMenuCon" v-if="selectedSecond==-1">
                    <!-- <div class="menuHeading">
                        <h5>Send a message...</h5>
                    </div> -->
                    <template v-for="(firstMenu, index) in menu">
                        <first-menu-component :key="index" :index="index" v-on:selected="index => {
                            selectedFirst=index;
                            selectedSecond=-1;
                        }" v-on:deleteFirst="deleteFirst(index)" :menu="firstMenu"></first-menu-component>
                    </template>
                    <template v-if="menu.length<3">
                        <template v-if="adding">
                            <div class="persistentMenuComponent">
                                Loading...
                            </div>
                        </template>
                        <template v-else>
                            <button class="addPersistentMenuBtn" @click="createMenu()">
                                <i class="material-icons">add</i><span>Add Menu Item</span>
                            </button>
                        </template>
                    </template>
                </div>
                <template v-if="selectedFirst!==-1 && menu[selectedFirst].content.type==2 ">
                    <div class="persistentMenuCon">
                        <div class="menuSubHeading">
                            <div class="backNavigate" @click="selectedFirst=-1;selectedSecond=-1;">
                                <i class="material-icons">chevron_left</i>
                            </div>
                            <h5 class="parentName">{{ menu[selectedFirst].content.title ? menu[selectedFirst].content.title : '-' }}</h5>
                        </div>
                        <template v-for="(secondMenu, index) in menu[selectedFirst].item">
                            <second-menu-component :key="index" :index="index" v-on:selected="index => {
                                selectedSecond=index
                            }" v-on:deleteSecond="deleteSecond(index)" :menu="secondMenu"></second-menu-component>
                        </template>
                        <template v-if="menu[selectedFirst].item.length<5">
                            <template v-if="menu[selectedFirst].creating">
                                <div class="persistentMenuComponent">
                                    Loading...
                                </div>
                            </template>
                            <template v-else>
                                <button class="addPersistentMenuBtn" @click="createSecondMenu()">
                                    <i class="material-icons">add</i><span>Add Menu Item</span>
                                </button>
                            </template>
                        </template>
                    </div>
                </template>
                <template v-if="selectedFirst!==-1 && menu[selectedFirst].content.type==2 && selectedSecond!==-1 && menu[selectedFirst].item[selectedSecond].content.type===2">
                    <div class="persistentMenuCon">
                        <div class="menuSubHeading">
                            <div class="backNavigate" @click="selectedSecond=-1;">
                                <i class="material-icons">chevron_left</i>
                            </div>
                            <h5 class="parentName">{{ menu[selectedFirst].item[selectedSecond].content.title ? menu[selectedFirst].item[selectedSecond].content.title : '-' }}</h5>
                        </div>
                        <template v-for="(thirdMenu, index) in menu[selectedFirst].item[selectedSecond].item">
                            <third-menu-component :key="index" :index="index" v-on:deleteThird="deleteThird(index)" :menu="thirdMenu"></third-menu-component>
                        </template>
                        <template v-if="menu[selectedFirst].item[selectedSecond].item.length<5">
                            <template v-if="menu[selectedFirst].item[selectedSecond].creating">
                                <div class="persistentMenuComponent">
                                    Loading...
                                </div>
                            </template>
                            <template v-else>
                                <button class="addPersistentMenuBtn" @click="createThirdMenu()">
                                    <i class="material-icons">add</i><span>Add Menu Item</span>
                                </button>
                            </template>
                        </template>
                    </div>
                </template>
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
import SecondMenuComponent from './SecondMenuComponent.vue';
import ThirdMenuComponent from './ThirdMenuComponent.vue';

@Component({
    components: {
        FirstMenuComponent,
        SecondMenuComponent,
        ThirdMenuComponent
    }
})
export default class PersistentMenuComponent extends Vue {
    private menu: PersistentMenu[] = [];
    private loading: boolean = true;
    private adding: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private selectedFirst: any = -1;
    private selectedSecond: any = -1;

    mounted() {
        this.loadMenu();
    }

    private async loadMenu() {
        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu`,
            method: 'get'
        }).then(res => {
            for(let i of res.data.data) {
                this.menu.push(new PersistentMenu(i, this.$store.state.projectInfo.id));
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
            this.menu.push(new PersistentMenu(res.data.data, this.$store.state.projectInfo.id));
        }).catch(err => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new persistent menu!');
                alert(mesg);
            }
        });

        this.adding = false;
    }

    private async createSecondMenu() {
        let createMenu = await this.menu[this.selectedFirst].createSecondMenu();

        if(!createMenu.status) {
            alert(createMenu.mesg);
        }
    }

    private async createThirdMenu() {
        let createMenu = await this.menu[this.selectedFirst].item[this.selectedSecond].createThirdMenu();

        if(!createMenu.status) {
            alert(createMenu.mesg);
        }
    }

    private async deleteFirst(index: any) {
        if(confirm("Are you sure you want to delete this menu?")) {
            await Axios({
                url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu/${this.menu[index].content.id}`,
                method: 'delete'
            }).then(res => {
                if(this.selectedFirst==index) {
                    this.selectedFirst = -1;
                    this.selectedSecond = -1;
                } else if(this.selectedFirst>index) {
                    this.selectedFirst--;
                }
                this.menu.splice(index, 1);
            }).catch(err => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to remove persistent menu!');
                    alert(mesg);
                }
            })
        }
    }

    private async deleteSecond(index: any) {
        if(confirm("Are you sure you want to delete this menu?")) {
            await Axios({
                url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu/${this.menu[this.selectedFirst].content.id}/${this.menu[this.selectedFirst].item[index].content.id}`,
                method: 'delete'
            }).then(res => {
                if(this.selectedSecond==index) {
                    this.selectedSecond = -1;
                } else if(this.selectedSecond>index) {
                    this.selectedSecond--;
                }
                this.menu[this.selectedFirst].item.splice(index, 1);
            }).catch(err => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to remove persistent menu!');
                    alert(mesg);
                }
            })
        }
    }

    private async deleteThird(index: any) {
        if(confirm("Are you sure you want to delete this menu?")) {
            await Axios({
                url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu/${this.menu[this.selectedFirst].content.id}/${this.menu[this.selectedFirst].item[this.selectedSecond].content.id}/${this.menu[this.selectedFirst].item[this.selectedSecond].item[index].content.id}`,
                method: 'delete'
            }).then(res => {
                this.menu[this.selectedFirst].item[this.selectedSecond].item.splice(index, 1);
            }).catch(err => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to remove persistent menu!');
                    alert(mesg);
                }
            })
        }
    }
}
</script>

