<?php

namespace AmeliaBooking\Application\Commands\Notification;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Notification\SMSAPIService;
use AmeliaBooking\Domain\Entity\Entities;
use Interop\Container\Exception\ContainerException;

/**
 * Class SendAmeliaSmsApiRequestCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Notification
 */
class SendAmeliaSmsApiRequestCommandHandler extends CommandHandler
{
    /**
     * @param SendAmeliaSmsApiRequestCommand $command
     *
     * @return CommandResult
     *
     * @throws ContainerException
     * @throws AccessDeniedException
     */
    public function handle(SendAmeliaSmsApiRequestCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::NOTIFICATIONS)) {
            throw new AccessDeniedException('You are not allowed to send test email');
        }

        $result = new CommandResult();

        /** @var SMSAPIService $smsApiService */
        $smsApiService = $this->getContainer()->get('application.smsApi.service');

        // Call method dynamically and pass data to the function. Method name is the request field.
        $apiResponse = $smsApiService->{$command->getField('action')}($command->getField('data'));

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Amelia SMS API request successful');
        $result->setData($apiResponse);

        return $result;
    }
}
