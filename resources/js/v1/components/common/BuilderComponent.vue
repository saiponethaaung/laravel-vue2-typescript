<template>
    <div class="contentRoot">
        <div class="contentList">
            <div v-for="(content, index) in contents" :key="index">
                <component :is="getComponent(content.type)" :content="content"></component>
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
                    <li class="contentActionList">
                        <i class="material-icons">reply</i>
                        <span class="contentActionName">Quick Reply</span>
                    </li>
                    <li class="contentActionList">
                        <i class="material-icons">textsms</i>
                        <span class="contentActionName">User Input</span>
                    </li>
                    <li class="contentActionList">
                        <i class="material-icons">list</i>
                        <span class="contentActionName">List</span>
                    </li>
                    <li class="contentActionList">
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
import TextComponent from './builder/TextComponent.vue';
import TypingComponent from './builder/TypingComponent.vue';
import Axios from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';
import TextContentModel from '../../models/bots/TextContentModel';

@Component({
    components: {
        TextComponent,
        TypingComponent
    }
})
export default class BuilderComponent extends Vue {
    private contents: Array<any> = [];
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    @Prop({
        type: Array,
        default: []
    }) value!: Array<any>;

    mounted() {
        for(let i in this.value) {
            switch(this.value[i].type) {
                case(1):
                    this.contents.push(new TextContentModel(this.value[i]));
                    break;
            }
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

    async appendComponent(content: any) {
        let data = new FormData();
        data.append('type', content.type);

        await Axios({
            url: `/api/v1/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}/content`,
            data: data,
            method: 'post'
        }).then((res) => {
            this.contents.push(new TextContentModel(res.data.data));
        }).catch((err) => {
            let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new content!');
            alert(mesg);
        });
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
        }

        return component;
    }
}
</script>
