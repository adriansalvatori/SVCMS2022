import cfwAjax                from '../../functions/cfwAjax';
import cfwUpdatePaymentMethod from '../../functions/cfwUpdatePaymentMethod';

/**
 * Ensure that paymentMethod is paymentMethod and not payment_method or pAyMeNtMeThOd
 * Because endpoint expects exactly paymentMethod.
 *
 * See: \Objectiv\Plugins\Checkout\Action\UpdatePaymentMethodAction
 */

jest.mock( '../../functions/cfwAjax' );
const { mock: { calls: ajaxCalls } } = cfwAjax as jest.MockedFunction<any>;
const getLastCallArgs                = () => ajaxCalls[ ajaxCalls.length - 1 ];

test( 'cfwUpdatePaymentMethod calls cfwAjax with correct data', () => {
    cfwUpdatePaymentMethod( 'my-cool-method' );

    const [ id, { data: { paymentMethod }, type } ]  = getLastCallArgs();

    expect( id ).toBe( 'update_payment_method' );
    expect( paymentMethod ).toBe( 'my-cool-method' );
    expect( type ).toBe( 'POST' );
} );
