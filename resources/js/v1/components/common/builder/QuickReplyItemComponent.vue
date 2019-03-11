<template>
    <li>
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
    </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from "vue-property-decorator";
import QuickReplyItemModel from "../../../models/bots/QuickReplyItemModel";

@Component
export default class QuickReplyItemComponent extends Vue {
    @Prop({
        type: QuickReplyItemModel
    }) qr!: QuickReplyItemModel;
    @Prop() isChildDeleting: any;
    @Prop() index: any;

    @Emit("delItem")
    deleteItem(index: any) {}

    @Emit("closeOtherSection")
    closeOtherSection(index: any) {}
}
</script>
