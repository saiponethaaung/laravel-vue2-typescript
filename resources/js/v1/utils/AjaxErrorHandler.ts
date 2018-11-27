export default class AjaxErrorHandler {
    globalHandler (err: any, mesg: string) {
        if(undefined===mesg) {
            return "Operation failed!";
        }

        if(err.response.status===422) {
            return this.handle422(err, mesg);
        }

        if(err.response.status===404) {
            return this.handle404(err, mesg);
        }

        return mesg;
    }

    handle422 (err: any, mesg: string) {
        if(err.response.data && err.response.data.mesg) {
            return err.response.data.mesg;
        }
        return mesg;
    }

    handle404 (err: any, mesg: string) {
        if(err.response.data && err.response.data.mesg) {
            return err.response.data.mesg;
        }
        return mesg;
    }
}