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
            <template v-if="!groups.loading">
                <ul class="aiC-groupCon">
                    <template v-for="(group, index) in groups.groups">
                        <li class="aiC-groupCon-child" :key="index">{{ group.name }}</li>
                    </template>
                    <li class="aiC-groupCon-child addmore" v-if="groups.creating">
                        creating...
                    </li>
                    <li class="aiC-groupCon-child addmore" v-else>
                        <button type="button">
                            <i class="material-icons">add</i>
                        </button>
                    </li>
                </ul>

                <div class="aiC-ruleCon">
                    <template v-for="ic in 5">
                        <div class="aiC-ruleCon-content" :key="ic">
                            <div class="aiC-ruleCon-content-keywords">
                                <h5>if user say something similar to</h5>
                                <div class="keywordSection">
                                    <div contenteditable="true" class="keywordbox"></div>
                                    <div class="keywordboxPlaceholder">
                                        <span class="aiKeywordBlock">hi</span>
                                        <i class="material-icons">subdirectory_arrow_left</i>
                                        <span class="aiKeywordBlock">hello</span>&nbsp;(press 'enter' to seperate phrases)
                                    </div>
                                </div>
                            </div>
                            <div class="aiC-ruleCon-content-response">
                                <h5>bot replies with</h5>
                                <ul>
                                    <li>
                                        <div class="addMoreCon">
                                            <i class="material-icons">add</i>
                                            <span>add <button type="button">Block</button> or <button>Text</button> reply</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </template>
                    <button type="button">
                        <i class="material-icons">add</i>
                        <span>Add AI Rule</span>
                    </button>
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

@Component
export default class AIComponent extends Vue {

    private groups: AIGroupListModel = new AIGroupListModel('');
    
    async mounted() {
        this.groups = new AIGroupListModel(this.$route.params.projectid)
        await this.groups.loadContent();
    }

    beforeDestory() {
        this.groups.cancelLoadContent();
    }
}
</script>
