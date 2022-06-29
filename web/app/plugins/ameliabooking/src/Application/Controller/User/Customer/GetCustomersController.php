<?php

namespace AmeliaBooking\Application\Controller\User\Customer;

use AmeliaBooking\Application\Commands\User\Customer\GetCustomersCommand;
use AmeliaBooking\Application\Controller\Controller;
use Slim\Http\Request;

/**
 * Class GetCustomersController
 *
 * @package AmeliaBooking\Application\Controller\User\Customer
 */
class GetCustomersController extends Controller
{
    /**
     * Instantiates the Get Customers command to hand it over to the Command Handler
     *
     * @param Request $request
     * @param         $args
     *
     * @return GetCustomersCommand
     * @throws \RuntimeException
     */
    protected function instantiateCommand(Request $request, $args)
    {
        $command = new GetCustomersCommand($args);
        $command->setField('params', (array)$request->getQueryParams());
        $requestBody = $request->getParsedBody();
        $this->setCommandFields($command, $requestBody);
        $command->setToken($request);

        return $command;
    }
}
