<template>
    <div>
        <h3 class="chatBotHeading">Chatbot</h3>
        <template v-if="blockLoading">Loading...</template>
        <template v-else>
            <draggable v-model="blocks" @end="updateBlockOrder" handle="orderBlock">
                <div v-for="(block, index) in blocks" :key="index" class="chatBlock">
                <template v-if="block.lock">
                    <h5 class="chatBlockHeading">{{ block.title }}</h5>
                    <div class="chatBlockContentList">
                    <div
                        v-for="(section, sIndex) in block.sections"
                        :key="sIndex"
                        class="chatBlockContent"
                        @click="selectBlock(index, sIndex)"
                        :class="{'selectedBlock': selectedBlock==section.id}"
                    >
                        {{ section.title }}
                        <div class="errorAlert" v-if="section.check"></div>
                    </div>
                    </div>
                </template>
                <template v-else>
                    <input
                    type="text"
                    v-model="block.title"
                    @blur="updateBlock(index)"
                    class="chatBlockHeading"
                    :disabled="block.updating"
                    >
                    <div class="chatBlockControl">
                        <button @click="delBlockIndex=index">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                    <!-- <div class="orderBlock">order</div> -->
                    <draggable
                    class="chatBlockContentList"
                    v-model="block.sections"
                    draggable=".sortCBC"
                    filter=".ignore-block"
                    @end="updateSectionOrder(index)"
                    >
                        <div
                            v-for="(section, sIndex) in block.sections"
                            :key="`${index}-${sIndex}`"
                            class="chatBlockContent sortCBC"
                            @click="selectBlock(index, sIndex)"
                            :class="{'selectedBlock': selectedBlock==section.id}"
                        >
                            {{ section.shortenTitle }}
                            <div class="errorAlert" v-if="section.check"></div>

                            <span 
                                class="blockOption" 
                                @click="section.option=!section.option">
                                <i class="material-icons">more_horiz</i>
                            </span>
                            <span class="menuOption" v-if="section.option" @click="delSection(section.id)">Delete</span>
                        </div>
                        <div
                            slot="footer"
                            v-if="!block.isSecCreating"
                            class="chatBlockContent addMore ignore-block"
                            @click="block.createNewSection()"
                        >
                            <i class="material-icons">add</i>
                        </div>
                        <div slot="footer" v-else class="chatBlockContent addMore ignore-block">
                            <i class="material-icons">autorenew</i>
                        </div>
                    </draggable>
                </template>
                </div>
                <template v-if="creating">Creating...</template>
                <template v-else>
                <button class="addMoreBlock" @click="createBlock">
                    <i class="material-icons">add</i> Add More
                </button>
                </template>
                <template v-if="showDelConfirm">
                    <popup-component :type="1">
                        <button class="closePopConfirm" @click="showDelConfirm=false;delBlockIndex=-1;">
                            <i class="material-icons">close</i>
                        </button>
                        <div class="delPopContent">
                            <p class="delPopHeading">
                                Are you sure you want to delete the
                                <b>{{ blocks[delBlockIndex].title }}</b>?
                                <br>
                                <span
                                    class="noticeList"
                                >It will effect the chatbot as following section are connected...</span>
                            </p>
                            <ul class="listOfSection">
                                <li
                                    v-for="(sub, index) in blocks[delBlockIndex].sections"
                                    :key="index"
                                >{{ sub.title }}</li>
                            </ul>
                        </div>
                        <div class="delPopActionFooter">
                            <div class="delPopActionCon">
                                <button @click="showDelConfirm=false;delBlockIndex=-1;">Cancel</button>
                                <button
                                    @click="blocks[delBlockIndex].allowDelete=true;showDelConfirm=false;deleteChatBlock();"
                                >Ok</button>
                            </div>
                        </div>
                    </popup-component>
                </template>
            </draggable>
        </template>
    </div>
</template>

<script lang="ts">
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";
import ChatBlockModel from "../../models/ChatBlockModel";
import Axios, { CancelTokenSource } from "axios";
import AjaxErrorHandler from "../../utils/AjaxErrorHandler";

@Component
export default class SidebarComponent extends Vue {
  private delBlockIndex: number = -1;
  private showDelConfirm: boolean = false;
  private creating: boolean = false;
  private blockLoading: boolean = false;
  private blocks: Array<ChatBlockModel> = [];
  private selectedBlock: number = 0;
  private cancelBlockOrder: CancelTokenSource = Axios.CancelToken.source();
  private blockId: number = 0;
  private ajaxHandler: AjaxErrorHandler = new AjaxErrorHandler();

  async mounted() {
    await this.loadBlocks();
  }

  @Watch("selectedBlock")
  removeSelected() {
    if (this.selectedBlock === 0) {
      this.$store.commit("selectChatBot", {
        block: -1,
        section: -1
      });
    }
  }

  selectBlock(index: any, sindex: any) {
    this.$store.commit("selectChatBot", {
      block: this.blocks[index].id,
      section: this.blocks[index].sections[sindex].id
    });
    this.selectedBlock = this.blocks[index].sections[sindex].id;
  }

  @Watch("delBlockIndex")
  async deleteChatBlock() {
    if (
      this.delBlockIndex == -1 ||
      undefined === this.blocks[this.delBlockIndex]
    )
      return;

    if (this.blocks[this.delBlockIndex].canDelete) {
      let deleteBlock = await this.blocks[this.delBlockIndex].deleteBlock();

      if (deleteBlock.status) {
        this.blocks.splice(this.delBlockIndex, 1);
        this.selectedBlock = 0;
        this.$store.commit("selectChatBot", { block: -1, section: -1 });
      } else {
        alert(deleteBlock.mesg);
        this.blocks[this.delBlockIndex].allowDelete = false;
      }

      this.delBlockIndex = -1;
    } else {
      this.showDelConfirm = true;
    }
  }

  @Watch("$store.state.delBot")
  async deleteSection() {
    if (
      this.$store.state.delBot.section == -1 &&
      this.$store.state.delBot.block == -1
    )
      return null;
    for (let i in this.blocks) {
      if (this.blocks[i].id != this.$store.state.delBot.block) continue;
      for (let s in this.blocks[i].sections) {
        if (this.blocks[i].sections[s].id != this.$store.state.delBot.section)
          continue;
        this.blocks[i].sections.splice(parseInt(s), 1);
        this.selectedBlock = 0;
        this.$store.commit("selectChatBot", { block: -1, section: -1 });
        break;
      }
      break;
    }
  }

  @Watch("$store.state.updateBot")
  async updateSectionTitle() {
    if (
      this.$store.state.updateBot.section == -1 &&
      this.$store.state.updateBot.block == -1
    )
      return null;
    for (let i in this.blocks) {
      if (this.blocks[i].id != this.$store.state.updateBot.block) continue;
      for (let s in this.blocks[i].sections) {
        if (
          this.blocks[i].sections[s].id != this.$store.state.updateBot.section
        )
          continue;
        this.blocks[i].sections[s].title = this.$store.state.updateBot.title;
        break;
      }
      break;
    }
  }

  async loadBlocks() {
    if (undefined === this.$store.state.projectInfo.id) return;
    this.blockLoading = true;

    await Axios({
      url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/blocks`
    })
      .then((res: any) => {
        for (let chatBlock of res.data.data) {
          
          this.blocks.push(
            new ChatBlockModel(chatBlock.block, chatBlock.sections)
          );

          for (let a in chatBlock.sections) {
            if(!chatBlock.sections[a].isValid) {
              for(let i in this.blocks) {
                if(this.blocks[i].id == chatBlock.block.id) {
                  for(let s in this.blocks[i].sections) {
                    if(this.blocks[i].sections[s].id == chatBlock.sections[a].id) {
                      this.blocks[i].sections[s].check = true;
                    }
                  }
                }
              }
            }
          }
        }

        
      })
      .catch((err: any) => {});

    this.blockLoading = false;
  }

  async createBlock() {
    this.creating = true;

    await Axios({
      url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block`,
      method: "POST"
    })
      .then((res: any) => {
        this.blocks.push(new ChatBlockModel(res.data.data, []));
      })
      .catch((err: any) => {});

    this.creating = false;
  }

  async updateBlock(index: any) {
    let update = await this.blocks[index].updateBlock();
    if (!update.status) {
      alert(update.mesg);
    }
  }

  updateSectionOrder(index: any) {
    let data = new FormData();

    for (let i in this.blocks[index].sections) {
      data.append(
        `sections[${i}]`,
        this.blocks[index].sections[i].id.toString()
      );
    }

    Axios({
      url: `/api/v1/project/${
        this.$store.state.projectInfo.id
      }/chat-bot/block/${this.blocks[index].id}/section/order`,
      data: data,
      method: "POST"
    }).catch(err => {
      if (err.response) {
        alert("Failed to order!");
      }
    });
  }

  async updateBlockOrder() {
    let data = new FormData();

    for (let i in this.blocks) {
      data.append(`blocks[${i}]`, this.blocks[i].id.toString());
    }

    this.cancelBlockOrder.cancel();
    this.cancelBlockOrder = Axios.CancelToken.source();

    await Axios({
      url: `/api/v1/project/${
        this.$store.state.projectInfo.id
      }/chat-bot/block/order`,
      data: data,
      cancelToken: this.cancelBlockOrder.token,
      method: "POST"
    }).catch(err => {
      if (err.response) {
        alert("Failed to order!");
      }
    });
  }

  async delSection($id: number) {

    if (
            confirm(
                "Are you sure you want to delete this block with it's content?"
            )
        ) {
            for(let i in this.blocks) {
              for(let s in this.blocks[i].sections) {
                if(this.blocks[i].sections[s].id == $id) {
                  this.blockId = this.blocks[i].id;
                }
              }
            }
            
            await Axios({
                url: `/api/v1/project/${this.$store.state.projectInfo.id}/chat-bot/block/${this.blockId}/section/${$id}`,
                method: "delete"
            })
                .then(res => {
                    this.$store.commit("deleteChatBot", {
                        block: this.$store.state.chatBot.block,
                        section: this.$store.state.chatBot.section
                    });
                })
                .catch(err => {
                    if (err.response) {
                        let mesg = this.ajaxHandler.globalHandler(
                            err,
                            "Failed to update block title!"
                        );
                        alert(mesg);
                    }
                });
        }
  }
}
</script>
