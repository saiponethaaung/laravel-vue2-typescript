import Axios, { CancelTokenSource } from "axios";
import { galleryContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";
import GalleryItemModel from "./GalleryItemModel";

export default class GalleryContentModel extends ChatBlockContentModel {

    private rootUrl: string;
    private galleryContent: Array<GalleryItemModel> = [];
    private creating: boolean = false;
    private orderToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}`;
        for (let i of content.content) {
            this.buildGalleryItem(i);
        }
    }

    private buildGalleryItem(content: galleryContent) {
        this.galleryContent.push(new GalleryItemModel(content, `${this.rootUrl}/gallery`));
    }

    get url(): string {
        return this.rootUrl;
    }

    get item(): Array<GalleryItemModel> {
        return this.galleryContent;
    }

    set item(gallery: Array<GalleryItemModel>) {
        this.galleryContent = gallery;
    }

    get isCreating(): boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }

    async createGallery() {
        this.isCreating = true;

        await Axios({
            url: `${this.rootUrl}/gallery`,
            method: 'post'
        }).then((res: any) => {
            this.buildGalleryItem(res.data.content);
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to create new list!');
            }
        });

        this.isCreating = false;
    }

    async delItem(index: number) {
        await Axios({
            url: `${this.rootUrl}/gallery/${this.item[index].id}`,
            method: 'delete',
        }).then((res) => {
            this.item.splice(index, 1);
        }).catch((err) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete a card!');
            }
        });
    }

    async updateOrder() {
        this.orderToken.cancel();
        this.orderToken = Axios.CancelToken.source();

        let data = new FormData();

        for (let i in this.item) {
            data.append(`order[${i}]`, this.item[i].id.toString());
        }

        await Axios({
            url: `${this.rootUrl}/gallery/order`,
            data: data,
            method: 'post',
            cancelToken: this.orderToken.token
        }).catch(err => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to update gallery order!');
            }
        });
    }
}