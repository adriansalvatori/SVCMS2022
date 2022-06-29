<?php

namespace AmeliaBooking\Application\Commands\Outlook;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;
use Interop\Container\Exception\ContainerException;

/**
 * Class GetOutlookAuthURLCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Outlook
 */
class GetOutlookAuthURLCommandHandler extends CommandHandler
{
    /**
     * @param GetOutlookAuthURLCommand $command
     *
     * @return CommandResult
     * @throws ContainerException
     */
    public function handle(GetOutlookAuthURLCommand $command)
    {
        $result = new CommandResult();

        /** @var OutlookCalendarService $outlookCalendarService */
        $outlookCalendarService = $this->container->get('infrastructure.outlook.calendar.service');

        $authUrl = $outlookCalendarService->createAuthUrl((int)$command->getField('id'));

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved outlook authorization URL');
        $result->setData([
            'authUrl' => filter_var($authUrl, FILTER_SANITIZE_URL)
        ]);

        return $result;
    }
}
