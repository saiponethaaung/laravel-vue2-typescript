import { ChatBlockSection } from "../configuration/interface";

export default class ChatBlockSectionModel {
    private blockSection: ChatBlockSection;

    constructor(blockSection: ChatBlockSection) {
        this.blockSection = blockSection;
    }

    get id(): number {
        return this.blockSection.id;
    }

    get title(): string {
        return this.blockSection.title;
    }

    set title(title: string) {
        this.blockSection.title = title;
    }

    get shortenTitle(): string {
        return this.blockSection.title.length > 20 ? this.blockSection.title.slice(0, 20) : this.blockSection.title;
    }
}