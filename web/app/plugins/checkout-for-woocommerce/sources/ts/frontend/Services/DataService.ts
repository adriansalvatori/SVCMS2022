declare let cfwEventData: any;

class DataService {
    /**
     * @type {any}
     * @private
     */
    private static _checkoutForm: JQuery<HTMLElement>;

    static initRunTimeParams(): void {
        cfwEventData.runtime_params = {};
    }

    static getSettings(): Record<string, unknown> {
        return cfwEventData.settings;
    }

    static getSetting( setting: string ): any {
        if ( cfwEventData.settings[ setting ] ) {
            return cfwEventData.settings[ setting ];
        }

        return false;
    }

    static getMessage( messageKey: string ): string {
        if ( cfwEventData.messages[ messageKey ] ) {
            return cfwEventData.messages[ messageKey ];
        }

        return '';
    }

    static getCompatibilityClass( key: string ): CompatibilityClass {
        return cfwEventData.compatibility[ key ];
    }

    static getElement( element: string ): JQuery<HTMLElement> {
        if ( cfwEventData.elements[ element ] ) {
            return jQuery( cfwEventData.elements[ element ] );
        }

        return jQuery();
    }

    static getCheckoutParams(): Record<string, unknown> {
        return cfwEventData.checkout_params;
    }

    /**
   * @param param
   */
    static getCheckoutParam( param: string ): string | boolean | null {
        if ( cfwEventData.checkout_params[ param ] ) {
            return cfwEventData.checkout_params[ param ];
        }

        return null;
    }

    static getRuntimeParameters(): Record<string, unknown> {
        return cfwEventData.runtime_params;
    }

    static getRuntimeParameter( param: string ): any {
        if ( cfwEventData.runtime_params[ param ] ) {
            return cfwEventData.runtime_params[ param ];
        }

        return null;
    }

    static setRuntimeParameter( param: string, value: any ): void {
        cfwEventData.runtime_params[ param ] = value;
    }

    /**
     * @returns {JQuery}
     */
    static get checkoutForm(): JQuery {
        return DataService._checkoutForm;
    }

    /**
     * @param {JQuery} value
     */
    static set checkoutForm( value: JQuery ) {
        this._checkoutForm = value;
    }
}

export default DataService;
