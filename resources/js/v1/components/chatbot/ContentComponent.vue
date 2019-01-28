<template>
    <div class="inheritHFW">
        <template v-if="$store.state.chatBot.section>0 && $store.state.chatBot.block>0">
            <template v-if="isLoading">
                Loading...
            </template>
            <template v-else>
                <builder-component
                    :isBroadcast="false"
                    :value="contents"
                    :section="section"></builder-component>
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
import Vue from 'vue';
import { Component, Watch } from 'vue-property-decorator';
import Axios from 'axios';
import AjaxErrorHandler from '../../utils/AjaxErrorHandler';

@Component
export default class ContentComponent extends Vue {
    private loadingToken: any = Axios.CancelToken.source();
    private isLoading: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private contents: any = [];
    private section: any = null;

    @Watch('$store.state.chatBot')
    async sectionChange() {
        if(this.$store.state.chatBot.section>0 && this.$store.state.chatBot.block>0) {
            await this.loadContent();
        }
    }

    async loadContent() {
        this.loadingToken.cancel();
        this.loadingToken = Axios.CancelToken.source();

        this.isLoading = true;
        this.contents = [];

        await Axios({
            url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.$store.state.chatBot.block}/section/${this.$store.state.chatBot.section}/content`,
            cancelToken: this.loadingToken.token
        }).then((res: any) => {
            this.contents = res.data.content;
            this.section = res.data.section;
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load content!');
                alert(mesg);
            } else {
                this.loadingToken.cancel();
            }
        });

        this.isLoading = false;
    }
}
</script>
