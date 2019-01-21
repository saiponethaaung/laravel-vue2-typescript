import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";
import SegmentModel from "./SegmentModel";

export default class SegmentListModel extends AjaxErrorHandler {
    
    private isLoading: boolean = false;
    private loadToken: CancelTokenSource = Axios.CancelToken.source();
    private segmentModels: Array<SegmentModel> = [];
    private projectId: string = "";

    constructor() {
        super();
    }

    set setProjectId(projectId: string) {
        this.projectId = projectId;
    }

    get loading() : boolean{
        return this.isLoading;
    }

    set loading(status: boolean) {
        this.isLoading = status;
    }

    get segments() : Array<SegmentModel> {
        return this.segmentModels;
    }

    set segments(segmentModel: Array<SegmentModel>) {
        this.segmentModels = segmentModel;
    }

    async loadSegment() {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.loading = true;

        this.loadToken.cancel();
        this.loadToken = Axios.CancelToken.source();

        this.segments = [];

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/segments`,
            method: 'get',
            cancelToken: this.loadToken.token
        }).then(res => {
            for(let i of res.data.data) {
                this.segments.push(new SegmentModel(i, this.projectId));
            }
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to load segment list!');
            }
        });

        this.loading = false;

        return res;
    }

    cancelLoading() {
        this.loadToken.cancel();
    }
}