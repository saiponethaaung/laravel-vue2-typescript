<template>
    <div>
        <template v-if="response.segmentId>0">
            <div class="selectedBlockCon">
                <div class="selectedLinkedBlock">
                    <span class="slbText">{{ response.segmentName }}</span>
                    <div class="slbDel" @click="delBlock()">
                        <i class="material-icons">delete</i>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
            <input
                type="text"
                v-model="blockKeyword"
                placeholder="Block name"
                @keyup="loadSuggestion()"
            />
            <template v-if="blockList.length>0">
                <div class="sugContainer">
                    <div v-for="(b, index) in blockList" :key="index" class="sugBlock">
                        <div class="sugBlockTitle">{{ b.title }}</div>
                        <div class="sugBlockSec">
                            <div
                                v-for="(s, sindex) in b.contents"
                                :key="sindex"
                                class="sugBlockSecTitle"
                                @click="addBlock(index, sindex)"
                            >{{ s.title }}</div>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from "vue-property-decorator";
import AIGroupRuleResponseModel from "../../models/ai/AIGroupRuleResponseModel";

@Component
export default class AIResponseSection extends Vue {
    @Prop() response!: AIGroupRuleResponseModel;
    private blockKeyword: string = "";
    private blockList: any = [];

    async delBlock() {
        let selectedSegment = this.response.segmentId;
        this.response.segmentId = 0;

        let update = await this.response.updateContent();

        if (!update.status) {
            alert(update.mesg);
            this.response.segmentId = selectedSegment;
        } else {
            this.response.segmentName = "";
        }
    }

    async loadSuggestion() {
        let suggestion = await this.response.searchSections(
            this.blockKeyword,
            this.$route.params.projectid
        );

        if (suggestion.type === "cancel") return;

        if (suggestion.status === false) {
            alert(suggestion.mesg);
            return;
        }

        this.blockList = suggestion.data;
    }

    async addBlock(block: any, section: any) {
        let selectedSegment = this.response.segmentId;
        this.response.segmentId = this.blockList[block].contents[section].id;

        let update = await this.response.updateContent();

        if (!update.status) {
            alert(update.mesg);
            this.response.segmentId = selectedSegment;
        } else {
            this.response.segmentName = this.blockList[block].contents[
                section
            ].title;
        }
    }
}
</script>
