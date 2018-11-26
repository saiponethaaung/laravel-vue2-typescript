<template>
    <div>
        <h3 class="chatBotHeading">Chatbot</h3>
        <div>
            <template v-if="blockLoading">
                Loading...
            </template>
            <template v-else>
                <div v-for="(block, index) in blocks" :key="index">
                    <h5 class="chatBlockHeading">{{ block.title }}</h5>
                    <div class="chatBlockContentList">
                        <div v-for="(section, sIndex) in block.sections" :key="sIndex" class="chatBlockContent">
                            {{ section.title }}
                        </div>
                        <div v-if="!block.lock" class="chatBlockContent addMore">
                            <i class="material-icons">add</i>
                        </div>
                    </div>
                </div>
                <template v-if="creating">
                    Creating...
                </template>
                <template v-else>
                    <button class="addMoreBlock" @click="createBlock">
                        <i class="material-icons">add</i> Add More
                    </button>
                </template>
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component } from 'vue-property-decorator';
import ChatBlockModel from '../../models/ChatBlockModel';
import Axios from 'axios';

@Component
export default class SidebarComponent extends Vue {
    
    private creating : boolean = false;
    private blockLoading : boolean = false;
    private blocks : Array<ChatBlockModel> = [];

    async mounted() {
        await this.loadBlocks();
    }

    async loadBlocks() {
        this.blockLoading = true;

        await Axios({
            url: "/api/v1/chat-bot/blocks"
        }).then((res: any) => {
            for(let chatBlock of res.data.data) {
                this.blocks.push(new ChatBlockModel(chatBlock.block, chatBlock.sections));
            }
        }).catch((err: any) => {
            
        });

        this.blockLoading = false;
    }

    async createBlock() {
        this.creating = true;

        await Axios({
            url: "/api/v1/chat-bot/block",
            method: "POST"
        }).then((res: any) => {
            this.blocks.push(new ChatBlockModel(res.data.data, []));
        }).catch((err: any) => {

        });
        
        this.creating = false;
    }

}
</script>
