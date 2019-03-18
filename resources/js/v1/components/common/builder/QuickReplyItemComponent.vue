<template>
    <li class="horizontalDragCon">
        <div class="quickReplyCapsule">
            <div
                class="quickReplyTitle"
                @click="closeOtherSection(index);qr.canShow=!qr.canShow;"
            >{{ qr.title ? qr.title : 'Enter button name'}}</div>
            <div
                class="quickReplyValue"
                @click="closeOtherSection(index);qr.canShow=!qr.canShow;"
                v-if="qr.block.length>0"
            >{{ qr.block[0].title }}</div>
            <div class="quickReplyInfoBox" :class="{'showInfoBox': qr.canShow}">
                <div class="QRActionName">
                    <input
                        placeholder="Title"
                        maxlength="20"
                        v-model="qr.title"
                        v-on:blur="qr.saveContent()"
                    >
                    <span class="limitReplyTitle">{{ qr.textLimitTitle }}</span>
                </div>
                <div>
                    <div>
                        <template v-if="qr.block.length>0">
                            <div class="selectedLinkedBlock">
                                <span class="slbText">{{ qr.block[0].title }}</span>
                                <div class="slbDel" @click="qr.delButton()">
                                    <i class="material-icons">delete</i>
                                </div>
                                <template v-if="qr.isBtnProcess">
                                    <div class="componentDeleting">
                                        <div class="deletingContainer"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template v-else>
                            <input
                                v-model="qr.blockKey"
                                @keyup="qr.loadSuggestion()"
                                placeholder="Enter block name"
                            >
                            <template v-if="qr.blockList.length>0">
                                <div class="sugContainer">
                                    <div
                                        v-for="(b, index) in qr.blockList"
                                        :key="index"
                                        class="sugBlock"
                                    >
                                        <div class="sugBlockTitle">{{ b.title }}</div>
                                        <div class="sugBlockSec">
                                            <div
                                                v-for="(s, sindex) in b.contents"
                                                :key="sindex"
                                                class="sugBlockSecTitle"
                                                @click="qr.addBlock(index, sindex)"
                                            >{{ s.title }}</div>
                                        </div>
                                    </div>
                                    <template v-if="qr.isBtnProcess">
                                        <div class="componentDeleting">
                                            <div class="deletingContainer"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </template>
                    </div>
                    <div class="attributeNotice">
                        <span>Save reply as attribute:</span>
                    </div>
                    <div class="attrTitleInputSuggestion" ref="attrTitleSuggest">
                        <input
                            placeholder="<Set attribute>"
                            class="qrAttr"
                            v-model="qr.attribute"
                            v-on:blur="qr.saveContent()"
                            @keyup="searchKeySuggestion"
                            :class="{'hasKeywordSuggest': keySuggestion.length>0}"
                        >
                        <template v-if="keySuggestion.length>0">
                            <div class="attrKeySuggestCon" ref="suggestion">
                                <ul>
                                    <template v-for="(key, index) in keySuggestion">
                                        <li
                                            :key="index"
                                            @click="qr.attribute=key;keySuggestion=[];"
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
                            v-model="qr.value"
                            v-on:blur="qr.saveContent()"
                        >
                    </div>
                </div>
            </div>
            <div class="delIcon" @click="deleteItem(index)">
                <i class="material-icons">delete</i>
            </div>
        </div>
        <template v-if="isChildDeleting===index">
            <div class="componentDeleting">
                <div class="deletingContainer"></div>
            </div>
        </template>
        <template v-if="qr.errorMesg!==''">
            <error-component :mesg="qr.errorMesg" @closeError="qr.errorMesg=''"></error-component>
        </template>
        <div class="horizontalDrag">
            <i class="material-icons">unfold_more</i>
        </div>
    </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from "vue-property-decorator";
import QuickReplyItemModel from "../../../models/bots/QuickReplyItemModel";
import Axios, { CancelTokenSource } from "axios";
import AjaxErrorHandler from "../../../utils/AjaxErrorHandler";

@Component
export default class QuickReplyItemComponent extends Vue {
    @Prop({
        type: QuickReplyItemModel
    })
    qr!: QuickReplyItemModel;
    @Prop() isChildDeleting: any;
    @Prop() index: any;

    @Emit("delItem")
    deleteItem(index: any) {}

    @Emit("closeOtherSection")
    closeOtherSection(index: any) {}

    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    private keyTimeout: any = null;
    private keyLoading: boolean = false;
    private showSuggest: boolean = false;
    private keySuggestion: any[] = [];
    private keyCancelToken: CancelTokenSource = Axios.CancelToken.source();

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

        if (this.qr.attribute == "") return;

        this.keyLoading = true;
        this.keyTimeout = setTimeout(async () => {
            this.keyCancelToken = Axios.CancelToken.source();

            this.keySuggestion = [];

            let data = new FormData();
            data.append("keyword", this.qr.attribute);

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

    documentClick(e: any) {
        let el: any = this.$refs.attrTitleSuggest;
        let target = e.target;
        if (el !== target && !el.contains(target)) {
            this.keySuggestion = [];
            return null;
        }
    }

    created() {
        document.addEventListener("click", this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener("click", this.documentClick);
    }
}
</script>
