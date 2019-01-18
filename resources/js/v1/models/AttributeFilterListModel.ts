import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import AttributeFilterModel from "./AttributeFilterModel";
import { attributeFilter } from "../configuration/interface";

export default class AttributeFilterListModel extends AjaxErrorHandler {

    private attributesList: Array<AttributeFilterModel> = [];

    constructor(
        private existing: boolean,
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
            option: 0,
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
}