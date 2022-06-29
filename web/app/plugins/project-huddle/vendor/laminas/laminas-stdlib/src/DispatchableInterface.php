<?php

declare(strict_types=1);

namespace ProjectHuddle\Vendor\Laminas\Stdlib;

interface DispatchableInterface
{
    /**
     * Dispatch a request
     *
     * @return Response|mixed
     */
    public function dispatch(RequestInterface $request, ?ResponseInterface $response = null);
}
