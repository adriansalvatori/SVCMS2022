// eslint-disable-next-line camelcase
declare const objectiv_cfw_admin: {
    root: string,
    nonce: string,
};

function getHeaders() {
    const { nonce } = objectiv_cfw_admin;

    return {
        'X-WP-Nonce': nonce,
        'Content-Type': 'application/json',
    };
}

function getSettingEndpoint( key: string ): string {
    const { root } = objectiv_cfw_admin;

    return `${root}checkoutwc/v1/setting/${key}`;
}

function updateSetting( key: string, value: string ): ReturnType<typeof fetch> {
    const options: RequestInit = {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify( { setting_value: value } ),
    };

    return fetch( getSettingEndpoint( key ), options );
}

function getSetting( key: string ): ReturnType<typeof fetch> {
    const options: RequestInit = {
        method: 'GET',
        headers: getHeaders(),
    };

    return fetch( getSettingEndpoint( key ), options );
}

export { updateSetting, getSetting };
