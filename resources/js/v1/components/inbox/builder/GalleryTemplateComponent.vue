<template>
    <div class="componentTypeOne">
        <div class="galleListComponentRoot">
            <ul class="galleListRoot">
                <li
                    class="galleListItem"
                    v-for="(galle, index) in content.attachment.payload.elements"
                    :key="index"
                >
                    <div class="chatGalleryContainer">
                        <figure class="chatGalleryImage" v-if="galle.image_url">
                            <div class="imageCon">
                                <img :src="galle.image_url" />
                            </div>
                        </figure>
                        <div class="chatGalleryContent">
                            <div>
                                <div class="displayBox">{{ galle.title }}</div>
                            </div>
                            <div>
                                <div class="displayBox">{{ galle.subtitle }}</div>
                            </div>
                            <!-- <div>
                                <input type="text" placeholder="Url" v-model="l.url" v-on:blur="l.saveContent()"/>
                            </div>-->
                        </div>
                        <div
                            class="chatGalleryButtons"
                            v-if="undefined!==galle.buttons && galle.buttons.length>0"
                        >
                            <div
                                class="addBtn btnCon"
                                v-for="(button, sindex) in galle.buttons"
                                :key="sindex"
                            >
                                <div class="buttonActionGroup" v-if="button.type==='postback'">
                                    <div class="buttonName">{{ button.title }}</div>
                                </div>
                                <a
                                    class="buttonActionGroup"
                                    :href="button.url"
                                    v-if="button.type==='web_url'"
                                >
                                    <div class="buttonName">{{ button.title }}</div>
                                </a>
                                <a
                                    class="buttonActionGroup"
                                    :href="'tel:'+button.payload"
                                    v-if="button.type==='phone_number'"
                                >
                                    <div class="buttonName">{{ button.title }}</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from "vue-property-decorator";

@Component
export default class GalleryTemplateComponent extends Vue {
    @Prop() content: any;
}
</script>
