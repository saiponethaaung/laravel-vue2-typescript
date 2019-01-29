import BroadcastModel from "./BroadcastModel";

export default class TriggerModel extends BroadcastModel {

    private trigger: any = {
        duration: 1,
        durationType: 1,
        triggerType: 1
    };

    constructor() {
        super();
    }

    init(content: any) {
        this.broadcastInit(content);
        
    }

    get duration(): number {
        return this.trigger.duration;
    }

    set duration(duration: number) {
        this.trigger.duration = duration;
    }

}