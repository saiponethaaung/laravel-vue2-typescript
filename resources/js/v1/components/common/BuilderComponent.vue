<template>
    <div class="contentRoot">
        <div class="builderSectionInfo" v-if="!isBroadcast">
            <template v-if="section.lock">
                <div>{{ section.title }}</div>
            </template>
            <template v-else>
                <input type="text" v-model="section.title" v-on:blur="updateSection"/>
                <div class="deleteAction" @click="delSection()">
                    <i class="material-icons">delete</i>
                </div>
            </template>
        </div>
        <div class="contentList">
            <div v-for="(content, index) in contents" :key="index" class="conentItem" :class="{'deleting': content.isDeleting}">
                <div class="optionSection">
                    <div class="deleteAction" @click="delItem(index)">
                        <i class="material-icons">delete</i>
                    </div>
                </div>
                <component :is="getComponent(content.type)" :content="content"></component>
                <template v-if="content.isDeleting">
                    <div class="componentDeleting">
                        <div class="deletingContainer">
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div v-for="i in creating" :key="i">
            <div class="creatingNewComponent">
                <div class="loadingInnerConV1">
                    <loading-component></loading-component>
                </div>
            </div>
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
                    <li class="contentActionList" @click="addQuickReply" v-if="!isBroadcast">
                        <i class="material-icons">reply</i>
                        <span class="contentActionName">Quick Reply</span>
                    </li>
                    <li class="contentActionList" @click="addUserInput">
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
                    <li class="contentActionList" @click="addImage">
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
import Axios,{ CancelTokenSource } from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

import TextComponent from './builder/TextComponent.vue';
import TypingComponent from './builder/TypingComponent.vue';
import ListComponent from './builder/ListComponent.vue';
import GalleryComponent from './builder/GalleryComponent.vue';
import QuickReplyComponent from './builder/QuickReplyComponent.vue';
import UserInputComponent from './builder/UserInputComponent.vue';
import ImageComponent from './builder/ImageComponent.vue';

import TextContentModel from '../../models/bots/TextContentModel';
import TypingContentModel from '../../models/bots/TypingContentModel';
import ListContentModel from '../../models/bots/ListContentModel';
import GalleryContentModel from '../../models/bots/GalleryContentModel';
import QuickReplyContentModel from '../../models/bots/QuickReplyContentModel';
import UserInputContentModel from '../../models/bots/UserInputContentModel';
import ImageContentModel from '../../models/bots/ImageContentModel';

@Component({
    components: {
        TextComponent,
        TypingComponent,
        ListComponent,
        GalleryComponent,
        QuickReplyComponent,
        UserInputComponent,
        ImageComponent
    }
})
export default class BuilderComponent extends Vue {
    private contents: Array<any> = [];
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private creating: number = 0;
    private sectionToken: CancelTokenSource = Axios.CancelToken.source();

    @Prop({
        type: Array,
        default: []
    }) value!: Array<any>;

    @Prop({
        type: Boolean,
        default: false
    }) isBroadcast!: boolean;

    @Prop() section!: any;

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

    async addUserInput() {
        await this.appendComponent({
            name: 'User Input section',
            type: 4
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
    
    async addImage() {
        await this.appendComponent({
            name: 'Image section',
            type: 7
        });
    }

    async appendComponent(content: any) {
        let data = new FormData();
        data.append('type', content.type);

        this.creating++;
        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}/content`,
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

            case(4):
                this.contents.push(new UserInputContentModel(value));
                break;

            case(5):
                this.contents.push(new ListContentModel(value));
                break;

            case(6):
                this.contents.push(new GalleryContentModel(value));
                break;

            case(7):
                this.contents.push(new ImageContentModel(value));
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

            case(4):
                component = UserInputComponent;
                break;

            case(5):
                component = ListComponent;
                break;

            case(6):
                component = GalleryComponent;
                break;

            case(7):
                component = ImageComponent;
                break;
        }

        return component;
    }

    async delItem(index:number) {
        if(confirm('Are you sure you want to delete?')) {
            this.contents[index].isDeleting = true;
            await Axios({
                url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}/content/${this.contents[index].contentId}`,
                method: 'delete'
            }).then((res) => {
                this.contents.splice(index, 1);   
            }).catch((err) => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to delete message!');
                    alert(mesg);
                    this.contents[index].isDeleting = false;
                }
            });
        }
    }

    async updateSection() {
        this.sectionToken.cancel();
        this.sectionToken = Axios.CancelToken.source();

        let data = new FormData();
        data.append('title', this.section.title);
        data.append('_method', 'put');

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}`,
            data: data,
            method: 'post',
            cancelToken: this.sectionToken.token
        }).then((res) => {
            this.$store.commit('updateChatBot', {
                block: this.$store.state.chatBot.block,
                section: this.$store.state.chatBot.section,
                title: this.section.title
            });
        }).catch((err) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to update block title!');
                alert(mesg);
            }
        });
    }

    async delSection() {
        if(confirm('Are you sure you want to delete this block with it\'s content?')) {
            await Axios({
                url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}`,
                method: 'delete',
            }).then((res) => {
                this.$store.commit('deleteChatBot', {
                    block: this.$store.state.chatBot.block,
                    section: this.$store.state.chatBot.section,
                });
            }).catch((err) => {
                if(err.response) {
                    let mesg = this.ajaxHandler.globalHandler(err, 'Failed to update block title!');
                    alert(mesg);
                }
            });
        }
    }
}
</script>
