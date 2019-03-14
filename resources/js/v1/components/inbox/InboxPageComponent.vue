<template>
    <div class="inheritHFW">
        <template v-if="undefined==$store.state.projectInfo.pageConnected">Loading...</template>
        <template v-else>
            <template v-if="$store.state.projectInfo.pageConnected">
                <template v-if="$store.state.selectedInbox>-1">
                    <div class="chatInfoPanel">
                        <div class="chatHisCon" v-on:scroll="scrollCallback()">
                            <div class="chatHisRoot" ref="chatBox">
                                <template v-if="prevLoading">Loading...</template>
                                <template v-for="(mesg, index) in mesgList">
                                    <div
                                        v-if="mesg.contentType!==2 && mesg.contentType!==3"
                                        :key="index"
                                        class="chatContentCon"
                                        :class="{'sender': mesg.isSend}"
                                    >
                                        <figure class="chatImage" v-if="!mesg.isSend || (mesg.isLive && mesg.isSend)">
                                            <img
                                                :src="mesg.isSend ? '/images/sample/logo.png' : $store.state.inboxList[$store.state.selectedInbox].profile_pic"
                                            >
                                        </figure>
                                        <div class="chatContent">
                                            <template v-if="mesg.contentType===1">
                                                <text-template-component
                                                    :content="JSON.parse(mesg.mesg)"
                                                ></text-template-component>
                                            </template>
                                            <template v-else-if="mesg.contentType===5">
                                                <list-template-component
                                                    :content="JSON.parse(mesg.mesg)"
                                                ></list-template-component>
                                            </template>
                                            <template v-else-if="mesg.contentType===6">
                                                <gallery-template-component
                                                    :content="JSON.parse(mesg.mesg)"
                                                ></gallery-template-component>
                                            </template>
                                            <template v-else>
                                                <div
                                                    v-html="processContent(mesg.mesg, mesg.contentType)"
                                                ></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="liveChatAction" v-if="$store.state.projectInfo.publish">
                                <button
                                    class="liveChatButton startLiveChat"
                                    @click="startLiveChat()"
                                    type="button"
                                    v-if="!$store.state.inboxList[$store.state.selectedInbox].live_chat"
                                >
                                    <img src="/images/icons/chat/chat_icon.png">
                                    <span>Start a live chat</span>
                                </button>
                                <button
                                    class="liveChatButton stopLiveChat stopLiveChatBtn"
                                    @click="stopLiveChat()"
                                    type="button"
                                    v-else
                                >
                                    <img src="/images/icons/chat/chat_stop.png">
                                    <span>Finish live chat</span>
                                </button>
                            </div>
                        </div>
                        <div class="chatInputCon">
                            <template v-if="$store.state.projectInfo.publish">
                                <template
                                    v-if="$store.state.inboxList[$store.state.selectedInbox].live_chat"
                                >
                                    <form class="chatInputMesgBox" @submit.prevent="sendReply()">
                                        <input
                                            type="text"
                                            v-model="mesg"
                                            placeholder="Send message..."
                                        >
                                    </form>
                                    <div class="chatInputEmoji">
                                        <i class="material-icons">sentiment_satisfied</i>
                                    </div>
                                    <div class="chatInputEmoji" @click="saveReply=true">
                                        <i class="material-icons">chat</i>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="chatInputEmoji">
                                        <i class="material-icons">sentiment_satisfied</i>
                                    </div>
                                    <div class="chatInputEmoji">
                                        <i class="material-icons">chat</i>
                                    </div>
                                </template>
                            </template>
                            <template v-else>
                                <div
                                    class="chatInputMesgBox"
                                >Activate Page in order to perform live chat</div>
                            </template>
                        </div>
                    </div>
                    <div class="popSavedReply">
                        <div class="popFixedContainer popFixedCenter" v-if="saveReply">
                            <div class="userAttributePop filterAttribute">
                                <div v-show="!createReply">
                                    <div class="popNav">
                                        <span class="saved">Saved Replies</span>
                                        <span class="manage" @click="saveReply = false">
                                            <i class="material-icons">clear</i>
                                        </span>
                                    </div>
                                    <div class="replyText">
                                        <i class="material-icons">search</i>
                                        <input
                                            class="inputText"
                                            placeholder="Search saved replies"
                                            @keyup="replyList.getReply(replyList.search)"
                                            v-model="replyList.search"
                                        >
                                    </div>
                                    <div class="savedList">
                                        <template v-if="replyList.listLoading">Loading...</template>
                                        <template
                                            v-for="(reply, index) in replyList.savedReplies"
                                            v-else
                                        >
                                            <div class="subReply" :key="index" @click="selectReply(index)">
                                                <div class="replyTitle">{{ reply.title }}</div>
                                                <div id="reply" class="replyMessage">{{ reply.message }}</div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="createSavedLink">
                                        <a href="#" @click="createReply = true">Create Saved Reply</a>
                                    </div>
                                </div>
                                <div class="createReply" v-show="createReply">
                                    <div class="createReplyNav">
                                        <div class="list" @click="createReply = false">
                                            <i class="material-icons">keyboard_arrow_left</i>
                                            <span>List</span>
                                        </div>
                                        <span class="saved">Create Saved Reply</span>
                                    </div>
                                    <div class="replyText">
                                        <input
                                            class="inputText"
                                            placeholder="Enter reply title"
                                            v-model="replyList.reply"
                                        >
                                    </div>
                                    <div class="replyMessages">
                                        <textarea
                                            class="inputMessage"
                                            placeholder="Enter message"
                                            v-model="replyList.message"
                                        />
                                    </div>
                                    <div class="buttonOption">
                                        <div class="alignBtn">
                                            <button
                                                class="btnAction"
                                                @click="replyList.createReply(), createReply = false"
                                            >Save</button>
                                            <button
                                                class="btnAction"
                                                @click="createReply = false"
                                            >Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="attributePanel">
                        <div class="attributeHeading">
                            <div
                                class="attributeOwnerName"
                            >{{ `${$store.state.inboxList[$store.state.selectedInbox].first_name} ${$store.state.inboxList[$store.state.selectedInbox].last_name}` }}</div>
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
                                    <template
                                        v-if="$store.state.inboxList[$store.state.selectedInbox].custom_attribute.length>0"
                                    >
                                        <tr
                                            v-for="(attr, index) in $store.state.inboxList[$store.state.selectedInbox].custom_attribute"
                                            :key="index"
                                        >
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
                        <div class="alignNote">
                            <div class="addNote" @click="showTags=!showTags">
                                <span>Write note about the shop</span>
                                <span class="iconSub">
                                    <i class="material-icons">
                                        <template v-if="showTags">expand_more</template>
                                        <template v-else>expand_less</template>
                                    </i>
                                </span>
                            </div>
                            <div v-show="showTags" class="adminNote">
                                <div class="noteContent">
                                    <template v-for="(note, index) in noteList.adminNotes">
                                        <div class="subNote" :key="index">
                                            <span class="userIcon"></span>
                                            <span class="userName">{{ note.name }}</span>
                                            <div class="userNote">
                                                <span>{{ note.note }}</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="noteInput">
                                    <form @submit.prevent="noteList.createNote()">
                                        <input
                                            type="text"
                                            placeholder="Type a note"
                                            v-model="noteList.note"
                                        >
                                    </form>
                                </div>
                            </div>
                        </div>
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
import { Component, Watch, Vue } from "vue-property-decorator";
import Axios from "axios";

import AdminNoteListModel from "../../models/inbox/AdminNoteListModel";
import GalleryTemplateComponent from "./builder/GalleryTemplateComponent.vue";
import ListTemplateComponent from "./builder/ListTemplateComponent.vue";
import TextTemplateComponent from "./builder/TextTemplateComponent.vue";
import SavedReplyListModel from "../../models/inbox/SavedReplyListModel";

@Component({
    components: {
        GalleryTemplateComponent,
        ListTemplateComponent,
        TextTemplateComponent
    }
})
export default class InboxPageComponent extends Vue {
    private mesg: string = "";
    private page: number = 1;
    private mesgList: any = [];
    private firstLoad: boolean = true;
    private el: any = null;
    private prevLoading: boolean = false;
    private lastScroll: number = 0;
    private showTags: boolean = false;
    private noteList: AdminNoteListModel = new AdminNoteListModel("", 0);
    private saveReply: boolean = false;
    private createReply: boolean = false;
    private replyList: SavedReplyListModel = new SavedReplyListModel("", 0);
    private message: string = "";

    @Watch("$store.state.selectedInbox")
    async reloadMesg() {
        if (this.$store.state.selectedInbox === -1) return;
        setTimeout(() => {
            this.el = this.$refs.mesgBox;
        }, 3000);
        this.noteList = new AdminNoteListModel(
            this.$store.state.projectInfo.id,
            this.$store.state.inboxList[this.$store.state.selectedInbox].id
        );
        this.replyList = new SavedReplyListModel(
            this.$store.state.projectInfo.id,
            this.$store.state.inboxList[this.$store.state.selectedInbox].id
        );
        this.mesgList = [];
        this.firstLoad = true;
        this.loadMesg(false);
        let status = await this.noteList.getNote();
        if (!status.status) {
            alert(status.mesg);
        }
        let replyStatus = await this.replyList.getReply();
        if (!replyStatus.status) {
            alert(status.mesg);
        }
    }

    private scrollCallback(a: any) {
        this.el = this.$el.querySelector(".chatHisRoot");

        if (
            this.el.getBoundingClientRect().top > -100 &&
            this.lastScroll < this.el.getBoundingClientRect().top &&
            !this.prevLoading
        ) {
            this.loadMesg(true);
        }
        this.lastScroll = this.el.getBoundingClientRect().top;
    }

    async loadMesg(prev: boolean) {
        let prevId: number | boolean = false;

        if (prev) {
            this.prevLoading = true;
            prevId = this.mesgList[0].id;
        }

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${
                this.$store.state.inboxList[this.$store.state.selectedInbox].id
            }/load-mesg?prev=${prevId.toString()}`,
            method: "get"
        })
            .then(res => {
                this.mesgList = [...res.data.data, ...this.mesgList];
                this.mesgList.sort((a: any, b: any) => {
                    return a.id > b.id;
                });

                this.getUnique();

                if (this.firstLoad) {
                    setTimeout(() => {
                        this.firstLoad = false;
                        this.el = this.$el.querySelector(".chatHisRoot");
                        var el: any = this.$el.querySelector(".chatHisCon");
                        el.scrollTop = this.el.getBoundingClientRect().height;
                    }, 1000);
                    setTimeout(() => {
                        this.checkNewMesg();
                    }, 5000);
                }

                if (prev) {
                    this.prevLoading = false;
                }
            })
            .catch(err => {
                if (prev) {
                    this.prevLoading = false;
                }
            });
    }

    private getUnique() {
        var uniques: any = [];
        var itemsFound: any = {};
        for (var i = 0, l = this.mesgList.length; i < l; i++) {
            var stringified: any = JSON.stringify(this.mesgList[i]);
            if (itemsFound[stringified]) {
                continue;
            }
            uniques.push(this.mesgList[i]);
            itemsFound[stringified] = true;
        }
        this.mesgList = uniques;
    }

    async checkNewMesg() {
        if (
            this.$store.state.selectedInbox === -1 ||
            this.$route.name !== "project.inbox"
        )
            return;
        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${
                this.$store.state.inboxList[this.$store.state.selectedInbox].id
            }/load-new?last_id=${this.mesgList[this.mesgList.length - 1].id}`,
            method: "get"
        })
            .then(res => {
                this.mesgList = [...this.mesgList, ...res.data.data];
                this.mesgList.sort((a: any, b: any) => {
                    return a.id > b.id;
                });

                setTimeout(() => {
                    this.checkNewMesg();
                }, 5000);
            })
            .catch(err => {
                setTimeout(() => {
                    this.checkNewMesg();
                }, 5000);
            });
    }

    async sendReply() {
        if (this.mesg === "") return;

        let data = new FormData();
        data.append("mesg", this.mesg);

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${
                this.$store.state.inboxList[this.$store.state.selectedInbox].id
            }/reply`,
            data: data,
            method: "post"
        })
            .then(res => {
                this.mesg = "";
                this.mesgList.push(res.data.data);
                if (this.mesgList.length === 1) {
                    setTimeout(() => {
                        this.checkNewMesg();
                    }, 5000);
                }
            })
            .catch(err => {});
    }

    processContent(content: string, type: number) {
        let res: any = "";
        let jc: any = "";

        switch (type) {
            case 0:
                res = `<div class="chatContentBody">${content}</div>`;
                break;

            case 1:
                jc = JSON.parse(content);
                if (undefined === jc.attachement) {
                    res = `<div class="chatContentBody">${jc.text}</div>`;
                } else {
                    res = type + "/|\\" + content;
                }
                break;

            case 4:
                jc = JSON.parse(content);
                res = `<div class="chatContentBody">${jc.text}</div>`;
                break;

            case 7:
                jc = JSON.parse(content);
                if (undefined !== jc.image) {
                    res = `<figure><img src='${jc.image}'/></figure>`;
                }
                break;

            default:
                res = type + "/|\\" + content;
                break;
        }

        return res;
    }

    async startLiveChat() {
        var data = new FormData();
        data.append("status", "true");

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${
                this.$store.state.inboxList[this.$store.state.selectedInbox].id
            }/live-chat`,
            data: data,
            method: "post"
        })
            .then(res => {
                this.$store.commit("updateInboxChatStatus", {
                    index: this.$store.state.selectedInbox,
                    status: true
                });
                this.$store.state.chatFilter = 0;
            })
            .catch(err => {});
    }

    async stopLiveChat() {
        var data = new FormData();
        data.append("status", "false");

        await Axios({
            url: `/api/v1/project/${this.$route.params.projectid}/chat/user/${
                this.$store.state.inboxList[this.$store.state.selectedInbox].id
            }/live-chat`,
            data: data,
            method: "post"
        })
            .then(res => {
                this.$store.commit("updateInboxChatStatus", {
                    index: this.$store.state.selectedInbox,
                    status: false
                });
                this.$store.state.chatFilter = 1;
            })
            .catch(err => {});
    }

    async selectReply(index: number) {

        this.mesg = this.replyList.savedReplies[index].message;
        this.saveReply = false;

    }
}
</script>
