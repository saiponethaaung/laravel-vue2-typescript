<template>
    <li
        :key="index"
        class="chatListItem"
        :class="{'listRequired': listItem.canShowError && listItem.title!=='' && (listItem.sub==='' || listItem.image==='' || listItem.button==null)}"
    >
        <div class="chatListContent">
            <div class="chatListInfo">
                <div class="chatListInput">
                    <div class="inputConLimitWrapper">
                        <input
                            type="text"
                            placeholder="Heading (required)"
                            maxlength="80"
                            v-model="listItem.title"
                            class="chatListTitle"
                            :class="{'required': listItem.title=='' && listItem.canShowError}"
                            v-on:blur="listItem.saveContent()"
                        >
                        <span class="limitListTitle">{{ listItem.textLimitTitle }}</span>
                    </div>
                    <div class="inputConLimitWrapper">
                        <input
                            type="text"
                            placeholder="Subtitle or description"
                            maxlength="80"
                            v-model="listItem.sub"
                            class="chatListSub"
                            v-on:blur="listItem.saveContent()"
                        >
                        <span class="limitListTitle limitSub">{{ listItem.textLimitSub }}</span>
                    </div>
                    <div>
                        <input
                            type="text"
                            placeholder="URL"
                            v-model="listItem.url"
                            class="chatListUrl"
                            v-on:blur="listItem.saveContent()"
                        >
                    </div>
                </div>
                <div class="chatListImage">
                    <figure>
                        <template v-if="listItem.image">
                            <div class="listItemImageCon">
                                <img :src="listItem.image">
                            </div>
                            <div class="hoverOptions">
                                <div class="removeIcon" @click="listItem.delImage()">
                                    <i class="material-icons">close</i>
                                    <span>remove</span>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <label>
                                <i class="material-icons">photo_camera</i>
                                <input type="file" @change="listItem.imageUpload($event)">
                            </label>
                        </template>
                    </figure>
                </div>
            </div>
            <div class="chatListButton noborder" v-if="listItem.addingNewBtn">Creating...</div>
            <div
                class="chatListButton"
                v-if="!listItem.addingNewBtn && listItem.button==null"
                @click="listItem.addButton()"
            >Add Button</div>
            <div class="chatListButton" v-if="listItem.button!==null">
                <div
                    @click="listItem.btnEdit=true"
                >{{ listItem.button.title ? listItem.button.title : 'Button Name' }}</div>
                <div class="delIcon" @click="listItem.delButton()">
                    <i class="material-icons">delete</i>
                </div>
                <button-component
                    :rootUrl="`${baseUrl}/button`"
                    :button="listItem.button"
                    :projectid="projectid"
                    v-if="listItem.btnEdit"
                    v-on:closeContent="(status) => {
                                        if(status && listItem.btnEdit===true) listItem.btnEdit=false;
                                    }"
                ></button-component>
            </div>
        </div>
        <div class="clear"></div>
        <div class="delIcon chatListItemDelIcon" v-if="index>1" @click="deleteItem(index)">
            <i class="material-icons">delete</i>
        </div>
        <template v-if="listItem.errorMesg!==''">
            <error-component :mesg="listItem.errorMesg" @closeError="listItem.errorMesg=''"></error-component>
        </template>
        <div class="requiredNotiCon">
            <div class="requiredNotiText">
                Set up at least one more item field: subtitle, button or image
            </div>
        </div>
    </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from "vue-property-decorator";
import ListItemModel from "../../../models/bots/ListItemModel";

@Component
export default class ListItemComponent extends Vue {
    @Prop({
        type: ListItemModel
    })
    listItem!: ListItemModel;

    @Prop() index!: any;
    @Prop() baseUrl!: string;
    @Prop() projectid!: number;

    @Emit("delItem")
    deleteItem(index: any) {}
}
</script>
