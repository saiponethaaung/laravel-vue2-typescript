<template>
    <div class="componentTypeOne quickReplyRoot">
        <ul class="quickReplyRootContainer" ref="dropdownMenu">
            <draggable
                v-model="content.item"
                class="draggable"
                handle=".horizontalDrag"
                @end="updateOrder"
            >
                <template v-for="(qr, index) in content.item">
                    <quick-reply-item-component
                        :key="index"
                        :qr="qr"
                        :isChildDeleting="content.isChildDeleting"
                        :index="index"
                        @delItem="delItem"
                        @closeOtherSection="closeOtherSection"
                    ></quick-reply-item-component>
                </template>
            </draggable>
            <li v-if="content.item.length<11">
                <div class="quickReplyCapsule qrAddMore" v-if="content.isCreating">Creating...</div>
                <div class="quickReplyCapsule qrAddMore" @click="createNewQuickReply" v-else>
                    <i class="material-icons">add</i>Add Quick Reply
                </div>
            </li>
        </ul>
        <template v-if="!isValid">
            <div class="quickReplyPositionError">
                <span class="noticIcon">
                    <i class="material-icons">warning</i>
                </span>
                <span
                    class="noticText"
                >Quick replies can be placed only under text, list, gallery or image cards</span>
            </div>
        </template>
        <template v-if="content.errorMesg!==''">
            <error-component :mesg="content.errorMesg" @closeError="content.errorMesg=''"></error-component>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from "vue-property-decorator";
import QuickReplyContentModel from "../../../models/bots/QuickReplyContentModel";
import QuickReplyItemComponent from "./QuickReplyItemComponent.vue";
import Axios from "axios";

@Component({
    components: {
        QuickReplyItemComponent
    }
})
export default class QuickReplyComponent extends Vue {
    @Prop({
        type: QuickReplyContentModel
    })
    content!: QuickReplyContentModel;
    @Prop({
        type: Boolean
    })
    isValid!: boolean;

    async createNewQuickReply() {
        this.content.createQuickReply();
    }

    async updateOrder() {
        await this.content.updateOrder();
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

    delItem(index: any) {
        this.content.delItem(index);
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
