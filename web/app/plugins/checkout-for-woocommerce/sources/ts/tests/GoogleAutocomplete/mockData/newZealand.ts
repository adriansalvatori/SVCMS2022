function newZealandAddress(): string {
    return '147 Quay Street, Auckland CBD, Auckland 1010, New Zealand';
}

function newZealandComponents(): google.maps.GeocoderAddressComponent[] {
    return <google.maps.GeocoderAddressComponent[]>[
        {
            long_name: '147',
            short_name: '147',
            types: [ 'street_number' ],
        },
        {
            long_name: 'Quay Street',
            short_name: 'Quay St',
            types: [ 'route' ],
        },
        {
            long_name: 'Auckland CBD',
            short_name: 'Auckland CBD',
            types: [ 'sublocality_level_1', 'sublocality', 'political' ],
        },
        {
            long_name: 'Auckland',
            short_name: 'Auckland',
            types: [ 'locality', 'political' ],
        },
        {
            long_name: 'Auckland',
            short_name: 'Auckland',
            types: [ 'administrative_area_level_1', 'political' ],
        },
        {
            long_name: 'New Zealand',
            short_name: 'NZ',
            types: [ 'country', 'political' ],
        },
        {
            long_name: '1010',
            short_name: '1010',
            types: [ 'postal_code' ],
        },
    ];
}

export { newZealandAddress, newZealandComponents };
