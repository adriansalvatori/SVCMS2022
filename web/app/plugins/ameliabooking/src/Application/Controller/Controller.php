<?php

namespace AmeliaBooking\Application\Controller;

use AmeliaBooking\Application\Commands\Command;
use AmeliaBooking\Domain\Services\Permissions\PermissionsService;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Domain\Events\DomainEventBus;
use AmeliaBooking\Application\Commands\CommandResult;
use League\Tactician\CommandBus;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Controller
 *
 * @package AmeliaBooking\Application\Controller
 */
abstract class Controller
{
    const STATUS_OK = 200;
    const STATUS_REDIRECT = 302;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUNT = 404;
    const STATUS_CONFLICT = 409;
    const STATUS_INTERNAL_SERVER_ERROR = 500;

    /**
     * @var CommandBus
     */
    protected $commandBus;
    /**
     * @var DomainEventBus
     */
    protected $eventBus;

    /**
     * @var PermissionsService
     */
    protected $permissionsService;
    protected $allowedFields = ['ameliaNonce'];

    /**
     * Base Controller constructor.
     *
     * @param Container $container
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __construct(Container $container)
    {
        $this->commandBus = $container->getCommandBus();
        $this->eventBus = $container->getEventBus();
        $this->permissionsService = $container->getPermissionsService();
    }

    /**
     * @param Request $request
     * @param         $args
     *
     * @return mixed
     */
    abstract protected function instantiateCommand(Request $request, $args);

    /**
     * Emit a success domain event, do nothing by default
     *
     * @param DomainEventBus $eventBus
     *
     * @param CommandResult  $result
     *
     * @return null
     */
    protected function emitSuccessEvent(DomainEventBus $eventBus, CommandResult $result)
    {
        return null;
    }

    /**
     * Emit a failure domain event, do nothing by default
     *
     * @param DomainEventBus $eventBus
     *
     * @param CommandResult  $data
     *
     * @return null
     */
    protected function emitFailureEvent(DomainEventBus $eventBus, CommandResult $data)
    {
        return null;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var Command $command */
        $command = $this->instantiateCommand($request, $args);

        if (!$command->validateNonce($request)) {
            return $response->withStatus(self::STATUS_FORBIDDEN);
        }

        /** @var CommandResult $commandResult */
        $commandResult = $this->commandBus->handle($command);

        if ($commandResult->getUrl() !== null) {
            $this->emitSuccessEvent($this->eventBus, $commandResult);

            /** @var Response $response */
            $response = $response->withHeader('Location', $commandResult->getUrl());
            $response = $response->withStatus(self::STATUS_REDIRECT);

            return $response;
        }

        if ($commandResult->hasAttachment() === false) {
            $responseBody = [
                'message' => $commandResult->getMessage(),
                'data'    => $commandResult->getData()
            ];

            $this->emitSuccessEvent($this->eventBus, $commandResult);

            switch ($commandResult->getResult()) {
                case (CommandResult::RESULT_SUCCESS):
                    $response = $response->withStatus(self::STATUS_OK);

                    break;
                case (CommandResult::RESULT_CONFLICT):
                    $response = $response->withStatus(self::STATUS_CONFLICT);

                    break;
                default:
                    $response = $response->withStatus(self::STATUS_INTERNAL_SERVER_ERROR);

                    break;
            }

            /** @var Response $response */
            $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
            $response = $response->write(
                json_encode(
                    $commandResult->hasDataInResponse() ?
                        $responseBody : array_merge($responseBody, ['data' => []])
                )
            );
        }

        if (($file = $commandResult->getFile()) !== null) {
            /** @var Response $response */
            $response = $response->withHeader('Content-Type', $file['type']);
            $response = $response->withHeader('Content-Disposition', 'inline; filename=' . '"' . $file['name'] . '"');
            $response = $response->withHeader('Cache-Control', 'max-age=0');

            if (array_key_exists('size', $file)) {
                $response = $response->withHeader('Content-Length', $file['size']);
            }

            $response = $response->write($file['content']);
        }

        return $response;
    }

    /**
     * @param Command $command
     * @param         $requestBody
     */
    protected function setCommandFields($command, $requestBody)
    {
        foreach ($this->allowedFields as $field) {
            if (!isset($requestBody[$field])) {
                continue;
            }
            $command->setField($field, $requestBody[$field]);
        }
    }

    /**
     * @param mixed $params
     */
    protected function setArrayParams(&$params)
    {
        $names = ['categories', 'services', 'packages', 'employees', 'providers', 'providerIds', 'locations', 'events', 'dates', 'types', 'fields'];

        foreach ($names as $name) {
            if (!empty($params[$name])) {
                $params[$name] = is_array($params[$name]) ? $params[$name] : explode(',', $params[$name]);
            }
        }
    }
}
