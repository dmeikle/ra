<?php

/*
 *  This file is part of the Quantum Unit Solutions development package.
 * 
 *  (c) Quantum Unit Solutions <http://github.com/dmeikle/>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace tests\Gossamer\Horus\EventListeners;

use Gossamer\Horus\EventListeners\EventDispatcher;
use Gossamer\Horus\Core\Request;

/**
 * EventDispatcherTest
 *
 * @author Dave Meikle
 */
class EventDispatcherTest extends \tests\BaseTest {
    
    public function testAddListener() {
        $request = $this->getRequest();
        
        $dispatcher = new EventDispatcher(null, $this->getLogger(), $request);
        $dispatcher->configListeners($this->getListenerConfig());
        $dispatcher->dispatch('all', 'request_start', array());
              
        $this->assertNotNull($request->getAttribute('result'));
        $this->assertEquals($request->getAttribute('result'), 'TestListener loaded successfully');
    }
    
    /**
     * @group initiate
     */
    public function testServerInitiate() {
        $request = $this->getRequest();
        
        $dispatcher = new EventDispatcher(null, $this->getLogger(), $request);
        $dispatcher->configListeners($this->getListenerConfig());
        $dispatcher->dispatch('server', 'server_initiate', array('host' => 'local', 'port' => '123'));
        $this->assertNotNull($request->getAttribute('result_on_server_initiate'));
      
    }
    
    /**
     * @group startup
     */
    public function testServerStartup() {
        $request = $this->getRequest();
        
        $dispatcher = new EventDispatcher(null, $this->getLogger(), $request);
        $dispatcher->configListeners($this->getListenerConfig());
        $dispatcher->dispatch('server', 'server_startup', array('host' => 'local', 'port' => '123'));
        $this->assertNotNull($request->getAttribute('result_on_server_startup'));
      
    }
    
    /**
     * @group connect
     */
    public function testServerConnect() {
        $request = $this->getRequest();
        
        $dispatcher = new EventDispatcher(null, $this->getLogger(), $request);
        $dispatcher->configListeners($this->getListenerConfig());
        $dispatcher->dispatch('server', 'client_server_connect', array('host' => 'local', 'port' => '123'));
        $this->assertNotNull($request->getAttribute('result_on_client_server_connect'));
      
    }
    
    
    private function getListenerConfig() {
        return array( 
            'all' => array(
                'listeners' => array (
                    array(
                        'event' => 'request_start',
                        'listener' => 'tests\\Gossamer\\Ra\\EventListeners\\TestListener' 
                    ),
                    array(
                        'event' => 'request_end',
                        'listener' => 'tests\\Gossamer\\Ra\\EventListeners\\TestListener' 
                    ),
                    array(
                        'event' => 'entry_point',
                        'listener' => 'Gossamer\\Ra\\Authorizations\\Listeners\\CheckServerCredentialsListener' 
                    )
                )
            ),
            'server' => array(  
                'listeners' => array(
                    array(
                        'event' => 'client_server_connect',
                        'listener' => 'tests\\Gossamer\\Ra\\EventListeners\\ServerEventListener' 
                    ),
                    array(
                        'event' => 'server_initiate',
                        'listener' => 'tests\\Gossamer\\Ra\\EventListeners\\ServerEventListener' 
                    ),
                    array(
                        'event' => 'server_startup',
                        'listener' => 'tests\\Gossamer\\Ra\\EventListeners\\ServerEventListener' 
                    )
                )
            )
        );
    }    
    
    private function getRequest() {
        $request = new Request();
        
        return $request;
    }
}
//listeners:
//        
//        - { 'event': 'request_start', 'listener': 'components\staff\listeners\LoadEmergencyContactsListener', 'datasource': 'datasource1' }
//        - { 'event': 'request_start', 'listener': 'core\eventlisteners\LoadListListener', 'datasource': 'datasource1', 'class': 'components\geography\models\ProvinceModel', 'cacheKey': 'Provinces' }
    