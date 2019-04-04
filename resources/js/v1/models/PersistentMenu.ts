import Axios from "axios";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import PersistentSecondMenu from "./PersistentSecondMenu";

export default class PersistentMenu extends AjaxErrorHandler {
    private secondMenu: PersistentSecondMenu[] = [];
    public creating: boolean = false;

    constructor(public content: any, public projectId: any) {
        super();
        for(let i of content.second_relation) {
            this.secondMenu.push(new PersistentSecondMenu(i, content.id, projectId));
        }
    }

    get item() : PersistentSecondMenu[] {
        return this.secondMenu;
    }

    set item(menu: PersistentSecondMenu[]) {
        this.secondMenu = menu;
    }

    async createSecondMenu() {
        let res = {
            status: true,
            mesg: ''
        };

        this.creating = true;

        await Axios({
            url: `/api/v1/project/${this.projectId}/persistent-menu/${this.content.id}`,
            method: 'post'
        }).then(res => {
            this.secondMenu.push(new PersistentSecondMenu(res.data.data, this.content.id, this.projectId));
        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to create new persistent menu!');
            }
        });

        this.creating = false;
        
        return res;
    }
}