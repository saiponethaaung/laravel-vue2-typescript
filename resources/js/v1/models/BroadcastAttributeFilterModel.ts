import { attributeFilter } from "../configuration/interface";
import AttributeFilterModel from "./AttributeFilterModel";
import Axios, { CancelTokenSource } from "axios";

export default class BroadcastAttributeFilterModel extends AttributeFilterModel {
    private updating: boolean = false;
    private updateToken: CancelTokenSource = Axios.CancelToken.source();

    constructor(
        attributeData: attributeFilter,
        public segment: any,
        private broadcast: number,
        private projectId: string
    ) {
        super(attributeData);
    }

    async updateFilter() {
        
        this.updateToken.cancel();
        this.updateToken = Axios.CancelToken.source();

        let res = {
            status: true,
            mesg: 'Success'
        };

        let data = new FormData();

        data.append(`option`, this.option.toString());
        data.append(`type`, this.type.toString());
        data.append(`name`, this.name);
        data.append(`value`, this.value);
        data.append(`condi`, this.condi.toString());
        data.append(`systemAttribute`, this.system.toString());
        data.append(`systemAttributeValue`, this.systemValue.toString());
        data.append(`userAttribute`, this.user.toString());
        data.append(`userAttributeValue`, this.userValue.toString());
        data.append(`segmentId`, this.segment.id.toString());
        
        this.updating = true

        await Axios({
            url: `/api/v1/project/${this.projectId}/broadcast/${this.broadcast}/filters/${this.id}`,
            method: 'post',
            data: data,
            cancelToken: this.updateToken.token
        }).then(res => {
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create a segment!');
            }
        });

        return res;
    }
}