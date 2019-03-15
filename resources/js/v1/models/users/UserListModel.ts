import Axios from "axios";
import { user } from "../../configuration/interface";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import AttributeModel from "./AttributeModel";

export default class UserListModel extends AjaxErrorHandler {

    private isChecked: boolean = false;
    private isAttributeLoad: boolean = false;
    private userAttributes: Array<AttributeModel> = [];
    private creatingAttr: number = 0;

    constructor(
        private user: user,
        private projectId: string
    ) {
        super();
    }

    get id(): number {
        return this.user.id;
    }

    get name(): string {
        return this.user.name;
    }

    get gender(): string {
        return this.user.gender;
    }

    get age(): number {
        return this.user.age;
    }

    get parsedAge(): string {
        return this.age > 0 ? this.age.toString() : "-";
    }

    get lastEngaged(): string {
        return this.user.lastEngaged;
    }

    get lastSeen(): string {
        return this.user.lastSeen;
    }

    get signup(): string {
        return this.user.signup;
    }

    get checked(): boolean {
        return this.isChecked;
    }

    set checked(status: boolean) {
        this.isChecked = status;
    }

    get creating(): number {
        return this.creatingAttr;
    }

    set creating(count: number) {
        this.creatingAttr = count;
    }

    get isAttrLoad(): boolean {
        return this.isAttributeLoad;
    }

    set isAttrLoad(status: boolean) {
        this.isAttributeLoad = status;
    }

    get attributes(): Array<AttributeModel> {
        return this.userAttributes;
    }

    public async loadAttribute() {
        let result = {
            'status': true,
            'mesg': 'success'
        };

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/${this.id}/attributes`,
            method: 'get'
        }).then(res => {
            console.log('attribute', res.data);
            for (let i of res.data.data) {
                this.userAttributes.push(new AttributeModel(i, this.projectId, this.id));
            }
            this.isAttrLoad = true;
        }).catch(err => {
            if (err.response) {
                result['status'] = false;
                result['mesg'] = this.globalHandler(err, 'Failed to load user attribute!');
            }
        });

        return result;
    }

    public async createAttribute() {
        let result = {
            'status': true,
            'mesg': 'success'
        };

        this.creating++;

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/${this.id}/attributes`,
            method: 'post'
        }).then(res => {
            this.userAttributes.push(new AttributeModel(res.data.data, this.projectId, this.id));
            this.isAttrLoad = true;
        }).catch(err => {
            if (err.response) {
                result['status'] = false;
                result['mesg'] = this.globalHandler(err, 'Failed create new attribute!');
            }
        });

        this.creating--;

        return result;
    }

    public async deleteAttribute(index: number) {
        let result = {
            'status': true,
            'mesg': 'success'
        };

        await Axios({
            url: `/api/v1/project/${this.projectId}/users/${this.id}/attributes/${this.userAttributes[index].id}`,
            method: 'delete'
        }).then(res => {
            this.userAttributes.splice(index, 1);
        }).catch(err => {
            if (err.response) {
                result['status'] = false;
                result['mesg'] = this.globalHandler(err, 'Failed create new attribute!');
            }
        });

        return result;
    }

    public async enabledLiveChat() {
        let result = {
            'status': true,
            'mesg': 'success'
        };

        let data = new FormData();
        data.append('status', 'true');

        await Axios({
            url: `/api/v1/project/${this.projectId}/chat/user/${this.id}/live-chat`,
            data: data,
            method: 'post'
        }).catch((err) => {
            if (err.response) {
                result['status'] = false;
                result['mesg'] = this.globalHandler(err, 'Failed create new attribute!');
            }
        });

        return result;
    }

    get csvFormat() {
        return [
            `"${this.name}"`,
            `"${this.gender}"`,
            `"${this.parsedAge}"`,
            `"${this.lastEngaged}"`,
            `"${this.lastSeen}"`,
            `"${this.signup}"`,
            '-'
        ];
    }
}