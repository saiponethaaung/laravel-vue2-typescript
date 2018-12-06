import ChatBlockContentModel from "../ChatBlockContentModel";
import { userInputContent } from "../../configuration/interface";
import UserInputItemModel from "./UserInputItemModel";
import Axios from "axios";

export default class UserInputContentModel extends ChatBlockContentModel {

    private userInputContent: Array<UserInputItemModel> = [];
    private creating: boolean = false;
    private rootUrl: string = '';
    
    constructor(content: any) {
        super(content);
        this.rootUrl = `/api/v1/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}/user-input`;
        for(let i of content.content) {
            this.buildUserInputItem(i);
        }
    }

    private buildUserInputItem(content: userInputContent) {
        this.userInputContent.push(new UserInputItemModel(content, this.rootUrl));
    }

    get item() : Array<UserInputItemModel> {
        return this.userInputContent;
    }

    get isCreating() : boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }

    async createUserInpt() {
        this.isCreating = true;

        await Axios({
            url: this.rootUrl,
            method: 'post'
        }).then((res: any) => {
            this.buildUserInputItem(res.data.data);
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to create new user input!');
                alert(mesg);
            }
        });

        this.isCreating = false;
    }
}