<template>
    <div>
        <div class="componentTypeOne">
            <div class="galleListComponentRoot">
                <ul class="galleListRoot">
                    <draggable
                        v-model="content.item"
                        class="draggable"
                        handle=".galleryDrag"
                        @end="updateOrder"
                    >
                        <template v-for="(l, index) in content.item">
                            <gallery-item-component
                                :listItem="l"
                                :index="index"
                                :baseUrl="content.url"
                                :isChildDeleting="content.isChildDeleting"
                                :projectid="content.project"
                                @delItem="delItem"
                                :key="index"
                            ></gallery-item-component>
                        </template>
                    </draggable>
                    <li class="addMoreChatGallery" v-if="content.item.length<10">
                        <div class="galleAddMore">
                            <template v-if="content.isCreating">
                                <div class="galleLoader">
                                    <loading-component></loading-component>
                                </div>
                            </template>
                            <template v-else>
                                <div class="addMoreGalleBtn" @click="createNewGallery">+</div>
                            </template>
                        </div>
                    </li>
                </ul>
            </div>
            <template v-if="content.errorMesg!==''">
                <error-component :mesg="content.errorMesg" @closeError="content.errorMesg=''"></error-component>
            </template>
        </div>
        <warning-component :mesg="content.warningText" v-if="content.showWarning"></warning-component>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from "vue-property-decorator";
import GalleryContentModel from "../../../models/bots/GalleryContentModel";
import GalleryItemComponent from "./GalleryItemComponent.vue";

@Component({
    components: {
        GalleryItemComponent
    }
})
export default class GalleryComponent extends Vue {
    @Prop({
        type: GalleryContentModel
    })
    content!: GalleryContentModel;

    createNewGallery() {
        this.content.createGallery();
    }

    delItem(index: any) {
        this.content.delItem(index);
    }

    async updateOrder() {
        await this.content.updateOrder();
    }
}
</script>
