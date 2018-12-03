import ChatBlockSectionModel from "../models/ChatBlockSectionModel";
import TextContentModel from "../models/bots/TextContentModel";
import TypingContentModel from "../models/bots/TypingContentModel";
import QuickReplyModel from "../models/bots/QuickReplyContentModel";
import ListContentModel from "../models/bots/ListContentModel";
import ImageContentModel from "../models/bots/ImageContentModel";
import GalleryContentModel from "../models/bots/GalleryContentModel";
import UserInputContentModel from "../models/bots/UserInputContentModel";

export interface ChatBlock {
    id: number,
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
    block: number
}

export interface galleryContent {

}

export interface textContent {
    content: string,
    button: Array<buttonContent>
}

export interface typingContent {
    duration: number
}

export interface quickReplyContent {

}

export interface imageContent {

}

export interface galleryContent {
    image: string,
    heading: string,
    desc: string,
    url: string,
    button: Array<buttonContent>
}

export interface listContent {
    id: number,
    image: string,
    title: string,
    sub: string,
    url: string,
    content_id: number,
    button: buttonContent
}

export interface userInputContent {

}

export interface buttonContent {
    type: number,
    content: string,
    block: Array<number>,
    url: string,
    phone: {
        countryCode: number,
        number: number
    }
}