import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import Axios, { CancelTokenSource } from "axios";
import { FilterGroup } from "../../configuration/interface";

export default class AIGroupModel extends AjaxErrorHandler {

    constructor(
        private group: FilterGroup
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

}