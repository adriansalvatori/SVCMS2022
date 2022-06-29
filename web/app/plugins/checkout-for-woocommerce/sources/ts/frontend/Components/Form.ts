class Form {
    constructor() {
        this.disableEnterSubmit();
    }

    disableEnterSubmit(): void {
        jQuery( document ).on( 'keydown', ':input:not(textarea):not(:submit)', ( event ) => event.key != 'Enter' );
    }
}

export default Form;
