// import React, { useState } from 'react';
// import { updateSetting }   from '../functions/SettingsFunctions';
// import ToggleRightLabel    from './ToggleRightLabel';
//
// type args = {
//     settingKey: string;
//     initialValue: 'yes'|'no';
// };
//
// export default function SettingToggle( { settingKey, initialValue }: args ): React.Component {
//     const [ toggleState, setToggleState ] = useState( initialValue === 'yes' );
//
//     const onChange = ( value: boolean ) => {
//         setToggleState( value );
//
//         updateSetting( settingKey, value ? 'yes' : 'no' )
//             .then( ( response ) => {
//                 if ( response.ok ) {
//                     return;
//                 }
//
//                 response.json().then( ( { message, data: { currentValue } } ) => {
//                     console.log( message );
//
//                     setToggleState( currentValue === 'yes' );
//                 } );
//             } );
//     };
//
//     return <ToggleRightLabel label="Activate CheckoutWC Templates" enabled={toggleState} onChange={onChange} />;
// }
