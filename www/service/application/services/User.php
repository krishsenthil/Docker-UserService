<?php

/**
 * User service class
 */
class Services_User
{
    /**
     * Constant defining validation type.
     * @const
     * @var const to all errorstack validation
     */
    const VALIDATION_TYPE = 'USER_SERVICE_VALIDATION';

    /**
     * Error Message.
     * @access private
     * @var Noobh_ErrorStackSingleton
     */
    private $_errorStack;

    /**
     * Collection of errorstack messages.
     * @access private
     * @var array
     */
    private $_errorList = array(
        '600' => 'Invalid input array',
        '601' => 'No result found',
        '602' => 'Unable to find the user',
        '603' => 'Search parameter missing',
        '604' => 'User Id missing',
    );

    /**
     * Create user data.
     *
     * @access public
     * @param array $data [User data]
     * @return bool
     */
    public function create($data)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        try {
            if (is_array($data)) {
                $userModel = new Models_User();
                $firstName = htmlspecialchars($data['firstName']);
                $lastName = htmlspecialchars($data['lastName']);
                $email = htmlspecialchars($data['email']);
                $username = htmlspecialchars($data['username']);
                $password = htmlspecialchars($data['password']);
                $userModel->setFirstName($firstName);
                $userModel->setLastName($lastName);
                $userModel->setEmail($email);
                $userModel->setUsername($username);
                $userModel->setPassword($password);
                if (empty($this->_errorStack->getErrorList())) {
                    $userModel->save();
                }
            } else {
                throw new Exception($this->_errorList[600], 600);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }

    /**
     * Update user data using user id.
     *
     * @access public
     * @param int   $userId [user id]
     * @param array $data   [user data]
     * @return bool
     */
    public function update($userId, $data)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        try {
            if (is_array($data)) {
                $userModel = new Models_User();
                $user = $userModel->getUserBy($email = null, $userId);
                if ($user) {
                    $firstName = htmlspecialchars($data['firstName']);
                    $lastName = htmlspecialchars($data['lastName']);
                    $username = htmlspecialchars($data['username']);
                    $password = htmlspecialchars($data['password']);
                    $userModel->setFirstName($firstName);
                    $userModel->setLastName($lastName);
                    $userModel->setUsername($username);
                    $userModel->setPassword($password);
                    if (empty($this->_errorStack->getErrorList())) {
                        $userModel->save();
                    }
                } else {
                    throw new Exception($this->_errorList[602], 602);
                }
            } else {
                throw new Exception($this->_errorList[600], 600);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }

    /**
     * Search user data using email or hash id.
     *
     * @access public
     * @param string $email [user email address]
     * @param string $hashId [user hashId]
     * @return array
     */
    public function search($email, $hashId)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        try {
            if (!is_null($email) || !is_null($hashId)) {
                $userModel = new Models_User();
                $result = $userModel->getUserBy($email, null, $hashId);
                if (!empty($result)) {
                    $userInfo = array(
                    'firstName' => $result->getFirstName(),
                    'lastName' => $result->getLastName(),
                    'username' => $result->getUsername(),
                    'email' => $result->getEmail(),
                    );

                    return $userInfo;
                } else {
                    throw new Exception($this->_errorList[601], 601);
                }
            } else {
                throw new Exception($this->_errorList[603], 603);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }

    /**
     * Deactivate user data using user id.
     *
     * @access public
     * @param int $userId [User id]
     * @return bool
     */
    public function deActivate($userId)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        try {
            if (!empty($userId)) {
                $userModel = new Models_User();
                $result = $userModel->getUserBy($email = null, $userId);
                if (!empty($result)) {
                    $status = $userModel->deActivate($userId);

                    return $status;
                } else {
                    throw new Exception($this->_errorList[602], 602);
                }
            } else {
                throw new Exception($this->_errorList[604], 604);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }

    /**
     * Activate user data using user id.
     *
     * @access public
     * @param int $userId [User id]
     * @return bool
     */
    public function activate($userId)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        try {
            if (!empty($userId)) {
                $userModel = new Models_User();
                $result = $userModel->getUserBy($email = null, $userId);
                if (!empty($result)) {
                    $status = $userModel->activate($userId);

                    return $status;
                } else {
                    throw new Exception($this->_errorList[602], 602);
                }
            } else {
                throw new Exception($this->_errorList[604], 604);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }

    /* --- Address Services --- */

    /**
     * Create user data.
     *
     * @access public
     * @param array $data [user address information]
     * @return bool
     */
    public function createAddress($data)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();
        try {
            if (is_array($data)) {
                $userId = htmlspecialchars($data['userId']);
                $address1 = htmlspecialchars(htmlspecialchars($data['address1']));
                $address2 = htmlspecialchars($data['address2']);
                $zip = htmlspecialchars($data['zip']);
                $country = htmlspecialchars($data['country']);
                $mobileNumber = htmlspecialchars($data['mobilePhone']);
                $homeNumber = htmlspecialchars($data['homePhone']);
                $addressModel = new Models_Address($userId);
                $addressModel->setAddress1($address1);
                $addressModel->setAddress2($address2);
                $addressModel->setZipCode($zip);
                $addressModel->setCountry($country);
                $addressModel->setMobileNumber($mobileNumber);
                $addressModel->setHomeNumber($homeNumber);
                if (empty($this->_errorStack->getErrorList())) {
                    $addressModel->save();
                }
            } else {
                throw new Exception($this->_errorList[600], 600);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }

    /**
     * Update user address using user id.
     *
     * @access public
     * @param int $id [address id]
     * @param array $data [user address information]
     * @return bool
     */
    public function updateAddress($id, $data)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();
        try {
            if (is_array($data)) {
                $address1 = htmlspecialchars(htmlspecialchars($data['address1']));
                $address2 = htmlspecialchars($data['address2']);
                $zip = htmlspecialchars($data['zip']);
                $country = htmlspecialchars($data['country']);
                $mobileNumber = htmlspecialchars($data['mobileNumber']);
                $homeNumber = htmlspecialchars($data['homeNumber']);
                //$addressModel = new Models_Address($userId = null, $id);
                $addressModel = new Models_Address();
                $result = $addressModel->getAddressBy($userId = null, $id);
                if (!$result) {
                    throw new Exception($this->_errorList[602], 602);
                } else {
                    echo $addressModel->getAddress1();
                    $addressModel->setAddress1($address1);
                    $addressModel->setAddress2($address2);
                    $addressModel->setZipCode($zip);
                    $addressModel->setCountry($country);
                    $addressModel->setMobileNumber($mobileNumber);
                    $addressModel->setHomeNumber($homeNumber);
                    if (empty($this->_errorStack->getErrorList())) {
                        $addressModel->save();
                    }
                }
            } else {
                throw new Exception($this->_errorList[600], 600);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }

    /**
     * Search user address data using userId or  id.
     *
     * @access public
     * @param int $userId [user id]
     * @param int $id     [addtress id]
     * @return string
     */
    public function searchAddress($userId, $id)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();
        try {
            $addressModel = new Models_Address();
            $result = $addressModel->getAddressBy($userId, $id);
            if (!empty($result)) {
                $addressInfo = array(
                    'address1' => $result->getAddress1(),
                    'address2' => $result->getAddress2(),
                    'country' => $result->getCountry(),
                    'zipCode' => $result->getZipCode(),
                    'mobileNumber' => $result->getMobileNumber(),
                    'phoneNumber' => $result->getHomeNumber(),
                );

                return $addressInfo;
            } else {
                throw new Exception($this->_errorList[601], 601);
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->_errorStack->push(self::VALIDATION_TYPE, $code, $message);
            Noobh_Log::error($message);
            throw new Exception($message);
        }
    }
}

?>