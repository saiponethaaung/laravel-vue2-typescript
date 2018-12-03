<template>
    <div class="componentTypeOne">
        <div class="typingRoot">
            <div class="typingInfo">
                <i class="material-icons">speaker_notes</i>
                <span>Show "Typing.." for at least</span>
            </div>
            <div class="typingDuration">
                <div @click="showOption=!showOption">{{ content.duration }} sec</div>
                <div v-show="showOption" class="dropDownSec">
                    <ul>
                        <li v-for="i in 20" :key="i" :class="{'selected': i===content.duration}" @click="content.duration===i ? showOption=false :content.duration=i">{{ i }} sec</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import TypingContentModel from '../../../models/bots/TypingContentModel';

@Component
export default class TypingComponent extends Vue {
    @Prop({
        type: TypingContentModel,
    }) content!: TypingContentModel;

    private showOption: boolean = false;

    @Watch('content.duration')
    async durationChange() {
        this.showOption = false;
        await this.content.saveDuration();
    }

    created() {
        // window.addEventListener('click', (e: any) => {
        //     // close dropdown when clicked outside
        //     console.log("status 1", this.showOption);
        //     if (!this.$el.getElementsByClassName('dropDownSec')[0].contains(e.target)){
        //         if(this.showOption) {
        //             console.log("status", this.showOption);
        //             this.showOption = false;
        //         }
        //     } 
        // });
    }
}
</script>
