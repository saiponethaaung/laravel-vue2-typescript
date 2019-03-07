import { BotContent } from "../configuration/interface";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";

export default class ChatBlockContentModel extends AjaxErrorHandler {
    private content: BotContent;
    private updating: boolean = false;
    private deleting: boolean = false;
    protected baseUrl: string = '';
    public errorMesg: string = '';

    constructor(content: any, baseUrl: string) {
        super();
        this.baseUrl = baseUrl;
        this.content = {
            id: content.id,
            type: content.type,
            block: content.block_id,
            section: content.section_id,
            project: content.project
        };
    }

    get contentId(): number {
        return this.content.id;
    }

    get section(): number {
        return this.content.section;
    }

    get block(): number {
        return this.content.block;
    }

    get type(): number {
        return this.content.type;
    }

    get isUpdating(): boolean {
        return this.updating;
    }

    set isUpdating(status: boolean) {
        this.updating = status;
    }

    get isDeleting(): boolean {
        return this.deleting;
    }

    set isDeleting(status: boolean) {
        this.deleting = status;
    }

    get project(): string {
        return this.content.project;
    }

}