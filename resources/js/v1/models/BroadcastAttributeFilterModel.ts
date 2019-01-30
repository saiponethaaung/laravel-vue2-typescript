import { attributeFilter } from "../configuration/interface";
import AttributeFilterModel from "./AttributeFilterModel";

export default class BroadcastAttributeFilterModel extends AttributeFilterModel {
    constructor(
        attributeData: attributeFilter,
        public segment: any
    ) {
        super(attributeData);
    }

}