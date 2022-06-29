<?php

namespace AmeliaBooking\Application\Commands\Outlook;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Outlook\OutlookCalendar;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Outlook\OutlookCalendarRepository;
use Interop\Container\Exception\ContainerException;

/**
 * Class DisconnectFromOutlookAccountCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Outlook
 */
class DisconnectFromOutlookAccountCommandHandler extends CommandHandler
{
    /**
     * @param DisconnectFromOutlookAccountCommand $command
     *
     * @return CommandResult
     * @throws AccessDeniedException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    public function handle(DisconnectFromOutlookAccountCommand $command)
    {

        /** @var UserApplicationService $userAS */
        $userAS = $this->getContainer()->get('application.user.service');

        if (!$this->getContainer()->getPermissionsService()->currentUserCanRead(Entities::EMPLOYEES)) {
            try {
                /** @var AbstractUser $user */
                $user = $userAS->authorization(
                    $command->getToken(),
                    Entities::PROVIDER
                );
            } catch (AuthorizationException $e) {
                $result = new CommandResult();
                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setData(
                    [
                        'reauthorize' => true
                    ]
                );

                return $result;
            }

            if ($userAS->isCustomer($user)) {
                throw new AccessDeniedException('You are not allowed');
            }
        }

        $result = new CommandResult();

        /** @var OutlookCalendarRepository $outlookCalendarRepository */
        $outlookCalendarRepository = $this->container->get('domain.outlook.calendar.repository');

        $outlookCalendar = $outlookCalendarRepository->getByProviderId($command->getArg('id'));

        if (!$outlookCalendar instanceof OutlookCalendar) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Unable to delete outlook calendar.');

            return $result;
        }

        if ($outlookCalendarRepository->delete($outlookCalendar->getId()->getValue())) {
            $result->setResult(CommandResult::RESULT_SUCCESS);
            $result->setMessage('Outlook calendar successfully deleted.');
        }

        return $result;
    }
}
