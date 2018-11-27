import { ChatBlock, ChatBlockSection } from "../configuration/interface";
import ChatBlockSectionModel from "./ChatBlockSectionModel";
import Axios from "axios";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";

export default class ChatBlockModel extends AjaxErrorHandler{
    private chatBlock: ChatBlock;
    private isAllowDelete: boolean = false;
    private isDeleting: boolean = false;

    constructor(chatBlock : ChatBlock, sections: Array<ChatBlockSection>) {
        super();
        this.chatBlock = chatBlock;
        this.chatBlock.sections = [];

        for(let section of sections) {
            this.buildContentModel(section);
        }
    }

    private buildContentModel(section: ChatBlockSection) {
        this.chatBlock.sections.push(new ChatBlockSectionModel(section));
    }

    get id() : number {
        return this.chatBlock.id;
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

    get deleting() : boolean {
        return this.isDeleting;
    }

    set deleting(status: boolean) {
        this.isDeleting = status;
    }

    get canDelete() : boolean {
        let status: boolean = false;

        if(this.sections.length===0) {
            status = true;
        } else if (this.sections.length>0 && this.allowDelete) {
            status = true;
        }

        return status;
    }

    get allowDelete() : boolean {
        return this.isAllowDelete;
    }

    set allowDelete(status: boolean) {
        this.isAllowDelete = status;
    }

    private createNewContent() {

    }

    async deleteBlock() {
        let res = {
            status: true,
            mesg: "Success"
        };

        await Axios({
            url: `/api/v1/chat-bot/block/${this.id}`,
            method: 'delete'
        }).catch((err: any) => {
            res.mesg = this.globalHandler(err, "Failed to delete category!");
            res.status = false;
        });

        return res;
    }
}