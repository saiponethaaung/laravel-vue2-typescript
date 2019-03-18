import Axios, { CancelTokenSource } from "axios";
import { segment } from "../configuration/interface";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import AttributeFilterModel from "./AttributeFilterModel";

export default class SegmentModel extends AjaxErrorHandler {
    private countLoaded: boolean = false;
    private attributeLoaded: boolean = false;
    private attributeLoading: boolean = false;
    private attributeCreating: boolean = false;
    private segmentUpdating: boolean = false;
    private attributeFilters: Array<AttributeFilterModel> = [];
    private loadAttributeToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(
        private segment: segment,
        private projectId: string
    ) {
        super();
    }

    get id(): number {
        return this.segment.id;
    }

    get name(): string {
        return this.segment.name;
    }

    set name(name: string) {
        this.segment.name = name;
    }

    get isAttrLoaded(): boolean {
        return this.attributeLoaded;
    }

    set isAttrLoaded(status: boolean) {
        this.attributeLoaded = status;
    }

    get isAttrLoading(): boolean {
        return this.attributeLoading;
    }

    set isAttrLoading(status: boolean) {
        this.attributeLoading = status;
    }

    get isAttrCreating(): boolean {
        return this.attributeCreating;
    }

    set isAttrCreating(status: boolean) {
        this.attributeCreating = status;
    }

    get updating(): boolean {
        return this.segmentUpdating;
    }

    set updating(status: boolean) {
        this.segmentUpdating = status;
    }

    get attributes(): Array<AttributeFilterModel> {
        return this.attributeFilters;
    }

    set attributes(attributeFilters: Array<AttributeFilterModel>) {
        this.attributeFilters = attributeFilters;
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
            url: `/api/v1/project/${this.projectId}/users/segments/${this.id}/filters`,
            method: 'get',
            cancelToken: this.loadAttributeToken.token
        }).then(res => {
            for (let i of res.data.data) {
                this.attributes.push(new AttributeFilterModel(i));
            }
            this.isAttrLoaded = true;
        }).catch(err => {
            if (err.response) {
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
            url: `/api/v1/project/${this.projectId}/users/segments/${this.id}/filters`,
            method: 'post'
        }).then(res => {
            this.attributes.push(new AttributeFilterModel({
                id: res.data.data.id,
                condi: 1,
                name: '',
                option: 1,
                systemAttribute: 1,
                systemAttributeValue: 1,
                type: 1,
                userAttribute: 1,
                userAttributeValue: 1,
                value: ''
            }));
        }).catch(err => {
            if (err.response) {
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
            url: `/api/v1/project/${this.projectId}/users/segments/${this.id}/filters/${this.attributes[index].id}`,
            method: 'delete'
        }).then(res => {
            this.attributes.splice(index, 1);
        }).catch(err => {
            if (err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to delete filter atttribute!');
            }
        });

        return res;
    }

    async updateSegment() {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.updating = true;

        let data = new FormData();
        data.append('name', this.name);
        data.append('_method', 'put');

        for (let i in this.attributes) {
            // if(this.attributes[i].type===2 && (this.attributes[i].name=='' || this.attributes[i].value=='')) continue;
            data.append(`filters[${i}][id]`, this.attributes[i].id.toString());
            data.append(`filters[${i}][option]`, this.attributes[i].option.toString());
            data.append(`filters[${i}][type]`, this.attributes[i].type.toString());
            data.append(`filters[${i}][name]`, this.attributes[i].name);
            data.append(`filters[${i}][value]`, this.attributes[i].value);
            data.append(`filters[${i}][condi]`, this.attributes[i].condi.toString());
            data.append(`filters[${i}][systemAttribute]`, this.attributes[i].system.toString());
            data.append(`filters[${i}][systemAttributeValue]`, this.attributes[i].systemValue.toString());
            data.append(`filters[${i}][userAttribute]`, this.attributes[i].user.toString());
            data.append(`filters[${i}][userAttributeValue]`, this.attributes[i].userValue.toString());
        }

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/segments/${this.id}`,
            data: data,
            method: 'post'
        }).then(res => {

        }).catch(err => {
            if (err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to update segment filter!');
            }
        });

        this.updating = false;

        return res;
    }

    async loadCount() {

    }
}