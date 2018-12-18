import ChatBlockContentModel from "../ChatBlockContentModel";
import Axios, { CancelTokenSource } from "axios";
import ListItemModel from "./ListItemModel";
import { listContent, buttonContent } from "../../configuration/interface";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

export default class ListContentModel extends ChatBlockContentModel {

    private rootUrl: string = "";
    private listContent: Array<ListItemModel> = [];
    private listButton: null | buttonContent = null;
    private creating: boolean = false;
    private buttonEdit: boolean = false;
    private orderToken: CancelTokenSource = Axios.CancelToken.source();
    private buttonCreating: boolean = false;
    private buttonToken: CancelTokenSource = Axios.CancelToken.source();
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

    constructor(content: any) {
        super(content);
        this.rootUrl = `/api/v1/project/${this.project}/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}`;
        for(let i in content.content.content) {
            this.buildListItem(content.content.content[i]);
        }
        this.listButton = content.content.button;
    }

    private buildListItem(content: listContent) {
        this.listContent.push(new ListItemModel(content, `${this.rootUrl}/list`));
    }

    get url() : string {
        return this.rootUrl;
    }

    get item() : Array<ListItemModel> {
        return this.listContent;
    }

    set item(list: Array<ListItemModel>) {
        this.listContent = list;
    }

    get isCreating(): boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }

    get addingNewBtn() : boolean {
        return this.buttonCreating;
    }

    set addingNewBtn(status: boolean) {
        this.buttonCreating = status;
    }

    get button() : null | buttonContent{
        return this.listButton;
    }

    set button(button: null | buttonContent) {
        this.listButton = button;
    }

    get btnEdit() : boolean {
        return this.buttonEdit;
    }

    set btnEdit(status: boolean) {
        this.buttonEdit = status;
    }

    async addButton() {
        this.addingNewBtn = true;

        this.buttonToken.cancel();
        this.buttonToken = Axios.CancelToken.source();

        await Axios({
            url: `${this.rootUrl}/button/list`,
            method: 'post',
            cancelToken: this.buttonToken.token
        }).then((res) => {
            this.listButton = res.data.button;
        }).catch((err) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new button!');
                alert(mesg);
            }
        });

        this.addingNewBtn = false;
    }

    async delButton() {
        if(this.button!==null) {
            await Axios({
                url: `${this.rootUrl}/button/${this.button.id}`,
                method: 'delete',
            }).then((res) => {
                this.button = null;
            }).catch((err) => {
                if(err.response) {
                    let mesg = this.globalHandler(err, 'Failed to delete a button!');
                    alert(mesg);
                }
            });
        }
    }

    async createList() {
        this.isCreating = true;

        await Axios({
            url: `/api/v1/project/${this.project}/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}/list`,
            method: 'post'
        }).then((res: any) => {
            this.buildListItem(res.data.content);
        }).catch((err: any) => {
            let mesg = this.globalHandler(err, 'Failed to create new list!');
            alert(mesg);
        });

        this.isCreating = false;
    }

    async delItem(index: number) {
        await Axios({
            url: `${this.rootUrl}/list/${this.item[index].id}`,
            method: 'delete',
        }).then((res) => {
            this.item.splice(index, 1);
        }).catch((err) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to delete a list!');
                alert(mesg);
            }
        });
    }
}