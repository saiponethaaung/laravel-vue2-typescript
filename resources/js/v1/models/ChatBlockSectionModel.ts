import { ChatBlockSection } from "../configuration/interface";

export default class ChatBlockSectionModel {
    private blockSection: ChatBlockSection;
    private isError: boolean = true;
    private isOption: boolean = false;

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

    get check(): boolean {
        return this.isError;
    }

    set check(status: boolean) {
        this.isError = status;
    }

    get option(): boolean {
        return this.isOption;
    }

    set option(status: boolean) {
        this.isOption = status;
    }

    get shortenTitle(): string {
        return this.blockSection.title.length > 20 ? this.blockSection.title.slice(0, 20) : this.blockSection.title;
    }
}