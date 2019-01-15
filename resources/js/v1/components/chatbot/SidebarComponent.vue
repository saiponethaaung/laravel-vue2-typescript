<template>
    <div>
        <h3 class="chatBotHeading">Chatbot</h3>
        <div>
            <template v-if="blockLoading">
                Loading...
            </template>
            <template v-else>
                <div v-for="(block, index) in blocks" :key="index" class="chatBlock">
                    <h5 class="chatBlockHeading">{{ block.title }}</h5>
                    <div v-if="!block.lock" class="chatBlockControl">
                        <button @click="delBlockIndex=index">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                    <div class="chatBlockContentList">
                        <div v-for="(section, sIndex) in block.sections" :key="sIndex" class="chatBlockContent"  @click="selectedBlock=`${index}-${sIndex}`" :class="{'selectedBlock': selectedBlock===`${index}-${sIndex}`}">
                            {{ section.title }}
                        </div>
                        <template v-if="!block.lock">
                            <div v-if="!block.isSecCreating" class="chatBlockContent addMore" @click="block.createNewSection()">
                                <i class="material-icons">add</i>
                            </div>
                            <div v-else class="chatBlockContent addMore">
                                <i class="material-icons">autorenew</i>
                            </div>
                        </template>
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
        <template v-if="showDelConfirm">
            <popup-component :type="1">
                <button class="closePopConfirm" @click="showDelConfirm=false;delBlockIndex=-1;">
                    <i class="material-icons">close</i>
                </button>
                <div class="delPopContent">
                    <p class="delPopHeading">
                        Are you sure you want to delete the <b>{{ blocks[delBlockIndex].title }}</b>?<br/>
                        <span class="noticeList">It will effect the chatbot as following section are connected...</span>
                    </p>
                    <ul class="listOfSection">
                        <li v-for="(sub, index) in blocks[delBlockIndex].sections" :key="index">
                            {{ sub.title }}
                        </li>
                    </ul>
                </div>
                <div class="delPopActionFooter">
                    <div>
                        <button @click="showDelConfirm=false;delBlockIndex=-1;">Cancel</button>
                        <button @click="blocks[delBlockIndex].allowDelete=true;showDelConfirm=false;deleteChatBlock();">Ok</button>
                    </div>
                </div>
            </popup-component>
        </template>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Watch } from 'vue-property-decorator';
import ChatBlockModel from '../../models/ChatBlockModel';
import Axios from 'axios';

@Component
export default class SidebarComponent extends Vue {
    
    private delBlockIndex: number = -1;
    private showDelConfirm: boolean = false;
    private creating: boolean = false;
    private blockLoading: boolean = false;
    private blocks: Array<ChatBlockModel> = [];
    private selectedBlock: string = "";

    async mounted() {
        await this.loadBlocks();
    }

    @Watch('selectedBlock')
    selectBlock() {
        if(this.selectedBlock==="") {
            this.$store.commit('selectChatBot', { block: -1, section: -1 });
        } else {
            let blockSection: any = this.selectedBlock.split("-");

            this.$store.commit('selectChatBot', { block: this.blocks[blockSection[0]].id, section: this.blocks[blockSection[0]].sections[blockSection[1]].id });
        }
    }

    @Watch('delBlockIndex')
    async deleteChatBlock() {
        if(this.delBlockIndex==-1 || undefined===this.blocks[this.delBlockIndex]) return;

        if(this.blocks[this.delBlockIndex].canDelete) {
            let deleteBlock = await this.blocks[this.delBlockIndex].deleteBlock();

            if(deleteBlock.status) {
                this.blocks.splice(this.delBlockIndex, 1);
                this.selectedBlock = "";
                this.$store.commit('selectChatBot', { block: -1, section: -1 });
            } else {
                alert(deleteBlock.mesg);
                this.blocks[this.delBlockIndex].allowDelete = false;
            }
 
            this.delBlockIndex = -1;
        } else {
            this.showDelConfirm = true;
        }
    }

    @Watch('$store.state.delBot')
    async deleteSection() {
        if(this.$store.state.delBot.section==-1 && this.$store.state.delBot.block==-1) return null;
        for(let i in this.blocks) {
            if(this.blocks[i].id!=this.$store.state.delBot.block) continue;
            for(let s in this.blocks[i].sections) {
                if(this.blocks[i].sections[s].id!=this.$store.state.delBot.section) continue;
                this.blocks[i].sections.splice(parseInt(s), 1);
                this.selectedBlock = "";
                this.$store.commit('selectChatBot', { block: -1, section: -1 });
                break;
            }
            break;
        }
    }

    @Watch('$store.state.updateBot')
    async updateSectionTitle() {
        if(this.$store.state.updateBot.section==-1 && this.$store.state.updateBot.block==-1) return null;
        for(let i in this.blocks) {
            if(this.blocks[i].id!=this.$store.state.updateBot.block) continue;
            for(let s in this.blocks[i].sections) {
                if(this.blocks[i].sections[s].id!=this.$store.state.updateBot.section) continue;
                this.blocks[i].sections[s].title = this.$store.state.updateBot.title;
                break;
            }
            break;
        }
    }

    async loadBlocks() {
        if(undefined===this.$store.state.projectInfo.id) return;
        this.blockLoading = true;

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/blocks`
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
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block`,
            method: "POST"
        }).then((res: any) => {
            this.blocks.push(new ChatBlockModel(res.data.data, []));
        }).catch((err: any) => {

        });
        
        this.creating = false;
    }

}
</script>
