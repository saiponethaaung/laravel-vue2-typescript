import AjaxErrorHandler from "../utils/AjaxErrorHandler";
import { segment } from "../configuration/interface";

export default class SegmentModel extends AjaxErrorHandler {
    private firstLoad: boolean = false;

    constructor(
        private segment: segment,
        private projectId: string
    ) {
        super();
    }

    get id() : number {
        return this.segment.id;
    }

    get name() : string {
        return this.segment.name;
    }

    set name(name: string) {
        this.segment.name = name;
    }

    async loadAttributes() {

    }

    async loadCount() {

    }
}