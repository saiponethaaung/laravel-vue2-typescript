<template>
    <div class="btnComponentTypeOne" ref="textBtn">
        <div class="buttonPopContent">
            <div class="buttonPopHeading">
                <p class="buttonPopInfo">Button Name</p>
                <div class="actionInfo">
                    <div>
                        <input
                            type="text"
                            class="buttonNameInput"
                            maxlength="20"
                            v-model="button.title"
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
                        <li @click="button.type=0" :class="{'activeOption': button.type===0}">
                            <span class="optionContent">Blocks</span>
                        </li>
                        <li @click="button.type=1" :class="{'activeOption': button.type===1}">
                            <span class="optionContent">Url</span>
                        </li>
                        <li @click="button.type=2" :class="{'activeOption': button.type===2}">
                            <span class="optionContent">Phone call</span>
                        </li>
                    </ul>
                    <div class="buttonValueCon">
                        <div class="optionValue" v-if="button.type===0">they receive the block
                            <template v-if="button.block.length>0">
                                <div class="selectedBlockCon">
                                    <div class="selectedLinkedBlock">
                                        <span class="slbText">{{ button.block[0].title }}</span>
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
                            <div class="attributeNotice">
                                <span>Save reply as attribute:</span>
                            </div>
                            <div class="attrTitleInputSuggestion" ref="attrTitleSuggest">
                                <input
                                    placeholder="<Set attribute>"
                                    v-model="button.attribute.title"
                                    @keyup="searchKeySuggestion"
                                    :class="{'hasKeywordSuggest': keySuggestion.length>0}"
                                >
                                <template v-if="keySuggestion.length>0">
                                    <div class="attrKeySuggestCon" ref="suggestion">
                                        <ul>
                                            <template v-for="(key, index) in keySuggestion">
                                                <li
                                                    :key="index"
                                                    @click="button.attribute.title=key;keySuggestion=[];"
                                                >{{ key }}</li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <input
                                    class="noMgb"
                                    placeholder="<Set value>"
                                    v-model="button.attribute.value"
                                >
                            </div>
                        </div>
                        <div class="optionValue" v-if="button.type===1">
                            <input
                                type="text"
                                v-model="button.url"
                                placeholder="Url"
                                v-on:focus="cancelUpdate()"
                                v-on:blur="updateContent()"
                            >
                        </div>
                        <div class="optionValue" v-if="button.type===2">
                            <input
                                type="text"
                                v-model="button.phone.number"
                                placeholder="Phone number"
                                v-on:focus="cancelUpdate()"
                                v-on:blur="updateContent()"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue, Emit } from "vue-property-decorator";
import {
    buttonContent,
    blockSuggestion
} from "../../../configuration/interface";
import AjaxErrorHandler from "../../../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";

@Component
export default class ButtonComponent extends Vue {
    @Prop() button!: buttonContent;
    @Prop() rootUrl!: string;
    @Prop() projectid!: string;

    private saveBlock: boolean = false;
    private deleteBlock: boolean = false;
    private blockKeyword: string = "";
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private blockList: Array<blockSuggestion> = [];
    private blockToken: CancelTokenSource = Axios.CancelToken.source();
    private updateToken: CancelTokenSource = Axios.CancelToken.source();

    private keyTimeout: any = null;
    private keyLoading: boolean = false;
    private showSuggest: boolean = false;
    private keySuggestion: any[] = [];
    private keyCancelToken: CancelTokenSource = Axios.CancelToken.source();

    @Emit("closeContent")
    closeContent(status: boolean) {
        this.keySuggestion = [];
    }

    documentClick(e: any) {
        let el: any = this.$refs.textBtn;
        let attrTitleSuggest: any = this.$refs.attrTitleSuggest;

        let target = e.target;
        if (el !== target && !el.contains(target)) {
            this.updateContent();
            setTimeout(() => {
                this.closeContent(true);
            }, 500);
            return null;
        }

        if (attrTitleSuggest !== target && !attrTitleSuggest.contains(target)) {
            this.keySuggestion = [];
            return null;
        }

        this.closeContent(false);
    }

    async loadSuggestion() {
        let suggestion = await this.ajaxHandler.searchSections(
            this.blockKeyword,
            this.projectid
        );

        if (suggestion.type === "cancel") return;

        if (suggestion.status === false) {
            alert(suggestion.mesg);
            return;
        }

        this.blockList = suggestion.data;
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
            url: `${this.rootUrl}/${this.button.id}/block`,
            data: data,
            method: "post",
            cancelToken: this.blockToken.token
        })
            .then((res: any) => {
                this.button.block.push({
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
            url: `${this.rootUrl}/${this.button.id}/block`,
            method: "delete"
        })
            .then((res: any) => {
                this.button.block = [];
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
        data.append("title", this.button.title);
        data.append("url", this.button.url);
        data.append(
            "number",
            this.button.phone.number ? this.button.phone.number.toString() : ""
        );
        data.append("type", this.button.type.toString());
        data.append("attrTitle", this.button.attribute.title);
        data.append("attrValue", this.button.attribute.value);
        data.append("_method", "put");

        Axios({
            url: `${this.rootUrl}/${this.button.id}`,
            data: data,
            method: "post",
            cancelToken: this.updateToken.token
        })
            .then(res => {
                if (this.button.type === 0) {
                    this.button.url = "";
                    this.button.phone.number = null;
                } else if (this.button.type === 1) {
                    this.button.block = [];
                    this.button.phone.number = null;
                } else if (this.button.type === 2) {
                    this.button.block = [];
                    this.button.url = "";
                }
            })
            .catch(err => {
                if (err.response) {
                    this.$store.state.errorMesg.push(
                        this.ajaxHandler.globalHandler(
                            err,
                            "Failed to update button!"
                        )
                    );
                }
            });

        if (close) {
            this.closeContent(true);
        }
    }

    async searchKeySuggestion(e: any) {
        console.log(e);
        if (
            e.keyCode == 37 ||
            e.keyCode == 38 ||
            e.keyCode == 39 ||
            e.keyCode == 40 ||
            e.keyCode == 17 ||
            e.keyCode == 16 ||
            e.keyCode == 18 ||
            (e.ctrlKey && e.keyCode == 65)
        ) {
            return;
        }

        this.keyCancelToken.cancel();
        this.keyLoading = false;
        this.showSuggest = true;
        clearTimeout(this.keyTimeout);

        if (this.button.attribute.title == "") return;

        this.keyLoading = true;
        this.keyTimeout = setTimeout(async () => {
            this.keyCancelToken = Axios.CancelToken.source();

            this.keySuggestion = [];

            let data = new FormData();
            data.append("keyword", this.button.attribute.title);

            await Axios({
                url: `/api/v1/project/${
                    this.$store.state.projectInfo.id
                }/attributes/serach/attribute`,
                data: data,
                method: "post",
                cancelToken: this.keyCancelToken.token
            })
                .then(res => {
                    this.keySuggestion = res.data.data;
                })
                .catch(err => {
                    if (err.response) {
                        this.$store.state.errorMesg.push(
                            this.ajaxHandler.globalHandler(
                                err,
                                "Failed to load attribute name suggestion!"
                            )
                        );
                    }
                });

            this.keyLoading = false;
        }, 1000);
    }

    created() {
        document.addEventListener("click", this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener("click", this.documentClick);
    }

    get textLimit() {
        return 20 - this.button.title.length;
    }
}
</script>
