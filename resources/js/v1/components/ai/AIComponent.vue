<template>
    <div class="aiContainerRoot">
        <div class="aiContainer">
            <div class="aiC-headingCon">
                <div class="aiC-desc">
                    <h4 class="aiC-desc-heading">Set up how bot replies to text messages</h4>
                    <p class="aiC-desc-description">Your bot will understand user phrases similar to those you write on the left and reply with some text or a block.</p>
                </div>
                <div class="aiC-Search">
                    <form class="aiC-form">
                        <div class="aiC-form-con">
                            <label class="aiC-form-label">
                                <i class="material-icons">search</i>
                                <input type="text" placeholder="Search keyword or block"/>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
            <template v-if="!groupList.loading">
                <ul class="aiC-groupCon">
                    <template v-for="(group, index) in groupList.groups">
                        <ai-group-component
                            :class="{'activeGroup': index===groupList.active}"
                            :key="index"
                            :index="index"
                            @deleteGroup="deleteGroup(index)"
                            @activeGroup="activeGroup(index)"
                            :group="group"></ai-group-component>
                    </template>
                    <li class="aiC-groupCon-child addmore creating" v-if="groupList.creating">
                        creating...
                    </li>
                    <li class="aiC-groupCon-child addmore"  @click="createNewGroup()" v-else>
                        <button type="button">
                            <i class="material-icons">add</i>
                        </button>
                    </li>
                </ul>

                <div class="aiC-ruleCon">
                    <template v-if="groupList.groups.length>0">
                        <template v-for="(rule, index) in groupList.groups[groupList.active].rules">
                            <ai-rule-component
                                :rule="rule"
                                :key="index"></ai-rule-component>
                        </template>
                        <template v-if="groupList.groups[groupList.active].creating || groupList.groups[groupList.active].loading">
                            Loading...
                        </template>
                        <template v-else>
                            <button class="addMoreRule" @click="createNewRule()" type="button">
                                <i class="material-icons">add</i>
                                <span>Add AI Rule</span>
                            </button>
                        </template>
                    </template>
                </div>
            </template>
            <template v-else>
                Loading...
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import AIGroupListModel from '../../models/ai/AIGroupListModel';
import AiGroupComponent from './AIGroupComponent.vue';
import AiRuleComponent from './AIRuleComponent.vue';

@Component({
    components: {
        AiGroupComponent,
        AiRuleComponent
    }
})
export default class AIComponent extends Vue {

    private groupList: AIGroupListModel = new AIGroupListModel('');
    
    async mounted() {
        this.groupList = new AIGroupListModel(this.$route.params.projectid)
        await this.groupList.loadContent();
    }

    async createNewGroup() {
        let create = await this.groupList.createContent();

        if(!create.status) {
            alert(create.mesg);
        }
    }

    async deleteGroup(index: any) {
        let deleteContent = await this.groupList.deleteContent(index);

        if(!deleteContent.status) {
            alert(deleteContent.mesg);
        }
    }

    activeGroup(index: number) {
        this.groupList.active = index;
        if(!this.groupList.groups[index].loaded && !this.groupList.groups[index].loading) {
            this.groupList.groups[index].loadRule();
        }
    }

    async createNewRule() {
        let createRule = await this.groupList.groups[this.groupList.active].createRule();

        if(!createRule.status) {
            alert(createRule.mesg);
        }
    }

    beforeDestory() {
        this.groupList.cancelLoadContent();
    }
}
</script>
