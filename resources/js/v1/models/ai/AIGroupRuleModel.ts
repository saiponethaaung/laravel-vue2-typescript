import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { FilterGroupRule, Filter } from "../../configuration/interface";
import Axios from "axios";
import AIGroupRuleResponseModel from "./AIGroupRuleResponseModel";

export default class AIGroupRuleModel extends AjaxErrorHandler {
    private valueUpdating: boolean = false;
    private createCount: number = 0;
    private responseModel: Array<AIGroupRuleResponseModel> = [];

    constructor(
        private rule: FilterGroupRule,
        private rootUrl: string
    ) {
        super();
        let response = this.rule.response;
        this.rule.response = [];
        for(let i of response) {
            this.response.push(new AIGroupRuleResponseModel(i, this.rootUrl+"/"+this.id+"/response"));
        }
    }

    get id(): number {
        return this.rule.id;
    }

    get filters(): Array<Filter> {
        return this.rule.filters;
    }

    set filters(filters: Array<Filter>) {
        this.rule.filters = filters;
    }

    get response(): Array<AIGroupRuleResponseModel> {
        return this.responseModel;
    }

    set response(response: Array<AIGroupRuleResponseModel>) {
        this.responseModel = response;
    }

    get responseCreating() : number {
        return this.createCount;
    }

    set responseCreating(count: number) {
        this.createCount = count;
    }

    async updateFilterValue() {
        let res = {
            status: true,
            mesg: 'success'
        };
        
        this.valueUpdating = true;

        let data = new FormData();
        for(let index in this.filters) {
            data.append(`keywords[${index}]`, this.filters[index].keyword);
        }
        
        await Axios({
            url: `${this.rootUrl}/${this.id}/keywords`,
            method: 'post',
            data: data
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to update filter keywords!');
            }
        })

        this.valueUpdating = false;

        return res;
    }

    async createResponse(type="text") {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.responseCreating++;
        let data = new FormData();
        data.append('type', type)
        
        await Axios({
            url: `${this.rootUrl}/${this.id}/response`,
            method: 'post',
            data: data
        }).then(res => {
            this.response.push(new AIGroupRuleResponseModel(res.data.data, this.rootUrl+"/"+this.id+"/response"));
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create response!');
            }
        });

        this.responseCreating--;

        return res;
    }

    async deleteResponse(index: any) {
        let res = {
            status: true,
            mesg: 'success'
        };

        await Axios({
            url: `${this.rootUrl}/${this.id}/response/${this.response[index].id}`,
            method: 'delete',
        }).then(res => {
            this.response.splice(index, 1);
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to delete response!');
            }
        });

        return res;
    }
}