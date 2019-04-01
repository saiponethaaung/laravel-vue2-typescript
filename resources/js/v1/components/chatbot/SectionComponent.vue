<template>
    <div class="chatBlockContent sortCBC">
        <div @click="selectBlock()">{{ section.shortenTitle }}</div>
        <div class="errorAlert" v-if="!section.check"></div>

        <span 
            class="blockOption"
            @click="section.option=!section.option"
            :class="{'showOptions': section.option}">
            
            <i class="material-icons">more_horiz</i>
        </span>
        <span class="menuOption" ref="optionSelector" v-if="section.option" @click="delSection()">Delete</span>
    </div>
</template>

<script lang="ts">
import { Prop, Vue, Component, Emit } from 'vue-property-decorator';
import ChatBlockSectionModel from '../../models/ChatBlockSectionModel';

@Component
export default class SectionComponent extends Vue {
    @Prop() section!: ChatBlockSectionModel;
    @Prop() index!: number;
    @Prop() sIndex!: number;
    
    @Emit('selectBlock')
    selectBlock() {
        return this.index, this.sIndex;
    }

    @Emit('delSection')
    delSection() {

    }

    documentClick(e: any){
        if(this.section.option) {
            let el: any = this.$refs.optionSelector;
            let target = e.target;
            if (undefined!==el && ( el !== target) && !el.contains(target)) {
                this.section.option = false;
            }
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