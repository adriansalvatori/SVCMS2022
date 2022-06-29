<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Repository\Payment;

use AmeliaBooking\Domain\Repository\BaseRepositoryInterface;

/**
 * Interface PaymentRepositoryInterface
 *
 * @package AmeliaBooking\Domain\Repository\Payment
 */
interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param int $status
     *
     * @return
     */
    public function findByStatus($status);
}
