<template>
    <div>
        <template v-if="botSection>0 && botBlock>0">
            {{ $store.state.chatBot.section }}
            {{ $store.state.chatBot.block }}
            <br/>- total change ({{ changeCount }})
        </template>
        <template v-else>
            There is noting to see here!
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

    private changeCount: number = 0;
    private loadingToken: any = Axios.CancelToken.source();
    private isLoading: boolean = false;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    
    get botSection() {
        return this.$store.state.chatBot.section;
    }

    get botBlock() {
        return this.$store.state.chatBot.block;
    }

    @Watch('$store.state.chatBot')
    async botSectionChange() {
        if(this.botSection>0 && this.botBlock>0) {
            this.loadContent();
        }
        this.changeCount++;
    }

    async loadContent() {
        this.loadingToken.cancel();
        this.loadingToken = Axios.CancelToken.source();

        this.isLoading = true;

        await Axios({
            url: `/api/v1/chat-bot/block/${this.botBlock}/section/${this.botSection}/content`,
            cancelToken: this.loadingToken.token        
        }).then((res: any) => {

        }).catch((err: any) => {
            let mesg = this.ajaxHandler.globalHandler(err, 'Failed to load content!');

            alert(mesg);
        });

        this.isLoading = false;
    }
}
</script>
