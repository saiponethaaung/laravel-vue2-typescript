<template>
    <div>
        <router-link
            class="projectName"
            :to="{name: 'project.home', params: { projectid: $store.state.projectList[index].id }}"
        >
            <div class="cardList">
                <figure class="addIcon">
                    <img
                        :src="$store.state.projectList[index].image ? $store.state.projectList[index].image : '/images/sample/logo.png'"
                        class="projectIcon"
                    />
                </figure>
                <div class="btnProject">{{ $store.state.projectList[index].name }}</div>
                <template v-if="$store.state.projectList[index].status==0">
                    <div class="projectDeactiveLabel">Deactivated</div>
                </template>
            </div>
        </router-link>
        <template v-if="processing">
            <div class="projectProcessing">
                <loading-component></loading-component>
            </div>
        </template>
        <div class="projectOption">
            <button @click="openOption=!openOption">
                <i class="material-icons">more_vert</i>
            </button>
            <template v-if="openOption">
                <ul ref="projectOption">
                    <template v-if="$store.state.projectList[index].status==0">
                        <li @click="activateProject">Activate</li>
                    </template>
                    <template v-else>
                        <li @click="deactivateProject">Deactivate</li>
                    </template>
                    <li @click="deleteProject">Delete</li>
                </ul>
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from "vue-property-decorator";
import Axios from "axios";

@Component
export default class DeactivatedProjectComponent extends Vue {
    @Prop() index!: number;
    openOption: boolean = false;
    processing: boolean = false;

    async deactivateProject() {
        this.processing = true;
        await Axios({
            url: `/api/v1/project/${this.$store.state.projectList[this.index].id}/deactivate`,
            method: "post"
        })
            .then(res => {
                this.$store.state.projectList[this.index].status = 0;
                this.openOption = false;
            })
            .catch((err: any) => {
                if (err.response) {
                    this.$store.state.errorMesg.push(
                        err.response.data.mesg ||
                            "Failed to deactivate project!"
                    );
                }
            });
        this.processing = false;
    }

    async activateProject() {
        this.processing = true;
        await Axios({
            url: `/api/v1/project/${this.$store.state.projectList[this.index].id}/activate`,
            method: "post"
        })
            .then(res => {
                this.$store.state.projectList[this.index].status = 1;
                this.openOption = false;
            })
            .catch((err: any) => {
                if (err.response) {
                    this.$store.state.errorMesg.push(
                        err.response.data.mesg || "Failed to activate project!"
                    );
                }
            });
        this.processing = false;
    }

    async deleteProject() {
        if (confirm("Are you sure you want to delete this project?")) {
            this.processing = true;
            await Axios({
                url: `/api/v1/project/${this.$store.state.projectList[this.index].id}`,
                method: "delete"
            })
                .then(res => {
                    this.$store.state.projectList.splice(this.index, 1);
                })
                .catch((err: any) => {
                    if (err.response) {
                        this.$store.state.errorMesg.push(
                            err.response.data.mesg ||
                                "Failed to delete project!"
                        );
                    }
                });
            this.processing = false;
        }
    }

    documentClick(e: any) {
        let el: any = this.$refs.projectOption;
        let target = e.target;
        if (el !== target && !el.contains(target)) {
            this.openOption = false;
            return null;
        }
    }

    created() {
        document.addEventListener("click", this.documentClick);
    }

    destroyed() {
        // important to clean up!!
        document.removeEventListener("click", this.documentClick);
    }
}
</script>
