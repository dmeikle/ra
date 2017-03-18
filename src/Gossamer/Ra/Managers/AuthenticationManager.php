<?php
/*
 *  This file is part of the Quantum Unit Solutions development package.
 *
 *  (c) Quantum Unit Solutions <http://github.com/dmeikle/>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: user
 * Date: 3/9/2017
 * Time: 12:18 AM
 */

namespace Gossamer\Ra\Managers;

use Gossamer\Neith\Logging\LoggingInterface;
use Gossamer\Horus\Http\HttpInterface;
use Gossamer\Ra\Exceptions\ClientCredentialsNotFoundException;
use Gossamer\Ra\Security\SecurityContextInterface;
use Gossamer\Ra\Security\SecurityToken;


/**
 * Class AuthenticationManager
 * @package Gossamer\Ra\Managers
 *
 * This class is the abstract authentication class. It is intended for handling all
 * requests that require authentication before proceeding. A provider will be
 * passed in which does the work unique to the context of the request
 * eg: retrieving member information without the manager being aware of where the
 * information came from.
 * The manager then determines the validity of the requesting source based on
 * information provided by the provider
 */
abstract class AuthenticationManager
{

    use \Gossamer\Set\Utils\ContainerTrait;

    protected $logger;
    
    protected $userAuthenticationProvider = null;
    
    protected $container;
    
    protected $node;

    protected $request;

    public function __construct(LoggingInterface $logger, HttpInterface $request) {
        $this->logger = $logger;
        $this->request = $request;
    }



    /**
     * authenticates a user based on their context
     *
     * @param \core\components\security\core\SecurityContextInterface $context
     *
     * @throws ClientCredentialsNotFoundException
     */
    public function authenticate(SecurityContextInterface &$context) {

        $token = $this->generateEmptyToken($this->getSession());

        try {
            $this->userAuthenticationProvider->loadClientByCredentials($token->getClient()->getCredentials());
        } catch (ClientCredentialsNotFoundException $e) {

            $this->logger->addAlert('Client not found ' . $e->getMessage());
            throw $e;
        }

        //validate the client, if good then add to the context
        if (true) {
            $context->setToken($token);
        }
    }


    /**
     * accessor for passing in array of params
     *
     * @param array $params
     *
     * @throws ArgumentNotPassedException
     */
    public function setParameters(array $params) {

        if (!array_key_exists('authentication_provider', $params)) {
            throw new ArgumentNotPassedException('authentication_provider not specified in config');
        }

        $this->userAuthenticationProvider = $params['authentication_provider'];
    }


    /**
     * generates a default token
     *
     * @return SecurityToken
     */
    public function generateEmptyToken($session) {

        $token = unserialize($session('_security_secured_area'));

        if (!$token) {
            return $this->generateNewToken();
        }

        return $token;
    }


    /**
     * generates a new token based on current client
     *
     * @return SecurityToken
     */
    public function generateNewToken() {
        $client = $this->getClient();
        $nodeConfig = $this->container->get('nodeConfig');

        $token = new SecurityToken($client, $nodeConfig['ymlkey'], $client->getRoles());

        return $token;
    }

    /**
     *
     * @return \Gossamer\Ra\Security\Client
     */
    public abstract function getClient();
    
    /**
     * retrieves a list of credentials (IS_ADMINISTRATOR|IS_ANONYMOUS...)
     *
     * @return array(credentials)|null
     */
    protected abstract function getClientHeaderCredentials();

    /**
     * @return mixed
     */
    protected abstract function getSession();
}