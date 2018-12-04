import Axios, { CancelToken, CancelTokenSource } from "axios";
import { textContent, BotContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";

export default class TextContentModel extends ChatBlockContentModel {

    private textContent: textContent;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    

    constructor(content: any) {
        super(content);
        this.textContent = {
            content: content.content.text,
            button: content.content.button
        };
    }

    get value() : string {
        return this.textContent.content;
    }

    set value(content: string) {
        this.textContent.content = content;
    }

    get type() : number {
        return 1;
    }

    async saveContent() {
        this.saveToken.cancel();
        this.saveToken = Axios.CancelToken.source();
        
        this.isUpdating = true;

        let data = new FormData();
        data.append('content', this.value);
        data.append('type', this.type.toString());
        data.append('_method', 'put');

        await Axios({
            url: `/api/v1/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}`,
            data: data,
            method: 'post',
            cancelToken: this.saveToken.token
        }).catch((err: any) => {
            if(err.response) {
                let mesg = this.globalHandler(err, 'Failed to save text content!');
                alert(mesg);
            }
        });

        this.isUpdating = false;
    }
}