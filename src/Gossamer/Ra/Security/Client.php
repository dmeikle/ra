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
 * Time: 12:32 AM
 */

namespace Gossamer\Ra\Security;


class Client implements ClientInterface {

    protected $id;
    protected $password = null;
    protected $roles = array();
    protected $credentials = 'anonymous';
    protected $ipAddress = null;
    protected $status = null;
    protected $email = null;
    /**
     *
     * @param array $params
     */
    public function __construct(array $params = array()) {

        if (count($params) > 0) {
            $this->id = (array_key_exists('id', $params)) ? $params['id'] : null;
            $this->password = (array_key_exists('password', $params)) ? $params['password'] : null;
            $this->roles = (array_key_exists('roles', $params)) ? $params['roles'] : null;
            $this->status = (array_key_exists('status', $params)) ? $params['status'] : null;
            $username = (array_key_exists('username', $params)) ? $params['username'] : null;
            $this->credentials = (array_key_exists('credentials', $params)) ? $params['credentials'] : $username;
            $this->ipAddress = (array_key_exists('ipAddress', $params)) ? $params['ipAddress'] :null;
            $this->email = (array_key_exists('email', $params)) ? $params['email'] : null;
        }
    }

    /**
     * accessor
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * accessor
     *
     * @param array $roles
     */
    public function setRoles(array $roles) {
        $this->roles = $roles;
        return $this;
    }

    /**
     * accessor
     *
     * @param string $credentials
     */
    public function setCredentials($credentials) {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * accessor
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress) {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * accessor
     *
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * accessor
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * accessor
     *
     * @return array
     */
    public function getRoles() {
        if (is_array($this->roles)) {
            return $this->roles;
        }

        return explode('|', $this->roles);
    }

    /**
     * accessor
     *
     * @return string
     */
    public function getCredentials() {
        return $this->credentials;
    }

    /**
     * accessor
     *
     * @return string
     */
    public function getIpAddress() {
        return $this->ipAddress;
    }

    /**
     * accessor
     *
     * @return string
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * accessor
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * accessor
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * accessor
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }
}