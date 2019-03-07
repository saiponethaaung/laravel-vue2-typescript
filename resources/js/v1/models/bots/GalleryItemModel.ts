import Axios, { CancelTokenSource } from "axios";
import { buttonContent, galleryContent } from "../../configuration/interface";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

export default class GalleryItemModel extends AjaxErrorHandler {

    private rootUrl: string = '';
    private content: galleryContent;
    private updating: boolean = false;
    private uploading: boolean = false;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    private imageToken: CancelTokenSource = Axios.CancelToken.source();
    private buttonCreating: boolean = false;
    private buttonEdit: number = -1;
    private buttonToken: CancelTokenSource = Axios.CancelToken.source();
    public errorMesg: string = '';


    constructor(content: galleryContent, rootUrl: string) {
        super();
        this.rootUrl = rootUrl;
        this.content = content;
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

    get image(): string {
        return this.content.image;
    }

    set image(image: string) {
        this.content.image = image;
    }

    get buttons(): Array<buttonContent> {
        return this.content.button;
    }

    set buttons(buttons: Array<buttonContent>) {
        this.content.button = buttons;
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

    get addingNewBtn(): boolean {
        return this.buttonCreating;
    }

    set addingNewBtn(status: boolean) {
        this.buttonCreating = status;
    }

    get btnEdit(): number {
        return this.buttonEdit;
    }

    set btnEdit(index: number) {
        this.buttonEdit = index;
    }

    async addButton() {
        this.addingNewBtn = true;

        this.buttonToken.cancel();
        this.buttonToken = Axios.CancelToken.source();

        await Axios({
            url: `${this.rootUrl.replace('/gallery', '')}/button/gallery/${this.id}`,
            method: 'post',
            cancelToken: this.buttonToken.token
        }).then((res) => {
            this.content.button.push(res.data.button);
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to create new button!');
            }
        });

        this.addingNewBtn = false;
    }

    async delButton(index: number) {
        await Axios({
            url: `${this.rootUrl.replace('/gallery', '')}/button/${this.buttons[index].id}`,
            method: 'delete',
        }).then((res) => {
            this.buttons.splice(index, 1);
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete a button!');
            }
        });
    }

    async delImage(index: number) {
        await Axios({
            url: `${this.rootUrl}/${this.id}/image`,
            method: 'delete',
        }).then((res) => {
            this.content.image = '';
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete an image!');
            }
        });
    }

    get textLimitTitle() {
        return 80 - this.title.length;
    }

    get textLimitSub() {
        return 80 - this.sub.length;
    }
}