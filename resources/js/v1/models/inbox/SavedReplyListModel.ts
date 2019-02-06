import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import SavedReplyModel from "./SavedReplyModel";
import Axios from "axios";

export default class SavedReplyListModel extends AjaxErrorHandler {

    private content: string = "";
    private messageContent: string = "";
    private savedReplyList : Array<SavedReplyModel> = [];

    constructor(
        private projectId: string,
        private userId: number
    ) {
        super();
    }

    get savedReplies() : Array<SavedReplyModel> {
        return this.savedReplyList;
    }

    set savedReplies(savedReplyList: Array<SavedReplyModel>) {
        this.savedReplyList = savedReplyList;
    }

    get reply() {
        return this.content;
    }

    set reply(reply: string) {
        this.content = reply;
    }

    get message() {
        return this.messageContent;
    }

    set message(message: string) {
        this.messageContent = message;
    }

    async getReply() {
        let res = {
            status: true,
            mesg: 'Success'
        };
    
        await Axios({
            url: `/api/v1/project/${this.projectId}/chat/user/${this.userId}/reply`,
            method: 'get'
        }).then(res => {
            for(let r of res.data.data) {
                this.savedReplyList.push(new SavedReplyModel(r));
            }
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to load saved reply list!');
            }
        });

        return res;
    }

    async createReply() {
        let res = {
            status: true,
            mesg: 'Success'
        };

        let data = new FormData();

        data.append('title', this.reply);
        data.append('message', this.message);

        await Axios({
            url: `/api/v1/project/${this.projectId}/chat/user/${this.userId}/reply`,
            method: 'post',
            data: data
        }).then(res => {
            this.reply = '';
            this.message = '';
            this.savedReplyList.push(new SavedReplyModel(res.data.data));
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create a reply!');
            }
        });
    }
}