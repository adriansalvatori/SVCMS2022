module.exports = {
    plugins: [
        require( 'postcss-rtlcss' )( {
            processKeyFrames: true,
        } ),
        require( 'autoprefixer' ),
    ],
};
