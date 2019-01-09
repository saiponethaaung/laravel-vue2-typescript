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
                            Reply content
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
    private mesgList: any = '';
    
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
        }).catch((err) => {

        });
    }
}
</script>
