import ChatBlockSectionModel from "../models/ChatBlockSectionModel";

export interface ChatBlock {
    id: number;
    project: string;
    title: string;
    lock: boolean;
    sections: Array<ChatBlockSectionModel>;
}

export interface ChatBlockSection {
    id: number;
    title: string;
}

export interface BotContent {
    id: number
    type: number;
    section: number;
    block: number;
    project: string;
}

export interface textContent {
    content: string;
    button: Array<buttonContent>;
}

export interface typingContent {
    duration: number;
}

export interface quickReplyContent {
    id: number;
    title: string;
    attribute: {
        id: number;
        title: string;
        value: string
    };
    content_id: number;
    block: Array<sectionLinked>;
}

export interface sectionLinked {
    id: number;
    title: string;
}

export interface imageContent {
    image: string;
}

export interface galleryContent {
    id: number;
    image: string;
    title: string;
    sub: string;
    url: string;
    content_id: number;
    button: Array<buttonContent>;
}

export interface listContent {
    id: number;
    image: string;
    title: string;
    sub: string;
    url: string;
    content_id: number;
    button: null | buttonContent;
}

export interface userInputContent {
    id: number;
    question: string;
    attribute: {
        id: number;
        title: string
    };
    content_id: number;
    validation: number;
}

export interface buttonContent {
    id: number;
    type: number;
    title: string;
    block: Array<sectionLinked>;
    url: string;
    phone: {
        countryCode: number;
        number: number | null;
    };
    attribute: {
        title: string;
        value: string
    };
}

export interface blockSuggestion {
    id: number;
    title: string;
    contents: [
        {
            id: number;
            title: string;
        }
    ]
}

export interface facebookPages {
    id: number;
    access_token: string;
    name: string;
    image: string;
    connected: boolean;
    currentProject: boolean;
}

export interface inboxUserList {
    id: number;
    name: string;
    urgent: boolean;
    fav: boolean;
    lastMesg: string;
    lastMesgType: number;
    image: string;
}

export interface attributeType {
    name: string;
    child: Array<any>;
}

export interface user {
    id: number;
    name: string;
    gender: string;
    age: number;
    lastEngaged: string;
    lastSeen: string;
    signup: string;
}

export interface attribute {
    id: number;
    name: string;
    value: string;
}

export interface axiosDefaultResponse {
    status: boolean;
    mesg: string;
}

export interface attributeFilter {
    id: number;
    option: number;
    type: number;
    name: string;
    value: string;
    condi: number;
    systemAttribute: number;
    systemAttributeValue: number;
    userAttribute: number;
    userAttributeValue: number;
}

export interface segment {
    id: number;
    name: string;
}

export interface messageTag {
    id: number;
    name: string;
    tag_format: string;
    mesg: string;
    notice: string;
    is_primary: boolean;
}

export interface adminNote {
    id: number;
    name: string;
    note: string;
    image: string;
    time: string;
}

export interface savedReply {
    id: number;
    title: string;
    message: string;
    time: string;
}

export interface FilterGroup {
    id: number;
    name: string;
}

export interface FilterGroupRule {
    id: number;
    filters: Array<Filter>;
    response: Array<FilterResponse>;
}

export interface Filter {
    keyword: string;
}

export interface FilterResponse {
    id: number;
    type: number;
    content: string;
    segmentId: number;
    segmentName: string;
}

export interface Admin {
    id: number,
    name: string,
    email: string,
    status: number
}

export interface invites {
    id: number,
    email: string,
}