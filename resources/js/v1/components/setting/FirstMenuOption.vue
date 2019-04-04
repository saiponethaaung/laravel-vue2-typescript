<template>
    <div class="btnComponentTypeOne" ref="textBtn">
        <div class="buttonPopContent">
            <div class="buttonPopHeading">
                <p class="buttonPopInfo">Menu Name</p>
                <div class="actionInfo">
                    <div>
                        <input
                            type="text"
                            class="buttonNameInput"
                            maxlength="20"
                            v-model="menu.content.title"
                            v-on:focus="cancelUpdate()"
                            v-on:blur="updateContent()"
                            v-on:keyup.enter="updateContent(true)"
                        >
                        <span class="limitBtnTitle">{{ textLimit }}</span>
                    </div>
                </div>
            </div>
            <div class="buttonOptions">
                <div class="buttonActions">
                    <ul class="buttonOptions">
                        <li @click="menu.content.type=0" :class="{'activeOption': menu.content.type===0}">
                            <span class="optionContent">Blocks</span>
                        </li>
                        <li @click="menu.content.type=1" :class="{'activeOption': menu.content.type===1}">
                            <span class="optionContent">Url</span>
                        </li>
                        <li @click="menu.content.type=2" :class="{'activeOption': menu.content.type===2}">
                            <span class="optionContent">Sub Menu</span>
                        </li>
                    </ul>
                    <div class="buttonValueCon">
                        <div class="optionValue" v-if="menu.content.type===0">they receive the block
                            <template v-if="menu.content.blocks.length>0">
                                <div class="selectedBlockCon">
                                    <div class="selectedLinkedBlock">
                                        <span class="slbText">{{ menu.content.blocks[0].title }}</span>
                                        <div class="slbDel" @click="delBlock()">
                                            <i class="material-icons">delete</i>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <input
                                    type="text"
                                    v-model="blockKeyword"
                                    placeholder="Block name"
                                    @keyup="loadSuggestion()"
                                >
                                <template v-if="blockList.length>0">
                                    <div class="sugContainer">
                                        <div v-if="loading">Loading...</div>
                                        <div
                                            v-for="(b, index) in blockList"
                                            :key="index"
                                            class="sugBlock"
                                        >
                                            <div class="sugBlockTitle">{{ b.title }}</div>
                                            <div class="sugBlockSec">
                                                <div
                                                    v-for="(s, sindex) in b.contents"
                                                    :key="sindex"
                                                    class="sugBlockSecTitle"
                                                    @click="addBlock(index, sindex)"
                                                >{{ s.title }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                        <div class="optionValue" v-if="menu.content.type===1">
                            <input
                                type="text"
                                v-model="menu.content.url"
                                placeholder="Url"
                                v-on:focus="cancelUpdate()"
                                v-on:blur="updateContent()"
                            >
                        </div>
                        <div class="optionValue" v-if="menu.content.type===2">
                            Create a submenu for this menu item
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';
import { blockSuggestion } from '../../configuration/interface';
import Axios,{ CancelTokenSource } from 'axios';
import { Vue, Emit, Prop, Component } from 'vue-property-decorator';
import PersistentMenu from '../../models/PersistentMenu';

@Component
export default class FirstMenuOption extends Vue {
    @Prop() menu!: PersistentMenu;
    private blockKeyword: string = '';

    private saveBlock: boolean = false;
    private deleteBlock: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private blockList: Array<blockSuggestion> = [];
    private blockToken: CancelTokenSource = Axios.CancelToken.source();
    private updateToken: CancelTokenSource = Axios.CancelToken.source();

    private loading: boolean = false;
    
    documentClick(e: any) {
        let el: any = this.$refs.textBtn;
        let target = e.target;
        if (el !== target && !el.contains(target)) {
            this.updateContent();
            setTimeout(() => {
                this.closeContent(false);
            }, 500);
            return null;
        }
    }

    @Emit('closeContent')
    closeContent(status: boolean) {
    }

    async loadSuggestion() {
        this.loading = true;
        let suggestion = await this.ajaxHandler.searchSections(
            this.blockKeyword,
            this.$store.state.projectInfo.id
        );

        if (suggestion.type === "cancel") return;

        if (suggestion.status === false) {
            alert(suggestion.mesg);
            return;
        }

        this.blockList = suggestion.data;
        this.loading = false;
    }

    async addBlock(block: number, section: number) {
        this.blockToken.cancel();
        this.blockToken = Axios.CancelToken.source();

        this.saveBlock = true;

        let data = new FormData();
        data.append(
            "section",
            this.blockList[block].contents[section].id.toString()
        );
        data.append("_method", "put");

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu/${this.menu.content.id}/block`,
            data: data,
            method: "post",
            cancelToken: this.blockToken.token
        })
            .then((res: any) => {
                this.menu.content.blocks.push({
                    id: this.blockList[block].contents[section].id,
                    title: this.blockList[block].contents[section].title
                });

                this.blockList = [];
            })
            .catch((err: any) => {
                if (err.response) {
                    this.$store.state.errorMesg.push(
                        this.ajaxHandler.globalHandler(
                            err,
                            "Failed to connect a block!"
                        )
                    );
                }
            });

        this.saveBlock = false;
    }

    async delBlock() {
        this.deleteBlock = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu/${this.menu.content.id}/block`,
            method: "delete"
        })
            .then((res: any) => {
                this.menu.content.blocks = [];
            })
            .catch((err: any) => {
                if (err.response) {
                    this.$store.state.errorMesg.push(
                        this.ajaxHandler.globalHandler(
                            err,
                            "Failed to delete a block!"
                        )
                    );
                }
            });

        this.deleteBlock = false;
    }

    private cancelUpdate() {
        this.updateToken.cancel();
        this.updateToken = Axios.CancelToken.source();
    }

    async updateContent(close: boolean = false) {
        this.updateToken.cancel();
        this.updateToken = Axios.CancelToken.source();

        let data = new FormData();
        data.append("title", this.menu.content.title);
        data.append("url", this.menu.content.url);
        data.append("type", this.menu.content.type.toString());
        data.append("_method", "put");

        Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/persistent-menu/${this.menu.content.id}`,
            data: data,
            method: "post",
            cancelToken: this.updateToken.token
        })
            .then(res => {
                if (this.menu.content.type === 0) {
                    this.menu.content.url = "";
                } else if (this.menu.content.type === 1) {
                    this.menu.content.block = [];
                } else if (this.menu.content.type === 2) {
                    this.menu.content.block = [];
                    this.menu.content.url = "";
                }
            })
            .catch(err => {
                if (err.response) {
                    this.$store.state.errorMesg.push(
                        this.ajaxHandler.globalHandler(
                            err,
                            "Failed to update menu!"
                        )
                    );
                }
            });

        if (close) {
            this.closeContent(true);
        }
    }

    created() {
        document.addEventListener("click", this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener("click", this.documentClick);
    }

    get textLimit() {
        return 20 - this.menu.content.title.length;
    }
}
</script>
