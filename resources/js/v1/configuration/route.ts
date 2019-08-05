import Vue from 'vue';
import VueRouter from 'vue-router';
import AIComponent from '../components/ai/AIComponent.vue';
import BroadcastComponent from '../components/broadcast/BroadcastComponent.vue';
import BroadcastScheduleComponent from '../components/broadcast/BroadcastScheduleComponent.vue';
import BroadcastSendNowComponent from '../components/broadcast/BroadcastSendNowComponent.vue';
import BroadcastSidebarComponent from '../components/broadcast/BroadcastSidebarComponent.vue';
import BroadcastTriggerComponent from '../components/broadcast/BroadcastTriggerComponent.vue';
import ChatBotComponent from '../components/chatbot/ContentComponent.vue';
import ChatBotSidebar from '../components/chatbot/SidebarComponent.vue';
import InboxPageComponent from '../components/inbox/InboxPageComponent.vue';
import InboxPageSidebarComponent from '../components/inbox/InboxPageSidebarComponent.vue';
import ProjectListComponent from '../components/ProjectListComponent.vue';
import ProjectRootComponent from '../components/ProjectRootComponent.vue';
import AdminComponent from '../components/setting/AdminComponent.vue';
import MessengerUserInputComponent from '../components/setting/MessengerUserInputComponent.vue';
import PersistentMenuComponent from '../components/setting/PersistentMenuComponent.vue';
import ProfileComponent from '../components/setting/ProfileComponent.vue';
import ProjectConfigrationComponent from '../components/setting/ProjectConfigrationComponent.vue';
import SettingSidebarComponent from '../components/setting/SettingSidebarComponent.vue';
import UserSegmentListComponent from '../components/user/SegmentListComponent.vue';
import UserSegmentListSidebarComponent from '../components/user/SegmentListSidebarComponent.vue';
import UserListComponent from '../components/user/UserListComponent.vue';
import UserListSidebarComponent from '../components/user/UserListSidebarComponent.vue';
import RouterViewComponent from '../configuration/RouterViewComponent.vue';
import RegisterComponent from '../non-member/RegisterComponent.vue';
import ResendOtpComponent from '../non-member/ResendOtpComponent.vue';
import VerifyEmailComponent from '../non-member/VerifyEmailComponent.vue';

Vue.use(VueRouter);

export default new VueRouter({
    base: '/',
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'home',
            component: ProjectListComponent
        },
        {
            path: '/login',
            name: 'login',
            component: ProjectListComponent
        },
        {
            path: '/register',
            name: 'register',
            component: RegisterComponent
        },
        {
            path: '/otp-resend',
            name: 'qrcode.resend',
            component: ResendOtpComponent
        },
        {
            path: '/verification',
            name: 'verify',
            component: VerifyEmailComponent
        },
        {
            path: '/:projectid',
            component: ProjectRootComponent,
            children: [
                {
                    path: '/',
                    name: 'project.home',
                    meta: {
                        sidebar: ChatBotSidebar,
                        section: 'chatbot'
                    },
                    component: ChatBotComponent
                },
                {
                    path: 'ai-setup',
                    name: 'project.ai',
                    meta: {
                        section: 'ai',
                        fullContent: true
                    },
                    component: AIComponent
                },
                {
                    path: 'inbox',
                    component: InboxPageComponent,
                    children: [
                        {
                            path: '/',
                            name: 'project.inbox',
                            meta: {
                                sidebar: InboxPageSidebarComponent,
                                section: 'inbox'
                            }
                        }
                    ]
                },
                {
                    path: 'users',
                    component: RouterViewComponent,
                    children: [
                        {
                            path: '/',
                            name: 'project.users',
                            component: UserListComponent,
                            meta: {
                                sidebar: UserListSidebarComponent,
                                section: 'user'
                            }
                        },
                        {
                            path: 'segments',
                            name: 'project.users.segments',
                            component: UserSegmentListComponent,
                            meta: {
                                sidebar: UserSegmentListSidebarComponent,
                                section: 'user'
                            }
                        }
                    ]
                },
                {
                    path: 'broadcast',
                    component: RouterViewComponent,
                    children: [
                        {
                            path: '/',
                            name: 'project.broadcast',
                            component: BroadcastComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        },
                        {
                            path: 'send',
                            name: 'project.broadcast.sendnow',
                            component: BroadcastSendNowComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        },
                        {
                            path: 'trigger/:triggerid',
                            name: 'project.broadcast.trigger',
                            component: BroadcastTriggerComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        },
                        {
                            path: 'schedule/:scheduleid',
                            name: 'project.broadcast.schedule',
                            component: BroadcastScheduleComponent,
                            meta: {
                                sidebar: BroadcastSidebarComponent,
                                section: 'broadcast'
                            }
                        }
                    ]
                },
                {
                    path: 'setting',
                    component: RouterViewComponent,
                    children: [
                        {
                            path: '/',
                            name: 'project.configuration',
                            meta: {
                                sidebar: SettingSidebarComponent,
                                section: 'setting',
                                sidebarSection: 'connected-page'
                            },
                            component: ProjectConfigrationComponent
                        },
                        {
                            path: 'persistent-menu',
                            name: 'project.configuration.persistent-menu',
                            meta: {
                                sidebar: SettingSidebarComponent,
                                section: 'setting',
                                sidebarSection: 'persistent-menu'
                            },
                            component: PersistentMenuComponent
                        },
                        {
                            path: 'messenger-user-input',
                            name: 'project.configuration.messenger-user-input',
                            meta: {
                                sidebar: SettingSidebarComponent,
                                section: 'setting',
                                sidebarSection: 'messenger-user-input'
                            },
                            component: MessengerUserInputComponent
                        },
                        {
                            path: 'admins',
                            name: 'project.configuration.admins',
                            meta: {
                                sidebar: SettingSidebarComponent,
                                section: 'setting',
                                sidebarSection: 'admins'
                            },
                            component: AdminComponent
                        },
                        {
                            path: 'profile',
                            name: 'project.configuration.profile',
                            meta: {
                                sidebar: SettingSidebarComponent,
                                section: 'setting',
                                sidebarSection: 'profile'
                            },
                            component: ProfileComponent
                        }
                    ]
                },
            ]
        },
    ]
});