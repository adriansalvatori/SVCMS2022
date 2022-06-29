<?php

namespace AmeliaBooking\Application\Services\Coupon;

use AmeliaBooking\Domain\Common\Exceptions\CouponInvalidException;
use AmeliaBooking\Domain\Common\Exceptions\CouponUnknownException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponEventRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponServiceRepository;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class CouponApplicationService
 *
 * @package AmeliaBooking\Application\Services\Coupon
 */
class CouponApplicationService
{
    private $container;

    /**
     * CouponApplicationService constructor.
     *
     * @param Container $container
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @param Coupon $coupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function add($coupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        /** @var CouponServiceRepository $couponServiceRepository */
        $couponServiceRepo = $this->container->get('domain.coupon.service.repository');

        /** @var CouponEventRepository $couponEventRepo */
        $couponEventRepo = $this->container->get('domain.coupon.event.repository');

        $couponId = $couponRepository->add($coupon);

        $coupon->setId(new Id($couponId));

        /** @var Service $service */
        foreach ($coupon->getServiceList()->getItems() as $service) {
            $couponServiceRepo->add($coupon, $service);
        }

        /** @var Event $event */
        foreach ($coupon->getEventList()->getItems() as $event) {
            $couponEventRepo->add($coupon, $event);
        }

        return $couponId;
    }

    /**
     * @param Coupon $oldCoupon
     * @param Coupon $newCoupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function update($oldCoupon, $newCoupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        /** @var CouponServiceRepository $couponServiceRepository */
        $couponServiceRepository = $this->container->get('domain.coupon.service.repository');

        /** @var CouponEventRepository $couponEventRepository */
        $couponEventRepository = $this->container->get('domain.coupon.event.repository');

        $couponRepository->update($oldCoupon->getId()->getValue(), $newCoupon);

        /** @var Service $newService */
        foreach ($newCoupon->getServiceList()->getItems() as $key => $newService) {
            if (!$oldCoupon->getServiceList()->keyExists($key)) {
                $couponServiceRepository->add($newCoupon, $newService);
            }
        }

        /** @var Service $oldService */
        foreach ($oldCoupon->getServiceList()->getItems() as $key => $oldService) {
            if (!$newCoupon->getServiceList()->keyExists($key)) {
                $couponServiceRepository->deleteForService($oldCoupon->getId()->getValue(), $key);
            }
        }


        /** @var Event $newEvent */
        foreach ($newCoupon->getEventList()->getItems() as $key => $newEvent) {
            if (!$oldCoupon->getEventList()->keyExists($key)) {
                $couponEventRepository->add($newCoupon, $newEvent);
            }
        }

        /** @var Event $oldEvent */
        foreach ($oldCoupon->getEventList()->getItems() as $key => $oldEvent) {
            if (!$newCoupon->getEventList()->keyExists($key)) {
                $couponEventRepository->deleteForEvent($oldCoupon->getId()->getValue(), $key);
            }
        }

        return true;
    }

    /**
     * @param Coupon $coupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function delete($coupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        /** @var CouponServiceRepository $couponServiceRepository */
        $couponServiceRepository = $this->container->get('domain.coupon.service.repository');

        /** @var CouponEventRepository $couponEventRepository */
        $couponEventRepository = $this->container->get('domain.coupon.event.repository');

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        return $couponServiceRepository->deleteByEntityId($coupon->getId()->getValue(), 'couponId') &&
            $couponEventRepository->deleteByEntityId($coupon->getId()->getValue(), 'couponId') &&
            $customerBookingRepository->updateByEntityId($coupon->getId()->getValue(), null, 'couponId') &&
            $couponRepository->delete($coupon->getId()->getValue());
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param string $couponCode
     * @param int    $entityId
     * @param string $entityType
     * @param int    $userId
     * @param bool   $inspectCoupon
     *
     * @return Coupon
     *
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws CouponUnknownException
     * @throws CouponInvalidException
     */
    public function processCoupon($couponCode, $entityId, $entityType, $userId, $inspectCoupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        $coupons = $couponRepository->getAllByCriteria(['code' => $couponCode]);

        /** @var Coupon $coupon */
        $coupon = $coupons->length() ? $coupons->getItem($coupons->keys()[0]) : null;

        $this->inspectCoupon($coupon, $entityId, $entityType, $userId, $inspectCoupon);

        return $coupon;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param Coupon $coupon
     * @param int    $entityId
     * @param string $entityType
     * @param int    $userId
     * @param bool   $inspectCoupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws CouponUnknownException
     * @throws CouponInvalidException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function inspectCoupon($coupon, $entityId, $entityType, $userId, $inspectCoupon)
    {
        switch ($entityType) {
            case Entities::APPOINTMENT:
                if (!$coupon || ($entityId !== null && !$coupon->getServiceList()->keyExists($entityId))) {
                    throw new CouponUnknownException(FrontendStrings::getCommonStrings()['coupon_unknown']);
                }

                break;
            case Entities::EVENT:
                if (!$coupon || ($entityId !== null && !$coupon->getEventList()->keyExists($entityId))) {
                    throw new CouponUnknownException(FrontendStrings::getCommonStrings()['coupon_unknown']);
                }

                break;
        }

        if ($inspectCoupon && ($coupon->getStatus()->getValue() === 'hidden' ||
                ($coupon && $coupon->getUsed()->getValue() >= $coupon->getLimit()->getValue())
        )) {
            throw new CouponInvalidException(FrontendStrings::getCommonStrings()['coupon_invalid']);
        }

        if ($inspectCoupon && $userId && $coupon->getCustomerLimit()->getValue() > 0 &&
            $this->getCustomerCouponUsedCount($coupon->getId()->getValue(), $userId) >=
            $coupon->getCustomerLimit()->getValue()
        ) {
            throw new CouponInvalidException(FrontendStrings::getCommonStrings()['coupon_invalid']);
        }

        return true;
    }

    /**
     * @param int $couponId
     * @param int $userId
     *
     * @return int
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getCustomerCouponUsedCount($couponId, $userId)
    {
        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');

        $customerAppointmentReservations = $appointmentRepo->getFiltered(
            [
                'customerId'      => $userId,
                'statuses'        => [BookingStatus::APPROVED, BookingStatus::PENDING],
                'bookingStatuses' => [BookingStatus::APPROVED, BookingStatus::PENDING],
                'bookingCouponId' => $couponId
            ]
        );

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        $customerEventReservations = $eventRepository->getFiltered(
            [
                'customerId'      => $userId,
                'bookingStatus'   => BookingStatus::APPROVED,
                'bookingCouponId' => $couponId
            ]
        );

        return $customerAppointmentReservations->length() + $customerEventReservations->length();
    }

    /**
     * @param Coupon       $coupon
     * @param AbstractUser $user
     *
     * @return int
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getAllowedCouponLimit($coupon, $user)
    {
        if ($coupon->getCustomerLimit()->getValue()) {
            $maxLimit = $coupon->getCustomerLimit()->getValue();

            $used = $user && $user->getId() ?
                $this->getCustomerCouponUsedCount($coupon->getId()->getValue(), $user->getId()->getValue()) : 0;
        } else {
            $maxLimit = $coupon->getLimit()->getValue();

            $used = $coupon->getUsed()->getValue();
        }

        return $maxLimit - $used;
    }
}
