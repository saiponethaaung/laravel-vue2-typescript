<template>
    <div class="aiC-ruleCon-content">
        <div class="aiC-ruleCon-content-keywords">
            <h5>if user say something similar to</h5>
            <div class="keywordSection">
                <div contenteditable="true" ref="keywordsCon" class="keywordbox" @keypress.prevent="recordKey"><span class="aiKeywordBlock">123</span><span class="aiKeywordBlock">321</span><span class="aiKeywordBlock">123</span><span class="aiKeywordBlock">321</span></div>
                <!-- <div class="keywordboxPlaceholder">
                    <span class="aiKeywordBlock">hi</span>
                    <i class="material-icons">subdirectory_arrow_left</i>
                    <span class="aiKeywordBlock">hello</span>&nbsp;(press 'enter' to seperate phrases)
                </div> -->
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
            {{ keywords }}
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from "vue-property-decorator";
import AIGroupRule from "../../models/ai/AIGroupRule";

@Component
export default class AIRuleComponent extends Vue {
    @Prop() rule!: AIGroupRule;
    private keywords: string = '';
    private nodeOffset: number = 0; 

    recordKey(e: any) {
        let textbox: any = this.$refs.keywordsCon;
        let range = document.createRange();
        let sel = window.getSelection();
        this.nodeOffset = 0;
        this.getPrevSibling(null===sel.focusNode.previousSibling ? sel.focusNode.parentNode : sel.focusNode.previousSibling);

        let content = textbox.childNodes[this.nodeOffset].innerText;
        console.log("node pos", this.nodeOffset);
        console.log("inner text", content);
        content = [content.slice(0, sel.focusOffset), e.key, content.slice(sel.focusOffset)].join('');
        textbox.childNodes[this.nodeOffset].innerText = content;

        // setTimeout(() => {
            range.setStart(textbox.childNodes[this.nodeOffset].childNodes[0], sel.getRangeAt(0).endOffset+1);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            textbox.focus();
        // }, 100);

        console.log(e);
        console.log('window select', sel);
        console.log('box', textbox);
        console.log("jog");

        // textbox.append(e.key);

    }

    getPrevSibling(winSel: any) {
        console.log("counter", winSel);
        if(null!==winSel.previousSibling) {
            this.nodeOffset++;
            this.getPrevSibling(winSel.previousSibling);
        }
    }
}
</script>
