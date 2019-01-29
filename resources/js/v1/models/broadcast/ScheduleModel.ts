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
                if(this.days[i2].days==i.day) {
                    this.days[i2].check = i.status;
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

    async updateSchedule() {
        let res = {
            status: true,
            mesg: 'success'
        };

        let month: string = (this.date.getMonth()+1).toString();
        let day = this.date.getDate().toString();
        
        month = month.length==1 ? `0${month}` : month;
        day = day.length==1 ? `0${day}` : day;

        let time: any = this.time.split(":");
        if(this.period==2) {
            time[0] = parseInt(time[0])+12;
            time[0] = time[0].length<10 ? `0${time[0]}` : time[0];
        }

        
        let data = new FormData();
        data.append('date', `${this.date.getFullYear()}-${month}-${day}`);
        data.append('time', `${time[0]}${time[1]}`);
        data.append('repeat', this.repeat.toString());
        
        let counter = 0;
        for(let i in this.days) {
            data.append(`day[${counter}][key]`, this.days[i].days.toString());
            data.append(`day[${counter}][value]`, this.days[i].check.toString());
            counter++;
        }

        await Axios({
            url: `/api/v1/project/${this.project}/broadcast/schedule/${this.id}`,
            data: data,
            method: 'post'
        }).then(res => {

        }).catch(err => {
            if(err.response) {
                res.status = false;
                res.mesg = this.globalHandler(err, 'Failed to update schedule!');
            }
        });

        return res;
    }
}