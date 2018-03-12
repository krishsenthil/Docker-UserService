<?php
/**
 * User Address controller
 * 
 */
class AddressController
{

    private $_errorStack;

    /*
    * Create user address Info
    *
    */
    public function createAction()
    {

        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();

        $request = $this->getRequest();
        $params = $request->getParams();

        $userId = htmlspecialchars($params['userId']);
        $address1 = htmlspecialchars($params['address1']);
        $address2 = htmlspecialchars($params['address2']);
        $zipCode = htmlspecialchars($params['zipCode']);
        $country = htmlspecialchars($params['country']);
        $mobileNumber = htmlspecialchars($params['mobileNumber']);
        $homeNumber = htmlspecialchars($params['homeNumber']); 
        $response = array();
        try {
            /**
             * Create User Info
             */ 
            $addressModel = new Models_Address();
            $address = $addressModel->getAddressBy($userId,null);
            if(empty($address)) {
                $addressModel->setUserId($userId);      
                $addressModel->setAddress1($address1);
                $addressModel->setAddress2($address2);
                $addressModel->setZipCode($zipCode);
                $addressModel->setCountry($country);
                $addressModel->setMobileNumber($mobileNumber);
                $addressModel->setHomeNumber($homeNumber);
                if(empty($this->_errorStack)) {
                    $addressModel->save();
                    $response['status'] = 'OK';
                    $response['error_code'] = 0;
                } else {
                    $response['errors'] = $this->_errorStack->getErrorList();
                }
            } else {
                $response['error_code'] = '402';
                $response['error_message'] = 'Address available for the user';
            }
        } catch (Exception $ex) {
            $response['error_code'] = $ex->getCode();
            $response['error_message'] = $ex->getMessage();
        }

        echo json_encode($response);
        exit;      
    
    }

    /*
    * Update user address Info
    *
    */
    public function updateAction()
    {
        $this->_errorStack = Noobh_ErrorStackSingleton::getInstance();
        $userId = htmlspecialchars($_POST['userId']);
        $address1 = htmlspecialchars($_POST['address1']);
        $address2 = htmlspecialchars($_POST['address2']);
        $zipCode = htmlspecialchars($_POST['zipCode']);
        $country = htmlspecialchars($_POST['country']);
        $mobileNumber = htmlspecialchars($_POST['mobileNumber']);
        $homeNumber = htmlspecialchars($_POST['homeNumber']); 
        $response = array();
        try {
            /**
             * Update User address Info
             */ 
            if (!empty($userId)) {
                $addressModel = new Models_Address();
                $address = $addressModel->getAddressBy($userId,null);
                if(!empty($address)) {       
                    $addressModel->setAddress1($address1);
                    $addressModel->setAddress2($address2);
                    $addressModel->setZipCode($zipCode);
                    $addressModel->setCountry($country);
                    $addressModel->setMobileNumber($mobileNumber);
                    $addressModel->setHomeNumber($homeNumber);
                    if (empty($this->_errorStack)) {
                        $addressModel->save();
                        $response['status'] = 'OK';
                        $response['error_code'] = 0;
                    } else {
                        $response['errors'] = $this->_errorStack->getErrorList();
                    }
                }
            } else {
                $response['error_code'] = '402';
                $response['error_message'] = 'User Not Available';
            }
        } catch (Exception $ex) {
            $response['error_code'] = $ex->getCode();
            $response['error_message'] = $ex->getMessage();
        }

        echo json_encode($response);
        exit;      
    
    }

    /*
    * Get user address Info
    *
    */
    public function getAction()
    {
        $response = array();
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $userId = $params['userId'];
            $addressModel = new Models_Address();
            $address = $addressModel->getAddessBy($userId,null);
            $response['data'] = json_encode($address);
            $response['status'] = 'ok';
        } catch (Exception $ex) {
            $response['error_code'] = $ex->getCode();
            $response['error_message'] = $ex->getMessage();
        }

        echo json_encode($response);
        exit;
    
    }
    



}
?>