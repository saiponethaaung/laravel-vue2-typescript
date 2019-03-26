import Axios, { CancelTokenSource } from "axios";
import { buttonContent, textContent } from "../../configuration/interface";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import ChatBlockContentModel from "../ChatBlockContentModel";

export default class TextContentModel extends ChatBlockContentModel {

    private rootUrl: string;
    private textContent: textContent;
    private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    private buttonToken: CancelTokenSource = Axios.CancelToken.source();
    private buttonCreating: boolean = false;
    private buttonEditIndex: number = -1;
    public warningText = 'Chat process on messenger will stop here due to incomplete text component!';

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}`;
        this.textContent = {
            content: content.content.text,
            button: content.content.button
        };
    }

    get url(): string {
        return this.rootUrl;
    }

    get value(): string {
        return this.textContent.content;
    }

    set value(content: string) {
        this.textContent.content = content;
    }

    get type(): number {
        return 1;
    }

    get buttons(): Array<buttonContent> {
        return this.textContent.button;
    }

    set buttons(buttons: Array<buttonContent>) {
        this.textContent.button = buttons;
    }

    get showBtn(): boolean {
        return this.textContent.button.length < 3;
    }

    get addingNewBtn(): boolean {
        return this.buttonCreating;
    }

    set addingNewBtn(status: boolean) {
        this.buttonCreating = status;
    }

    get btnEditIndex(): number {
        return this.buttonEditIndex;
    }

    set btnEditIndex(index: number) {
        this.buttonEditIndex = index;
    }

    get showWarning() {
        return this.textContent.content==='';
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
            this.btnEditIndex = this.buttons.length - 1;
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.ajaxHandler.globalHandler(err, 'Failed to create new button!');
            }
        });

        this.addingNewBtn = false;
    }

    async delButton(index: number) {
        await Axios({
            url: `${this.rootUrl}/button/${this.buttons[index].id}`,
            method: 'delete',
        }).then((res) => {
            this.buttons.splice(index, 1);
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete a button!');
            }
        });
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
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to save text content!');
            }
        });

        this.isUpdating = false;
    }
}