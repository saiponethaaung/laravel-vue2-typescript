import { ChatBlockSection } from "../configuration/interface";

export default class ChatBlockSectionModel {
    private blockSection: ChatBlockSection;

    constructor(blockSection: ChatBlockSection) {
        this.blockSection = blockSection;
    }

    get id() : number {
        return this.blockSection.id;
    }

    get title() : string {
        return this.blockSection.title;
    }

    set title(title: string) {
        this.blockSection.title = title;
    }
}