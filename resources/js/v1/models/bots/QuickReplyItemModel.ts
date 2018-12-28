import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { quickReplyContent, blockSuggestion } from "../../configuration/interface";
import Axios, { CancelTokenSource } from "axios";

export default class QuickReplyItemModel extends AjaxErrorHandler {

    private show: boolean = false;
    private keyword: string = '';
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    private suggestion: Array<blockSuggestion> = [];
    private saveBlock: boolean = false;
    private blockToken: CancelTokenSource = Axios.CancelToken.source();
    private deleting: boolean = false;

    constructor(
        private content: quickReplyContent,
        private rootUrl: string,
        private projectId: string
    ) {
        super();
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

    get attribute() : string {
        return this.content.attribute.title;
    }

    set attribute(title: string){
        this.content.attribute.title = title;
    }

    get value() : string {
        return this.content.attribute.value;
    }

    set value(value: string) {
        this.content.attribute.value = value;
    }

    get blockKey() : string {
        return this.keyword;
    }

    set blockKey(keyword: string) {
        this.keyword = keyword;
    }

    get block() : any {
        return this.content.block;
    }

    get blockList() : Array<blockSuggestion> {
        return this.suggestion;
    }

    get isDeleting() : boolean {
        return this.deleting;
    }

    set isDeleting(status: boolean){
        this.deleting = status;
    }

    async saveContent() {
        this.saveToken.cancel();
        this.saveToken = Axios.CancelToken.source();

        let data = new FormData();
        data.append('title', this.title);
        data.append('attribute', this.attribute);
        data.append('value', this.value);
        data.append('_method', 'put');

        Axios({
            url: `${this.rootUrl}/${this.id}`,
            data: data,
            method: 'post',
            cancelToken: this.saveToken.token
        }).then((res: any) => {
            this.content.attribute.id = res.data.data.attribute;
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to update quick reply!');
                alert(mesg);
            }
        });
    }

    async loadSuggestion() {
        let suggestion = await this.searchSections(this.blockKey, this.projectId);

        if(suggestion.type==='cancel') return;

        if(suggestion.status===false) {
            alert(suggestion.mesg);
            return;
        }

        this.suggestion = suggestion.data;
    }

    async addBlock(block: number, section: number) {
        this.blockToken.cancel();
        this.blockToken = Axios.CancelToken.source();

        this.saveBlock = true;

        let data = new FormData();
        data.append('section', this.suggestion[block].contents[section].id.toString());

        Axios({
            url: `${this.rootUrl}/${this.id}/block`,
            data: data,
            method: 'post',
            cancelToken: this.blockToken.token
        }).then((res: any) => {
            this.content.block.push({
                id: this.suggestion[block].contents[section].id,
                title: this.suggestion[block].contents[section].title
            });
    
            this.suggestion = [];
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to connect a block!');
                alert(mesg);
            }
        });

        this.saveBlock = false;
    }

    async delButton(index: number) {
        this.isDeleting = true;
        await Axios({
            url: `${this.rootUrl}/${this.id}/block`,
            method: 'delete',
        }).then((res) => {
            this.content.block = [];
        }).catch((err) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to delete a block!');
                alert(mesg);
                this.isDeleting = true;
            }
        });
    }
}