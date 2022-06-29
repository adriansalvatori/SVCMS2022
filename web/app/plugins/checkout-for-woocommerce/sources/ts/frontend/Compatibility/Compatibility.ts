import LoggingService from '../Services/LoggingService';

declare let cfwEventData: any;

abstract class Compatibility {
  protected key;

  /**
   * @param key Unique string matching localized json from server.
   */
  protected constructor( key: string ) {
      this.key = key;
  }

  /**
   * Literally anything function. Runs user code.
   *
   * @param {any} params Params for the child class to run on load
   */
  public abstract load( params ): void;

  /**
   * Dynamic Load
   *
   * Given an array of active class objects { class: name, params: ... },
   * init and load this compatibility class if found in active array.
   * It is assumed a class could not have more than one instance inside the activeClasses array.
   */
  public maybeLoad() {
      Object.values( cfwEventData.compatibility ).filter( ( { class: className } ) => this.key === className ).forEach( ( { params } ) => {
          this.load( params );

          LoggingService.log( `Loaded ${this.key} module. 🧩` );
      } );
  }
}

export default Compatibility;
