<?php
/**
 * User controller
 * 
 */
class UserController
{

    private $_errorStack;
	
    /*
    * Create user Info
    *
    */
    public function createAction()
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        $request = $this->getRequest();
        $params = $request->getParams();

        $firstName = htmlspecialchars($params['firstName']);
        $lastName = htmlspecialchars($params['lastName']);
        $username = htmlspecialchars($params['userName']);
        $password = htmlspecialchars($params['password']);
        $email = htmlspecialchars($params['email']);
        $password = md5($password);
        $response = array();
        try {
            $user = new Models_User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPassword($password);
            if(!empty($this->_errorStack)) {
                $user->save();
                $response['error_code'] = 0;
                $response['user'] = $user->getHash();
            } else {
                $response['errors'] = $this->_errorStack->getErrorList();
            }
        } catch (Exception $ex) {
            $response['error_code'] = $ex->getCode();
            $response['error_message'] = $ex->getMessage();
        }

        echo json_encode($response);
        exit;
    
    }

    /*
    * Update user Info
    *
    */
    public function updateAction()
    {

        $request = $this->getRequest();
        $params = $request->getParams();
        $userId = htmlspecialchars($params['userId']);
        $firstName = htmlspecialchars($params['firstName']);
        $lastName = htmlspecialchars($params['lastName']);
        $username = htmlspecialchars($params['username']);
        $password = htmlspecialchars($params['password']);
        $email = htmlspecialchars($params['email']);
        $password = md5($password);

        $response = array();
        try {
            if (!empty($userId)) {
                $userModel = new Models_User();
                $user = $userModel->getUserBy($email, $userId);
                if(!empty($user)) {
                    $userModel->setFirstName($firstName);
                    $userModel->setLastName($lastName);
                    $userModel->setEmail($email);
                    $userModel->setUsername($username);
                    $userModel->setPassword($password);
                }
                if ($user = $user->save()) {
                    $response['status'] = 'OK';
                    $response['error_code'] = 0;
                } else {
                    $response['error_code'] = '401';
                    $response['error_message'] = 'Bad Request';
                }
            } else {
                $response['error_code'] = '402';
                $response['error_message'] = 'Email Not Available';
            }
        } catch (Exception $ex) {
            $response['error_code'] = '401';
            $response['error_message'] = 'Bad Request';
        }

         return json_encode($response);       
    
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
            $email = $params['email'];
            $userModel = new Models_User();
            $user = $userModel->getUserBy($email);

            $response['data'] = json_encode($user);
            $response['status'] = 'ok';
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
    public function deleteAction($userId)
    {
        $response = array();
        try {
        $user = new Models_User($userId,null);
            /**
             * Inactivate User Info
             */ 
            $status = '-1';
            $userId = $user->id;
            
            if ($user->id != '') {       
                if ($user->inActivate($userId)) {
                    $response['status'] = 'OK';
                    $response['error_code'] = 0;
                    $response['user_status'] = 'Inactivated';
                }
                
            } else {
                $response['error_code'] = '401';
                $response['error_message'] = 'User Id not available';
            }
           
        } catch (Exception $ex) {
            $response['error_code'] = '401';
            $response['error_message'] = 'Bad Request';
        }

         return json_encode($response);       
    
    }


}
?>