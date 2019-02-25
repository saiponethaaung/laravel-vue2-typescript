import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { FilterResponse } from "../../configuration/interface";
import Axios from "axios";


export default class AIGroupRuleResponseModel extends AjaxErrorHandler {
    private update: boolean = false;

    constructor(
        private response: FilterResponse,
        private rootUrl: string
    ){
        super();
    }

    get id() : number {
        return this.response.id;
    }

    get content(): string {
        return this.response.content;
    }
    
    set content(content: string) {
        this.response.content = content;
    }

    get type(): number {
        return this.response.type;
    }
    
    get segmentId(): number {
        return this.response.segmentId;
    }
    
    set segmentId(segmentId: number) {
        this.response.segmentId = segmentId;
    }

    get segmentName(): string {
        return this.response.segmentName;
    }
    
    set segmentName(segmentName: string) {
        this.response.segmentName = segmentName;
    }

    get updating(): boolean {
        return this.update;
    }
    
    set updating(updating: boolean) {
        this.update = updating;
    }

    async updateContent() {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.updating = true;

        let data = new FormData();
        data.append('content', this.content);
        data.append('segment', this.segmentId.toString());
        data.append('_method', 'put');
        
        await Axios({
            url: `${this.rootUrl}/${this.id}`,
            data: data,
            method: 'post'
        }).then(res => {
            
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to update response content!');
            }    
        });

        this.updating = false;

        return res;
    }
}