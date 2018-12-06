import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { userInputContent } from "../../configuration/interface";
import Axios, { CancelTokenSource } from "axios";

export default class UserInputItemModel extends AjaxErrorHandler {

    private content: userInputContent;
    private rootUrl: string;
    private showValidation: boolean = false;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(content: userInputContent, rootUrl: string) {
        super();
        this.content = content;
        this.rootUrl = rootUrl;
    }

    get id() : number {
        return this.content.id;
    }

    get question() : string {
        return this.content.question;
    }

    set question(question: string) {
        this.content.question = question;
    }

    get validation() : number {
        return this.content.validation;
    }

    set validation(validation: number) {
        this.content.validation = validation;
    }

    get attribute() : string {
        return this.content.attribute.title;
    }

    set attribute(attribute: string) {
        this.content.attribute.title = attribute;
    }

    get showVal() : boolean {
        return this.showValidation;
    }

    set showVal(status: boolean) {
        this.showValidation = status;
    }

    async saveContent() {
        this.saveToken.cancel();
        this.saveToken = Axios.CancelToken.source();

        let data = new FormData();
        data.append('question', this.question);
        data.append('attribute', this.attribute);
        data.append('validation', this.validation.toString());
        data.append('_method', 'put');

        await Axios({
            url: `${this.rootUrl}/${this.id}`,
            data: data,
            method: 'post',
            cancelToken: this.saveToken.token
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to update user input!');
                alert(mesg);
            }
        });
    }
}