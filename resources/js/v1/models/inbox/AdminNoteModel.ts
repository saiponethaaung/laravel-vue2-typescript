import AjaxErrorHandler from "../../utils/AjaxErrorHandler";
import { adminNote } from "../../configuration/interface";

export default class AdminNoteModel extends AjaxErrorHandler {
    constructor(
        private adminNote: adminNote  
    ) {
        super();
    }

    get id() : number {
        return this.adminNote.id;
    }

    get name() : string {
        return this.adminNote.name;
    }

    set name(name: string) {
        this.adminNote.name = name;
    }

    get note() : string {
        return this.adminNote.note;
    }

    set note(note: string) {
        this.adminNote.note = note;
    }

    get image() : string {
        return this.adminNote.image;
    }

    set image(image: string) {
        this.adminNote.image = image;
    }

    get time() : string {
        return this.adminNote.time;
    }

    set time(time: string) {
        this.adminNote.time = time;
    }

}