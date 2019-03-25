import Axios, { CancelTokenSource } from "axios";
import { imageContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";

export default class ImageContentModel extends ChatBlockContentModel {

    private rootUrl: string;
    private imageContent: imageContent = {
        image: ''
    };
    private uploading: boolean = false;
    private imageToken: CancelTokenSource = Axios.CancelToken.source();
    public deletingImage: boolean = false;

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.imageContent.image = content.content.image;
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}/image`;
    }

    get image(): string {
        return this.imageContent.image;
    }

    set image(image: string) {
        this.imageContent.image = image;
    }

    get isUploading(): boolean {
        return this.uploading;
    }

    set isUploading(status: boolean) {
        this.uploading = status;
    }
    
    get showWarning() {
        return true;
    }

    async imageUpload(e: any) {
        this.imageToken.cancel();
        this.imageToken = Axios.CancelToken.source();
        this.isUploading = true;

        let data = new FormData();
        data.append('image', e.target.files[0]);

        await Axios({
            url: `${this.rootUrl}`,
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

    async delImage() {
        this.deletingImage = true;
        await Axios({
            url: `${this.rootUrl}`,
            method: 'delete',
        }).then((res) => {
            this.image = '';
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete an image!');
            }
        });
        this.deletingImage = false;
    }
}