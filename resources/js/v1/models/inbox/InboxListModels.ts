import UserListModel from "../users/UserListModel";
import { user } from "../../configuration/interface";

export default class InboxListModels extends UserListModel {
    
    constructor(
        user: user,
        project: string
    ) {
        super(user, project);
    }

}