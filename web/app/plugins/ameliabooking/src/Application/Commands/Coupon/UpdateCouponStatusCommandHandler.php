<?php

namespace AmeliaBooking\Application\Commands\Coupon;

use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Domain\Repository\Coupon\CouponRepositoryInterface;

/**
 * Class UpdateCouponStatusCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Coupon
 */
class UpdateCouponStatusCommandHandler extends CommandHandler
{

    /**
     * @var array
     */
    public $mandatoryFields = [
        'status',
    ];

    /**
     * @param UpdateCouponStatusCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(UpdateCouponStatusCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::LOCATIONS)) {
            throw new AccessDeniedException('You are not allowed to update coupon!');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var CouponRepositoryInterface $couponRepository */
        $couponRepository = $this->getContainer()->get('domain.coupon.repository');

        $couponRepository->updateStatusById(
            $command->getArg('id'),
            $command->getField('status')
        );

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully updated coupon');
        $result->setData(true);

        return $result;
    }
}
