import Axios, { CancelTokenSource } from "axios";
import { typingContent } from "../../configuration/interface";
import ChatBlockContentModel from "../ChatBlockContentModel";

export default class TypingContentModel extends ChatBlockContentModel {

    private typingContent: typingContent;
    private saveToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(content: any, baseUrl: string) {
        super(content, baseUrl);
        this.typingContent = {
            duration: content.content.duration
        };
    }

    get duration(): number {
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
            url: `/api/v1/project/${this.project}/${this.baseUrl}/section/${this.section}/content/${this.contentId}`,
            data: data,
            method: 'post',
            cancelToken: this.saveToken.token
        }).catch((err: any) => {
            if (err.response) {
                this.errorMesg = this.globalHandler(err, 'Failed to update typing duration!');
            }
        });

        this.isUpdating = false;
    }
}