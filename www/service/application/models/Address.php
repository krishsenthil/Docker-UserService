<?php

/**
 * Models_Address class
 */
class Models_Address
{
    /**
     * Constant defining validation type.
     * @const
     * @var const to all errorstack validation
     */
    const VALIDATION_TYPE = 'ADDRESS_VALIDATION';

    /**
     * Default id of user address object.
     * @access private
     * @var int
     */
    private $_id;

    /**
     * Id of user.
     * @access private
     * @var int Models_User
     */
    private $_userId;

    /**
     * First address of the user.
     * @access private
     * @var string
     */
    private $_address1;

    /**
     * Second address of the user.
     * @access private
     * @var string
     */
    private $_address2;

    /**
     * Zip code for user address.
     * @access private
     * @var int
     */
    private $_zipCode;

    /**
     * Country of user.
     * @access private
     * @var string
     */
    private $_country;

    /**
     * User Mobilenumber.
     * @access private
     * @var int
     */
    private $_mobileNumber;

    /**
     * User home number.
     * @access private
     * @var int
     */
    private $_homeNumber;

    /**
     * User address data added date.
     * @access private
     * @var string
     */
    private $_createdOn;

    /**
     * User address data updated date.
     * @access private
     * @var string
     */
    private $_updatedOn;

    /**
     * Database Adapter
     * @access  private
     * @var Noobh_DB_Adapter
     */
    private $_dbAdapter;

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
        '901' => 'Invalid input string',
        '902' => 'Invalid input string Length',
        '903' => 'Invalid input integer',
        '904' => 'Address already exists.',
        '905' => 'Invalid Mobile Number.',
        '906' => 'Invalid Home Number',
        '908' => 'Unable to save the user address.',
        '909' => 'Unable to find the user address',
        '910' => 'Unable to create the user address.',
        '911' => 'Unable to update the user address.',
    );

    /**
     * Returns the user address based on the user id or id
     * and initialize the error stack.
     *
     * @param Models_user $userId [Models_User Id]
     * @param int         $id     [Models_Address Id]
     * @return array
     */
    public function __construct($userId = null, $id = null)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        if (!is_null($userId) || !is_null($id)) {
            return $this->getAddressBy($userId, $id);
        }

        return $this;
    }

    /**
     * Get address id.
     *
     * @access public
     * @param void
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get user id.
     *
     * @access public
     * @param void
     * @return int
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Set user first address by validating the input
     * string and the length of input string.
     *
     * @access public
     * @param string $address1 [First address of user]
     * @return void
     */
    public function setAddress1($address1)
    {
        if (!$this->isValidString($address1)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 901, $this->_errorList[901]);
        }
        $maxLength = 90;
        if (!$this->isValidInputLength($address1, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_address1 = $address1;
    }

    /**
     * Get user first Address.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getAddress1()
    {
        return $this->_address1;
    }

    /**
     * Set user second address by validating the input
     * string and the length of inputstring.
     *
     * @access public
     * @param string $address2 [Second address of user]
     * @return void
     */
    public function setAddress2($address2)
    {
        if (!$this->isValidString($address2)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 901, $this->_errorList[901]);
        }
        $maxLength = 90;
        if (!$this->isValidInputLength($address2, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_address2 = $address2;
    }

    /**
     * Get user second address.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getAddress2()
    {
        return $this->_address2;
    }

    /**
     * Set user zip code by validating the input type
     * and the length of input value.
     *
     * @access public
     * @param int $zipCode [User zipcode value]
     * @return void
     */
    public function setZipCode($zipCode)
    {
        if (!is_numeric($zipCode)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 903, $this->_errorList[903]);
        }
        $maxLength = 15;
        if (!$this->isValidInputLength($zipCode, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_zipCode = $zipCode;
    }

    /**
     * Get user zip code.
     *
     * @access public
     * @param void
     * @return int
     */
    public function getZipCode()
    {
        return $this->_zipCode;
    }

    /**
     * Set user country by validating the input string
     * and the length of input string.
     *
     * @access public
     * @param string $country [Country name of user]
     * @return void
     */
    public function setCountry($country)
    {
        if (!$this->isValidString($country)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 901, $this->_errorList[901]);
        }
        $maxLength = 80;
        if (!$this->isValidInputLength($country, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_country = $country;
    }

    /**
     * Get user country.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getCountry()
    {
        return $this->_country;
    }

    /**
     * Set user mobile number by validating the input.
     *
     * @access public
     * @param int $mobileNumber [User mobile number]
     * @return void
     */
    public function setMobileNumber($mobileNumber)
    {
        if (!$this->isValidContactNumber($mobileNumber)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 905, $this->_errorList[905]);
        }
        $maxLength = 20;
        if (!$this->isValidInputLength($mobileNumber, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_mobileNumber = $mobileNumber;
    }

    /**
     * Get user  mobile number.
     *
     * @access public
     * @param void
     * @return int
     */
    public function getMobileNumber()
    {
        return $this->_mobileNumber;
    }

    /**
     * Set user  home number by validating the input.
     *
     * @access public
     * @param int $homeNumber [User home phone number]
     * @return void
     */
    public function setHomeNumber($homeNumber)
    {
        if (!$this->isValidContactNumber($homeNumber)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 906, $this->_errorList[906]);
        }
        $maxLength = 20;
        if (!$this->isValidInputLength($homeNumber, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_homeNumber = $homeNumber;
    }

    /**
     * Get user home number.
     *
     * @access public
     * @param void
     * @return int
     */
    public function getHomeNumber()
    {
        return $this->_homeNumber;
    }

    /**
     * Get created on date.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getCreatedOn()
    {
        return $this->_createdOn;
    }

    /**
     * Get updated date.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getUpdatedOn()
    {
        return $this->_updatedOn;
    }

    /**
     * Get user address by user id or address id.
     *
     * @access public
     * @param int $userId [Models_User Id]
     * @param int $id     [Models_Address Id]
     * @return array
     */
    public function getAddressBy($userId = null, $id = null)
    {
        $status = false;
        $result = array();
        $bind = array();
        $where = '';
        try {
            if (!is_null($userId)) {
                $where = 'WHERE `user_id` = ?';
                $bind[] = $userId;
            }
            if (!is_null($id)) {
                if (empty($where)) {
                    $where = ' WHERE `id` = ?';
                } else {
                    $where .= ' AND `id` = ?';
                }
                $bind[] = $id;
            }
            if (isset($where) && isset($bind)) {
                $this->_dbAdapter = new Noobh_DB_Adapter();
                $sql = 'SELECT * FROM `um_address` '.$where;
                $statement = $this->_dbAdapter->query($sql, $bind);
                //Log debug message
                Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.', Completed executing Sql Query - '.$sql);
                $result = $statement->fetchAssoc();
                if (count($result) > 0) {
                    $this->_id = $result[0]['id'];
                    $this->setAddress1($result[0]['address1']);
                    $this->setAddress2($result[0]['address2']);
                    $this->setZipCode($result[0]['zip']);
                    $this->setCountry($result[0]['country']);
                    $this->setMobileNumber($result[0]['mobile_phone']);
                    $this->setHomeNumber($result[0]['home_phone']);
                    $status = true;
                    //Log info
                    Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'  -  User address exists for id : '.$this->_id);
                }
            }
        } catch (Exception $ex) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 909, $this->_errorList[909]);
            //Log exception
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                '  Get user address failed with following error :'.
                ' , Error code '. $ex->getCode().
                ' , Error Message '. $ex->getMessage());
        }
        if ($status == true) {
            return $this;
        } else {
            return $status;
        }
    }

    /**
     * Update user address if address id exists else insert
     * data .
     *
     * @access public
     * @param void
     * @return bool
     */
    public function save()
    {
        //Store response status
        $response = false;
        try {
            if (empty($this->_dbAdapter)) {
                $this->_dbAdapter = new Noobh_DB_Adapter();
            }
            $this->_dbAdapter->beginTransaction();
            //Log info
            Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'  -  Begin transaction for saving user address information');
            if (!empty($this->_id)) {
                $this->_update();
            } else {
                $this->_insert();
            }
            $this->_dbAdapter->commit();
            $response = true;
        } catch (Exception $ex) {
            $this->_dbAdapter->rollBack();
            //Log exception
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.'  -  User address save failed with following error : ['.$ex->getCode() .']' . $ex->getMessage());
        }

        return $response;
    }

    /**
     * Create user address.
     *
     * @access private
     * @param void
     * @return bool
     */
    private function _insert()
    {
        try {
            $bind = array(
                $this->_userId,
                $this->_address1,
                $this->_address2,
                $this->_zipCode,
                $this->_country,
                $this->_mobileNumber,
                $this->_homeNumber,
            );
            $sql = 'INSERT INTO `um_address` (`user_id`,`address1`,`address2`,`zip`,`country`,`mobile_phone`,`home_phone`,`created_on`,`updated_on`,`um_user_id`)'
                 . 'VALUES (?,?,?,?,?,?,?,NOW(),NOW(),0)';
            $this->_dbAdapter->query($sql, $bind);
            //Log debug message
            Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.', Completed executing Sql Query - '.$sql);
            $this->_id = $this->_dbAdapter->lastInsertId();
            //Log info
            Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'  - New user address created with id : '. $this->_id);
        } catch (Exception $ex) {
            $this->_errorStack->push(self::VALIDATION_TYPE,910, $this->_errorList[910]);
            //Log fatal error
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                ' User insert failed for user id: '.$this->_userId.
                ' , Error code '. $ex->getCode().
                ' , Error Message '. $ex->getMessage());
        }
    }

    /**
     * Update user address by address id.
     *
     * @access private
     * @param void
     * @return bool
     */
    private function _update()
    {
        try {
            $bind = array(
                $this->_address1,
                $this->_address2,
                $this->_zipCode,
                $this->_country,
                $this->_mobileNumber,
                $this->_homeNumber,
            );
            //update based on id
            $sql = 'UPDATE um_address SET address1 = ?, address2 = ?,zip = ?, country = ?, mobile_phone = ?, home_phone = ?'
                 ." WHERE id = $this->_id";
            $this->_dbAdapter->query($sql, $bind);
            //Log debug message
            Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.'Completed executing Sql Query - '.$sql);
            //Log info
            Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'Updated user address with id : '. $this->_id);
        } catch (Exception $ex) {
            $this->_errorStack->push(self::VALIDATION_TYPE,911, $this->_errorList[911]);
            //Log fatal error
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                ' User address update failed for id: '.$this->_id.
                ' , Error code '. $ex->getCode().
                ' , Error Message '. $ex->getMessage());
        }
    }

    /**
     * Checks if address exists for the user id,
     * else it returns false.
     *
     * @access private
     * @param null $userId [Models_User id]
     * @param null $id     [Models_Address id]
     * @return bool
     */
    private function _isExists($userId = null, $id = null)
    {
        //Log info
        Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'Checks user exists for userid : '. $userId);
        return $this->getAddressBy($userId, $id);
    }

    /**
     * Returns false if the input string doesnot contain
     * letters and numbers, else it returns true.
     *
     * @access public
     * @param string $inputString [Input value]
     * @return bool
     */
    public function isValidString($inputString)
    {
        if (!preg_match('/[A-Za-z][A-Za-z0-9]/', $inputString)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns false if the input string length exceeds
     * the limit, else return true.
     *
     * @access public
     * @param string $inputString [Input value]
     * @param int    $maxLength   [maximum character length]
     * @return bool
     */
    public function isValidInputLength($inputString, $maxLength)
    {
        $strLength = strlen($inputString);
        if ($strLength > $maxLength) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns false if the input is not a valid contact
     * number.
     *
     * @access public
     * @param string $contactNumber [User contact number]
     * @return bool
     */
    public function isValidContactNumber($contactNumber)
    {
        if (!preg_match('/^\+?([0-9]{1,4})\)?[-. ]?([0-9]{10})$/', $contactNumber)) {
            return false;
        } else {
            return true;
        }
    }
}

?>