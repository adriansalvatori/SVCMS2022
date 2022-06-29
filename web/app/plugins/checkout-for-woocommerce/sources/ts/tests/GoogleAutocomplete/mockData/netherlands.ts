function netherlandsAddress(): string {
    return 'Herengracht 341, 1016 AZ Amsterdam, Netherlands';
}

function netherlandsComponents(): google.maps.GeocoderAddressComponent[] {
    return <google.maps.GeocoderAddressComponent[]>[
        {
            long_name: '341',
            short_name: '341',
            types: [ 'street_number' ],
        },
        {
            long_name: 'Herengracht',
            short_name: 'Herengracht',
            types: [ 'route' ],
        },
        {
            long_name: 'Amsterdam-Centrum',
            short_name: 'Amsterdam-Centrum',
            types: [ 'sublocality_level_1', 'sublocality', 'political' ],
        },
        {
            long_name: 'Amsterdam',
            short_name: 'Amsterdam',
            types: [ 'locality', 'political' ],
        },
        {
            long_name: 'Amsterdam',
            short_name: 'Amsterdam',
            types: [ 'administrative_area_level_2', 'political' ],
        },
        {
            long_name: 'Noord-Holland',
            short_name: 'NH',
            types: [ 'administrative_area_level_1', 'political' ],
        },
        {
            long_name: 'Netherlands',
            short_name: 'NL',
            types: [ 'country', 'political' ],
        },
        {
            long_name: '1016 AZ',
            short_name: '1016 AZ',
            types: [ 'postal_code' ],
        },
    ];
}

export { netherlandsAddress, netherlandsComponents };
