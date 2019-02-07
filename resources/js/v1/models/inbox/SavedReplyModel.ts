import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { savedReply } from "../../configuration/interface";

export default class SavedReplyModel extends AjaxErrorHandler {
    constructor(
        private savedReply : savedReply
    ) {
        super();
    }

    get id() {
        return this.savedReply.id;
    }

    get title() {
        return this.savedReply.title;
    }

    set title(title: string) {
        this.savedReply.title = title;
    }

    get message() {
        return this.savedReply.message;
    }

    set message(message: string) {
        this.savedReply.message = message;
    }

    get time() {
        return this.savedReply.time;
    }

    set time(time: string) {
        this.savedReply.time = time;
    }
}