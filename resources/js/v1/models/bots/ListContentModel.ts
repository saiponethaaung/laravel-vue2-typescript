import Axios, { CancelTokenSource } from "axios";
import { buttonContent, listContent } from "../../configuration/interface";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import ChatBlockContentModel from "../ChatBlockContentModel";
import ListItemModel from "./ListItemModel";

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
    private delChild: number = -1;
    public warningText = '';
    public status = false;

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}`;
        for (let i in content.content.content) {
            this.buildListItem(content.content.content[i]);
        }
        this.listButton = content.content.button;
    }

    private buildListItem(content: listContent) {
        this.listContent.push(new ListItemModel(content, `${this.rootUrl}/list`));
    }

    get url(): string {
        return this.rootUrl;
    }

    get item(): Array<ListItemModel> {
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

    get addingNewBtn(): boolean {
        return this.buttonCreating;
    }

    set addingNewBtn(status: boolean) {
        this.buttonCreating = status;
    }

    get button(): null | buttonContent {
        return this.listButton;
    }

    set button(button: null | buttonContent) {
        this.listButton = button;
    }

    get btnEdit(): boolean {
        return this.buttonEdit;
    }

    set btnEdit(status: boolean) {
        this.buttonEdit = status;
    }

    get isChildDeleting(): number {
        return this.delChild;
    }

    set isChildDeleting(index: number) {
        this.delChild = index;
    }
    
    get showWarning() {
        console.log('initial');
        this.warningText = 'Chat process on messenger will stop here due to incomplete list component!';

        if(this.item.length==0) {
            return true;
        }

        for(let i in this.item) {
            if(!this.item[i].isValid) {
                // let position: any = parseInt(i)+1;
                // switch(parseInt(i)) {
                //     case 0:
                //         position = position+'st';
                //         break;
                        
                //     case 1:
                //         position = position+'nd';
                //         break;
                        
                //     case 2:
                //         position = position+'rd';
                //         break;
                        
                //     default:
                //         position = position+'th';
                //         break;
                // }

                // this.warningText = `Chat process on messenger will stop here because ${position} list is incomplete!`;
                return true;
            }
                
        }

        console.log('closing');

        return this.status;
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
            this.btnEdit = true;
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.ajaxHandler.globalHandler(err, 'Failed to create new button!');
            }
        });

        this.addingNewBtn = false;
    }

    async delButton() {
        if (this.button !== null) {
            await Axios({
                url: `${this.rootUrl}/button/${this.button.id}`,
                method: 'delete',
            }).then((res) => {
                this.button = null;
            }).catch((err) => {
                if (err.response) {
                    this.errorMesg = this.globalHandler(err, 'Failed to delete a button!');
                }
            });
        }
    }

    async createList() {
        this.isCreating = true;

        await Axios({
            url: `${this.rootUrl}/list`,
            method: 'post'
        }).then((res: any) => {
            this.buildListItem(res.data.content);
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to create new list!');
            }
        });

        this.isCreating = false;
    }

    async delItem(index: number) {
        this.isChildDeleting = index;
        await Axios({
            url: `${this.rootUrl}/list/${this.item[index].id}`,
            method: 'delete',
        }).then((res) => {
            this.item.splice(index, 1);
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete a list!');
            }
        });
        this.isChildDeleting = -1;
    }
}