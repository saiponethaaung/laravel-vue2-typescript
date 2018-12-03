<template>
    <div class="componentTypeOne">
        <div class="chatListComponentRoot">
            <ul class="chatListRoot">
                <li v-for="(l, index) in content.item" :key="index" class="chatListItem">
                    <div class="chatListContent">
                        <div class="chatListInfo">
                            <div class="chatListInput">
                                <div>
                                    <input type="text" placeholder="Heading (required)" v-model="l.title" class="chatListTitle" v-on:blur="l.saveContent()"/>
                                </div>
                                <div>
                                    <input type="text" placeholder="Subtitle or description" v-model="l.sub" class="chatListSub" v-on:blur="l.saveContent()"/>
                                </div>
                                <div>
                                    <input type="text" placeholder="URL" v-model="l.url" class="chatListUrl" v-on:blur="l.saveContent()"/>
                                </div>
                            </div>
                            <div class="chatListImage">
                                <figure>
                                    <label>
                                        <i class="material-icons">photo_camera</i>
                                        <input type="file"/>
                                    </label>
                                </figure>
                            </div>
                        </div>
                        <div class="chatListButton">Add Button</div>
                    </div>
                    <div class="clear"></div>
                </li>
                <li v-if="content.isCreating" class="addMoreChatListItem addBtn">
                    Creating...
                </li>
                <li v-if="content.item.length<4 && !content.isCreating" @click="createNewList" class="addMoreChatListItem addBtn">
                    + Add Item
                </li>
                <li class="chatListRootButton addBtn">
                    Button
                </li>
            </ul>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import ListContentModel from '../../../models/bots/ListContentModel';

@Component
export default class ListComponent extends Vue {
    @Prop({
        type: ListContentModel,
    }) content!: ListContentModel;

    createNewList() {
        this.content.createList();
    }
}
</script>
