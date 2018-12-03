import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { listContent } from "../../configuration/interface";
import ListContentModel from "./ListContentModel";
import Axios, { CancelTokenSource } from "axios";

export default class ListItemModel extends AjaxErrorHandler{

    private content: listContent;
    private rootUrl: String = '';
    private updating: boolean = false;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(content: listContent, rootUrl: string) {
        super();
        this.content = content;
        this.rootUrl = rootUrl;
    }

    get id() : number {
        return this.content.id;
    }

    get title() : string {
        return this.content.title;
    }

    set title(title: string) {
        this.content.title = title;
    }

    get sub() : string {
        return this.content.sub;
    }

    set sub(sub: string) {
        this.content.sub = sub;
    }

    get url() : string {
        return this.content.url;
    }

    set url(url: string) {
        this.content.url = url;
    }

    get isUpdating() : boolean {
        return this.updating;
    }

    set isUpdating(status: boolean) {
        this.updating = status;
    }

    async saveContent() {
        this.saveToken.cancel();
        this.saveToken = Axios.CancelToken.source();

        this.isUpdating = true;

        let data = new FormData();
        data.append('title', this.title);
        data.append('sub', this.sub);
        data.append('url', this.url);
        data.append('_method', 'put');

        await Axios({
            url: `${this.rootUrl}/${this.id}`,
            data: data,
            method: 'post'
        }).catch((err: any) => {
            let mesg = this.globalHandler(err, 'Failed to update list!');
            alert(mesg);
        });

        this.isUpdating = false;
    }
}