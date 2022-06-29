function unitedStatesAddress(): string {
    return '8525 Garland Rd, Dallas, TX 75218';
}

function unitedStatesComponents(): google.maps.GeocoderAddressComponent[] {
    return <google.maps.GeocoderAddressComponent[]>[
        {
            long_name: '8525',
            short_name: '8525',
            types: [ 'street_number' ],
        },
        {
            long_name: 'Garland Road',
            short_name: 'Garland Rd',
            types: [ 'route' ],
        },
        {
            long_name: 'Dallas',
            short_name: 'Dallas',
            types: [ 'locality', 'political' ],
        },
        {
            long_name: 'Dallas County',
            short_name: 'Dallas County',
            types: [ 'administrative_area_level_2', 'political' ],
        },
        {
            long_name: 'Texas',
            short_name: 'TX',
            types: [ 'administrative_area_level_1', 'political' ],
        },
        {
            long_name: 'United States',
            short_name: 'US',
            types: [ 'country', 'political' ],
        },
        {
            long_name: '75218',
            short_name: '75218',
            types: [ 'postal_code' ],
        },
    ];
}

export { unitedStatesAddress, unitedStatesComponents };
