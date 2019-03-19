<template>
    <li class="aiC-groupCon-child">
        
        <input
            v-if="group.edit"
            v-model="group.name"
            type="text"
            class="aiC-groupCon-input"
            @blur="saveRename()"
            ref="editGroupName"
            required/>
        <span class="aiC-groupCon-placeHolder" @click="activeGroup()">{{ group.name }}</span>
        
        <span
            v-if="!confirmDelete && !group.edit"
            class="aiC-groupCon-optionButton"
            :class="{'showOptions': group.option}"
            @click="group.option=!group.option">
            <i class="material-icons">more_horiz</i>
        </span>
        
        <ul class="aiC-groupCon-options" ref="optionSelector" v-if="group.option">
            <li @click="renameGroup()">Rename</li>
            <li @click="confirmDelete=true;group.option=false">Delete</li>
        </ul>

        <template v-if="confirmDelete">
            <popup-component>
                <div class="aiC-group-confirmDelCon">
                    <div class="aiC-group-delHeadingCon">
                        <h5 class="aiC-group-delHeading">Confirm deletion</h5>
                        <button class="aiC-group-delClose" @click="confirmDelete=false">
                            <i class="material-icons">close</i>
                        </button>
                    </div>
                    <p class="aiC-group-delMesg">Please enter ‘delete’ to delete this group of AI rules. This is needed to help you avoid accidental purging of the whole group of AI rules</p>
                    <input type="text" class="aiC-group-delInput" v-model="confirmText" placeholder="delete"/>
                    <button class="aiC-group-delAction" @click="deleteGroup()" :disabled="'delete'!==confirmText">Delete</button>
                </div>
            </popup-component>
        </template>

        <template v-if="errorAIGroup!==''">
            <error-component :mesg="errorAIGroup" @closeError="errorAIGroup=''"></error-component>
        </template>
    </li>
</template>

<script lang="ts">
import { Prop, Vue, Component, Emit } from 'vue-property-decorator';
import AIGroupModel from '../../models/ai/AIGroupModel';

@Component
export default class AIGroupComponent extends Vue {
    @Prop() group!: AIGroupModel;
    @Prop() index!: number;

    private confirmDelete: boolean = false;
    private confirmText: string = '';
    private errorAIGroup: string = "";

    renameGroup() {
        this.group.option = false;
        this.group.edit = true;
        setTimeout(() => {
            this.focusName();
        }, 10);
    }

    @Emit('deleteGroup')
    deleteGroup() {
        this.confirmText = '';
        this.confirmDelete = false;
        return this.index;
    }

    @Emit('activeGroup')
    activeGroup() {
        return this.index;
    }

    async saveRename() {
        if(this.group.name==='') {
            this.errorAIGroup = "Group name cannot be empty!";
            return false;
        }

        let update = await this.group.updateGroupName();
        
        if(!update.status) {
            this.errorAIGroup = update.mesg;
            this.focusName();
        }
    }

    focusName() {
        let el: any = this.$refs.editGroupName;
        el.focus();
        el.setSelectionRange(0, this.group.name.length);
    }

    documentClick(e: any){
        if(this.group.option && !this.group.edit) {
            let el: any = this.$refs.optionSelector;
            let target = e.target;
            if (undefined!==el && ( el !== target) && !el.contains(target)) {
                this.group.option = false;
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
