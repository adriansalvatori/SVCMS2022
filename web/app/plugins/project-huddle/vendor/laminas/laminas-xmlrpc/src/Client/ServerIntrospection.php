<?php

namespace ProjectHuddle\Vendor\Laminas\XmlRpc\Client;

use ProjectHuddle\Vendor\Laminas\XmlRpc\Client as XMLRPCClient;

use function count;
use function gettype;
use function is_array;

/**
 * Wraps the XML-RPC system.* introspection methods
 */
class ServerIntrospection
{
    private ServerProxy $system;

    public function __construct(XMLRPCClient $client)
    {
        $this->system = $client->getProxy('system');
    }

    /**
     * Returns the signature for each method on the server,
     * autodetecting whether system.multicall() is supported and
     * using it if so.
     *
     * @return array
     */
    public function getSignatureForEachMethod()
    {
        $methods = $this->listMethods();

        try {
            $signatures = $this->getSignatureForEachMethodByMulticall($methods);
        } catch (Exception\FaultException $e) {
            // degrade to looping
        }

        if (empty($signatures)) {
            $signatures = $this->getSignatureForEachMethodByLooping($methods);
        }

        return $signatures;
    }

    /**
     * Attempt to get the method signatures in one request via system.multicall().
     * This is a boxcar feature of XML-RPC and is found on fewer servers.  However,
     * can significantly improve performance if present.
     *
     * @param  array $methods
     * @throws Exception\IntrospectException
     * @return array array(array(return, param, param, param...))
     */
    public function getSignatureForEachMethodByMulticall($methods = null)
    {
        if ($methods === null) {
            $methods = $this->listMethods();
        }

        $multicallParams = [];
        foreach ($methods as $method) {
            $multicallParams[] = [
                'methodName' => 'system.methodSignature',
                'params'     => [$method],
            ];
        }

        $serverSignatures = $this->system->multicall($multicallParams);

        if (! is_array($serverSignatures)) {
            $type  = gettype($serverSignatures);
            $error = "Multicall return is malformed.  Expected array, got $type";
            throw new Exception\IntrospectException($error);
        }

        if (count($serverSignatures) !== count($methods)) {
            $error = 'Bad number of signatures received from multicall';
            throw new Exception\IntrospectException($error);
        }

        // Create a new signatures array with the methods name as keys and the signature as value
        $signatures = [];
        foreach ($serverSignatures as $i => $signature) {
            $signatures[$methods[$i]] = $signature;
        }

        return $signatures;
    }

    /**
     * Get the method signatures for every method by
     * successively calling system.methodSignature
     *
     * @param array $methods
     * @return array
     */
    public function getSignatureForEachMethodByLooping($methods = null)
    {
        if ($methods === null) {
            $methods = $this->listMethods();
        }

        $signatures = [];
        foreach ($methods as $method) {
            $signatures[$method] = $this->getMethodSignature($method);
        }

        return $signatures;
    }

    /**
     * Call system.methodSignature() for the given method
     *
     * @param  array  $method
     * @throws Exception\IntrospectException
     * @return array  array(array(return, param, param, param...))
     */
    public function getMethodSignature($method)
    {
        $signature = $this->system->methodSignature($method);
        if (! is_array($signature)) {
            $error = 'Invalid signature for method "' . $method . '"';
            throw new Exception\IntrospectException($error);
        }
        return $signature;
    }

    /**
     * Call system.listMethods()
     *
     * @return array  array(method, method, method...)
     */
    public function listMethods()
    {
        return $this->system->listMethods();
    }
}
