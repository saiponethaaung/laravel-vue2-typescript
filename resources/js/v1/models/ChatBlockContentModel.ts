import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import { BotContent } from "../configuration/interface";

export default class ChatBlockContentModel extends AjaxErrorHandler {
    private content : BotContent;
    private updating: boolean = false;

    constructor(content: any) {
        super();
        this.content = {
            id: content.id,
            type: content.type,
            block: content.block_id,
            section: content.section_id,
        };
    }

    get contentId() : number {
        return this.content.id;
    }

    get section() : number {
        return this.content.section;
    }

    get block() : number {
        return this.content.block;
    }

    get type() : number {
        return this.content.type;
    }

    get isUpdating() : boolean {
        return this.updating;
    }

    set isUpdating(status: boolean) {
        this.updating = status;
    }

}