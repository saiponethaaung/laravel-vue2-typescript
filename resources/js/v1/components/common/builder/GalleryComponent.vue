<template>
    <div class="componentTypeOne">
        <div class="galleListComponentRoot">
            <ul class="galleListRoot">
                <li v-for="(l, index) in content.item" :key="index" class="galleListItem">
                    <div class="chatGalleryContainer">
                        <figure class="chatGalleryImage">
                            <template v-if="l.image">
                                <img :src="l.image"/>
                            </template>
                            <template v-else>
                                <label>
                                    <i class="material-icons">photo_camera</i>
                                    <!-- <input type="file"/> -->
                                    <input type="file" @change="l.imageUpload($event)"/>
                                </label>
                            </template>
                        </figure>
                        <div class="chatGalleryContent">
                            <div>
                                <input type="text" placeholder="Heading (required)" v-model="l.title" v-on:blur="l.saveContent()"/>
                            </div>
                            <div>
                                <input type="text" placeholder="Subtitle or description" v-model="l.sub" v-on:blur="l.saveContent()"/>
                            </div>
                            <div>
                                <input type="text" placeholder="Url" v-model="l.url" v-on:blur="l.saveContent()"/>
                            </div>
                        </div>
                        <div class="chatGalleryButtons">
                            <div class="addBtn">+ Add Button</div>
                        </div>
                    </div>
                </li>
                <li class="addMoreChatGallery" v-if="content.item.length<10">
                    <div class="galleAddMore">
                        <template v-if="content.isCreating">
                            Creating...
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
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import GalleryContentModel from '../../../models/bots/GalleryContentModel';

@Component
export default class GalleryComponent extends Vue {
    @Prop({
        type: GalleryContentModel,
    }) content!: GalleryContentModel;

    createNewGallery() {
        this.content.createGallery();
    }
}
</script>
