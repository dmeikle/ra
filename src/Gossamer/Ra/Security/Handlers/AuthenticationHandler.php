<?php

/*
 *  This file is part of the Quantum Unit Solutions development package.
 *
 *  (c) Quantum Unit Solutions <http://github.com/dmeikle/>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace core\components\security\handlers;

use core\services\ServiceInterface;
use core\datasources\DatasourceAware;
use Gossamer\Caching\CacheManager;
use Monolog\Logger;
use libraries\utils\Container;
use libraries\utils\YAMLParser;
use libraries\utils\URISectionComparator;

/**
 * this class handles all authentication when a user logs in. No need to create
 * a function inside a controller (don't waste time looking for it there, like
 * I sometimes forget a year later.. haha).
 *
 * Configuration is handled in the security.yml file
 * step 1:
 * create a manager that will be called during startup by the services manager.
 * This configuration is stored in the services.yml file.
 * eg:

  authentication_manager:
  handler: 'core\components\security\core\AuthenticationManager'
  'arguments':
  - '@user_authentication_provider'
  #the '@' sign means it's a service already configured.
  #no '@' sign means you specify the relative path to the file to load

 * step 2:
 * create a provider that can be passed into the manager to do the work.
 * Different providers can be directed to perform differently based on
 * yml file configuration.
 * eg:

  user_authentication_provider:
  handler: 'core\components\security\providers\UserAuthenticationProvider'
  datasource: datasource3
 *
 * the services manager will create the UserAuthenticationProvider and pass in
 * the datasource specified by yml key. Then it will create the
 * AuthenticationManager and pass the provider into it. The work is done by
 * the provider (which database, which checks to perform) - the manager just
 * orchestrates the calls.
 *
 * step 3:
 * create a reference that will define the handler to use the manager and the
 * provider inside the services.yml file
 *
  simple_auth:
  'handler': 'core\components\security\handlers\AuthenticationHandler'
  #3 is the local db conn wrapped in a connection adapter
  'datasource': 'datasource3'
  'arguments':
  security_context: '@security_context'
  authentication_manager: '@authentication_manager'
 *
 * step 4:
 * create the rule that calls all of this in firewall.yml :
 *
 * admin_area:
  pattern: /admin
  authentication: simple_auth
  fail_url: admin/login
 *
 *
 * in a nutshell:
 * 1.   create a provider and specify any passed in objects
 * 2.   create a manager and specify any passed in objects, including the provider
 *      to use in this context
 * 3.   create a handler and specify the manager to use
 * 4.   create a firewall reference and tell it which handler to call when the
 *      matching URI pattern occurs
 *
 * @author Dave Meikle
 */
class AuthenticationHandler extends DatasourceAware implements ServiceInterface {

    private $securityContext = null;
    private $securityManager = null;
    private $logger = null;
    private $container = null;
    private $node = null;

    const FIREWALL_CACHE_KEY = 'FIREWALL_RULES';
    /**
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->loadNodeConfig();
    }

    /**
     * accessor
     *
     * @param Container $container
     */
    public function setContainer(Container $container) {
        $this->container = $container;
    }

    /**
     * main method called. calls the provider and gets the provider to
     * authenticate the user
     *
     * @return type
     */
    public function execute() {
        $this->container->set('securityContext', $this->securityContext);

        if (is_null($this->node) || !array_key_exists('authentication', $this->node)) {
            return;
        }
        if (array_key_exists('security', $this->node) && (!$this->node['security'] || $this->node['security'] == 'false')) {
            error_log('security element null or not found in node');
            return;
        }
       
        $token = $this->getToken();
        
        try {

            $this->securityManager->authenticate($this->securityContext);
        } catch (\Exception $e) {


            if(array_key_exists('fail_url', $this->node)) {
                header('Location: ' . $this->getSiteURL() . $this->node['fail_url']);
            } else{
                echo json_encode(array('message' => $e->getMessage(), 'code' => $e->getCode()));
            }


            die();
        }

        //this is handled in the UserLoginManager
        //$this->container->set('securityContext', $this->securityContext);
    }

    /**
     * accessor
     *
     * @return string
     */
    private function getSiteURL() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'] . '/';

        return $protocol . $domainName;
    }

    /**
     * loads the firewall configuration
     *
     * @return empty|array
     */
    private function loadNodeConfig() {

        $loader = new YAMLParser($this->logger);
        $loader->setFilePath(__SITE_PATH . '/app/config/firewall.yml');
        $config = $loader->loadConfig();
        unset($loader);
//        $manager = new CacheManager($this->logger);
//
//        $config = $manager->retrieveFromCache(self::FIREWALL_CACHE_KEY);
//        if($config === false) {
//            $config = $this->createCachedFirewallRules();
//        }
        $parser = new URISectionComparator();
        $key = $parser->findPattern($config, __URI);
        unset($parser);

        if (empty($key)) {
            return;
        }

        $this->node = $config[$key];
    }

    private function createCachedFirewallRules() {
        $loader = new YAMLParser($this->logger);
        $loader->setFilePath(__SITE_PATH . '/app/config/firewall.yml');
        $retval = $loader->loadConfig();
        $subdirectories = getDirectoryList();
        $componentFirewalls = array();

        foreach ($subdirectories as $folder) {

            $loader->setFilePath($folder . '/config/firewall.yml');
         
            $config = $loader->loadConfig();

            if (is_array($config)) {
                $retval[] = $config;
            }
        }

        unset($loader);
        $manager = new CacheManager($this->logger);

        $manager->saveToCache(self::FIREWALL_CACHE_KEY, $retval);

        return $retval;
    }

    /**
     * accessor
     *
     * @param array $params
     */
    public function setParameters(array $params) {

        $this->securityContext = $params['security_context'];
        $this->securityManager = $params['authentication_manager'];
    }

    /**
     * accessor
     *
     * @return SecurityToken
     */
    protected function getToken() {

        return $this->securityManager->generateEmptyToken();
    }

}
