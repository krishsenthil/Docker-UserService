<?php
/**
 *
 * Collash Inc Internal
 *
 *
 * All user related function are specified in this
 * file
 *
 * @category   Controller
 * @package    Controller
 * @subpackage Controller
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
 * @package    Controller
 * @subpackage Controller
 * @since      0.1
 * @date Oct 23, 2015
 *
 */
class IndexController extends Noobh_Controller
{

    /*
    *   Error stack holder
    */
    private $_errorStack;

    /**
     * Collection of errorstack messages.
     * @access private
     * @var array
     */
    private $_errorList = array(
        '801' => 'Invalid input string',
        '802' => 'Invalid input string Length',
        '803' => 'Invalid email',
        '805' => 'Invalid user status',
        '806' => 'Invalid username, minimum length must be 5 characters',
        '807' => 'Password length must be greater than 8 characters',
        '808' => 'Password length should not exceed 20 characters',
        '809' => 'Unable to save the user data',
        '810' => 'Inavlid input given for address',
        '812' => 'Unable to find the user',
        '813' => 'Unable to create the user.',
        '814' => 'Unable to update the user.',
        '815' => 'Password must have atleast one numeric value',
        '816' => 'Password must have atleast one uppercase letter',
        '817' => 'Password must have atleast one lowercase letter',
        '818' => 'Password must have atleast one special character',
        '819' => 'Unable to delete the user.',
        '820' => 'Unable to activate the user.',
        '821' => 'User already exists for given email',
        '822' => 'Invalid input. Email or userId not present.',
        '823' => 'Invalid number of input parameters',
    );

    public function init()
    {
        $this->_disableLayout();
        $this->_disableView();

        if($this->getRequest()->getAction() == "index") {
            $this->getRequest()->redirectUrl('/get');
        }
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();
    }

    /*
    * Create new user account
    *
    */
    public function createAction()
    {
        $response = array();
        try {
            $request = $this->getRequest();

            if($request->isPOST()) {
                $params = $request->getParams();

                /** 
                    TODO: 
                        Currently validting with munber of input parameters. 
                        Need to solve it in better way.
                */
                if(count($params) != 5) {
                    $response['error_code'] =  823;
                    $response['errors'] =  $this->_errorList[823];

                    echo json_encode($response);
                    exit;
                }

                $firstName = htmlspecialchars($params['firstName']);
                $lastName = htmlspecialchars($params['lastName']);
                $username = htmlspecialchars($params['userName']);
                $password = htmlspecialchars($params['password']);
                $email = htmlspecialchars($params['email']);
                $password = md5($password);

                $user = new Models_User($email);
                if(!empty($user->getId())) {
                    $response['error_code'] =  821;
                    $response['errors'] =  $this->_errorList[821];

                    echo json_encode($response);
                    exit;
                } else {
                    $user->setFirstName($firstName);
                    $user->setLastName($lastName);
                    $user->setEmail($email);
                    $user->setUsername($username);
                    $user->setPassword($password);
                    if(!empty($this->_errorStack)) {
                        if ($user->save()) {
                            $response['error_code'] = 0;
                            $response['user'] = $user->getHash();
                        } else {
                            $response['error_code'] =  813;
                            $response['error_message'] =  $this->_errorList[813];
                        }
                    } else {
                        $response['error_code'] =  813;
                        $response['error_message'] = $this->_errorStack->getErrorList();
                    }
                }
            } else {
                $response['error_code'] =  400;
                $response['error_message'] = 'Bad Request';
            }
        } catch (Exception $ex) {
            $response['error_code'] =  813;
            $response['error_message'] =  $this->_errorList[813];
        }

        echo json_encode($response);
        exit;
    }

    /*
    * Update user details
    *
    */
    public function updateAction()
    {
        $response = array();
        try {
            $request = $this->getRequest();

            if($request->isPOST()) {
                $params = $request->getParams();
                $userId = htmlspecialchars($params['userId']);
                $email = htmlspecialchars($params['email']);
                
                if (!empty($userId) || !empty($email)) {
                    $userModel = new Models_User();
                    $user = $userModel->getUserBy($email, null, $userId);

                    if(!empty($user)) {
                        if(isset($params['firstName'])) {
                            $firstName = htmlspecialchars($params['firstName']);
                            $userModel->setFirstName($firstName);
                        }
                        if(isset($params['lastName'])) {
                            $lastName = htmlspecialchars($params['lastName']);
                            $userModel->setLastName($lastName);
                        }
                        if(isset($params['password'])) {
                            $password = md5(htmlspecialchars($params['password']));
                            $userModel->setPassword($password);
                        }
                        if ($user->save()) {
                            $response['status'] = 'OK';
                            $response['error_code'] = 0;
                        } else {
                            $response['error_code'] =  814;
                            $response['error_message'] =  $this->_errorList[814];
                        }
                    } else {
                        $response['error_code'] = 812;
                        $response['error_message'] = $this->_errorList[812];
                    }
                } else {
                    $response['error_code'] = 822;
                    $response['error_message'] = $this->_errorList[822];
                }
            } else {
                $response['error_code'] =  400;
                $response['error_message'] = 'Bad Request';
            }
        } catch (Exception $ex) {
            $response['error_code'] =  814;
            $response['error_message'] =  $this->_errorList[814];
        }

         echo json_encode($response);   
         exit;
    }

    /*
    * Get user Info
    *
    */
    public function getAction()
    {
        $response = array();
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            if(!isset($params['email'])) {
                $response['error_code'] =  803;
                $response['error_message'] =  $this->_errorList[803];
            } else {
                $email = htmlspecialchars($params['email']);
                $user = new Models_User($email, null, null, true);
                if(!$user->getHash()) {
                    $response['status'] = 'OK';
                    $response['user'] = json_encode($user);
                } else {
                    $response['error_code'] =  812;
                    $response['error_message'] =  $this->_errorList[812]; 
                }
            }
        } catch (Exception $ex) {
            $response['error_code'] = '401';
            $response['error_message'] = 'Bad Request';
        }

        echo json_encode($response);
        exit;
    }

   /*
    * Delete user Info
    * 
    */
    public function deleteAction()
    {
        $response = array();
        try {
            $request = $this->getRequest();
            $params = $request->getParams();

            if($request->isPOST()) {
                if(!isset($params['email'])) {
                    $response['error_code'] =  803;
                    $response['error_message'] =  $this->_errorList[803];
                } else {
                    $email = htmlspecialchars($params['email']);
                    $user = new Models_User($email);
                    if($user->delete()) {
                        $response['status'] = 'OK';
                        $response['error_code'] = 0;
                    } else {
                        $response['error_code'] =  819;
                        $response['error_message'] =  $this->_errorList[819]; 
                    }
                }
            } else {
                $response['error_code'] =  400;
                $response['error_message'] = 'Bad Request';
            }
        } catch (Exception $ex) {
            $response['error_code'] =  819;
            $response['error_message'] =  $this->_errorList[819];
        }

        echo json_encode($response);   
        exit;
    }

    /*
        Activate the given user.
    */
    public function activateAction() 
    {
        $response = array();
        try {
            $request = $this->getRequest();
            $params = $request->getParams();

            if($request->isPOST()) {
                if(!isset($params['email'])) {
                    $response['error_code'] =  803;
                    $response['error_message'] =  $this->_errorList[803];
                } else {
                    $email = htmlspecialchars($params['email']);
                    $user = new Models_User($email);
                    if($user->activate($user->getHash())) {
                        $response['status'] = 'OK';
                        $response['error_code'] = 0;
                    } else {
                        $response['error_code'] =  820;
                        $response['error_message'] =  $this->_errorList[820]; 
                    }
                }
            } else {
                $response['error_code'] =  400;
                $response['error_message'] = 'Bad Request';
            }
        } catch (Exception $ex) {
            $response['error_code'] =  820;
            $response['error_message'] =  $this->_errorList[820];
        }

         echo json_encode($response);   
         exit;
    }

    private function validateParams() 
    {


    }
}