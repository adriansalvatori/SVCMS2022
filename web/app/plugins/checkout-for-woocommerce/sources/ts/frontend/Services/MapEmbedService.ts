import DataService from './DataService';

declare let google: any;

class MapEmbedService {
    /**
     * Attach change events to postcode fields
     */
    setMapEmbedHandlers() {
        if ( DataService.getSetting( 'enable_map_embed' ) === true ) {
            this.initMap();
        }
    }

    initMap() {
        if ( jQuery( '#map' ).length == 0 || typeof google === 'undefined' ) {
            return;
        }

        const map = new google.maps.Map( document.getElementById( 'map' ), {
            center: { lat: -34.397, lng: 150.644 },
            zoom: 15,
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: false,
            rotateControl: false,
            fullscreenControl: false,
        } );

        const geocoder = new google.maps.Geocoder();

        geocoder.geocode( { address: DataService.getSetting( 'thank_you_shipping_address' ) }, ( results, status ) => {
            if ( status == google.maps.GeocoderStatus.OK ) {
                map.setCenter( results[ 0 ].geometry.location );

                const marker = new google.maps.Marker( {
                    map,
                    position: results[ 0 ].geometry.location,
                } );

                const parts = <any>results[ 0 ].address_components.reduce( ( parts, component ) => {
                    parts[ component.types[ 0 ] ] = component.long_name || '';

                    return parts;
                }, {} );

                const shipping_address_label = DataService.getMessage( 'shipping_address_label' );
                const city = parts.locality || parts.postal_town || parts.sublocality_level_1 || parts.administrative_area_level_2 || parts.administrative_area_level_3;
                const state = parts.administrative_area_level_1;
                let shipping_address = city;

                if ( state.length !== 0 ) {
                    shipping_address = `${shipping_address}, ${state}`;
                }

                const contentString = `<div id="info_window_content"><span class="small-text">${shipping_address_label}</span><br /><span class="emphasis">${shipping_address}</span></div>`;
                const infowindow = new google.maps.InfoWindow( {
                    content: contentString,
                } );

                infowindow.open( map, marker );
            } else {
                jQuery( '#map' ).hide();
            }
        } );
    }
}

export default MapEmbedService;
