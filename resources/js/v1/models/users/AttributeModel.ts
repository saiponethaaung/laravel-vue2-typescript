import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { attribute } from "../../configuration/interface";
import Axios, { CancelTokenSource } from "axios";

export default class AttributeModel extends AjaxErrorHandler {
    
    private nameUpdateToken: CancelTokenSource = Axios.CancelToken.source();
    private valueUpdateToken: CancelTokenSource = Axios.CancelToken.source();
    
    constructor(
        private attribute: attribute,
        private projectId: string,
        private userId: number
    ) {
        super();
    }

    get id() : number {
        return this.attribute.id;
    }

    get name() : string {
        return this.attribute.name;
    }

    set name(name: string){
        this.attribute.name = name;
    }

    get value() : string {
        return this.attribute.value;
    }

    set value(value: string) {
        this.attribute.value = value;
    }

    public async updateAttributeName() {
        
        this.nameUpdateToken.cancel();
        this.nameUpdateToken = Axios.CancelToken.source();

        let result = {
            'status': true,
            'mesg': 'success'
        };

        let data = new FormData();
        data.append('name', this.attribute.name);

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/${this.userId}/attributes/${this.attribute.id}/name`,
            method: 'post',
            data: data,
            cancelToken: this.nameUpdateToken.token
        }).then(res => {
            console.log('attribute', res.data);
        }).catch(err => {
            if(err.response) {
                result['status'] = false;
                result['mesg'] = this.globalHandler(err, 'Failed to update attribute name!');
            }
        });

        return result;
    }

    public async updateAttributeValue() {

        this.valueUpdateToken.cancel();
        this.valueUpdateToken = Axios.CancelToken.source();

        let result = {
            'status': true,
            'mesg': 'success'
        };

        let data = new FormData();
        data.append('value', this.attribute.value);

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/${this.userId}/attributes/${this.attribute.id}/value`,
            method: 'post',
            data: data,
            cancelToken: this.valueUpdateToken.token
        }).then(res => {
            console.log('attribute', res.data);
        }).catch(err => {
            if(err.response) {
                result['status'] = false;
                result['mesg'] = this.globalHandler(err, 'Failed to update attribute value!');
            }
        });

        return result;
    }
}