<template>
    <div class="pageRoot" :class="{'connectedPage': page.currentProject}">
        <figure class="pageImage">
            <img :src="page.image" />
        </figure>
        <div class="pageInfo">
            <p>{{ page.name }}</p>
            <template v-if="page.currentProject">
                <div class="pageAction connectedBtn">
                    <div class="pageActionInfoCon" @click="openDropDown=true">
                        <span>Facebook page connected</span>
                        <i
                            class="material-icons"
                        >{{ openDropDown ? 'arrow_drop_up' : 'arrow_drop_down' }}</i>
                    </div>
                    <ul class="pageInfoActions" ref="selectAction" v-if="openDropDown">
                        <li @click="disconnectPage();openDropDown=false;">Diconnect</li>
                    </ul>
                </div>
            </template>
            <template v-else-if="currentPage==-1 && page.connected">
                <div class="pageAction">Connected on other bot</div>
            </template>
            <template v-else-if="currentPage==-1">
                <div
                    class="pageAction headerButtonTypeOne connectBtn"
                    @click="connectPage()"
                >Connect to facebook page</div>
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from "vue-property-decorator";

@Component
export default class PageList extends Vue {
    @Prop() page!: any;
    @Prop() index!: number;
    @Prop() currentPage!: number;
    private openDropDown: boolean = false;

    @Emit("disconnectPage")
    private disconnectPage() {
        return this.index;
    }

    @Emit("connectPage")
    private connectPage() {
        return this.index;
    }

    documentClick(e: any) {
        let el: any = this.$refs.selectAction;

        let target = e.target;
        if (el !== target && !el.contains(target)) {
            this.openDropDown = false;
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

