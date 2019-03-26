import Axios from "axios";
import { userInputContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";
import UserInputItemModel from "./UserInputItemModel";

export default class UserInputContentModel extends ChatBlockContentModel {

    private userInputContent: Array<UserInputItemModel> = [];
    private creating: boolean = false;
    private rootUrl: string = '';
    public warningText = '';

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.rootUrl = `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}/user-input`;
        for (let i of content.content) {
            this.buildUserInputItem(i);
        }
    }

    private buildUserInputItem(content: userInputContent) {
        this.userInputContent.push(new UserInputItemModel(content, this.rootUrl));
    }

    get item(): Array<UserInputItemModel> {
        return this.userInputContent;
    }

    set item(userInput: Array<UserInputItemModel>) {
        this.userInputContent = userInput;
    }

    get isCreating(): boolean {
        return this.creating;
    }

    set isCreating(status: boolean) {
        this.creating = status;
    }
    
    get showWarning() {
        this.warningText = 'Chat process on messenger will stop here due to incomplete user input component!';

        if(this.item.length==0) {
            return true;
        }

        for(let i in this.item) {
            if(!this.item[i].isValid) {
                let position: any = parseInt(i)+1;
                switch(parseInt(i)) {
                    case 0:
                        position = position+'st';
                        break;
                        
                    case 1:
                        position = position+'nd';
                        break;
                        
                    case 2:
                        position = position+'rd';
                        break;
                        
                    default:
                        position = position+'th';
                        break;
                }

                this.warningText = `Chat process on messenger will stop here because ${position} user input is incomplete!`;
                return true;
            }
        }

        return false;
    }

    async createUserInpt() {
        this.isCreating = true;

        await Axios({
            url: this.rootUrl,
            method: 'post'
        }).then((res: any) => {
            this.buildUserInputItem(res.data.data);
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to create new user input!');
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
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to delete a user input!');
            }
        });
    }
}