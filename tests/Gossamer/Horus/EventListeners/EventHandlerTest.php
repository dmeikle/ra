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

use Gossamer\Horus\EventListeners\EventHandler;
use Gossamer\Horus\Core\Request;

/**
 * EventHandlerTest
 *
 * @author Dave Meikle
 */
class EventHandlerTest extends \tests\BaseTest{
    
    public function testAddListener() {
        $request = $this->getRequest();
        $handler = new EventHandler($this->getLogger(), $request);
        $listenerConfig = array('listener' => 'tests\\Gossamer\\Ra\\EventListeners\\TestListener', 'event' => 'request_start');
        
        $handler->addListener($listenerConfig);
        $handler->setState('request_start', array());
        
        $handler->notifyListeners();
        
        $this->assertNotNull($request->getAttribute('result'));
        $this->assertEquals($request->getAttribute('result'), 'TestListener loaded successfully');
    }
    
    private function getRequest() {
        $request = new Request();
        
        return $request;
    }
}
