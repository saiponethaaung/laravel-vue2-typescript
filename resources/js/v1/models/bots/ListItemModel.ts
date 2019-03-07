import Axios, { CancelTokenSource } from "axios";
import { buttonContent, listContent } from "../../configuration/interface";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

export default class ListItemModel extends AjaxErrorHandler {

    private content: listContent;
    private rootUrl: String = '';
    private updating: boolean = false;
    private uploading: boolean = false;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    private imageToken: CancelTokenSource = Axios.CancelToken.source();
    private buttonCreating: boolean = false;
    private buttonEdit: boolean = false;
    private buttonToken: CancelTokenSource = Axios.CancelToken.source();
    public errorMesg: string = '';

    constructor(content: listContent, rootUrl: string) {
        super();
        this.content = content;
        this.rootUrl = rootUrl;
    }

    get id(): number {
        return this.content.id;
    }

    get title(): string {
        return this.content.title;
    }

    set title(title: string) {
        this.content.title = title;
    }

    get sub(): string {
        return this.content.sub;
    }

    set sub(sub: string) {
        this.content.sub = sub;
    }

    get url(): string {
        return this.content.url;
    }

    set url(url: string) {
        this.content.url = url;
    }

    get button(): null | buttonContent {
        return this.content.button;
    }

    set button(button: null | buttonContent) {
        this.content.button = button;
    }

    get image(): string {
        return this.content.image;
    }

    set image(image: string) {
        this.content.image = image;
    }

    get isUpdating(): boolean {
        return this.updating;
    }

    set isUpdating(status: boolean) {
        this.updating = status;
    }

    get isUploading(): boolean {
        return this.uploading;
    }

    set isUploading(status: boolean) {
        this.uploading = status;
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
            method: 'post',
            cancelToken: this.saveToken.token
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to update list!');
            }
        });

        this.isUpdating = false;
    }

    get addingNewBtn(): boolean {
        return this.buttonCreating;
    }

    set addingNewBtn(status: boolean) {
        this.buttonCreating = status;
    }

    get btnEdit(): boolean {
        return this.buttonEdit;
    }

    set btnEdit(status: boolean) {
        this.buttonEdit = status;
    }

    async imageUpload(e: any) {
        this.imageToken.cancel();
        this.imageToken = Axios.CancelToken.source();
        this.isUploading = true;

        let data = new FormData();
        data.append('image', e.target.files[0]);

        await Axios({
            url: `${this.rootUrl}/${this.id}/image`,
            data: data,
            method: 'post',
            cancelToken: this.imageToken.token
        }).then((res: any) => {
            this.image = res.data.image;
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to update list!');
            }
        });

        this.isUploading = false;
    }

    async delImage(e: any) {
        await Axios({
            url: `${this.rootUrl}/${this.id}/image`,
            method: 'delete',
        }).then((res: any) => {
            this.image = '';
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete an image!');
            }
        });
    }

    async delButton() {
        if (this.button !== null) {
            await Axios({
                url: `${this.rootUrl.replace('/list', '')}/button/${this.button.id}`,
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

    async addButton() {
        this.addingNewBtn = true;

        this.buttonToken.cancel();
        this.buttonToken = Axios.CancelToken.source();

        await Axios({
            url: `${this.rootUrl.replace('/list', '')}/button/list/${this.id}`,
            method: 'post',
            cancelToken: this.buttonToken.token
        }).then((res) => {
            this.button = res.data.button;
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to create new button!');
            }
        });

        this.addingNewBtn = false;
    }

    get textLimitTitle() {
        return 80 - this.title.length;
    }

    get textLimitSub() {
        return 80 - this.sub.length;
    }
}