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
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Coupon\CouponFactory;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;

/**
 * Class AddCouponCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Coupon
 */
class AddCouponCommandHandler extends CommandHandler
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
     * @param AddCouponCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(AddCouponCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::COUPONS)) {
            throw new AccessDeniedException('You are not allowed to add new coupon.');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        /** @var Collection $services */
        $services = $command->getFields()['services'] ? $serviceRepository->getByCriteria([
            'services' => $command->getFields()['services']
        ]) : new Collection();

        /** @var Coupon $coupon */
        $coupon = CouponFactory::create($command->getFields());

        $coupon->setServiceList($services);

        /** @var Collection $events */
        $events = $command->getFields()['events'] ? $eventRepository->getFiltered([
            'ids' => $command->getFields()['events']
        ]) : new Collection();

        $coupon->setEventList($events);

        if (!$coupon instanceof Coupon) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Unable to create coupon.');

            return $result;
        }

        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        /** @var CouponApplicationService $couponAS */
        $couponAS = $this->container->get('application.coupon.service');

        $couponRepository->beginTransaction();

        try {
            if (!($couponId = $couponAS->add($coupon))) {
                $couponRepository->rollback();
                return $result;
            }

            $coupon->setId(new Id($couponId));
        } catch (QueryExecutionException $e) {
            $couponRepository->rollback();
            throw $e;
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('New coupon successfully created.');
        $result->setData([
            Entities::COUPON => $coupon->toArray(),
        ]);

        $couponRepository->commit();

        return $result;
    }
}
