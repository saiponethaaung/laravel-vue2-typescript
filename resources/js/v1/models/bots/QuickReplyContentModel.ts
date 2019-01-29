import ChatBlockContentModel from "../ChatBlockContentModel";
import QuickReplyItemModel from "./QuickReplyItemModel";
import { quickReplyContent } from "../../configuration/interface";
import Axios from "axios";

export default class QuickReplyContentModel extends ChatBlockContentModel {

    private quickReplyContent: Array<QuickReplyItemModel> = [];
    private creating: boolean = false;
    private rootUrl: string = '';
    private delChild: number = -1;
    
    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}/quick-reply`;
        for(let i of content.content) {
            this.buildQuickReplyItem(i);
        }
    }

    private buildQuickReplyItem(content: quickReplyContent) {
        this.quickReplyContent.push(new QuickReplyItemModel(content, this.rootUrl, this.project));
    }

    get item() : Array<QuickReplyItemModel> {
        return this.quickReplyContent;
    }

    set item(quickReply: Array<QuickReplyItemModel>) {
        this.quickReplyContent = quickReply;
    }

    get isCreating() : boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }

    get isChildDeleting() : number {
        return this.delChild;
    }

    set isChildDeleting(index: number) {
        this.delChild = index;
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
    
    async delItem(index: number) {
        this.isChildDeleting = index;
        await Axios({
            url: `${this.rootUrl}/${this.item[index].id}`,
            method: 'delete',
        }).then((res) => {
            this.item.splice(index, 1);
        }).catch((err) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to delete a quick reply!');
                alert(mesg);
            }
        });
        this.isChildDeleting = -1;
    }
}