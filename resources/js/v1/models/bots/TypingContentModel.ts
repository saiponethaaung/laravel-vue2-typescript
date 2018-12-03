import { typingContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";
import Axios, { CancelTokenSource } from "axios";

export default class TypingContentModel extends ChatBlockContentModel {

    private typingContent: typingContent;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();
    
    constructor(content: any) {
        super(content);
        this.typingContent = {
            duration: content.content.duration
        };
    }

    get duration() : number {
        return this.typingContent.duration;
    }

    set duration(duration: number) {
        this.typingContent.duration = duration;
    }

    async saveDuration() {
        this.saveToken.cancel();
        this.saveToken = Axios.CancelToken.source();

        let data = new FormData();
        data.append('duration', this.duration.toString());
        data.append('type', this.type.toString());
        data.append('_method', 'put');

        this.isUpdating = true;
        
        await Axios({
            url: `/api/v1/chat-bot/block/${this.block}/section/${this.section}/content/${this.contentId}`,
            data: data,
            method: 'post',
            cancelToken: this.saveToken.token
        }).catch((err: any) => {
            let mesg = this.globalHandler(err, 'Failed to update typing duration!');
            alert(mesg);
        });

        this.isUpdating = false;
    }
}