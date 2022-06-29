<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Commands\Coupon;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Coupon\CouponApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Coupon\CouponFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class UpdateCouponCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Coupon
 */
class UpdateCouponCommandHandler extends CommandHandler
{
    /** @var array */
    public $mandatoryFields = [
        'code',
        'discount',
        'deduction',
        'limit',
        'status',
        'services',
        'events'
    ];

    /**
     * @param UpdateCouponCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function handle(UpdateCouponCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::COUPONS)) {
            throw new AccessDeniedException('You are not allowed to update coupon.');
        }

        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');
        /** @var CouponApplicationService $couponAS */
        $couponAS = $this->container->get('application.coupon.service');
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $couponId = $command->getArg('id');

        /** @var Coupon $oldCoupon */
        $oldCoupon = $couponRepository->getById($couponId);

        /** @var Collection $allServices */
        $allServices = $serviceRepository->getAll();

        /** @var Collection $services */
        $services = new Collection();

        /** @var Service $service */
        foreach ($allServices->getItems() as $service) {
            if (in_array($service->getId()->getValue(), $command->getFields()['services'])) {
                $services->addItem($service, $service->getId()->getValue());
            }
        }

        /** @var Collection $allEvents */
        $allEvents = $eventRepository->getAll();

        /** @var Collection $events */
        $events = new Collection();

        /** @var Event $event */
        foreach ($allEvents->getItems() as $event) {
            if (in_array($event->getId()->getValue(), $command->getFields()['events'])) {
                $events->addItem($event, $event->getId()->getValue());
            }
        }

        $newCoupon = CouponFactory::create($command->getFields());

        $newCoupon->setServiceList($services);

        $newCoupon->setEventList($events);

        if (!($newCoupon instanceof Coupon)) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update coupon.');

            return $result;
        }

        $couponRepository->beginTransaction();

        if (!($couponId = $couponAS->update($oldCoupon, $newCoupon))) {
            $couponRepository->rollback();

            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update coupon.');

            return $result;
        }

        $couponRepository->commit();

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Coupon successfully updated.');
        $result->setData(
            [
                Entities::COUPON => $newCoupon->toArray(),
            ]
        );

        return $result;
    }
}
