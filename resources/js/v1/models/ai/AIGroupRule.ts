import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { FilterGroupRule } from "../../configuration/interface";

export default class AIGroupRule extends AjaxErrorHandler {
    // private rule: FilterGroupRule

    constructor(
        private rule: any,
        private rootUrl: string
    ) {
        super();
    }

    get id(): number {
        return this.rule.id;
    }

}