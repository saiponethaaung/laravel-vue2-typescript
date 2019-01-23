<template>
    <div class="userListRoot">
        <div class="userListControlCon">
            <div class="userListFilterCon">
                <h5 class="headingOne">Showing all users</h5>
            </div>
        </div>
        <table class="userTable">
            <thead>
                <tr>
                    <th>
                        <div>
                            Sent Date
                        </div>
                    </th>
                    <th>
                        <div class="utColumn">
                            Type
                        </div>
                    </th>
                    <th>
                        <div class="utColumn">
                            Content
                        </div>
                    </th>
                    <th>
                        <div class="utColumnOption">
                            Blocked
                            <i class="material-icons iconAlign">
                                expand_more
                            </i>
                        </div>
                    </th>
                    <th>
                        <div class="utColumnOption">
                            Sent
                            <i class="material-icons iconAlign">
                                expand_more
                            </i>
                        </div>
                    </th>
                    <th>
                        <div class="utColumnOption">
                            Read
                            <i class="material-icons iconAlign">
                                expand_more
                            </i>
                        </div>
                    </th>
                    <th>
                        <div class="utColumnOption">
                            Clicked
                            <i class="material-icons iconAlign">
                                expand_more
                            </i>
                        </div>
                    </th>
                    <th>
                        <i class="material-icons iconAlign">
                            error_outline
                        </i>
                    </th>
                </tr>
            </thead>
            <tbody>
               <template>
                    <tr>
                        <td>
                            <div>
                                Dec 20, 2018 10:00 AM
                            </div>
                        </td>
                        <td>
                            <div>
                                Subscription
                            </div>
                        </td>
                        <td>
                            <div>
                                Thank you for contacting us. We are ...
                            </div>
                        </td>
                        <td>
                            <div>
                                2%
                            </div>
                            <div class="sugBlockTitle">
                                2/100
                            </div>
                        </td>
                        <td>
                            <div>
                                98%
                            </div>
                            <div class="sugBlockTitle">
                                89/100
                            </div>
                        </td>
                        <td>
                            <div>
                                50%
                            </div>
                            <div class="sugBlockTitle">
                                49/89
                            </div>
                        </td>
                        <td>
                            <div>
                                50%
                            </div>
                            <div class="sugBlockTitle">
                                49/89
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

@Component
export default class BroadcastComponent extends Vue {

    mounted() {

    }
}
</script>