import { ChatBlock, ChatBlockSection } from "../configuration/interface";
import ChatBlockSectionModel from "./ChatBlockSectionModel";

export default class ChatBlockModel {
    private chatBlock : ChatBlock;

    constructor(chatBlock : ChatBlock, sections: Array<ChatBlockSection>) {
        this.chatBlock = chatBlock;
        this.chatBlock.sections = [];

        for(let section of sections) {
            this.buildContentModel(section);
        }
    }

    private buildContentModel(section: ChatBlockSection) {
        this.chatBlock.sections.push(new ChatBlockSectionModel(section));
    }

    get title() : string{
        return this.chatBlock.title;
    }

    set title(title: string) {
        this.chatBlock.title = title;
    }

    get lock() : boolean {
        return this.chatBlock.lock;
    }

    get sections() : Array<ChatBlockSectionModel> {
        return this.chatBlock.sections;
    }

    private createNewContent() {

    }
}