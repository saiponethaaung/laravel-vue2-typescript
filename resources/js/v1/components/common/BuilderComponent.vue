<template>
    <div class="contentRoot">
        <div class="contentList">
            <div v-for="(content, index) in contents" :key="index">
                <component :is="getComponent(content.type)" :content="content"></component>
            </div>
        </div>
        <div v-for="i in creating" :key="i">
            Loading...
        </div>
        <div class="contentBuilderWidget">
            <h5 class="contentActionMesg">Add Message</h5>
            <div>
                <ul class="contentActionRoot">
                    <li class="contentActionList" @click="addText">
                        <i class="material-icons">speaker_notes</i>
                        <span class="contentActionName">Text</span>
                    </li>
                    <li class="contentActionList" @click="addTyping">
                        <i class="material-icons">speaker_notes</i>
                        <span class="contentActionName">Typing</span>
                    </li>
                    <li class="contentActionList" @click="addQuickReply">
                        <i class="material-icons">reply</i>
                        <span class="contentActionName">Quick Reply</span>
                    </li>
                    <li class="contentActionList">
                        <i class="material-icons">textsms</i>
                        <span class="contentActionName">User Input</span>
                    </li>
                    <li class="contentActionList" @click="addList">
                        <i class="material-icons">list</i>
                        <span class="contentActionName">List</span>
                    </li>
                    <li class="contentActionList" @click="addGallery">
                        <i class="material-icons">add_to_photos</i>
                        <span class="contentActionName">Gallery</span>
                    </li>
                    <li class="contentActionList">
                        <i class="material-icons">insert_photo</i>
                        <span class="contentActionName">Image</span>
                    </li>
                    <li class="contentActionList">
                        <i class="material-icons">stars</i>
                        <span class="contentActionName">Plugins</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>


<script lang="ts">
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import Axios from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

import TextComponent from './builder/TextComponent.vue';
import TypingComponent from './builder/TypingComponent.vue';
import ListComponent from './builder/ListComponent.vue';
import GalleryComponent from './builder/GalleryComponent.vue';
import QuickReplyComponent from './builder/QuickReplyComponent.vue';

import TextContentModel from '../../models/bots/TextContentModel';
import TypingContentModel from '../../models/bots/TypingContentModel';
import ListContentModel from '../../models/bots/ListContentModel';
import GalleryContentModel from '../../models/bots/GalleryContentModel';
import QuickReplyContentModel from '../../models/bots/QuickReplyContentModel';

@Component({
    components: {
        TextComponent,
        TypingComponent,
        ListComponent,
        GalleryComponent
    }
})
export default class BuilderComponent extends Vue {
    private contents: Array<any> = [];
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private creating: number = 0;

    @Prop({
        type: Array,
        default: []
    }) value!: Array<any>;

    mounted() {
        for(let i in this.value) {
            this.buildConetnt(this.value[i]);
        }
    }

    async addText() {
        await this.appendComponent({
            name: 'Text section',
            type: 1
        });
    }

    async addTyping() {
        await this.appendComponent({
            name: 'Typing section',
            type: 2
        });
    }

    async addQuickReply() {
        await this.appendComponent({
            name: 'Quick Reply section',
            type: 3
        });
    }

    async addList() {
        await this.appendComponent({
            name: 'List section',
            type: 5
        });
    }
    
    async addGallery() {
        await this.appendComponent({
            name: 'Gallery section',
            type: 6
        });
    }

    async appendComponent(content: any) {
        let data = new FormData();
        data.append('type', content.type);

        this.creating++;
        await Axios({
            url: `/api/v1/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}/content`,
            data: data,
            method: 'post'
        }).then((res: any) => {
            this.buildConetnt(res.data.data);
        }).catch((err: any) => {
            let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new content!');
            alert(mesg);
        });

        if(this.creating>0) {
            this.creating--;
        } else {
            this.creating = 0;
        }
    }

    private buildConetnt(value: any) {
        switch(value.type) {
            case(1):
                this.contents.push(new TextContentModel(value));
                break;

            case(2):
                this.contents.push(new TypingContentModel(value));
                break;

            case(3):
                this.contents.push(new QuickReplyContentModel(value));
                break;

            case(5):
                this.contents.push(new ListContentModel(value));
                break;

            case(6):
                this.contents.push(new GalleryContentModel(value));
                break;
        }
    }

    private getComponent(type: number) {
        let component = null;

        switch(type) {
            case(1):
                component = TextComponent;
                break;

            case(2):
                component = TypingComponent;
                break;

            case(3):
                component = QuickReplyComponent;
                break;

            case(5):
                component = ListComponent;
                break;

            case(6):
                component = GalleryComponent;
                break;
        }

        return component;
    }
}
</script>
