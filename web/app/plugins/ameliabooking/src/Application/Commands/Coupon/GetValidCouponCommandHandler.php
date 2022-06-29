<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Commands\Coupon;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Coupon\CouponApplicationService;
use AmeliaBooking\Application\Services\User\CustomerApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\CouponInvalidException;
use AmeliaBooking\Domain\Common\Exceptions\CouponUnknownException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\Customer;

/**
 * Class GetValidCouponCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Coupon
 */
class GetValidCouponCommandHandler extends CommandHandler
{
    public $mandatoryFields = [
        'code',
        'user',
        'id',
        'type'
    ];

    /**
     * @param GetValidCouponCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     */
    public function handle(GetValidCouponCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var CouponApplicationService $couponAS */
        $couponAS = $this->container->get('application.coupon.service');

        /** @var CustomerApplicationService $customerAS */
        $customerAS = $this->container->get('application.user.customer.service');

        $userData = $command->getField('user');

        /** @var Customer $user */
        $user = ($userData['firstName'] && $userData['lastName']) ?
            $customerAS->getNewOrExistingCustomer($command->getField('user'), $result) : null;

        if ($result->getResult() === CommandResult::RESULT_ERROR) {
            return $result;
        }

        try {
            /** @var Coupon $coupon */
            $coupon = $couponAS->processCoupon(
                $command->getField('code'),
                $command->getField('id'),
                $command->getField('type'),
                ($user && $user->getId()) ? $user->getId()->getValue() : null,
                true
            );

            $coupon->setServiceList(new Collection());
            $coupon->setEventList(new Collection());
        } catch (CouponUnknownException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage($e->getMessage());
            $result->setData([
                'couponUnknown' => true
            ]);

            return $result;
        } catch (CouponInvalidException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage($e->getMessage());
            $result->setData([
                'couponInvalid' => true
            ]);

            return $result;
        }

        if ($result->getResult() !== CommandResult::RESULT_ERROR) {
            $result->setResult(CommandResult::RESULT_SUCCESS);
            $result->setMessage('Successfully retrieved coupon.');
            $result->setData(
                [
                    Entities::COUPON => $coupon->toArray(),
                    'limit'          => $couponAS->getAllowedCouponLimit($coupon, $user)
                ]
            );
        }

        return $result;
    }
}
