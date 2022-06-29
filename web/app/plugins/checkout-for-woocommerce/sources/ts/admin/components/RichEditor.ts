declare let wp: any;
declare let _: any;

class RichEditor {
    private instance: any;

    constructor( targetSelector: string, mode: string = null ) {
        let editorMode = mode;

        if ( editorMode === 'php' ) {
            editorMode = 'application/x-httpd-php-open';
        }

        const element = jQuery( targetSelector );

        if ( element.length ) {
            const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};

            editorSettings.codemirror = _.extend(
                {},
                editorSettings.codemirror,
                {
                    indentUnit: 2,
                    tabSize: 2,
                    mode: editorMode,
                },
            );

            this.instance = wp.codeEditor.initialize( element, editorSettings );

            if ( mode === 'php' ) {
                // We have to do this since WP doesn't configure application/x-httpd-php-open
                this.instance.codemirror.on( 'keyup', () => { // eslint-disable-line complexity
                    const token =  this.instance.codemirror.getTokenAt(  this.instance.codemirror.getCursor() );

                    if ( token.type === 'string' || token.type === 'comment' ) {
                        return;
                    }

                    const shouldAutocomplete = token.type === 'keyword' || token.type === 'variable';

                    if ( shouldAutocomplete ) {
                        this.instance.codemirror.showHint( { completeSingle: false } );
                    }
                } );
            }
        }
    }
}

export default RichEditor;
