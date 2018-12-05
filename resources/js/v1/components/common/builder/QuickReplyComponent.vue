<template>
    <div class="componentTypeOne quickReplyRoot">
        <ul class="quickReplyRootContainer">
            <li v-for="(qr, index) in content.item" :key="index">
                <div class="quickReplyCapsule">
                    <div class="quickReplyTitle" @click="qr.canShow=!qr.canShow">{{ qr.title ? qr.title : 'Enter button name'}}</div>
                    <div class="quickReplyValue" @click="qr.canShow=!qr.canShow">Shoppper</div>
                    <div class="quickReplyInfoBox" :class="{'showInfoBox': qr.canShow}">
                        <div>
                            <input placeholder="Title"/>
                        </div>
                        <div>
                            <hr/>
                            <div><input placeholder="Enter block name"/></div>
                            <div><span>Save reply as attribute:</span></div>
                            <div><input placeholder="<set attribute>"></div>
                            <div><input placeholder="<set value>"></div>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div class="quickReplyCapsule qrAddMore" v-if="content.isCreating">
                    Creating...
                </div>
                <div class="quickReplyCapsule qrAddMore" @click="createNewQuickReply" v-else>
                    <i class="material-icons">add</i>Add Quick Reply
                </div>
            </li>
        </ul>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import QuickReplyContentModel from '../../../models/bots/QuickReplyContentModel';

@Component
export default class QuickReplyComponent extends Vue {

    @Prop({
        type: QuickReplyContentModel,
    }) content!: QuickReplyContentModel;

    async createNewQuickReply() {
        this.content.createQuickReply();
    }
}
</script>
