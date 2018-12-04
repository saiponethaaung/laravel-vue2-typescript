import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { galleryContent } from "../../configuration/interface";
import Axios, { CancelTokenSource } from "axios";

export default class GalleryItemModel extends AjaxErrorHandler{

    private rootUrl: string = '';
    private content: galleryContent;
    private updating: boolean = false;
    private uploading: boolean = false;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    private imageToken: CancelTokenSource = Axios.CancelToken.source();


    constructor(content: galleryContent, rootUrl: string) {
        super();
        this.rootUrl = rootUrl;
        this.content = content;
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

    get image() : string {
        return this.content.image;
    }

    set image(image: string) {
        this.content.image = image;
    }

    get isUpdating() : boolean {
        return this.updating;
    }

    set isUpdating(status: boolean) {
        this.updating = status;
    }

    get isUploading() : boolean {
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
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to update list!');
                alert(mesg);
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
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to update list!');
                alert(mesg);
            }
        });

        this.isUploading = false;
    }
}