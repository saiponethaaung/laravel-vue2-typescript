import BroadcastModel from "./BroadcastModel";
import Axios from "axios";

export default class ScheduleModels extends BroadcastModel{

    private loading = false;
    private schedule: any = {
        date: new Date(),
        time: '12:34',
        period: 2,
        repeat: 1,
        days: {
            1: {
                name: 'Mon',
                days: 1,
                check: false
            },
            2: {
                name: 'Tue',
                days: 2,
                check: false
            },
            3: {
                name: 'Wed',
                days: 3,
                check: false
            },
            4: {
                name: 'Thu',
                days: 4,
                check: false
            },
            5: {
                name: 'Fri',
                days: 5,
                check: false
            },
            6: {
                name: 'Sat',
                days: 6,
                check: false
            },
            7: {
                name: 'Sun',
                days: 7,
                check: false
            }
        }
    };

    constructor() {
        super();
    }

    init(content: any) {
        this.broadcastInit(content);
        this.date = new Date(content.date);
        this.time = content.time;
        this.period = content.period;
        this.repeat = content.repeat;
        for(let i of content.days) {
            for(let i2 in this.days) {
                if(this.days[i2].days==i.day && i.status) {
                    this.days[i2].check = true;
                    break;
                }
            }
        }
    }

    get date(): Date {
        return this.schedule.date;
    }

    set date(date: Date) {
        this.schedule.date = date;
    }

    get time() : string {
        return this.schedule.time;
    }

    set time(time: string) {
        this.schedule.time = time;
    }

    get period(): number {
        return this.schedule.period;
    }

    set period(period: number) {
        this.schedule.period = period;
    }

    get repeat() : number {
        return this.schedule.repeat;
    }

    set repeat(repeat: number) {
        this.schedule.repeat = repeat;
    }
    
    get days() : any {
        return this.schedule.days;
    }
    
    set days(days: any) {
        this.schedule.days = days;
    }

    get isLoading() : boolean {
        return this.loading;
    }
}