<template>
    <div class="componentTypeOne">
        <div class="galleListComponentRoot">
            <ul class="galleListRoot">
                <li v-for="(l, index) in content.item" :key="index" class="galleListItem">
                    <div class="chatGalleryContainer">
                        <figure class="chatGalleryImage">
                            <template v-if="l.image">
                                <div class="imageCon">
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
                            <div class="addBtn btnCon" v-for="(button, sindex) in l.buttons" :key="sindex">
                                <div class="buttonActionGroup" @click="l.btnEdit=sindex">
                                    <div class="buttonName">{{ button.title ? button.title : 'New Button' }}</div>
                                    <div class="buttonActionName" v-if="button.type===0 && button.block.length>0">{{ button.block[0].title }}</div>
                                    <div class="buttonActionName" v-if="button.type===1 && button.url">{{ button.url }}</div>
                                    <div class="buttonActionName" v-if="button.type===2 && button.phone.number">{{ button.phone.number }}</div>
                                </div>
                                <div class="delIcon" @click="l.delButton(sindex)">
                                    <i class="material-icons">delete</i>
                                </div>
                                <button-component
                                    :rootUrl="`${content.url}/button`"
                                    :button="button"
                                    :projectid="content.project"
                                    v-if="l.btnEdit===sindex"
                                    v-on:closeContent="(status) => {
                                        if(status && l.btnEdit===sindex) l.btnEdit=-1;
                                    }"></button-component>
                            </div>
                            <div class="addBtn btnCon" v-if="l.addingNewBtn">Creating...</div>
                            <div class="addBtn" v-if="!l.addingNewBtn && l.buttons.length<3" @click="l.addButton()">
                                <i class="material-icons">add</i>Add Button
                            </div>
                        </div>
                        <div class="delIcon" @click="content.delItem(index)">
                            <i class="material-icons">delete</i>
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
