import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";
import AIGroupModel from "./AIGroupModel";

export default class AIGroupListModel extends AjaxErrorHandler {

    private groupModelList: Array<AIGroupModel> = [];
    private loadCancelToken: CancelTokenSource = Axios.CancelToken.source();
    private isLoading: boolean = false;
    private isCreating: boolean = false;
    private rootUrl: string = '';
    private activeSection: number = 0;

    constructor(
        private projectId: string,
    ) {
        super();
        this.rootUrl = `/api/v1/project/${this.projectId}/ai-setup`;
    }

    get loading(): boolean {
        return this.isLoading;
    }

    set loading(loading: boolean) {
        this.isLoading = loading;
    }

    get creating(): boolean {
        return this.isCreating;
    }

    set creating(creating: boolean) {
        this.isCreating = creating;
    }

    get groups() : Array<AIGroupModel> {
        return this.groupModelList;
    }

    set groups(groups: Array<AIGroupModel>) {
        this.groupModelList = groups;
    }

    get active(): number {
        return this.activeSection;
    }

    set active(index: number) {
        this.activeSection = index;
    }

    async loadContent() {
        this.cancelLoadContent();
        this.loading = true;

        await Axios({
            url: this.rootUrl,
            method: 'get',
            cancelToken: this.loadCancelToken.token
        }).then(async res => {
            for(let i of res.data.data) {
                this.groups.push(new AIGroupModel(i, this.rootUrl));
            }
            if(this.groups.length>0) {
                await this.groups[0].loadRule();
            }
            this.loading = false;
        });
    }

    async deleteContent(index: number) {
        let res = {
            status: true,
            mesg: 'success'
        };

        await Axios({
            url: `${this.rootUrl}/${this.groups[index].id}`,
            method: 'delete'
        }).then(res => {
            this.groups.splice(index, 1);
        }).catch(err => {
            if (err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to delete group!');
            }
        });

        return res;
    }

    cancelLoadContent() {
        this.loadCancelToken.cancel();
        this.loadCancelToken = Axios.CancelToken.source();
    }

    async createContent() {
        let res = {
            status: true,
            mesg: 'Success'
        };

        this.creating = true;

        await Axios({
            url: this.rootUrl,
            method: 'post'
        }).then(res => {
            this.groupModelList.push(new AIGroupModel(res.data.data, this.rootUrl));
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create new group!');
            }
        });

        this.creating = false;

        return res;
    }
}