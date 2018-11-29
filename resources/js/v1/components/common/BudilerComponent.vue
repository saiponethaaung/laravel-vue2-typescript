<template>
    <div class="contentRoot">
        <div class="contentList">
            <div v-for="(content, index) in contents" :key="index">
                <component :is="getComponent(content.type)" :obj="content"></component>
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
import { Component, Watch, Vue } from 'vue-property-decorator';
import TextComponent from './builder/TextComponent.vue';
import TypingComponent from './builder/TypingComponent.vue';

@Component({
    components: {
        TextComponent,
        TypingComponent
    }
})
export default class BuilderComponent extends Vue {
    private contents: Array<any> = [];

    private addText() {
        this.appendComponent({
            name: 'Text section',
            type: 'text' 
        });
    }

    private addTyping() {
        this.appendComponent({
            name: 'Typing section',
            type: 'typing' 
        });
    }

    private appendComponent(content: any) {
        this.contents.push(content);
    }

    private getComponent(type: string) {
        let component = null;

        switch(type) {
            case('text'):
                component = TextComponent;
                break;

            case('typing'):
                component = TypingComponent;
                break;
        }

        return component;
    }
}
</script>
