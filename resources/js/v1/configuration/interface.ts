import ChatBlockSectionModel from "../models/ChatBlockSectionModel";

export interface ChatBlock {
    id: number,
    project: string,
    title: string,
    lock: boolean,
    sections: Array<ChatBlockSectionModel>
}

export interface ChatBlockSection {
    id: number,
    title: string
}

export interface BotContent {
    id: number
    type: number,
    section: number,
    block: number,
    project: string
}

export interface textContent {
    content: string,
    button: Array<buttonContent>
}

export interface typingContent {
    duration: number
}

export interface quickReplyContent {
    id: number,
    title: string,
    attribute: {
        id: number,
        title: string,
        value: string
    },
    content_id: number,
    block: Array<sectionLinked>
}

export interface sectionLinked {
    id: number,
    title: string
}

export interface imageContent {
    image: string,
}

export interface galleryContent {
    id: number,
    image: string,
    title: string,
    sub: string,
    url: string,
    content_id: number,
    button: Array<buttonContent>
}

export interface listContent {
    id: number,
    image: string,
    title: string,
    sub: string,
    url: string,
    content_id: number,
    button: null | buttonContent
}

export interface userInputContent {
    id: number,
    question: string,
    attribute: {
        id: number,
        title: string
    },
    content_id: number,
    validation: number
}

export interface buttonContent {
    id: number,
    type: number,
    title: string,
    block: Array<sectionLinked>,
    url: string,
    phone: {
        countryCode: number,
        number: number | null
    }
}

export interface blockSuggestion {
    id: number,
    title: string,
    contents: [
        {
            id: number,
            title: string
        }
    ]
}

export interface facebookPages {
    id: number,
    access_token: string,
    name: string,
    image: string,
    connected: boolean,
    currentProject: boolean
}