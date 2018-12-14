import Axios, { CancelToken, CancelTokenSource } from "axios";
import { textContent, BotContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

export default class TextContentModel extends ChatBlockContentModel {
    
    private rootUrl: string;
    private textContent: textContent;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    private buttonToken: CancelTokenSource = Axios.CancelToken.source();
    private buttonCreating: boolean = false;

    constructor(content: any) {
        super(content);
        this.rootUrl = `/api/v1/project/${this.project}/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}`;
        this.textContent = {
            content: content.content.text,
            button: content.content.button
        };
    }

    get value() : string {
        return this.textContent.content;
    }

    set value(content: string) {
        this.textContent.content = content;
    }

    get type() : number {
        return 1;
    }

    get showBtn() : boolean {
        return this.textContent.button.length<3;
    }

    get addingNewBtn() : boolean {
        return this.buttonCreating;
    }

    set addingNewBtn(status: boolean) {
        this.buttonCreating = status;
    }

    async addButton() {
        this.addingNewBtn = true;

        this.buttonToken.cancel();
        this.buttonToken = Axios.CancelToken.source();

        await Axios({
            url: `${this.rootUrl}/button/text`,
            method: 'post',
            cancelToken: this.buttonToken.token
        }).then((res) => {
            this.textContent.button.push(res.data.button);
        }).catch((err) => {
            if(err.response) {
                let mesg = this.ajaxHandler.globalHandler(err, 'Failed to create new button!');
                alert(mesg);
            }
        });

        this.addingNewBtn = false;
    }

    async saveContent() {
        this.saveToken.cancel();
        this.saveToken = Axios.CancelToken.source();
        
        this.isUpdating = true;

        let data = new FormData();
        data.append('content', this.value);
        data.append('type', this.type.toString());
        data.append('_method', 'put');

        await Axios({
            url: this.rootUrl,
            data: data,
            method: 'post',
            cancelToken: this.saveToken.token
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to save text content!');
                alert(mesg);
            }
        });

        this.isUpdating = false;
    }
}