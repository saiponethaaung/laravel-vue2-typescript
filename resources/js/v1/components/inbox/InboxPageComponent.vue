<template>
    <div class="inheritHFW">
        <template v-if="undefined==$store.state.projectInfo.pageConnected">
            Loading...
        </template>
        <template v-else>
            <template v-if="$store.state.projectInfo.pageConnected">
                <template v-if="$store.state.selectedInbox>-1">
                    <div class="chatInfoPanel">
                        <div class="chatHisCon">
                            <div v-for="(mesg, index) in mesgList" :key="index" class="chatContentCon" :class="{'sender': mesg.isSend}">
                                <figure class="chatImage">
                                    <img :src="mesg.isSend ? 'http://localhost:8087/images/sample/logo.png' : $store.state.inboxList[$store.state.selectedInbox].profile_pic"/>
                                </figure>
                                <div class="chatContent">
                                    <div class="chatContentBody">
                                        {{ mesg.mesg }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chatInputCon">
                            <template v-if="$store.state.projectInfo.publish">
                                <form class="chatInputMesgBox" @submit.prevent="sendReply()">
                                    <input type="text" v-model="mesg" placeholder="Send message..."/>
                                </form>
                                <div class="chatInputEmoji">
                                    <i class="material-icons">sentiment_satisfied</i>
                                </div>
                            </template>
                            <template v-else>
                                <div class="chatInputMesgBox">
                                    Activate Page in order to perform live chat
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="attributePanel">
                        <div class="attributeHeading">
                            <div class="attributeOwnerName">{{ `${$store.state.inboxList[$store.state.selectedInbox].first_name} ${$store.state.inboxList[$store.state.selectedInbox].last_name}` }}</div>
                        </div>
                        <div class="attributeTableRoot">
                            <table class="attributeTable">
                                <thead>
                                    <tr>
                                        <th class="attrTitle">User Attributes</th>
                                        <th class="attrValue">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="attrTitle">
                                            <span class="attrTitleWrapper">Name</span>
                                        </td>
                                        <td class="attrValue">value</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="attributeTable">
                                <thead>
                                    <tr>
                                        <th class="attrTitle">Custom Attributes</th>
                                        <th class="attrValue">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="$store.state.inboxList[$store.state.selectedInbox].custom_attribute.length>0">
                                        <tr v-for="(attr, index) in $store.state.inboxList[$store.state.selectedInbox].custom_attribute" :key="index">
                                            <td class="attrTitle">
                                                <span class="attrTitleWrapper">{{ attr.name }}</span>
                                            </td>
                                            <td class="attrValue">{{ attr.value }}</td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td class="attrTitle">-</td>
                                            <td class="attrValue">-</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <table class="attributeTable">
                                <thead>
                                    <tr>
                                        <th class="attrTitle">System Attributes</th>
                                        <th class="attrValue">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="attrTitle">
                                            <span class="attrTitleWrapper">Name</span>
                                        </td>
                                        <td class="attrValue">value</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div>Note</div>
                    </div>
                </template>
                <template v-else>
                    <div class="noContent">
                        <i class="material-icons">history</i>
                        <span clss="noContentInfo">Select a user to view chat history</span>
                    </div>
                </template>
            </template>
            <template v-else>
                <div class="noContent">
                    <i class="material-icons">assignment</i>
                    <span clss="noContentInfo">Connect a facebook page to access an inbox function.</span>
                </div>
            </template>
        </template>
    </div>   
</template>

<script lang="ts">
import { Component, Watch, Vue } from 'vue-property-decorator';
import Axios from 'axios';

@Component
export default class InboxPageComponent extends Vue {

    private mesg: string = '';
    private page: number = 1;
    private mesgList: any = [];

    @Watch('$store.state.selectedInbox')
    reloadMesg() {
        if(this.$store.state.selectedInbox===-1) return;
        this.mesgList = [];
        this.loadMesg();
    }

    async loadMesg() {
        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${this.$store.state.inboxList[this.$store.state.selectedInbox].id}/load-mesg`,
            method: 'get'
        }).then((res) => {
            this.mesgList = res.data.data;
            this.mesgList.sort((a:any, b:any) => {
                return a.id>b.id;
            });

            setTimeout(() => {
                this.checkNewMesg();
            }, 5000);
        }).catch((err) => {

        });
    }

    async checkNewMesg() {
        if(this.$store.state.selectedInbox===-1 || this.$route.name!=='project.inbox') return;
        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${this.$store.state.inboxList[this.$store.state.selectedInbox].id}/load-new?last_id=${this.mesgList[this.mesgList.length-1].id}`,
            method: 'get'
        }).then((res) => {
            this.mesgList = [...this.mesgList, ...res.data.data];
            this.mesgList.sort((a:any, b:any) => {
                return a.id>b.id;
            });

            setTimeout(() => {
                this.checkNewMesg();
            }, 5000);
        }).catch((err) => {
            setTimeout(() => {
                this.checkNewMesg();
            }, 5000);
        });
    }
    
    async sendReply() {
        if(this.mesg==='') return;

        let data = new FormData();
        data.append('mesg', this.mesg);

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${this.$store.state.inboxList[this.$store.state.selectedInbox].id}/reply`,
            data: data,
            method: 'post'
        }).then((res) => {
            this.mesg = '';
            this.mesgList.push(res.data.data);
        }).catch((err) => {

        });
    }

    processContent(content: string, type: number) {

    }
}
</script>
