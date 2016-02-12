<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * All user related function are specified in this
 * file
 *
 * @category   Models
 * @package    Models
 * @subpackage Models
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 *
 * Collash Inc Internal
 *
 * Model user class for performing user related activities.
 * Input parameters are validated and user information is
 * saved into the database. Function for updating user data,
 * deactivate and activate user are available.
 *
 * @author     Senthilraj Krish <senthilrajk@gmail.com>
 * @copyright  Collash Inc
 * @package    Models
 * @subpackage Models
 * @since      0.1
 * @date Oct 23, 2015
 *
 */

/**
 * Models_User class
 */
class Models_User
{
    /**
     * Constant used for hasing hash Id.
     * @const
     */
    const SALT = 'Collash_User';

    /**
     * Constant defining validation type.
     * @var const to all errorstack validation
     */
    const VALIDATION_TYPE = 'USER_VALIDATION';
    /**
     * Default id of user object.
     * @access private
     * @var int
     */
    private $_id;

    /**
     * Hash id used for user object.
     * @access private
     * @var int
     */
    private $_hashId;

    /**
     * First Name of the user.
     * @access private
     * @var string
     */
    private $_firstName;

    /**
     * Last name of the user.
     * @access private
     * @var string
     */
    private $_lastName;

    /**
     * Email ID of the user.
     * @access private
     * @var string
     */
    private $_email;

    /**
     * Username for the user.
     * @access private
     * @var string
     */
    private $_username;

    /**
     * Address of the user.
     * @access private
     * @var Models_Address
     */
    private $_address;

    /**
     * Hashed password of the user.
     * @access private
     * @var string
     */
    private $_password;

    /**
     * Constant used for password hashing.
     * @access private
     * @var string
     */
    private $_salt;

    /**
     * Status of the user.
     * @access private
     * @var bool
     */
    private $_isActive;

    /**
     * User information added date.
     * @access private
     * @var string
     */
    private $_createdOn;

    /**
     * User information updated date.
     * @access private
     * @var string
     */
    private $_updatedOn;

    /**
     * Database Adapter
     * @access private
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
        '903' => 'Invalid email',
        '905' => 'Invalid user status',
        '906' => 'Invalid username, minimum length must be 5 characters',
        '907' => 'Password length must be greater than 8 characters',
        '908' => 'Password length should not exceed 20 characters',
        '909' => 'Unable to save the user data',
        '910' => 'Inavlid input given for address',
        '912' => 'Unable to find the user',
        '913' => 'Unable to create the user.',
        '914' => 'Unable to update the user.',
        '915' => 'Password must have atleast one numeric value',
        '916' => 'Password must have atleast one uppercase letter',
        '917' => 'Password must have atleast one lowercase letter',
        '918' => 'Password must have atleast one special character',
        '919' => 'Could not inactivate',
        '920' => 'Could not activate',
    );

    /**
     * Returns the user data based on the parameter passed through
     * the function and initialize the error stack.
     *
     * @access public
     * @param string $email  [User email address]
     * @param int    $id     [User id]
     * @param null   $hashId [User hashId]
     * @return $this
     */
    public function __construct($email = null, $id = null, $hashId = null, $isActive = false)
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        if (!is_null($hashId) || !is_null($email) || !is_null($id)) {
            return $this->getUserBy($email, $id, $hashId, $isActive);
        }

        return $this;
    }

    /**
     * Get user ID.
     *
     * @access public
     * @param  void
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get hash value.
     *
     * @access public
     * @param  void
     * @return int
     */
    public function getHash()
    {
        return $this->_hashId;
    }

    /**
     * Set firstName by validating the input string and the length of input string.
     *
     * @access public
     * @param string $firstName [firstname of user]
     * @return Void
     */
    public function setFirstName($firstName)
    {
        if (!$this->isValidString($firstName)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 901, $this->_errorList[901]);
        }
        $maxLength = 50;
        if (!$this->isValidInputLength($firstName, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_firstName = $firstName;
    }

    /**
     * Get user firstName.
     *
     * @access public
     * @param  void
     * @return string
     */
    public function getFirstName()
    {
        return $this->_firstName;
    }

    /**
     * Set user lastName by validating the input string and the length of inputstring.
     *
     * @access public
     * @param string $lastName [Lastname of user]
     * @return Void
     */
    public function setLastName($lastName)
    {
        if (!$this->isValidString($lastName)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 901, $this->_errorList[901]);
        }
        $maxLength = 50;
        if (!$this->isValidInputLength($lastName, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_lastName = $lastName;
    }

    /**
     * Get user lastName.
     *
     * @access public
     * @param  void
     * @return string
     */
    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * Set user email by validating the input string and the length of inputstring.
     *
     * @access public
     * @param string $email [User email address]
     * @return Void
     */
    public function setEmail($email)
    {
        if (!$this->_isValidEmail($email)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 903, $this->_errorList[903]);
        }
        $maxLength = 50;
        if (!$this->isValidInputLength($email, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_email = $email;
    }

    /**
     * Get user Email.
     *
     * @access public
     * @param  void
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Set user username by validating the input string and the length of inputstring.
     *
     * @access public
     * @param string $username [Username of user]
     * @return Void
     */
    public function setUsername($username)
    {
        if (!$this->_isValidUsername($username)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 906, $this->_errorList[906]);
        }
        $maxLength = 45;
        if (!$this->isValidInputLength($username, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_username = $username;
    }

    /**
     * Get user username.
     *
     * @access public
     * @param  void
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Set user password by validating the input string and the length of inputstring.
     *
     * @access public
     * @param string $password [User password]
     * @return Void
     */
    public function setPassword($password)
    {
        $this->_isValidPassword($password);
        $this->_password = md5($password);
    }

    /**
     * Get user password.
     *
     * @access public
     * @param  void
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Set model address, Check whether its an array or object of model address.
     *
     * @access public
     * @param array $address [Models_Address]
     * @return Void
     */
    public function setAddress($address)
    {
        if (is_array($address)) {
            $address = new Models_Address();
            $address->setAddress1($address['address1']);
            $address->setAddress2($address['address2']);
            $address->setZipCode($address['zipCode']);
            $address->setCountry($address['country']);
            $address->setMobileNumber($address['mobileNumber']);
            $address->setHomeNumber($address['homeNumber']);
            $this->_address = $address;
        } elseif (is_a($address, 'Models_Address')) {
            $this->_address = $address;
        } else {
            $this->_errorStack->push(self::VALIDATION_TYPE, 910, $this->_errorList[910]);
        }
    }

    /**
     * Get model address.
     *
     * @access public
     * @param  void
     * @return Models_Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * set user isActive value.
     *
     * @access public
     * @param bool $boolean
     * @return Void
     */
    public function setIsActive($boolean)
    {
        if (!is_numeric($boolean)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 905, $this->_errorList[905]);
        }
        $maxLength = 1;
        if (!$this->isValidInputLength($boolean, $maxLength)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 902, $this->_errorList[902]);
        }
        $this->_isActive = $boolean;
    }

    /**
     * Get user isActive value.
     *
     * @access public
     * @param  void
     * @return bool
     */
    public function getIsActive()
    {
        return $this->_isActive;
    }

    /**
     * Get user account created date.
     *
     * @access public
     * @param  void
     * @return string
     */
    public function getCreatedOn()
    {
        return $this->_createdOn;
    }

    /**
     * Get user account updated date.
     *
     * @access public
     * @param  void
     * @return string
     */
    public function getUpdatedOn()
    {
        return $this->_updatedOn;
    }

    /**
     * Get the user data by user email, user id or hashId .
     *
     * @access public
     * @param string $email  [User email address]
     * @param int    $id     [User id]
     * @param null   $hashId [User hashId]
     * @return array
     */
    public function getUserBy($email = null, $id = null, $hashId = null, $isActive = false)
    {
        $result = false;
        $bind = array();
        $resultSet = array();
        $where = '';

        try {
            if (!is_null($email)) {
                $where = 'WHERE `email` = ?';
                $bind[] = $email;
            }
            if (!is_null($id)) {
                if (empty($where)) {
                    $where = ' WHERE `id` = ?';
                } else {
                    $where .= ' AND `id` = ?';
                }
                $bind[] = $id;
            }
            if (!is_null($hashId)) {
                if (empty($where)) {
                    $where = ' WHERE `hash_id` = ?';
                } else {
                    $where .= ' AND `hash_id` = ?';
                }
                $bind[] = $hashId;
            }
            if($isActive) {
                if (empty($where)) {
                    $where = ' WHERE `is_active` = ?';
                } else {
                    $where .= ' AND `is_active` = ?';
                }
                $bind[] = 1;
            }
            if (isset($where) && isset($bind)) {
                if (empty($this->_dbAdapter)) {
                    $this->_dbAdapter = new Noobh_DB_Adapter();
                }
                $sql = 'SELECT * FROM `us_user` '.$where;
                $statement = $this->_dbAdapter->query($sql, $bind);
                //Log debug message
                Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.', Sql Query - '.$sql);
                $resultSet = $statement->fetchAssoc();
                if (count($resultSet) > 0) {
                    $this->_id = $resultSet[0]['id'];
                    $this->setFirstName($resultSet[0]['first_name']);
                    $this->setLastName($resultSet[0]['last_name']);
                    $this->setEmail($resultSet[0]['email']);
                    $this->setUsername($resultSet[0]['username']);
                    $result = true;
                    //Log info
                    Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'  -  User information exists for id : '.$this->_id);
                }
            }
        } catch (Exception $ex) {
            //Log exception
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                ' Get user data failed with following error :' . ' , Error code '. $ex->getCode() .  ' , Error Message '. $ex->getMessage());
        }
        if ($result == true) {
            return $this;
        } else {
            return $result;
        }
    }

    /**
     * Update user data if the user exists else insert data.
     *
     * @access public
     * @param  void
     * @return bool $response
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
            Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'  -  Begin transaction for saving user information');
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
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.'  -  User save failed with following error : ['.$ex->getCode() .']' . $ex->getMessage());
            throw $ex;
        }
        return $response;
    }

    /**
     * Create user data.
     *
     * @access private
     * @param  void
     * @return bool
     */
    private function _insert()
    {
        try {
            $hashId = md5(self::SALT.$this->_email.date('YmdHis'));
            $bind = array(
                $hashId,    // hash value for the current user
                $this->_firstName,
                $this->_lastName,
                $this->_email,
                $this->_username,
                $this->_password,
            );
            $sql = 'INSERT INTO `us_user` (`hash_id`, `first_name`,`last_name`,`email`,`username`,`password`,`created_on`,`updated_on`,`is_active`)'
                 . 'VALUES (?,?,?,?,?,?,NOW(),NOW(),0)';
            $this->_dbAdapter->query($sql, $bind);
            //Log debug message
            Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.', Sql Query - '.$sql);
            $this->_id = $this->_dbAdapter->lastInsertId();
            $this->_hashId = $hashId;
            //Log info
            Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'  - New user created with id : '. $this->_id);
        } catch (Exception $ex) {
            $this->_errorStack->push(self::VALIDATION_TYPE,913, $this->_errorList[913]);
            //Log fatal error
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                ' User insert failed for email: '. $this->_email . ' , Error code '. $ex->getCode() . ' , Error Message '. $ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Update user data by user id.
     *
     * @access private
     * @param  void
     * @return bool
     */
    private function _update()
    {
        try {
            $bind = array(
                $this->_firstName,
                $this->_lastName,
                $this->_password,
            );
            //update based on id
            $sql = 'UPDATE us_user SET first_name = ?, last_name = ?, password = ?, updated_on = NOW() '
                 ." WHERE id = $this->_id";
            $this->_dbAdapter->query($sql, $bind);
            //Log debug message
            Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.'Completed executing Sql Query - '.$sql);
            //Log info
            Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'Updated user record with id : '. $this->_id);
        } catch (Exception $ex) {
            $this->_errorStack->push(self::VALIDATION_TYPE,914, $this->_errorList[914]);
            //Log fatal error
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                ' User update failed for id: '. $this->_id . ' , Error code '. $ex->getCode() . ' , Error Message '. $ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Delete/De-Activate the User
     *
     * @access public
     * @param  void
     * @return bool
     */
    public function delete()
    {
        $bind = array();
        $where = '';
        $status = false;
        try {
            if (!is_null($this->_hashId)) {
                $where = 'WHERE `hash_id` = ?';
                $bind[] = $this->_hashId;
            }
            if (isset($where) && isset($bind)) {
                if (empty($this->_dbAdapter)) {
                    $this->_dbAdapter = new Noobh_DB_Adapter();
                }
                $sql = 'UPDATE us_user SET is_active = 0 ' . $where;
                $statement = $this->_dbAdapter->query($sql, $bind);
                //Log debug message
                Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.' Completed executing Sql Query - '.$sql);
                $status = true;
                //Log info
                Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.' Deactivated user with id : '. $userId);
            }
        } catch (Exception $ex) {
            $this->_errorStack->push(self::VALIDATION_TYPE,919, $this->_errorList[919]);
            //Log fatal error
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                ' Unable to de-activate the user: ' . $userId . ' , Error code: '. $ex->getCode() .
                ' , Error Message: '. $ex->getMessage());
        }

        return $status;
    }

    /**
     * Activate User by id.
     *
     * @access public
     * @param string $userId [User's hash id]
     * @return bool
     */
    public function activate($userId)
    {
        $bind = array();
        $where = '';
        $status = false;
        try {
            if (!is_null($userId)) {
                $where = 'WHERE `hash_id` = ?';
                $bind[] = $userId;
            } else {
                //Log warning message
                Noobh_Log::warning(__CLASS__.'::'.__FUNCTION__.' Missing Argument userid');
            }
            if (isset($where) && isset($bind)) {
                if (empty($this->_dbAdapter)) {
                    $this->_dbAdapter = new Noobh_DB_Adapter();
                }
                $sql = 'UPDATE us_user SET is_active = 1 '.$where;
                $statement = $this->_dbAdapter->query($sql, $bind);
                //Log debug message
                Noobh_Log::debug(__CLASS__.'::'.__FUNCTION__.' Completed executing Sql Query - '.$sql);
                $status = true;
                //Log info
                Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.' Activated user with id : '. $userId);
            }
        } catch (Exception $ex) {
            $this->_errorStack->push(self::VALIDATION_TYPE,920, $this->_errorList[920]);
            //Log fatal error
            Noobh_Log::fatal(__CLASS__.'::'.__FUNCTION__.
                '  User activate failed for id: '.$userId.
                ' , Error code '. $ex->getCode().
                ' , Error Message '. $ex->getMessage());
            throw new  $ex;
        }

        return $status;
    }

    /**
     * Returns true if user exists for the email id or user id,
     * else it return false.
     *
     * @access private
     * @param string $email [Email address]
     * @param null   $id    [User Id]
     * @return bool
     */
    private function _isExists($email, $id = null)
    {
        //Log info
        Noobh_Log::info(__CLASS__.'::'.__FUNCTION__.'Checks user exists for email : '. $email);
        return $this->getUserBy($email, $id);
    }

    /**
     * Returns false if the input string doesnot contain letters
     * and numbers else it returns true.
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
     * Returns false if the input string length exceeds the limit , else return true.
     *
     * @access public
     * @param string $inputString [Input value]
     * @param int    $maxLength   [max character length]
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
     * Returns false if the email id is not valid.
     *
     * @access private
     * @param string $email [user email Address]
     * @return bool
     */
    private function _isValidEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns false if the username doesnot contain any of the given
     * expression,Username length must be greater than 5.
     *
     * @access private
     * @param string $username [Username]
     * @return bool
     */
    private function _isValidUsername($username)
    {
        if (!preg_match('/^[a-zA-Z0-9@_]{5,}$/', $username)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validates the password for min,max length, special characters,
     * Upper characters, Lower characters and numeric value.
     *
     * @access private
     * @param string $password [Passowrd]
     * @return bool
     */
    private function _isValidPassword($password)
    {
        if (strlen($password) < 8) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 907, $this->_errorList[907]);
        }
        if (strlen($password) > 20) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 908, $this->_errorList[908]);
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 915, $this->_errorList[915]);
        }
        if (!preg_match('/[A-Z]/', $password)) {
             $this->_errorStack->push(self::VALIDATION_TYPE, 916, $this->_errorList[916]);
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 917, $this->_errorList[917]);
        }
        if (!preg_match('/[\^£$&*()}{@#~?><>,|=_+¬-]/', $password)) {
            $this->_errorStack->push(self::VALIDATION_TYPE, 918, $this->_errorList[918]);
        }
    }

}