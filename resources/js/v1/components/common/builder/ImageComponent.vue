<template>
    <div>
        <div class="componentTypeOne">
            <figure class="imageComponentCon">
                <template v-if="content.image">
                    <img :src="content.image"/>
                    <template v-if="content.deletingImage">
                        <div class="deletingImage">
                            <div class="galleLoader">
                                <loading-component></loading-component>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="hoverOptions" @click="content.delImage()">
                            <div class="removeIcon">
                                <i class="material-icons">close</i>
                                <span>remove</span>
                            </div>
                        </div>
                    </template>
                </template>
                <template v-else>
                    <label>
                        <template v-if="content.isUploading">
                            <div class="galleLoader">
                                <loading-component></loading-component>
                            </div>
                        </template>
                        <template v-else>
                            <i class="material-icons">photo_camera</i>
                            <input type="file" @change="content.imageUpload($event)"/>
                        </template>
                    </label>
                </template>
            </figure>
        </div>
        <warning-component :mesg="content.warningText" v-if="content.showWarning"></warning-component>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import ImageContentModel from '../../../models/bots/ImageContentModel';

@Component
export default class ImageComponent extends Vue {
    @Prop({
        type: ImageContentModel,
    }) content!: ImageContentModel;
}
</script>
