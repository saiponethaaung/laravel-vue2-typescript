<template>
    <div class="componentTypeOne">
        <div class="typingRoot">
            <div class="typingInfo">
                <i class="material-icons">speaker_notes</i>
                <span>Show "Typing.." for at least</span>
            </div>
            <div class="typingDuration" ref="dropdownMenu" @click="showOption=!showOption">
                <div class="duInfo">
                    <span>{{ content.duration }} sec</span>
                    <span class="iconCon">
                        <i class="material-icons">
                            <template v-if="showOption">expand_less</template>
                            <template v-else>expand_more</template>
                        </i>
                    </span>
                </div>
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

    documentClick(e: any){
        let el: any = this.$refs.dropdownMenu;
        let target = e.target;
        if (( el !== target) && !el.contains(target)) {
            this.showOption = false;
        }
    }

    created() {
      document.addEventListener('click', this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener('click', this.documentClick);
    }
}
</script>
