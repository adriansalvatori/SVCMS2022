function canadaAddress(): string {
    return '145 Richmond St W, Toronto, ON M5H 2L2, Canada';
}

function canadaComponents(): google.maps.GeocoderAddressComponent[] {
    return <google.maps.GeocoderAddressComponent[]>[
        {
            long_name: '145',
            short_name: '145',
            types: [ 'street_number' ],
        },
        {
            long_name: 'Richmond Street West',
            short_name: 'Richmond St W',
            types: [ 'route' ],
        },
        {
            long_name: 'Old Toronto',
            short_name: 'Old Toronto',
            types: [ 'sublocality_level_1', 'sublocality', 'political' ],
        },
        {
            long_name: 'Toronto',
            short_name: 'Toronto',
            types: [ 'locality', 'political' ],
        },
        {
            long_name: 'Toronto',
            short_name: 'Toronto',
            types: [ 'administrative_area_level_2', 'political' ],
        },
        {
            long_name: 'Ontario',
            short_name: 'ON',
            types: [ 'administrative_area_level_1', 'political' ],
        },
        {
            long_name: 'Canada',
            short_name: 'CA',
            types: [ 'country', 'political' ],
        },
        {
            long_name: 'M5H 2L2',
            short_name: 'M5H 2L2',
            types: [ 'postal_code' ],
        },
    ];
}

export { canadaAddress, canadaComponents };
