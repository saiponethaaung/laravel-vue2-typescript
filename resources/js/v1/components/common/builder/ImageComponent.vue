<template>
    <div class="componentTypeOne">
        <figure class="imageComponentCon">
            <template v-if="content.image">
                <img :src="content.image"/>
                <div class="hoverOptions" @click="content.delImage()">
                    <div class="removeIcon">
                        <i class="material-icons">close</i>
                        <span>remove</span>
                    </div>
                </div>
            </template>
            <template v-else>
                <label>
                    <i class="material-icons">photo_camera</i>
                    <!-- <input type="file"/> -->
                    <input type="file" @change="content.imageUpload($event)"/>
                </label>
            </template>
        </figure>
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
