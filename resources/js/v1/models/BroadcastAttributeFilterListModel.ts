import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";
import BroadcastAttributeFilterModel from "./BroadcastAttributeFilterModel";

export default class SegmentModel extends AjaxErrorHandler {
    private attributeLoaded: boolean = false;
    private attributeLoading: boolean = false;
    private segmentLoading: boolean = false;
    private attributeCreating: boolean = false;
    private segmentUpdating: boolean = false;
    private attributeFilters: Array<BroadcastAttributeFilterModel> = [];
    private loadAttributeToken: CancelTokenSource = Axios.CancelToken.source();
    private broadcastId: number = -1;
    private segmentList: any = [];

    constructor(
        private projectId: string,
    ) {
        super();
        this.loadSegments();
    }

    get id() : number{
        return this.broadcastId;
    }

    set id(broadcastId: number) {
        this.broadcastId = broadcastId;
    }

    get isAttrLoaded() : boolean {
        return this.attributeLoaded;
    }

    set isAttrLoaded(status: boolean) {
        this.attributeLoaded = status;
    }

    get isAttrLoading() : boolean {
        return this.attributeLoading;
    }

    set isAttrLoading(status: boolean) {
        this.attributeLoading = status;
    }

    get isAttrCreating() : boolean {
        return this.attributeCreating;
    }

    set isAttrCreating(status: boolean) {
        this.attributeCreating = status;
    }
    
    get isSegmentLoading() : boolean {
        return this.segmentLoading;
    }

    set isSegmentLoading(status: boolean) {
        this.segmentLoading = status;
    }

    get updating() : boolean {
        return this.segmentUpdating;
    }

    set updating(status: boolean) {
        this.segmentUpdating = status;
    }

    get attributes() : Array<BroadcastAttributeFilterModel> {
        return this.attributeFilters;
    }

    set attributes(attributeFilters : Array<BroadcastAttributeFilterModel>) {
        this.attributeFilters = attributeFilters;
    }

    get segments() : Array<any> {
        return this.segmentList;
    }

    async loadAttributes() {
        let res = {
            status: true,
            mesg: 'success'
        };
        
        this.isAttrLoading = true;
        
        this.loadAttributeToken.cancel();
        this.loadAttributeToken = Axios.CancelToken.source();
        
        await Axios({
            url: `/api/v1/project/${this.projectId}/broadcast/${this.id}/filters`,
            method: 'get',
            cancelToken: this.loadAttributeToken.token
        }).then(res => {
            for(let i of res.data.data) {
                this.attributes.push(new BroadcastAttributeFilterModel(i.filters, i.segment));
            }
            this.isAttrLoaded = true;
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to load segments!');
            }
        });
        
        this.isAttrLoading = false;
        return res;
    }

    async createNewAttribute() {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.isAttrCreating = true;
        
        await Axios({
            url: `/api/v1/project/${this.projectId}/broadcast/${this.id}/filters`,
            method: 'post'   
        }).then(res => {
            this.attributes.push(new BroadcastAttributeFilterModel(res.data.data.filters, res.data.data.segment));
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create new attribute filter!');
            }
        });

        this.isAttrCreating = false;

        return res;
    }

    async deleteFilter(index: number) {
        let res = {
            status: true,
            mesg: 'success'
        }; 

        await Axios({
            url: `/api/v1/project/${this.projectId}/broadcast/${this.id}/filters/${this.attributes[index].id}`,
            method: 'delete'
        }).then(res => {
            this.attributes.splice(index, 1);
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to delete filter atttribute!');
            }
        });

        return res;
    }

    async loadSegments() {
        this.isSegmentLoading = true;
        await Axios({
            url: `/api/v1/project/${this.projectId}/users/segments`,
            method: 'get'
        }).then(res => {
            for(let i of res.data.data) {
                this.segmentList.push({
                    key: i.id,
                    value: i.name
                });
            }
        });
        this.isSegmentLoading = false;
    }

    async loadCount() {

    }
}