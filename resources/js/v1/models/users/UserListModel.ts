import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { userList } from "../../configuration/interface";

export default class UserListModel extends AjaxErrorHandler{

    private user: userList;
    private isChecked: boolean = false;

    constructor(user: userList) {
        super();
        this.user = user;
    }

    get id() : number {
        return this.user.id;
    }

    get name() : string {
        return this.user.name;
    }

    get gender() : string {
        return this.user.gender;
    }

    get age() : number {
        return this.user.age;
    }

    get parsedAge() : string {
        return this.age>0 ? this.age.toString() : "-";
    }

    get lastEngaged() : string {
        return this.user.lastEngaged;
    }

    get lastSeen() : string {
        return this.user.lastSeen;
    }

    get signup() : string {
        return this.user.signup;
    }

    get checked() : boolean {
        return this.isChecked;
    }

    set checked(status: boolean) {
        this.isChecked = status;
    }
}