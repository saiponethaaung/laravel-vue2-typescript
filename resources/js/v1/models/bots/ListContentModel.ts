import ChatBlockContentModel from "../ChatBlockContentModel";
import Axios, { CancelTokenSource } from "axios";
import ListItemModel from "./ListItemModel";
import { listContent, buttonContent } from "../../configuration/interface";

export default class ListContentModel extends ChatBlockContentModel {

    private listContent: Array<ListItemModel> = [];
    private listButton: null | buttonContent = null;
    private creating: boolean = false;
    private orderToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(content: any) {
        super(content);
        for(let i of content.content.content) {
            this.buildListItem(i);
        }
        this.listButton = content.content.button;
    }

    private buildListItem(content: listContent) {
        this.listContent.push(new ListItemModel(content, `/api/v1/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}/list`));
    }

    get item() : Array<ListItemModel> {
        return this.listContent;
    }

    get isCreating(): boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }

    async createList() {
        this.isCreating = true;

        await Axios({
            url: `/api/v1/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}/list`,
            method: 'post'
        }).then((res: any) => {
            this.buildListItem(res.data.content);
        }).catch((err: any) => {
            let mesg = this.globalHandler(err, 'Failed to create new list!');
            alert(mesg);
        });

        this.isCreating = false;
    }

}