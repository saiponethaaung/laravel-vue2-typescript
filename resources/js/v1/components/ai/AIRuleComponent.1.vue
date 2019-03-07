<template>
    <div class="aiC-ruleCon-content">
        <div class="aiC-ruleCon-content-keywords">
            <h5>if user say something similar to</h5>
            <div class="keywordSection">
                <div
                    contenteditable="true"
                    ref="keywordsCon"
                    class="keywordbox contentBox"
                    @blur="checkContent()"
                    @focus="checkContentEmpty()"
                    @keyup.prevent="recordKey"
                    @keypress.prevent
                    @keydown.prevent
                ></div>
                <div class="keywordboxPlaceholder" v-if="showPlaceholder">
                    <span class="aiKeywordBlock">hi</span>
                    <i class="material-icons">subdirectory_arrow_left</i>
                    <span class="aiKeywordBlock">hello</span>&nbsp;(press 'enter' to seperate phrases)
                </div>
            </div>
        </div>
        <div class="aiC-ruleCon-content-response">
            <h5>bot replies
                <template v-if="rule.response.length>1">
                    &nbsp;
                    <b>randomly</b>
                </template>&nbsp;with
            </h5>
            <ul>
                <template v-for="(response, index) in rule.response">
                    <li :key="index" class="responseList">
                        <template v-if="response.type===1">
                            <ai-response-text :response="response"></ai-response-text>
                        </template>
                        <template v-if="response.type===2">
                            <ai-response-section :response="response"></ai-response-section>
                        </template>
                        <button class="responseDelete" type="button" @click="deleteResponse(index)">
                            <i class="material-icons">delete</i>
                        </button>
                    </li>
                </template>
                <li v-if="rule.responseCreating>0">
                    <div class="addMoreCon">Loading...</div>
                </li>
                <li>
                    <div class="addMoreCon">
                        <i class="material-icons">add</i>
                        <span>
                            add
                            <button type="button" @click="createSectionResponse()">Block</button> or
                            <button type="button" @click="createTextResponse()">Text</button> reply
                        </span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from "vue-property-decorator";
import AIGroupRuleModel from "../../models/ai/AIGroupRuleModel";
import AiResponseSection from "./AIResponseSection.vue";
import AiResponseText from "./AIResponseText.vue";

@Component({
    components: {
        AiResponseSection,
        AiResponseText
    }
})
export default class AIRuleComponent extends Vue {
    @Prop() rule!: AIGroupRuleModel;
    private keywords: string = "";
    private nodeOffset: number = 0;
    private textbox: any = this.$refs.keywordsCon;
    private showPlaceholder: boolean = true;

    mounted() {
        this.textbox = this.$refs.keywordsCon;
        if (this.rule.filters.length > 0) {
            for (let filter of this.rule.filters) {
                let span = document.createElement("SPAN");
                span.innerHTML = filter.keyword;
                span.className = "aiKeywordBlock";
                this.textbox.appendChild(span);
                this.nodeOffset = 0;
            }
        }

        this.checkContent(false);
    }

    async createTextResponse() {
        let ctr = this.rule.createResponse();
    }

    async createSectionResponse() {
        let csr = this.rule.createResponse("section");
    }

    async checkContent(update = true) {
        this.showPlaceholder = true;
        if (undefined !== this.textbox) {
            if (update) {
                this.rule.filters = [];
            }
            for (let i = 0; i < this.textbox.childNodes.length; i++) {
                let parsedText = this.textbox.childNodes[i].innerText.trim();
                if (parsedText !== "") {
                    if (update) {
                        this.rule.filters.push({ keyword: parsedText });
                    }
                    this.showPlaceholder = false;
                }
            }
        }

        if (update) {
            let updateRule = await this.rule.updateFilterValue();
            if (!updateRule.status) {
                alert(updateRule.mesg);
            }
        }
    }

    checkContentEmpty() {
        // Check keyword section have any node
        if (this.textbox.childNodes.length === 0) {
            // Create empty keyword container if section is empty
            let range = document.createRange();
            let sel = window.getSelection();
            let span = document.createElement("SPAN");
            span.innerHTML = "&nbsp;";
            span.className = "aiKeywordBlock emptyBlock";
            this.textbox.appendChild(span);
            // refocus cursor position
            range.setStart(
                this.textbox.childNodes[this.nodeOffset].childNodes[0],
                0
            );
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            this.textbox.focus();
        }
    }

    recordKey(e: any) {
        // list of allow key range
        if (
            (e.keyCode >= 48 && e.keyCode <= 57) ||
            (e.keyCode >= 65 && e.keyCode <= 90) ||
            (e.keyCode >= 96 && e.keyCode <= 111) ||
            (e.keyCode >= 144 && e.keyCode <= 192) ||
            (e.keyCode >= 219 && e.keyCode <= 222) ||
            e.keyCode == 13 ||
            e.keyCode == 61 ||
            e.keyCode == 46 ||
            e.keyCode == 37 ||
            e.keyCode == 39 ||
            e.keyCode == 32 ||
            e.keyCode == 8
        ) {
            let textbox: any = this.$refs.keywordsCon;
            let range = document.createRange();
            let sel = window.getSelection();
            // let anchor = window.getSelection().anchorOffset;
            let offset = window.getSelection().focusOffset;
            // let isOne = false;
            // if (anchor > offset) {
            //     let offsetPlaceholder = offset;
            //     offset = anchor;
            //     anchor = offsetPlaceholder;
            // }
            // if (offset === anchor || anchor + 1 === offset) {
            //     isOne = true;
            // }
            this.nodeOffset = 0;
            // check it have more than 1 keyword box
            if (textbox.childNodes.length > 1) {
                this.getPrevSibling(
                    null === sel.focusNode.previousSibling
                        ? sel.focusNode.parentNode
                        : sel.focusNode.previousSibling
                );
            }

            // console.log("isone", isOne);
            // console.log("anchor", anchor);
            // console.log("offset", offset);
            // console.log("selection", sel);

            // get inner content of current node
            let content = textbox.childNodes[this.nodeOffset].innerText;

            if (content.length === 1) {
                content = content.trim();
            }

            switch (e.keyCode) {
                // handle enter key
                case 13:
                    // if content is not empty create keyword box next to current node
                    if (
                        content !== "&nbsp;" &&
                        content !== "" &&
                        content !== " "
                    ) {
                        let allowAppend = true;
                        let span = document.createElement("SPAN");

                        // if caret position is at the beginning or at the end create empty keyword box next to current node
                        if (offset == 0 || content.length == offset) {
                            // check if empty keyword box already exists next to current node
                            if (
                                undefined ==
                                    textbox.childNodes[this.nodeOffset + 1] ||
                                (undefined !==
                                    textbox.childNodes[this.nodeOffset + 1] &&
                                    textbox.childNodes[
                                        this.nodeOffset + 1
                                    ].innerText.trim() !== "")
                            ) {
                                span.innerHTML = "&nbsp;";
                                span.className = "aiKeywordBlock emptyBlock";
                            } else {
                                allowAppend = false;
                            }
                        } else {
                            // if caret position is not at the end or beginning split the content into new keyword by caret position
                            span.innerHTML = content.slice(offset);
                            content = content.slice(0, offset);
                            textbox.childNodes[
                                this.nodeOffset
                            ].innerText = content;
                            span.className = "aiKeywordBlock";
                        }

                        if (allowAppend) {
                            // if current node is last node append the new node
                            if (textbox.childNodes.length === this.nodeOffset) {
                                textbox.appendChild(span);
                            } else {
                                // if current node is not last node append after current node
                                textbox.insertBefore(
                                    span,
                                    textbox.children[this.nodeOffset + 1]
                                );
                            }
                        }

                        // Change focus node to newly created one
                        this.nodeOffset++;
                    } else {
                        // Change current node to last node
                        this.nodeOffset = textbox.childNodes.length;
                    }

                    // change caret position to at the beginning
                    offset = -1;
                    // trigger placeholder checker
                    this.checkContent();
                    break;

                case 37:
                    // left arrow key event
                    if (offset > 0) {
                        // if caret position is not at the beginning change caret postion to left by 1
                        offset -= 2;
                    } else if (this.nodeOffset > 0) {
                        // if caret position is at the beginning and another node exist before current node
                        // change current node to the one before current one and set caret positon to the end of node
                        this.nodeOffset--;
                        offset =
                            textbox.childNodes[this.nodeOffset].innerText
                                .length - 1;
                    }
                    break;

                case 39:
                    // right arrow key event
                    if (
                        textbox.childNodes.length > this.nodeOffset &&
                        content.length == offset
                    ) {
                        // if caret positon is at the next and another node exist after current node
                        // change current node to next one and caret positon at the beginning of the node
                        this.nodeOffset++;
                        offset = -1;
                    }
                    break;

                case 8:
                    // backspace event
                    if (content.length > 1) {
                        // if caret position is not at first character remove character before caret
                        if (offset > 0) {
                            // if (isOne) {
                                content = [
                                    content.slice(0, offset - 1),
                                    content.slice(offset)
                                ].join("");
                            // } else {
                            //     content = [
                            //         content.slice(0, anchor),
                            //         content.slice(offset, content.length)
                            //     ].join("");
                            //     offset = anchor + 1;
                            // }
                        }
                    } else {
                        content = "";
                    }

                    // if node is empty or caret is at the beginning of the node merge current node with the one before or delete current node
                    if (content === "" || offset === 0) {
                        // Remove current node
                        textbox.removeChild(
                            textbox.childNodes[this.nodeOffset]
                        );
                        // Check child node count
                        if (textbox.childNodes.length === 0) {
                            // if there is no node create empty keyword node
                            let span = document.createElement("SPAN");
                            span.innerHTML = "&nbsp;";
                            span.className = "aiKeywordBlock emptyBlock";
                            textbox.appendChild(span);
                            this.nodeOffset = 0;
                            offset = -1;
                        } else {
                            // if there is node before current one
                            if (
                                textbox.childNodes.length - 1 <
                                this.nodeOffset
                            ) {
                                // change curret node to the one before
                                this.nodeOffset = textbox.childNodes.length - 1;
                            }
                            // if caret position is at the beginning
                            if (offset === 0) {
                                // change caret position to the end of the node
                                offset =
                                    textbox.childNodes[this.nodeOffset]
                                        .innerText.length - 1;
                                // merge previous current node content with current node content
                                textbox.childNodes[this.nodeOffset].innerText =
                                    textbox.childNodes[this.nodeOffset]
                                        .innerText + content;
                            } else {
                                // update caret position
                                offset =
                                    textbox.childNodes[this.nodeOffset]
                                        .innerText.length - 1;
                            }
                        }
                    } else {
                        // update node content
                        textbox.childNodes[this.nodeOffset].innerText = content;
                        offset -= 2;
                    }
                    break;

                case 46:
                    // delete event
                    if (content.length > 1) {
                        // if caret positon is no at the end
                        if (offset < content.length) {
                            // if (isOne) {
                                content = [
                                    content.slice(0, offset),
                                    content.slice(offset + 1)
                                ].join("");
                            // } else {
                            //     content = [
                            //         content.slice(0, anchor),
                            //         content.slice(offset, content.length)
                            //     ].join("");
                            //     offset = anchor + 1;
                            // }
                        }
                    } else {
                        content = "";
                    }

                    // if content is empty or caret position is at the end
                    if (content === "" || offset === content.length) {
                        // if caret position is not the end remove current node
                        if (
                            offset !==
                            textbox.childNodes[this.nodeOffset].innerText.length
                        ) {
                            textbox.removeChild(
                                textbox.childNodes[this.nodeOffset]
                            );
                        }

                        // if there is no node create empty keyword node
                        if (textbox.childNodes.length === 0) {
                            let span = document.createElement("SPAN");
                            span.innerHTML = "&nbsp;";
                            span.className = "aiKeywordBlock emptyBlock";
                            textbox.appendChild(span);
                            this.nodeOffset = 0;
                            offset = -1;
                        } else {
                            // if current node is greater than total node
                            if (
                                textbox.childNodes.length - 1 <
                                this.nodeOffset
                            ) {
                                // change current node position to last node of total node
                                this.nodeOffset = textbox.childNodes.length - 1;
                                // set caret position at the end of the node
                                offset =
                                    textbox.childNodes[this.nodeOffset]
                                        .innerText.length - 1;
                            }
                            // if caret position is at the end and current node is not the last
                            if (
                                offset === content.length &&
                                this.nodeOffset < textbox.childNodes.length - 1
                            ) {
                                // set caret position to at the end of the node
                                offset =
                                    textbox.childNodes[this.nodeOffset]
                                        .innerText.length - 1;
                                // merge node next to current node content into current
                                textbox.childNodes[this.nodeOffset].innerText =
                                    content +
                                    textbox.childNodes[this.nodeOffset + 1]
                                        .innerText;
                                // remove node next to the current node
                                textbox.removeChild(
                                    textbox.childNodes[this.nodeOffset + 1]
                                );
                            }
                        }
                    } else {
                        // update node content
                        textbox.childNodes[this.nodeOffset].innerText = content;
                        offset--;
                    }
                    break;

                default:
                    // remove empty keyword class
                    textbox.childNodes[this.nodeOffset].className =
                        "aiKeywordBlock";
                    let appendContent = e.key == " " ? "&nbsp;" : e.key;
                    // if node is empty replace '&nbsp;' value with inserted value
                    if (
                        content == "&nbsp;" ||
                        content == "" ||
                        content == " "
                    ) {
                        content = appendContent;
                    } else {
                        // update content at caret position with user inserted value
                        content = [
                            content.slice(0, offset),
                            appendContent,
                            content.slice(offset)
                        ].join("");
                    }
                    // update node content
                    textbox.childNodes[this.nodeOffset].innerHTML = content;
                    if (content.length === 1) {
                        offset = 0;
                    }
                    break;
            }

            // refocus cursor position
            range.setStart(
                textbox.childNodes[this.nodeOffset].childNodes[0],
                offset + 1
            );
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            textbox.focus();
        }
    }

    async deleteResponse(index: any) {
        if (confirm("Are you sure you want to delete this response?")) {
            let deleteResponse = await this.rule.deleteResponse(index);
            if (!deleteResponse.status) {
                alert(deleteResponse.mesg);
            }
        }
    }

    getPrevSibling(winSel: any) {
        if (null !== winSel.previousSibling) {
            this.nodeOffset++;
            this.getPrevSibling(winSel.previousSibling);
        }
    }
}
</script>
