<template>
    <div class="inheritHFW">
        <template v-if="$store.state.chatBot.section>0 && $store.state.chatBot.block>0">
            <template v-if="isLoading">
                <div class="contentRoot">
                    <div class="builderSectionInfo">Loading...</div>
                </div>
            </template>
            <template v-else-if="section!==null">
                <builder-component
                    :isBroadcast="false"
                    :value="contents"
                    v-on:contentChanged="reloadSection()"
                    :section="section"
                ></builder-component>
            </template>
        </template>
        <template v-else>
            <div class="noContent">
                <i class="material-icons">assignment</i>
                <span clss="noContentInfo">Select a block to start building a bot.</span>
            </div>
        </template>
    </div>
</template>

<script lang="ts">
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";
import Axios from "axios";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

@Component
export default class ContentComponent extends Vue {
    private loadingToken: any = Axios.CancelToken.source();
    private reloadSectionToken: any = Axios.CancelToken.source();
    private isLoading: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private contents: any = [];
    private section: any = null;

    @Watch("$store.state.chatBot")
    async sectionChange() {
        if (
            this.$store.state.chatBot.section > 0 &&
            this.$store.state.chatBot.block > 0
        ) {
            await this.loadContent();
        }
    }

    async reloadSection() {
        this.reloadSectionToken.cancel();
        this.reloadSectionToken = Axios.CancelToken.source();

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}/isValid`,
            cancelToken: this.reloadSectionToken.token
        })
            .then((res: any) => {
                this.$store.commit("updateChatBotValid", {
                    block: this.$store.state.chatBot.block,
                    section: this.$store.state.chatBot.section,
                    valid: res.data.isValid
                });
            })
            .catch((err: any) => {});
    }

    async loadContent() {
        this.loadingToken.cancel();
        this.loadingToken = Axios.CancelToken.source();

        this.isLoading = true;
        this.contents = [];

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}/content`,
            cancelToken: this.loadingToken.token
        })
            .then((res: any) => {
                this.contents = res.data.content;
                this.section = res.data.section;
                this.isLoading = false;
            })
            .catch((err: any) => {
                if (err.response) {
                    let mesg = this.ajaxHandler.globalHandler(
                        err,
                        "Failed to load content!"
                    );
                    alert(mesg);
                    this.isLoading = false;
                }
            });
    }
}
</script>
