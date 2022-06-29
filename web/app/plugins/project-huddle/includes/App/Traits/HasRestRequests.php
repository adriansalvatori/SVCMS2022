<?php

namespace PH\Traits;

use PH\Services\RestRequest;

if (!defined('ABSPATH')) {
    exit;
}

trait HasRestRequests
{
    public static function rest()
    {
        if (property_exists(get_called_class(), 'rest_endpoint')) {
            $endpoint = (new static())->getRestEndpoint();
        } else {
            $endpoint = strtolower((new \ReflectionClass(get_called_class()))->getShortName());
        }
        return new RestRequest($endpoint);
    }

    public function getRestEndpoint()
    {
        return $this->rest_endpoint;
    }
}
