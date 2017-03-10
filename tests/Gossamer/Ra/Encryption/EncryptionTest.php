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
 * Time: 9:02 PM
 */

namespace tests\Gossamer\Ra\Encryption;


use Gossamer\Ra\Encryption\Encryption;

class EncryptionTest extends \tests\BaseTest
{

    public function testValid() {
        $encryption = new Encryption();
        $password = 'testphp';

        $encrypted = $encryption->generateHash($password);

        $this->assertTrue($encryption->compareHash($password, $encrypted));
    }

    public function testInvalid() {
        $encryption = new Encryption();
        $password = 'testphp';
        $mismatchedPassword = 'wrongpwd';

        $encrypted = $encryption->generateHash($password);

        $this->assertFalse($encryption->compareHash($mismatchedPassword, $encrypted));
    }
}