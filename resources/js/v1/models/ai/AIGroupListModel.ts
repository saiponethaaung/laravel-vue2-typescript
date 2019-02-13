import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";
import AIGroupModel from "./AIGroupModel";

export default class AIGroupListModel extends AjaxErrorHandler {

    private groupModelList: Array<AIGroupModel> = [];
    private loadCancelToken: CancelTokenSource = Axios.CancelToken.source();
    private isLoading: boolean = false;
    private isCreating: boolean = false;

    constructor(
        private projectId: string,
    ) {
        super();
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

    async loadContent() {
        this.cancelLoadContent();
        this.loading = true;

        await Axios({
            url: `/api/v1/project/${this.projectId}/ai-setup`,
            method: 'get',
            cancelToken: this.loadCancelToken.token
        }).then(res => {
            for(let i of res.data.data) {
                this.groupModelList.push(new AIGroupModel(i));
            }
            this.loading = false;
        })
    }

    cancelLoadContent() {
        this.loadCancelToken.cancel();
        this.loadCancelToken = Axios.CancelToken.source();
    }
}