<template>
    <div class="sddListCon">
        <template v-if="undefined!==this.options">
            <div  ref="spinnerDropDown" @click="showOption=!showOption">{{ options[selected].value }}
                <i class="material-icons iconRight">
                    <template v-if="showOption">expand_less</template>
                    <template v-else>expand_more</template>
                </i>
            </div>
            <ul class="sddList" v-if="showOption && options.length>1">
                <li v-for="(option, index) in options" :key="index" @click="selectNewOption(option.key)">{{ option.value }}</li>
            </ul>
        </template>
    </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Emit } from 'vue-property-decorator';

@Component
export default class SpinnerDropDownComponent extends Vue {
    private showOption: boolean = false;
    @Prop() options!: Array<any>;
    @Prop({default: -1}) selectedKey!: number;

    get selected(): number {
        if(this.selectedKey === -1) return 0;

        let index: any = 0;

        for(let i in this.options) {
            if(this.options[i].key!==this.selectedKey) continue;
            index = i;
            break;
        }

        return index;
    }
    
    @Emit('input')
    selectNewOption(key: number) {
        return key;
    }

    documentClick(e: any){
        let el: any = this.$refs.spinnerDropDown;
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
