import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { quickReplyContent } from "../../configuration/interface";

export default class QuickReplyItemModel extends AjaxErrorHandler {

    private content: quickReplyContent;
    private rootUrl: string;
    private show: boolean = false;

    constructor(content: quickReplyContent, rootUrl: string) {
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

    get canShow() : boolean {
        return this.show;
    }

    set canShow(status: boolean) {
        this.show = status;
    }
}