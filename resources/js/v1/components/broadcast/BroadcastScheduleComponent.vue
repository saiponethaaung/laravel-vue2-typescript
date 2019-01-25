<template>
    <div>
        <div class="broadcastFilterCon">
            <div>
                Subscription
            </div>
            <div>
                <div>Filter</div>
                <div>Reachable user</div>
            </div>
            <div class="broadcastCondition">
                <h5 class="bccHeading float-left">
                    Schedule:
                </h5>
                <div class="bccCalender float-left">
                    <span>{{ showDate }}</span>
                    <i class="material-icons">date_range</i>
                    <div class="calendarPlugin">
                        <v-date-picker
                            v-model="date"
                            :min-date="new Date()"
                        ></v-date-picker>
                    </div>
                </div>
                <div class="bccTime float-left">
                    <div>
                        12:00
                    </div>
                    <div>
                        am
                    </div>
                </div>
            </div>
        </div>
        <div>
            <!-- <builder-component></builder-component> -->
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue} from 'vue-property-decorator';

@Component
export default class BroadcastScheduleComponent extends Vue{
    private date: any = new Date();

    get showDate() {
        let date = '';
        let month = this.date.getMonth()+1;
        return `${this.$store.state.months[month]} ${this.date.getDate()}, ${this.date.getFullYear()}`;
    }
}
</script>
