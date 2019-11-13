<template>
    <div class="ai-response-text-con">
        <textarea
            class="ai-response-textarea"
            @blur="updateContent()"
            placeholder="Reply text here"
            v-model="response.content"
        ></textarea>
        <div
            class="ai-response-text-placeholder"
            v-html="response.content.replace(/\n/g, '<br />')"
        ></div>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from "vue-property-decorator";
import AIGroupRuleResponseModel from "../../models/ai/AIGroupRuleResponseModel";

@Component
export default class AIResponseText extends Vue {
    @Prop() response!: AIGroupRuleResponseModel;

    async updateContent() {
        let update = await this.response.updateContent();
        if (!update.status) {
            alert(update.mesg);
        }
    }
}
</script>
