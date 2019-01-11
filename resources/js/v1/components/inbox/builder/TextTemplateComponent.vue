<template>
    <div>
        <template v-if="undefined==content.attachment">
           <div class="chatContentBody">{{ content.text }}</div>
        </template>
        <template v-else>
            <template v-if="undefined!==content.attachment.payload.buttons && undefined!==content.attachment.payload.buttons.length>0">
                <div class="componentTypeOne">
                    <div class="botTextComponent">
                        <p>{{ content.attachment.payload.text }}</p>
                        <div class="textBtn">
                            <div class="addBtn btnCon" v-for="(button, index) in content.attachment.payload.buttons" :key="index">
                                <div class="buttonActionGroup" @click="content.btnEditIndex=index">
                                    <template v-if="button.type==='postback'">{{ button.title }}</template>
                                    <template v-if="button.type==='web_url'">
                                        <a :href="button.url" >{{ button.title }}</a>
                                    </template>
                                    <template v-if="button.type==='phone_number'">
                                        <a :href="'tel:'+button.payload">{{ button.title }}</a>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div v-if="content.isUpdating" class="loadingConV1">
                            <div class="loadingInnerConV1">
                                <loading-component/>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="chatContentBody">{{ content.attachment.payload.text }}</div>
            </template>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';

@Component
export default class TextTemplateComponent extends Vue {
    @Prop() content: any;
}
</script>
