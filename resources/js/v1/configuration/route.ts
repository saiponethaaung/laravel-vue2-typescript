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
                        sidebar: ChatBotSidebar
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
                                sidebar: InboxPageSidebarComponent
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
                                sidebar: UserListSidebarComponent
                            }
                        },
                        {
                            path: "segments",
                            name: "project.users.segments",
                            component: UserSegmentListComponent,
                            meta: {
                                sidebar: UserSegmentListSidebarComponent
                            }
                        }
                    ]
                },
                {
                    path: "configuration",
                    name: "project.configuration",
                    meta: {
                        sidebar: null
                    },
                    component: ProjectConfigrationComponent
                },
            ]
        },
    ]
});