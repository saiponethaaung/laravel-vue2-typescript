import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import AdminNoteModel from "./AdminNoteModel";
import Axios from "axios";

export default class AdminNoteListModel extends AjaxErrorHandler {

    private content: string = "";
    private adminNoteList : Array<AdminNoteModel> = [];

    constructor(
        private projectId: string,
        private userId: number
    ) {
        super();
    }

    get adminNotes() : Array<AdminNoteModel> {
        return this.adminNoteList;
    }

    set adminNotes(adminNoteList: Array<AdminNoteModel>) {
        this.adminNoteList = adminNoteList;
    }

    get note() : string{
        return this.content;
    }

    set note(note: string) {
        this.content = note;
    }
    
    async createNote() {
        let res = {
            status: true,
            mesg: 'Success'
        };

        let data = new FormData();

        data.append('note', this.note);

        console.log(this.content);

        await Axios({
            url: `/api/v1/project/${this.projectId}/chat/user/${this.userId}/note`,
            method: 'post',
            data: data
        }).then(res => {
            this.content = '';
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create a note!');
            }
        });
    }

    async getNote() {
        let res = {
            status: true,
            mesg: 'Success'
        };

        await Axios({
            url: `/api/v1/project/${this.projectId}/inbox`
        }).then(res => {
            this.content = '';
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create a note!');
            }
        });

        return res;
    }
}