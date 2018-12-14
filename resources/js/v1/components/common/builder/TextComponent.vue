<template>
    <div class="componentTypeOne">
        <div class="botTextComponent">
            <textarea class="textBody" v-model="content.value" v-on:blur="content.saveContent()"></textarea>
            <div class="textBtn">
                <div class="addBtn btnCon" v-for="(button, index) in content.buttons" :key="index" @click="content.btnEditIndex=index">
                    {{ button.title ? button.title : 'New Button' }}
                    <button-component
                        :button="button"
                        v-if="content.btnEditIndex===index"
                        v-on:closeContent="(status) => {
                            if(status && content.btnEditIndex===index) content.btnEditIndex=-1;
                        }"></button-component>
                </div>
                <div class="addBtn btnCon" v-if="content.addingNewBtn">
                    Creating...
                </div>
                <div class="addBtn" v-if="content.buttons.length<3 && !content.addingNewBtn" @click="content.addButton()">
                    <i class="material-icons">add</i>Add Button
                </div>
                <!-- <div class="buttonPopBox" v-if="content.showBtn">
                    <div>
                        asd
                    </div>
                </div> -->
            </div>
            <div v-if="content.isUpdating">
                Updating...
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import TextContentModel from '../../../models/bots/TextContentModel';

@Component
export default class TextComponent extends Vue {
    @Prop({
        type: TextContentModel,
    }) content!: TextContentModel;

    mounted() {
        let textarea: any = this.$el.querySelector('textarea');

        textarea.addEventListener('keydown', function(){
            setTimeout(function(){
                textarea.style.cssText = 'height:auto; padding:0';
                textarea.style.cssText = 'height:' + (textarea.scrollHeight+10) + 'px';
            },0);
        });
    }
}
</script>
