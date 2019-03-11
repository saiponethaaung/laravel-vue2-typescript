<template>
    <li
        :key="index"
        class="galleListItem horizontalDragCon"
        :class="{'listRequired': listItem.canShowError && listItem.title!=='' && (listItem.sub==='' && listItem.image==='' && listItem.buttons.length==0)}"
    >
        <div class="chatGalleryContainer">
            <figure class="chatGalleryImage">
                <template v-if="listItem.image">
                    <div class="imageCon">
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
                        <!-- <input type="file"/> -->
                        <input type="file" @change="listItem.imageUpload($event)">
                    </label>
                </template>
            </figure>
            <div class="chatGalleryContent">
                <div class="inputConLimitWrapper">
                    <input
                        type="text"
                        placeholder="Heading (required)"
                        maxlength="80"
                        v-model="listItem.title"
                        :class="{'required': listItem.title=='' && listItem.canShowError}"
                        v-on:blur="listItem.saveContent()"
                    >
                    <span class="limitGalleryTitle limitSub">{{ listItem.textLimitTitle }}</span>
                </div>
                <div class="inputConLimitWrapper">
                    <input
                        type="text"
                        placeholder="Subtitle or description"
                        maxlength="80"
                        v-model="listItem.sub"
                        v-on:blur="listItem.saveContent()"
                    >
                    <span class="limitGalleryTitle limitSub limitGalSub">{{ listItem.textLimitSub }}</span>
                </div>
                <div>
                    <input
                        type="text"
                        placeholder="Url"
                        v-model="listItem.url"
                        v-on:blur="listItem.saveContent()"
                    >
                </div>
            </div>
            <div class="chatGalleryButtons">
                <div
                    class="addBtn btnCon"
                    v-for="(button, sindex) in listItem.buttons"
                    :key="sindex"
                >
                    <div class="buttonActionGroup" @click="listItem.btnEdit=sindex">
                        <div class="buttonName">{{ button.title ? button.title : 'Button Name' }}</div>
                        <div
                            class="buttonActionName"
                            v-if="button.type===0 && button.block.length>0"
                        >{{ button.block[0].title }}</div>
                        <div
                            class="buttonActionName"
                            v-if="button.type===1 && button.url"
                        >{{ button.url }}</div>
                        <div
                            class="buttonActionName"
                            v-if="button.type===2 && button.phone.number"
                        >{{ button.phone.number }}</div>
                    </div>
                    <div class="delIcon" @click="listItem.delButton(sindex)">
                        <i class="material-icons">delete</i>
                    </div>
                    <button-component
                        :rootUrl="`${baseUrl}/button`"
                        :button="button"
                        :projectid="projectid"
                        v-if="listItem.btnEdit===sindex"
                        v-on:closeContent="(status) => {
                            if(status && listItem.btnEdit===sindex) listItem.btnEdit=-1;
                        }"
                    ></button-component>
                </div>
                <div class="addBtn btnCon" v-if="listItem.addingNewBtn">Creating...</div>
                <div
                    class="addBtn"
                    v-if="!listItem.addingNewBtn && listItem.buttons.length<3"
                    @click="listItem.addButton()"
                >
                    <i class="material-icons">add</i>Add Button
                </div>
            </div>
            <div class="delIcon" @click="deleteItem(index)">
                <i class="material-icons">delete</i>
            </div>
        </div>
        <template v-if="listItem.errorMesg!==''">
            <error-component :mesg="listItem.errorMesg" @closeError="listItem.errorMesg=''"></error-component>
        </template>
        <div class="requiredNotiCon">
            <div
                class="requiredNotiText"
            >Set up at least one more item field: subtitle, button or image</div>
        </div>
        <div class="horizontalDrag">
            <i class="material-icons">unfold_more</i>
        </div>
    </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from "vue-property-decorator";
import GalleryItemModel from "../../../models/bots/GalleryItemModel";

@Component
export default class GalleryItemComponent extends Vue {
    @Prop({
        type: GalleryItemModel
    })
    listItem!: GalleryItemModel;

    @Prop() index!: any;
    @Prop() baseUrl!: string;
    @Prop() projectid!: string;

    @Emit("delItem")
    deleteItem(index: any) {}
}
</script>
