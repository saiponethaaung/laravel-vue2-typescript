import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";
import { FilterGroup } from "../../configuration/interface";
import AIGroupRule from "./AIGroupRule";

export default class AIGroupModel extends AjaxErrorHandler {

    private isEdit: boolean = false;
    private showOption: boolean = false;
    private cancelUpdateToken: CancelTokenSource = Axios.CancelToken.source();
    private ruleLoading: boolean = false;
    private ruleLoaded: boolean = false;
    private loadFailed: boolean = false;
    private isCreating: boolean = false;
    private ruleList: Array<AIGroupRule> = [];

    constructor(
        private group: FilterGroup,
        private rootUrl: string
    ){
        super();
    }

    get id(): number {
        return this.group.id;
    }

    get name(): string {
        return this.group.name;
    }

    set name(name: string) {
        this.group.name = name;
    }

    get edit(): boolean {
        return this.isEdit;
    }

    set edit(status: boolean) {
        this.isEdit = status;
    }

    get option(): boolean {
        return this.showOption;
    }

    set option(status: boolean) {
        this.showOption = status;
    }

    get rules() : Array<AIGroupRule> {
        return this.ruleList;
    }

    set rules(rules: Array<AIGroupRule>) {
        this.ruleList = rules;
    }

    get creating(): boolean {
        return this.isCreating;
    }

    set creating(status: boolean) {
        this.isCreating = status;
    }

    get loading(): boolean {
        return this.ruleLoading;
    }

    set loading(status: boolean) {
        this.ruleLoading = status;
    }

    get loaded(): boolean {
        return this.ruleLoaded;
    }

    async updateGroupName() {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.cancelUpdateToken.cancel();
        this.cancelUpdateToken = Axios.CancelToken.source();

        let data = new FormData();
        data.append('name', this.group.name);
        data.append('_method', 'put');

        await Axios({
            url: `${this.rootUrl}/${this.id}`,
            data: data,
            method: 'post',
            cancelToken: this.cancelUpdateToken.token
        }).then(res => {
            this.edit = false;
        }).catch(err => {
            if (err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to update group name!');
            }
        });

        return res;
    }

    async loadRule() {
        this.ruleLoading = true;
        
        await Axios({
            url: `${this.rootUrl}/${this.id}/rules`,
            method: 'get'
        }).then(res => {
            console.log('rule list', res.data);
            this.rules = [];
            for(let i of res.data.data) {
                this.rules.push(new AIGroupRule(i, `${this.rootUrl}/${this.id}/rules`));
            }
            this.ruleLoaded = true;
        }).catch(err => {
            if(err.response) {
                this.loadFailed = true;
            }
        });

        this.ruleLoading = false;
    }

    async createRule() {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.isCreating = true;

        await Axios({
            url: `${this.rootUrl}/${this.id}/rules`,
            method: 'post'
        }).then(res => {
            this.rules.push(new AIGroupRule(res.data.data, `${this.rootUrl}/${this.id}/rules`));
        }).catch(err => {
            if (err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create new rule!');
            }
        });

        this.isCreating = false;

        return res;
    }
}