// import { Switch } from '@headlessui/react';
// import React      from 'react';
//
// type args = {
//     label: string;
//     enabled: boolean;
//     onChange: ( value: boolean ) => void;
// }
//
// export default function ToggleRightLabel( { label, enabled, onChange }: args ): React.Component {
//     const switchBgColor   = enabled ? 'bg-green-600' : 'bg-gray-200';
//     const switchClassName = [
//         switchBgColor,
//         'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500',
//     ].join( ' ' );
//
//     const spanTranslate = enabled ? 'translate-x-5' : 'translate-x-0';
//
//     return (
//         <Switch.Group as="div" className="flex items-center">
//             <Switch checked={enabled} onChange={onChange} className={switchClassName} >
//                 <span aria-hidden="true" className={`${spanTranslate} pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200`} />
//             </Switch>
//             <Switch.Label as="span" className="ml-3">
//                 <span className="text-sm font-medium text-gray-900">{label}</span>
//             </Switch.Label>
//         </Switch.Group>
//     );
// }
