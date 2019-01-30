import BroadcastModel from "./BroadcastModel";
import Axios from "axios";

export default class TriggerModel extends BroadcastModel {

    private trigger: any = {
        duration: 1,
        durationType: 1,
        triggerType: 1,
        time: '12:34',
        period: 2,
        attr: '',
        value: '',
        condi: 1
    };

    constructor() {
        super();
    }
    
    init(content: any) {
        this.broadcastInit(content);
        this.duration = content.duration;
        this.durationType = content.durationType;
        this.triggerType = content.triggerType;
        this.time = content.time;
        this.period = content.period;
        this.attr = content.attributeName;
        this.value = content.attributeValue;
        this.condi = content.attributeCondi;
    }

    get duration(): number {
        return this.trigger.duration;
    }

    set duration(duration: number) {
        this.trigger.duration = duration;
    }

    get durationType() : number {
        return this.trigger.durationType;
    }

    set durationType(durationType: number) {
        this.trigger.durationType = durationType;
    }

    get triggerType() : number {
        return this.trigger.triggerType;
    }

    set triggerType(triggerType: number) {
        this.trigger.triggerType = triggerType;
    }
    
    get time() : string {
        return this.trigger.time;
    }

    set time(time: string) {
        this.trigger.time = time;
    }

    get period(): number {
        return this.trigger.period;
    }

    set period(period: number) {
        this.trigger.period = period;
    }

    get attr(): string {
        return this.trigger.attr;
    }

    set attr(attr: string) {
        this.trigger.attr = attr;
    }

    get value(): string {
        return this.trigger.value;
    }

    set value(value: string) {
        this.trigger.value = value;
    }

    get condi(): number {
        return this.trigger.condi;
    }

    set condi(condi: number) {
        this.trigger.condi = condi;
    }
    
    async updateTrigger() {
        let res = {
            status: true,
            mesg: 'success'
        };
        
        let time: any = this.time.split(":");
        if(this.period==2) {
            time[0] = parseInt(time[0])+12;
            time[0] = time[0].length<10 ? `0${time[0]}` : time[0];
        }

        
        let data = new FormData();
        data.append('time', `${time[0]}${time[1]}`);
        data.append('duration', this.duration.toString());
        data.append('durationType', this.durationType.toString());
        data.append('triggerType', this.triggerType.toString());
        data.append('attribute', this.attr);
        data.append('value', this.value);
        data.append('condi', this.condi.toString());

        await Axios({
            url: `/api/v1/project/${this.project}/broadcast/trigger/${this.id}`,
            data: data,
            method: 'post'
        }).then(res => {

        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to update trigger!');
            }
        });

        return res;
    }
}