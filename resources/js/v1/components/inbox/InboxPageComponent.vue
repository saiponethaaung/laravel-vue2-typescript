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
                            <div class="chatHisRoot">
                                <template v-for="(mesg, index) in mesgList">
                                    <div v-if="mesg.contentType!==2 && mesg.contentType!==3" :key="index" class="chatContentCon" :class="{'sender': mesg.isSend}">
                                        <figure class="chatImage">
                                            <img :src="mesg.isSend ? '/images/sample/logo.png' : $store.state.inboxList[$store.state.selectedInbox].profile_pic"/>
                                        </figure>
                                        <div class="chatContent">
                                            <template v-if="mesg.contentType===5">
                                                <list-template-component :content="JSON.parse(mesg.mesg)"></list-template-component>
                                            </template>
                                            <template v-else-if="mesg.contentType===6">
                                                <gallery-template-component :content="JSON.parse(mesg.mesg)"></gallery-template-component>
                                            </template>
                                            <template v-else>
                                                <div v-html="processContent(mesg.mesg, mesg.contentType)"></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="liveChatAction" v-if="$store.state.projectInfo.publish">
                                <button class="liveChatButton startLiveChat" @click="startLiveChat()" type="button" v-if="!$store.state.inboxList[$store.state.selectedInbox].live_chat">
                                    <img src="/images/icons/chat_icon.png"/>
                                    <span>Start a live chat</span>
                                </button>
                                <button class="liveChatButton stopLiveChat" @click="stopLiveChat()" type="button" v-else>
                                    <img src="/images/icons/chat_stop.png"/>
                                    <span>Finish live chat</span>
                                </button>
                            </div>
                        </div>
                        <div class="chatInputCon">
                            <template v-if="$store.state.projectInfo.publish">
                                <template v-if="$store.state.inboxList[$store.state.selectedInbox].live_chat">
                                    <form class="chatInputMesgBox" @submit.prevent="sendReply()">
                                        <input type="text" v-model="mesg" placeholder="Send message..."/>
                                    </form>
                                    <div class="chatInputEmoji">
                                        <i class="material-icons">sentiment_satisfied</i>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="chatInputMesgBox">
                                        <input type="text" placeholder="Send message..." disabled/>
                                    </div>
                                    <div class="chatInputEmoji">
                                        <i class="material-icons">sentiment_satisfied</i>
                                    </div>
                                </template>
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
                        <!-- <div>Note</div> -->
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

import GalleryTemplateComponent from './builder/GalleryTemplateComponent.vue';
import ListTemplateComponent from './builder/ListTemplateComponent.vue';

@Component({
    components: {
        GalleryTemplateComponent,
        ListTemplateComponent
    }
})
export default class InboxPageComponent extends Vue {

    private mesg: string = '';
    private page: number = 1;
    private mesgList: any = [];
    private firstLoad: boolean = true;

    @Watch('$store.state.selectedInbox')
    reloadMesg() {
        if(this.$store.state.selectedInbox===-1) return;
        this.mesgList = [];
        this.firstLoad = true;
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
            if(this.mesgList.length===1) {
                setTimeout(() => {
                    this.checkNewMesg();
                }, 5000);
            }
        }).catch((err) => {

        });
    }

    processContent(content: string, type: number) {
        let res: any = '';
        let jc: any = '';

        switch(type) {
            case(0):
                res = `<div class="chatContentBody">${content}</div>`;
                break;

            case(1):
                jc = JSON.parse(content);
                if(undefined===jc.attachement) {
                    res = `<div class="chatContentBody">${jc.text}</div>`;
                } else {
                    res = type+'/|\\'+content;
                }
                break;

            case(4):
                jc = JSON.parse(content);
                res = `<div class="chatContentBody">${jc.text}</div>`;
                break;

            case(7):
                jc = JSON.parse(content);
                if(undefined!==jc.image) {
                    res = `<figure><img src='${jc.image}'/></figure>`
                }
                break;

            default:
                res = type+'/|\\'+content;
                break;
        }

        return res;
    }

    async startLiveChat() {
        var data = new FormData();
        data.append('status', 'true');

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${this.$store.state.inboxList[this.$store.state.selectedInbox].id}/live-chat`,
            data: data,
            method: 'post'
        }).then((res) => {
            this.$store.commit('updateInboxChatStatus', {
                index: this.$store.state.selectedInbox,
                status: true
            });
            this.$store.state.chatFilter = 0;
        }).catch((err) => {

        });
    }

    async stopLiveChat() {
        var data = new FormData();
        data.append('status', 'false');

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${this.$store.state.inboxList[this.$store.state.selectedInbox].id}/live-chat`,
            data: data,
            method: 'post'
        }).then((res) => {
            this.$store.commit('updateInboxChatStatus', {
                index: this.$store.state.selectedInbox,
                status: false
            });
            this.$store.state.chatFilter = 1;
        }).catch((err) => {

        });

    }
}
</script>
