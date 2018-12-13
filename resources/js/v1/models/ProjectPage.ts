import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import { facebookPages } from "../configuration/interface";

export default class ProjectPage extends AjaxErrorHandler {

    private page: facebookPages;

    constructor(page: facebookPages) {
        super();
        this.page = page;
    }

    get id() : number {
        return this.page.id;
    }

    get name() : string {
        return this.page.name;
    }

    get image() : string {
        return this.page.image;
    }

    get token() : string {
        return this.page.access_token
    }

    get connected() : boolean {
        return this.page.connected;
    }

    get currentProject() : boolean {
        return this.page.currentProject;
    }

    set connected(status: boolean) {
        this.page.connected = status;
    }

    set currentProject(status: boolean){
        this.page.currentProject = status;
    }
}