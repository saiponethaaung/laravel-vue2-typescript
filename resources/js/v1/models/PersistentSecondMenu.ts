import Axios from "axios";
import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import PersistentThirdMenu from "./PersistentThirdMenu";

export default class PersistentSecondMenu extends AjaxErrorHandler {
    private thirdMenu: PersistentThirdMenu[] = [];
    public creating: boolean = false;

    constructor(public content: any, public parent: any, public projectId: any) {
        super();
        for(let i of content.third_relation) {
            this.thirdMenu.push(new PersistentThirdMenu(i, parent, content.id, projectId));
        }
    }

    get item() : PersistentThirdMenu[] {
        return this.thirdMenu;
    }

    set item(menu: PersistentThirdMenu[]) {
        this.thirdMenu = menu;
    }

    async createThirdMenu() {
        let res = {
            status: true,
            mesg: ''
        };

        this.creating = true;

        await Axios({
            url: `/api/v1/project/${this.projectId}/persistent-menu/${this.parent}/${this.content.id}`,
            method: 'post'
        }).then(res => {
            this.thirdMenu.push(new PersistentThirdMenu(res.data.data, this.parent, this.content.id, this.projectId));
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