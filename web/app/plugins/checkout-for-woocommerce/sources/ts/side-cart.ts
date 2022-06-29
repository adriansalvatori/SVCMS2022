// Fired from compatibility-classes.ts
import { cfwDomReady }             from './_functions';
import AddToCart                   from './frontend/Components/AddToCart';
import CartItemQuantityControl     from './frontend/Components/CartItemQuantityControl';
import SideCart                    from './frontend/Components/SideCart';

cfwDomReady( () => {
    new CartItemQuantityControl();
    new AddToCart();
    new SideCart();
} );
