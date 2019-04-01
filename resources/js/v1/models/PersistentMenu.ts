import AjaxErrorHandler from "../utils/AjaxErrorHandler";

export default class PersistentMenu extends AjaxErrorHandler {
    constructor(public content: any) {
        super();
    }
}