function spainAddress(): string {
    return 'Carrer Benidorm, 3, 03710 Calp, Alicante, Spain';
}

function spainComponents(): google.maps.GeocoderAddressComponent[] {
    return <google.maps.GeocoderAddressComponent[]>[
        {
            long_name: '3',
            short_name: '3',
            types: [ 'street_number' ],
        },
        {
            long_name: 'Carrer Benidorm',
            short_name: 'Carrer Benidorm',
            types: [ 'route' ],
        },
        {
            long_name: 'Calp',
            short_name: 'Calp',
            types: [ 'locality', 'political' ],
        },
        {
            long_name: 'Alicante',
            short_name: 'A',
            types: [ 'administrative_area_level_2', 'political' ],
        },
        {
            long_name: 'Comunidad Valenciana',
            short_name: 'VC',
            types: [ 'administrative_area_level_1', 'political' ],
        },
        {
            long_name: 'Spain',
            short_name: 'ES',
            types: [ 'country', 'political' ],
        },
        {
            long_name: '03710',
            short_name: '03710',
            types: [ 'postal_code' ],
        },
    ];
}

export { spainAddress, spainComponents };
