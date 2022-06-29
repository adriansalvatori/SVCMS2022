<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Factory\Booking\Appointment;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageCustomerServiceFactory;
use AmeliaBooking\Domain\Factory\Booking\Event\CustomerBookingEventTicketFactory;
use AmeliaBooking\Domain\Factory\Coupon\CouponFactory;
use AmeliaBooking\Domain\Factory\Payment\PaymentFactory;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\IntegerValue;
use AmeliaBooking\Domain\ValueObjects\String\Token;

/**
 * Class CustomerBookingFactory
 *
 * @package AmeliaBooking\Domain\Factory\Booking\Appointment
 */
class CustomerBookingFactory
{

    /**
     * @param $data
     *
     * @return CustomerBooking
     * @throws InvalidArgumentException
     */
    public static function create($data)
    {
        $customerBooking = new CustomerBooking(
            new Id($data['customerId']),
            new BookingStatus($data['status']),
            new IntegerValue($data['persons'])
        );

        if (isset($data['id'])) {
            $customerBooking->setId(new Id($data['id']));
        }

        if (isset($data['price'])) {
            $customerBooking->setPrice(new Price($data['price']));
        }

        if (isset($data['appointmentId'])) {
            $customerBooking->setAppointmentId(new Id($data['appointmentId']));
        }

        if (isset($data['couponId'])) {
            $customerBooking->setCouponId(new Id($data['couponId']));
        }

        if (isset($data['coupon'])) {
            $customerBooking->setCoupon(CouponFactory::create($data['coupon']));
        }

        if (isset($data['customer'])) {
            $customerBooking->setCustomer(UserFactory::create($data['customer']));
        }

        if (isset($data['customFields'])) {
            $customerBooking->setCustomFields(new Json($data['customFields']));
        }

        if (isset($data['info'])) {
            $customerBooking->setInfo(new Json($data['info']));
        }

        if (isset($data['utcOffset'])) {
            $customerBooking->setUtcOffset(new IntegerValue($data['utcOffset']));
        }

        if (isset($data['aggregatedPrice'])) {
            $customerBooking->setAggregatedPrice(new BooleanValueObject($data['aggregatedPrice']));
        }

        if (isset($data['isChangedStatus'])) {
            $customerBooking->setChangedStatus(new BooleanValueObject($data['isChangedStatus']));
        }

        if (isset($data['deposit'])) {
            $customerBooking->setDeposit(new BooleanValueObject($data['deposit']));
        }

        if (isset($data['packageCustomerService'])) {
            /** @var PackageCustomerService $packageCustomerService */
            $packageCustomerService = PackageCustomerServiceFactory::create($data['packageCustomerService']);

            $customerBooking->setPackageCustomerService($packageCustomerService);
        }

        $payments = new Collection();

        if (isset($data['payments'])) {
            foreach ((array)$data['payments'] as $key => $value) {
                $payments->addItem(
                    PaymentFactory::create($value),
                    $key
                );
            }
        }

        $customerBooking->setPayments($payments);

        $extras = new Collection();

        if (isset($data['extras'])) {
            foreach ((array)$data['extras'] as $key => $value) {
                $extras->addItem(
                    CustomerBookingExtraFactory::create($value),
                    $key
                );
            }
        }

        $customerBooking->setExtras($extras);

        if (isset($data['token'])) {
            $customerBooking->setToken(new Token($data['token']));
        }

        $ticketsBooking = new Collection();

        if (!empty($data['ticketsData'])) {
            foreach ((array)$data['ticketsData'] as $key => $value) {
                $ticketsBooking->addItem(
                    CustomerBookingEventTicketFactory::create($value),
                    $key
                );
            }
        }

        $customerBooking->setTicketsBooking($ticketsBooking);

        return $customerBooking;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public static function reformat($rows)
    {
        $data = [];

        foreach ($rows as $row) {
            $id = $row['booking_id'];

            $customerId = !empty($row['customer_id']) ? $row['customer_id'] : null;

            $paymentId = !empty($row['payment_id']) ? $row['payment_id'] : null;

            $couponId = !empty($row['coupon_id']) ? $row['coupon_id'] : null;

            $bookingEventTicketId = !empty($row['booking_ticket_id']) ? $row['booking_ticket_id'] : null;

            if ($id && empty($data[$id])) {
                $data[$id] = [
                    'id'              => $id,
                    'appointmentId'   => $row['booking_appointmentId'],
                    'customerId'      => $row['booking_customerId'],
                    'status'          => $row['booking_status'],
                    'price'           => $row['booking_price'],
                    'persons'         => $row['booking_persons'],
                    'couponId'        => $row['booking_couponId'],
                    'customFields'    => !empty($row['booking_customFields']) ? $row['booking_customFields'] : null,
                    'info'            => !empty($row['booking_info']) ? $row['booking_info'] : null,
                    'utcOffset'       => $row['booking_utcOffset'],
                    'aggregatedPrice' => $row['booking_aggregatedPrice'],
                ];
            }

            if ($data[$id] && $customerId && empty($data[$id]['customer'])) {
                $data[$id]['customer'] = [
                    'id'        => $customerId,
                    'firstName' => $row['customer_firstName'],
                    'lastName'  => $row['customer_lastName'],
                    'email'     => $row['customer_email'],
                    'note'      => $row['customer_note'],
                    'phone'     => $row['customer_phone'],
                    'gender'    => $row['customer_gender'],
                    'birthday'  => $row['customer_birthday'],
                ];
            }

            if ($data[$id] && $paymentId && empty($data[$id]['payments'][$paymentId])) {
                $data[$id]['payments'][$paymentId] = [
                    'id'                => $paymentId,
                    'customerBookingId' => $id,
                    'amount'            => $row['payment_amount'],
                    'dateTime'          => $row['payment_dateTime'],
                    'status'            => $row['payment_status'],
                    'gateway'           => $row['payment_gateway'],
                    'gatewayTitle'      => $row['payment_gatewayTitle'],
                    'data'              => $row['payment_data'],
                    'wcOrderId'         => !empty($row['payment_wcOrderId']) ? $row['payment_wcOrderId'] : null,
                ];
            }

            if ($data[$id] && $couponId && empty($data[$id]['coupon'])) {
                $data[$id]['coupon'] = [
                    'id'            => $couponId,
                    'code'          => $row['coupon_code'],
                    'discount'      => $row['coupon_discount'],
                    'deduction'     => $row['coupon_deduction'],
                    'limit'         => $row['coupon_limit'],
                    'customerLimit' => $row['coupon_customerLimit'],
                    'status'        => $row['coupon_status'],
                ];
            }

            if ($data[$id] && $bookingEventTicketId && empty($data[$id]['ticketsData'][$bookingEventTicketId])) {
                $data[$id]['ticketsData'][$bookingEventTicketId] = [
                    'id'                => $bookingEventTicketId,
                    'eventTicketId'     => $row['booking_ticket_eventTicketId'],
                    'customerBookingId' => $id,
                    'persons'           => $row['booking_ticket_persons'],
                    'price'             => $row['booking_ticket_price'],
                ];
            }
        }

        return $data;
    }
}
