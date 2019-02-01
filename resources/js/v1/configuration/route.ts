import Vue from 'vue';
import VueRouter from 'vue-router';

import Example from '../Example.vue';

import RouterViewComponent from '../configuration/RouterViewComponent.vue';

import ProjectListComponent from '../components/ProjectListComponent.vue';
import ProjectRootComponent from '../components/ProjectRootComponent.vue';

import ProjectConfigrationComponent from '../components/ProjectConfigrationComponent.vue';

import ChatBotComponent from '../components/chatbot/ContentComponent.vue';
import ChatBotSidebar from '../components/chatbot/SidebarComponent.vue';

import InboxPageComponent from '../components/inbox/InboxPageComponent.vue';
import InboxPageSidebarComponent from '../components/inbox/InboxPageSidebarComponent.vue';

import UserListComponent from '../components/user/UserListComponent.vue';
import UserListSidebarComponent from '../components/user/UserListSidebarComponent.vue';
import UserSegmentListComponent from '../components/user/SegmentListComponent.vue';
import UserSegmentListSidebarComponent from '../components/user/SegmentListSidebarComponent.vue';

import BroadcastComponent from '../components/broadcast/BroadcastComponent.vue';
import BroadcastSidebarComponent from '../components/broadcast/BroadcastSidebarComponent.vue';
import BroadcastSendNowComponent from '../components/broadcast/BroadcastSendNowComponent.vue';
import BroadcastTriggerComponent from '../components/broadcast/BroadcastTriggerComponent.vue';
import BroadcastScheduleComponent from '../components/broadcast/BroadcastScheduleComponent.vue';

Vue.use(VueRouter);

export default new VueRouter({
    base: '/',
    mode: 'history',
    routes: [
        {
            path: "/",
            name: "home",
            component: ProjectListComponent
        },
        {
            path: "/:projectid",
            component: ProjectRootComponent,
            children: [
                {
                    path: "/",
                    name: "project.home",
                    meta: {
                        sidebar: ChatBotSidebar,
                        section: 'chatbot'
                    },
                    component: ChatBotComponent
                },
                {
                    path: "inbox",
                    component: InboxPageComponent,
                    children: [
                        {
                            path: "/",
                            name: "project.inbox",
                            meta: {
                                sidebar: InboxPageSidebarComponent,
                                section: 'inbox'
                            }
                        }
                    ]
                },
                {
                    path: "users",
                    component: RouterViewComponent,
                    children: [
                        {
                            path: "/",
                            name: "project.users",
                            component: UserListComponent,
                            meta: {
                                sidebar: UserListSidebarComponent,
                                section: 'user'
                            }
                        },
                        {
                            path: "segments",
                            name: "project.users.segments",
                            component: UserSegmentListComponent,
                            meta: {
                                sidebar: UserSegmentListSidebarComponent,
                                section: 'user'
                            }
                        }
                    ]
                },
                {
                    path: "broadcast",
                    component: RouterViewComponent,
                    children: [
                        {
                            path: "/",
                            name: "project.broadcast",
                            component: BroadcastComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        },
                        {
                            path: "send",
                            name: "project.broadcast.sendnow",
                            component: BroadcastSendNowComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        },
                        {
                            path: "trigger/:triggerid",
                            name: "project.broadcast.trigger",
                            component: BroadcastTriggerComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        },
                        {
                            path: "schedule/:scheduleid",
                            name: "project.broadcast.schedule",
                            component: BroadcastScheduleComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        }
                    ]
                },
                {
                    path: "configuration",
                    name: "project.configuration",
                    meta: {
                        sidebar: null,
                        section: 'setting'
                    },
                    component: ProjectConfigrationComponent
                },
            ]
        },
    ]
});