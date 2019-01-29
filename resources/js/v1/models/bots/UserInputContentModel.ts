import ChatBlockContentModel from "../ChatBlockContentModel";
import { userInputContent } from "../../configuration/interface";
import UserInputItemModel from "./UserInputItemModel";
import Axios from "axios";

export default class UserInputContentModel extends ChatBlockContentModel {

    private userInputContent: Array<UserInputItemModel> = [];
    private creating: boolean = false;
    private rootUrl: string = '';
    
    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}/user-input`;
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

    set item(userInput: Array<UserInputItemModel>) {
        this.userInputContent = userInput;
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

    async delItem(index: number) {
        await Axios({
            url: `${this.rootUrl}/${this.item[index].id}`,
            method: 'delete',
        }).then((res) => {
            this.item.splice(index, 1);
        }).catch((err) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to delete a user input!');
                alert(mesg);
            }
        });
    }
}