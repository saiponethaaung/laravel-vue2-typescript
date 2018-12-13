import ChatBlockContentModel from "../ChatBlockContentModel";
import QuickReplyItemModel from "./QuickReplyItemModel";
import { quickReplyContent } from "../../configuration/interface";
import Axios from "axios";

export default class QuickReplyContentModel extends ChatBlockContentModel {

    private quickReplyContent: Array<QuickReplyItemModel> = [];
    private creating: boolean = false;
    private rootUrl: string = '';
    
    constructor(content: any) {
        super(content);
        this.rootUrl = `/api/v1/project/${this.project}/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}/quick-reply`;
        for(let i of content.content) {
            this.buildQuickReplyItem(i);
        }
    }

    private buildQuickReplyItem(content: quickReplyContent) {
        this.quickReplyContent.push(new QuickReplyItemModel(content, this.rootUrl));
    }

    get item() : Array<QuickReplyItemModel> {
        return this.quickReplyContent;
    }

    get isCreating() : boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }

    async createQuickReply() {
        this.isCreating = true;

        await Axios({
            url: this.rootUrl,
            method: 'post'
        }).then((res: any) => {
            this.buildQuickReplyItem(res.data.data);
        }).catch((err: any) => {
            let mesg = this.globalHandler(err, 'Failed to create new quick reply!');
            alert(mesg);
        });

        this.isCreating = false;
    }
}