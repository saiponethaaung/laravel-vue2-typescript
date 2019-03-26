<template>
    <div>
        <div class="componentTypeOne">
            <div class="chatListComponentRoot">
                <ul class="chatListRoot">
                    <template v-for="(l, index) in content.item">
                        <list-item-component
                            :listItem="l"
                            :index="index"
                            :baseUrl="content.url"
                            :isChildDeleting="content.isChildDeleting"
                            :projectid="content.project"
                            @delItem="delItem"
                            :key="index"
                        ></list-item-component>
                    </template>
                    <li v-if="content.isCreating" class="addMoreChatListItem addBtn">Creating...</li>
                    <li
                        v-if="content.item.length<4 && !content.isCreating"
                        @click="createNewList"
                        class="addMoreChatListItem addBtn"
                    >+ Add Item</li>
                    <li class="chatListRootButton addBtn" v-if="content.button!==null">
                        <div class="buttonActionGroup" @click="content.btnEdit=true">
                            <div
                                class="buttonName"
                            >{{ content.button.title ? content.button.title : 'Button Name' }}</div>
                            <div
                                class="buttonActionName"
                                v-if="content.button.type===0 && content.button.block.length>0"
                            >{{ content.button.block[0].title }}</div>
                            <div
                                class="buttonActionName"
                                v-if="content.button.type===1 && content.button.url"
                            >{{ content.button.url }}</div>
                            <div
                                class="buttonActionName"
                                v-if="content.button.type===2 && content.button.phone.number"
                            >{{ content.button.phone.number }}</div>
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
                            }"
                        ></button-component>
                    </li>
                    <div
                        class="addBtn"
                        v-if="content.button==null && !content.addingNewBtn"
                        @click="content.addButton()"
                    >
                        <i class="material-icons">add</i>Add Button
                    </div>
                    <div class="addBtn btnCon" v-if="content.addingNewBtn">Creating...</div>
                </ul>
            </div>
        </div>
        <warning-component :mesg="content.warningText" v-if="content.showWarning"></warning-component>
    </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from "vue-property-decorator";
import ListContentModel from "../../../models/bots/ListContentModel";
import ListItemComponent from "./ListItemComponent.vue";

@Component({
    components: {
        ListItemComponent
    }
})
export default class ListComponent extends Vue {
    @Prop({
        type: ListContentModel
    })
    content!: ListContentModel;

    createNewList() {
        this.content.createList();
    }

    delItem(index: any) {
        this.content.delItem(index);
    }
}
</script>
