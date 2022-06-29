<?php

namespace AmeliaBooking\Application\Services\Reservation;

use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Infrastructure\Common\Container;
use InvalidArgumentException;

/**
 * Class ReservationService
 *
 * @package AmeliaBooking\Application\Services\Reservation
 */
class ReservationService
{
    /** @var Container $container */
    protected $container;

    /**
     * AbstractReservationService constructor.
     *
     * @param Container $container
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @return ReservationServiceInterface
     */
    public function get($type)
    {
        switch ($type) {
            case (Entities::APPOINTMENT):
                /** @var ReservationServiceInterface $reservationService */
                $reservationService = $this->container->get('application.reservation.appointment.service');

                return $reservationService;

            case (Entities::PACKAGE):
                /** @var ReservationServiceInterface $reservationService */
                $reservationService = $this->container->get('application.reservation.package.service');

                return $reservationService;

            case (Entities::EVENT):
                /** @var ReservationServiceInterface $reservationService */
                $reservationService = $this->container->get('application.reservation.event.service');

                return $reservationService;
        }

        return null;
    }
}
