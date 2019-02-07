import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import SavedReplyModel from "./SavedReplyModel";
import Axios, { CancelTokenSource } from "axios";

export default class SavedReplyListModel extends AjaxErrorHandler {

    private content: string = "";
    private messageContent: string = "";
    private searchContent: string = "";
    private savedReplyList : Array<SavedReplyModel> = [];
    private cancelToken: CancelTokenSource = Axios.CancelToken.source();
    public listLoading: boolean = false;

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

    get search() {
        return this.searchContent;
    }

    set search(search: string) {
        this.searchContent = search;
    }

    async getReply() {
        let res = {
            status: true,
            mesg: 'Success'
        };

        this.listLoading = true;

        this.cancelToken.cancel();
        this.cancelToken = Axios.CancelToken.source();

        let keyword = "";
        keyword = this.search;
        console.log(keyword);
    
        await Axios({
            url: `/api/v1/project/${this.projectId}/chat/user/${this.userId}/reply?keyword=${keyword}`,
            method: 'get',
            cancelToken: this.cancelToken.token
        }).then(res => {
            this.savedReplyList = [];
            for(let r of res.data.data) {
                this.savedReplyList.push(new SavedReplyModel(r));
            }
            this.listLoading = false;
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to load saved reply list!');
                this.listLoading = false;
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