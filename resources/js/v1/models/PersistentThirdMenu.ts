import AjaxErrorHandler from "../utils/AjaxErrorHandler";

export default class PersistentThirdMenu extends AjaxErrorHandler {
    constructor(public content: any, public first: any, public second: any, public project: any) {
        super();
    }
}