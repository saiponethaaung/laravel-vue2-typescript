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
                                    <template v-if="l.image">
                                        <div class="listItemImageCon">
                                            <img :src="l.image"/>
                                        </div>
                                        <div class="hoverOptions">
                                            <div class="removeIcon" @click="l.delImage()">
                                                <i class="material-icons">close</i>
                                                <span>remove</span>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <label>
                                            <i class="material-icons">photo_camera</i>
                                            <input type="file" @change="l.imageUpload($event)"/>
                                        </label>
                                    </template>
                                </figure>
                            </div>
                        </div>
                        <div class="chatListButton noborder" v-if="l.addingNewBtn">
                            Creating...
                        </div>
                        <div class="chatListButton" v-if="!l.addingNewBtn && l.button==null" @click="l.addButton()">
                            Add Button
                        </div>
                        <div class="chatListButton" v-if="l.button!==null">
                            <div @click="l.btnEdit=true">
                                {{ l.button.title ? l.button.title : 'New Button' }}
                            </div>
                            <div class="delIcon" @click="l.delButton()">
                                <i class="material-icons">delete</i>
                            </div>
                            <button-component
                                :rootUrl="`${content.url}/button`"
                                :button="l.button"
                                :projectid="content.project"
                                v-if="l.btnEdit"
                                v-on:closeContent="(status) => {
                                    if(status && l.btnEdit===true) l.btnEdit=false;
                                }"></button-component>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="delIcon chatListItemDelIcon" @click="content.delItem(index)">
                        <i class="material-icons">delete</i>
                    </div>
                </li>
                <li v-if="content.isCreating" class="addMoreChatListItem addBtn">
                    Creating...
                </li>
                <li v-if="content.item.length<4 && !content.isCreating" @click="createNewList" class="addMoreChatListItem addBtn">
                    + Add Item
                </li>
                <li class="chatListRootButton addBtn" v-if="content.button!==null">
                    <div class="buttonActionGroup" @click="content.btnEdit=true">
                        <div class="buttonName">{{ content.button.title ? content.button.title : 'New Button' }}</div>
                        <div class="buttonActionName" v-if="content.button.type===0 && content.button.block.length>0">{{ content.button.block[0].title }}</div>
                        <div class="buttonActionName" v-if="content.button.type===1 && content.button.url">{{ content.button.url }}</div>
                        <div class="buttonActionName" v-if="content.button.type===2 && content.button.phone.number">{{ content.button.phone.number }}</div>
                    </div>
                    <div class="delIcon" @click="content.delButton()">
                        <i class="material-icons">delete</i>
                    </div>
                    <button-component
                        :rootUrl="`${content.url}/button`"
                        :button="content.button"
                        :projectid="content.project"
                        v-if="content.btnEdit"
                        v-on:closeContent="(status) => {
                            if(status && content.btnEdit===true) content.btnEdit=false;
                        }"></button-component>
                </li>
                <div class="addBtn" v-if="content.button==null && !content.addingNewBtn" @click="content.addButton()">
                    <i class="material-icons">add</i>Add Button
                </div>
                <div class="addBtn btnCon" v-if="content.addingNewBtn">
                    Creating...
                </div>
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
