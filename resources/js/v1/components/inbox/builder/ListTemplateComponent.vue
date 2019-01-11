<template>
    <div class="componentTypeOne">
        <div class="chatListComponentRoot">
            <ul class="chatListRoot">
                <li v-for="(list, index) in content.attachment.payload.elements" :key="index" class="chatListItem">
                    <div class="chatListContent">
                        <div class="chatListInfo">
                            <div class="chatListInput">
                                <div>
                                    <div class="listDisplayBox">{{ list.title }}</div>
                                </div>
                                <div>
                                    <div class="listDisplayBox">{{ list.subtitle }}</div>
                                </div>
                            </div>
                            <div class="chatListImage" v-if="list.image_url">
                                <figure>
                                    <div class="listItemImageCon">
                                        <img :src="list.image_url"/>
                                    </div>
                                </figure>
                            </div>
                        </div>
                        <div class="chatListButton listButtonDisplayBox" v-if="undefined!==list.buttons && undefined!==list.buttons[0]">
                            <template v-if="list.buttons[0].type==='postback'">{{ list.buttons[0].title }}</template>
                            <template v-if="list.buttons[0].type==='web_url'">
                                <a :href="list.buttons[0].url" >{{ list.buttons[0].title }}</a>
                            </template>
                            <template v-if="list.buttons[0].type==='phone_number'">
                                <a :href="'tel:'+list.buttons[0].payload">{{ list.buttons[0].title }}</a>
                            </template>
                        </div>
                    </div>
                    <div class="clear"></div>
                </li>
                <li class="chatListRootButton addBtn" v-if="undefined!==content.attachment.payload.buttons && content.attachment.payload.buttons.length>0">
                    <div class="buttonActionGroup">
                        <div class="buttonName">
                            <template v-if="content.attachment.payload.buttons[0].type==='postback'">{{ content.attachment.payload.buttons[0].title }}</template>
                            <template v-if="content.attachment.payload.buttons[0].type==='web_url'">
                                <a :href="content.attachment.payload.buttons[0].url" >{{ content.attachment.payload.buttons[0].title }}</a>
                            </template>
                            <template v-if="content.attachment.payload.buttons[0].type==='phone_number'">
                                <a :href="'tel:'+content.attachment.payload.buttons[0].payload">{{ content.attachment.payload.buttons[0].title }}</a>
                            </template>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';

@Component
export default class ListTemplateComponent extends Vue {
    @Prop() content: any;

    mounted() {
        console.log(this.content);
    }
}
</script>
