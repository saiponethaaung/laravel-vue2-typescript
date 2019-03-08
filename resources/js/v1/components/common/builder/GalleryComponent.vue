<template>
    <div class="componentTypeOne">
        <div class="galleListComponentRoot">
            <ul class="galleListRoot">
                <template v-for="(l, index) in content.item">
                    <gallery-item-component
                        :listItem="l"
                        :index="index"
                        :baseUrl="content.url"
                        :projectid="content.porjectid"
                        @delItem="delItem"
                        :key="index"
                    ></gallery-item-component>
                </template>
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
}
</script>
