import cfwAjax from './cfwAjax';

export default function cfwUpdatePaymentMethod( paymentMethod: string ): void {
    const params = {
        type: 'POST',
        data: {
            paymentMethod,
        },
    };

    cfwAjax( 'update_payment_method', params );
}
