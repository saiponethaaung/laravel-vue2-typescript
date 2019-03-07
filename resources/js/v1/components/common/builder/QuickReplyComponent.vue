<template>
    <div class="componentTypeOne quickReplyRoot">
        <ul class="quickReplyRootContainer" ref="dropdownMenu">
            <li v-for="(qr, index) in content.item" :key="index">
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
                            <div>
                                <input
                                    placeholder="<Set attribute>"
                                    v-model="qr.attribute"
                                    v-on:blur="qr.saveContent()"
                                >
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
                    <div class="delIcon" @click="content.delItem(index)">
                        <i class="material-icons">delete</i>
                    </div>
                </div>
                <template v-if="content.isChildDeleting===index">
                    <div class="componentDeleting">
                        <div class="deletingContainer"></div>
                    </div>
                </template>
                <template v-if="qr.errorMesg!==''">
                    <error-component :mesg="qr.errorMesg" @closeError="qr.errorMesg=''"></error-component>
                </template>
            </li>
            <li v-if="content.item.length<11">
                <div class="quickReplyCapsule qrAddMore" v-if="content.isCreating">Creating...</div>
                <div class="quickReplyCapsule qrAddMore" @click="createNewQuickReply" v-else>
                    <i class="material-icons">add</i>Add Quick Reply
                </div>
            </li>
        </ul>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from "vue-property-decorator";
import QuickReplyContentModel from "../../../models/bots/QuickReplyContentModel";

@Component
export default class QuickReplyComponent extends Vue {
    @Prop({
        type: QuickReplyContentModel
    })
    content!: QuickReplyContentModel;

    async createNewQuickReply() {
        this.content.createQuickReply();
    }

    documentClick(e: any) {
        let el: any = this.$refs.dropdownMenu;
        let target = e.target;
        if (el !== target && !el.contains(target)) {
            this.closeOtherSection(-1);
        }
    }

    closeOtherSection(index: any) {
        for (let i in this.content.item) {
            if (i === index) continue;
            this.content.item[i].canShow = false;
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
