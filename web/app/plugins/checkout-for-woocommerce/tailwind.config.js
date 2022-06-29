module.exports = {
    content: [
        './sources/ts/admin/**/*',
        './includes/**/*',
        './sources/**/*',
    ],
    theme: {
        extend: {
            colors: {
                blue: {
                    500: '#4e9ae0',
                    600: '#2372c5',
                },
                gray: {
                    100: '#f0f0f1',
                },
            },
        },
    },
    plugins: [
        require( '@tailwindcss/forms' ),
    ],
    corePlugins: {
        preflight: true,
    },
};
