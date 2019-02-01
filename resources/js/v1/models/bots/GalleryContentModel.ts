import ChatBlockContentModel from "../ChatBlockContentModel";
import { galleryContent } from "../../configuration/interface";
import GalleryItemModel from "./GalleryItemModel";
import Axios, { CancelTokenSource } from "axios";

export default class GalleryContentModel extends ChatBlockContentModel {
    
    private rootUrl: string;
    private galleryContent: Array<GalleryItemModel> = [];
    private creating: boolean = false;
    private orderToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}`;
        for(let i of content.content) {
            this.buildGalleryItem(i);
        }
    }

    private buildGalleryItem(content: galleryContent) {
        this.galleryContent.push(new GalleryItemModel(content, `${this.rootUrl}/gallery`));
    }

    get url() : string {
        return this.rootUrl;
    }

    get item() : Array<GalleryItemModel> {
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
            let mesg = this.globalHandler(err, 'Failed to create new list!');
            alert(mesg);
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
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to delete a card!');
                alert(mesg);
            }
        });
    }
}