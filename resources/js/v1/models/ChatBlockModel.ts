import Axios from "axios";
import { ChatBlock, ChatBlockSection } from "../configuration/interface";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import ChatBlockSectionModel from "./ChatBlockSectionModel";

export default class ChatBlockModel extends AjaxErrorHandler {
    private chatBlock: ChatBlock;
    private isAllowDelete: boolean = false;
    private isDeleting: boolean = false;
    private creatingSection: boolean = false;
    private update: boolean = false;

    constructor(chatBlock: ChatBlock, sections: Array<ChatBlockSection>) {
        super();
        this.chatBlock = chatBlock;
        this.chatBlock.sections = [];

        for (let section of sections) {
            this.buildContentModel(section);
        }
    }

    private buildContentModel(section: ChatBlockSection) {
        this.chatBlock.sections.push(new ChatBlockSectionModel(section));
    }

    get id(): number {
        return this.chatBlock.id;
    }

    get title(): string {
        return this.chatBlock.title;
    }

    set title(title: string) {
        this.chatBlock.title = title;
    }

    get lock(): boolean {
        return this.chatBlock.lock;
    }

    get sections(): Array<ChatBlockSectionModel> {
        return this.chatBlock.sections;
    }

    set sections(sections: Array<ChatBlockSectionModel>) {
        this.chatBlock.sections = sections;
    }

    get deleting(): boolean {
        return this.isDeleting;
    }

    set deleting(status: boolean) {
        this.isDeleting = status;
    }

    get canDelete(): boolean {
        let status: boolean = false;

        if (this.sections.length === 0) {
            status = true;
        } else if (this.sections.length > 0 && this.allowDelete) {
            status = true;
        }

        return status;
    }

    get allowDelete(): boolean {
        return this.isAllowDelete;
    }

    set allowDelete(status: boolean) {
        this.isAllowDelete = status;
    }

    get isSecCreating(): Boolean {
        return this.creatingSection;
    }

    get project(): string {
        return this.chatBlock.project;
    }

    get updating(): boolean {
        return this.update;
    }

    set updating(updating: boolean) {
        this.update = updating;
    }

    async updateBlock() {
        let res = {
            status: true,
            mesg: 'success'
        };

        this.updating = true;

        let data = new FormData();
        data.append('title', this.title);
        data.append('_method', 'put');

        await Axios({
            url: `/api/v1/project/${this.project}/chat-bot/block/${this.id}`,
            method: 'post',
            data: data
        }).catch((err: any) => {
            if (err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create new section!')
            }
        });

        this.updating = false;

        return res;
    }

    async createNewSection() {
        this.creatingSection = true;

        await Axios({
            url: `/api/v1/project/${this.project}/chat-bot/block/${this.id}/section`,
            method: 'post'
        }).then((res: any) => {
            this.buildContentModel(res.data.data);
        }).catch((err: any) => {
            let mesg = this.globalHandler(err, 'Failed to create new section!')
            alert(mesg);
        });

        this.creatingSection = false;
    }

    async deleteBlock() {
        let res = {
            status: true,
            mesg: "Success"
        };

        await Axios({
            url: `/api/v1/project/${this.project}/chat-bot/block/${this.id}`,
            method: 'delete'
        }).catch((err: any) => {
            res.mesg = this.globalHandler(err, "Failed to delete block!");
            res.status = false;
        });

        return res;
    }
}