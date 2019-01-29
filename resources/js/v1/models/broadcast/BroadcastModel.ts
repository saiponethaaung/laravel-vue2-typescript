import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

export default class BroadcastModel extends AjaxErrorHandler {

    private content: any = {
        id: -1,
        tag: 2,
        project: '',
        type: 1,
        section: {
            id: -1,
            boradcast: -1
        }
    };

    constructor() {
        super();
    }

    broadcastInit(content: any) {
        this.id = content.id;
        this.project = content.project;
        this.type = content.type;
        this.tag = content.tag;
        this.section = content.section;
    }

    get id(): number {
        return this.content.id;
    }

    set id(id: number) {
        this.content.id = id;
    }

    get tag(): number {
        return this.content.tag;
    }

    set tag(tag: number) {
        this.content.tag = tag;
    }

    get project(): string{
        return this.content.project;
    }

    set project(project: string) {
        this.content.project = project;
    }

    get type(): string{
        return this.content.type;
    }

    set type(type: string) {
        this.content.type = type;
    }

    get section() : any{
        return this.content.section;
    }

    set section(section: any) {
        this.content.section = section;
    }
}