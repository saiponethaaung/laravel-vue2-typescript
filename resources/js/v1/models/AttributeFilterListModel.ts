import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import AttributeFilterModel from "./AttributeFilterModel";
import { attributeFilter } from "../configuration/interface";
import Axios from "axios";

export default class AttributeFilterListModel extends AjaxErrorHandler {

    private segmentName: string = '';
    private creating: boolean = false;
    private attributesList: Array<AttributeFilterModel> = [];

    constructor(
        private existing: boolean,
        private projectId: string,
        attributeFilters: Array<attributeFilter>
    ) {
        super();
        for(let attributeFilter of attributeFilters) {
            this.buildAttributesFilter(attributeFilter);
        }
    }

    private buildAttributesFilter(attributeFilter: attributeFilter) {
        this.attributesList.push(new AttributeFilterModel(attributeFilter))
    }

    createNewAttributeFilter() {
        this.buildAttributesFilter({
            id: -1,
            name: '',
            option: 2,
            type: 0,
            value: '',
            condi: 1,
            systemAttribute: 1,
            systemAttributeValue: 1,
            userAttribute: 1,
            userAttributeValue: 1
        });
    }

    get attributes() : Array<AttributeFilterModel> {
        return this.attributesList;
    }

    set attributes(attributesList: Array<AttributeFilterModel>) {
        this.attributesList = attributesList;
    }

    get name() : string{
        return this.segmentName;
    }

    set name(name: string) {
        this.segmentName = name;
    }

    async createSegment() {
        let res = {
            status: true,
            mesg: 'Success'
        };

        let data = new FormData();
        data.append('name', this.segmentName);

        for(let i in this.attributes) {
            if(this.attributes[i].type===2 && (this.attributes[i].name=='' || this.attributes[i].value=='')) continue;
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
        
        this.creating = true

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/segments`,
            method: 'post',
            data: data
        }).then(res => {
            this.segmentName = '';
            this.attributes = [];
            this.createNewAttributeFilter();
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create a segment!');
            }
        });

        return res;
    }
}