import Axios, { CancelTokenSource } from "axios";
import { quickReplyContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";
import QuickReplyItemModel from "./QuickReplyItemModel";

export default class QuickReplyContentModel extends ChatBlockContentModel {

    private quickReplyContent: Array<QuickReplyItemModel> = [];
    private creating: boolean = false;
    private rootUrl: string = '';
    private delChild: number = -1;
    private orderToken: CancelTokenSource = Axios.CancelToken.source();
    public warningText = '';

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}/quick-reply`;
        for (let i of content.content) {
            this.buildQuickReplyItem(i);
        }
    }

    private buildQuickReplyItem(content: quickReplyContent) {
        this.quickReplyContent.push(new QuickReplyItemModel(content, this.rootUrl, this.project));
    }

    get item(): Array<QuickReplyItemModel> {
        return this.quickReplyContent;
    }

    set item(quickReply: Array<QuickReplyItemModel>) {
        this.quickReplyContent = quickReply;
    }

    get isCreating(): boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }

    get isChildDeleting(): number {
        return this.delChild;
    }

    set isChildDeleting(index: number) {
        this.delChild = index;
    }

    get showWarning() {
        this.warningText = 'Chat process on messenger will stop here due to incomplete quick reply component!';

        if(this.item.length===0) {
            return true;
        }

        for(let i in this.item) {
            if(this.item[i].title==='') {
                let position: any = parseInt(i)+1;
                switch(parseInt(i)) {
                    case 0:
                        position = position+'st';
                        break;
                        
                    case 1:
                        position = position+'nd';
                        break;
                        
                    case 2:
                        position = position+'rd';
                        break;
                        
                    default:
                        position = position+'th';
                        break;
                }

                this.warningText = `Chat process on messenger will stop here because ${position} quick reply is incomplete!`;
                return true;
            }
        }

        return false;
    }

    async createQuickReply() {
        this.isCreating = true;

        await Axios({
            url: this.rootUrl,
            method: 'post'
        }).then((res: any) => {
            this.buildQuickReplyItem(res.data.data);
            this.item[this.item.length - 1].canShow = true;
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to create new quick reply!');
            }
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
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete a quick reply!');
            }
        });
        this.isChildDeleting = -1;
    }

    async updateOrder() {

        this.orderToken.cancel();
        this.orderToken = Axios.CancelToken.source();

        let data = new FormData();

        for (let i in this.item) {
            data.append(`order[${i}]`, this.item[i].id.toString());
        }

        await Axios({
            url: `${this.rootUrl}/order`,
            data: data,
            method: 'post',
            cancelToken: this.orderToken.token
        }).catch(err => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to update quick reply order!');
            }
        });
    }
}