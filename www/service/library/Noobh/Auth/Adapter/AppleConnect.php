<?php
/**
	* Noobh Framework
	*
	* Collash Inc Internal
	*
	*
	* @category   Framework
	* @package    Noobh_Auth
	* @subpackage Adapter
	* @copyright  Copyright (c) Collash Inc
	* @version    0.1
	* @license    Collash Inc
	*/
	/**
	* 
	* Collash Inc Internal
	* 
	* 
	* Authenticate Collash connect users and return user
	* related information
	* 
	* Reference:
	* 
	* Wiki - Noobh Collash Connect Auth Documentation:
	* 
	* Collash Connect Auth Documentation: 
	* http://dsweb.Collash.com/dsdev/docs/daw/DS%20Auth%20Web%20Developer%20Guide.pdf
	*  
	* @author Vijay <vbose@Collash.com>
	* @copyright Collash Inc
	* @package    Noobh_Auth
	* @subpackage Adapter
	* @since   0.1
	* @date Mar 28, 2012
	* 
*/
class Noobh_Auth_Adapter_CollashConnect implements Noobh_Auth_Adapter_Interface {
	
	/**
	* Mapping mandatory params for server request
	* Any change in server params need to be updated 
	* in this location
	*/
	const APP_ID = 'appId';
	const BASE_URL = 'baseURL';
	const PATH = 'path';
	const APP_KEY = 'appIdKey';
	const APP_ADMIN_PASSWORD = 'appAdminPassword';
	const COOKIE = 'cookie';
	const IP = 'ip';
	const INFO = 'func';
	/**
	* Status response from Collash connect validation
	* 
	* For more information refer Collash Connect Documentation
	* This is fluid class so the response form the server
	* will be returned to the user and user can take further 
	* decision.
	*/
	const STATUS_SUCCESS = 0;
	const STATUS_INVALID_IP = 1; 
	const STATUS_IP_NOT_SUPPLIED = 2;
	const STATUS_INVALID_SESSION = 3; 
	const STATUS_EXPIRED_SESSION = 4; 
	const STATUS_INVALID_APP_ID = 5; 
	const STATUS_COOKIE_NOT_SUPPLIED = 6; 
	const STATUS_CAN_NOT_KEEP_ALIVE = 7;
	const STATUS_BAD_ALLGROUP_PARAM_SUPPLIED = 8;
	const STATUS_INVALID_COOKIE = 9;
	const STATUS_EXPIRED_SESSION_FOR_APP = 10;
	const STATUS_CAN_NOT_FETCH_SESSION = 11;
	const STATUS_NOT_IN_AUTHORIZED_GROUP= 12; 
	const STATUS_DS_AUTH_WEB_UNDER_MAINTENANCE= 99;
	
	// Person type validation failure redirect
	const INVALID_PERSON_TYPE_REDIRECT_URL = '/software/apps/ac_error_persontype.html';

	/**
	* Store Collash Connect server raw response
	* @access private
	* @var {array}
	*/
	private $_serverResponse;
	
	/**
	* Store Collash Connect Configurations
	* @access private
	* @var {array}
	*/
	private $_config;

	/**
	* Store error messages
	* @access private
	* @var {array}
	*/
	private $_errorMessages = array();
	
	/**
	* Store Collash authentication cookie
	* @access private
	* @var {string}
	*/
	private $_authCookie;
	
	/**
	* Store Collash authenticated user information
	* @access private
	* @var {array}
	*/
	private $_userInfo;
	
	/**
	* Store status for bypassing auth
	* @access private
	* @var {bool}
	*/
	private $_isBypassed;
	
	/**
	* Store User Identity element for
	* result class
	* @access protected
	* @var {string}
	*/
	protected $_identity = 'emailAddress';
	
	/**
	* Store list of user information retrieved from
	* Collash Connect Server
	* @access protected
	* @var {array}
	*/
	private $_userInfoList;
	
	/**
	* Store list of person types
	* @access private
	* @var {array}
	*/
	private $_personTypes = array(
		1  => 'Collash Employee',
		2  => 'Collash Contractor',
		3  => 'Independent Contractor',
		4  => 'On-site Vendor',
		5  => 'Developer',
		6  => 'Vendor',
		7  => 'Education Customer',
		8  => 'Customer Lite',
		9  => 'Reseller',
		10 => 'Customer',
		11 => 'Business Customer',
		12 => 'Job Seeker',
		13 => 'B2B Customer'
	);

	/**
	* Store application appId passed from application.ini
	* This uniquely identifies the requesting application
	* application.resource.appId = 'MAIL_TOOLS'
	*
	* @access private
	* @var {string}
	*/
	private $_appIdentifier;

	/**
	* Return Collash connect for authenticating users. Internal mapping of 
	* Noobh_Config to Collash connect params are handled in the class.
	* 
	* @access public
	* @param {Array} $config, Collash Connect configuration Noobh_Config
	* @param {String} $authCookie, User cookie stored in User browser
	* @param {Array}  $userInfo, List of user information from Collash Connect
	* @return {Noobh_Auth_Adapter_CollashConnect}
	*/
	public function __construct ($config = array(), $authCookie = NULL, $userInfo = array()) {

		$this->_config = isset($config['resource']['CollashConnect']) ? $config['resource']['CollashConnect'] : array();
		$this->_isBypassed = isset($config['resource']['CollashConnect']['bypass']) ? (bool) $config['resource']['CollashConnect']['bypass'] : FALSE;
		$this->_authCookie = trim($authCookie);
		$this->_userInfoList = $userInfo;
		$this->_appIdentifier = isset($config['application']['resource']['appId'])? $config['application']['resource']['appId'] : null;

	}

	/**
	* Get Collash Connect configuration
	* @access public
	* @param {void}
	* @return {string} $config
	*/
	public function getConfig () {
		return $this->_config;
	}

	/**
	* Set Collash Connect configuration
	* @access public
	* @param {string} $config
	* @return {void}
	*/
	public function setConfig ( $config ) {
		$this->_config = $config;
	}

	/**
	* Get Collash authentication cookie
	* @access public
	* @param {void}
	* @return {string} $authCookie
	*/
	public function getAuthCookie () {
		return $this->_authCookie;
	}

	/**
	* Set Collash authentication cookie
	* @access public
	* @param {string} $authCookie
	* @return {void}
	*/
	public function setAuthCookie ( $authCookie ) {
		$this->_authCookie = trim($authCookie);
	}

	/**
	* Get authentication is bypassed or not
	* @access public
	* @param {void}
	* @return {bool} $isBypassed
	*/
	public function isBypassed () {
		return $this->_isBypassed;
	}

	/**
	* (non-PHPdoc)
	* @see Noobh_Auth_Adapter_Interface::authenticate()
	* 
	* Check for valid auth cookie else redirect to Collash
	* connect login
	* For a valid authCookie validate the user and return 
	* user information
	* 
	* Params for Collash Connect Authentication
	* 
	* Mandatory :
	* App Id
	* Login Url
	* App Id Key
	* 
	* Optional:
	* path to redirect
	* Base Url to redirect
	* 
	* 
	* NB: If base url or path is not specified then Collash Connect will
	* redirect to base url associated with AppID
	* 
	* For all params other than login url server will be showing
	* error page, to save some execution time from validation
	* 
	* @access public
	* @param void
	* @return {Noobh_Auth_Result}
	*/
	public function authenticate () {
		
		// If bypass status is true then return dummy result
		if ( APPLICATION_ENV == 'development' && $this->_isBypassed && isset($this->_config['data']['emailAddress']) ) {
			$result = new Noobh_Auth_Result(Noobh_Auth_Result::SUCCESS,
				$this->_config['data']['emailAddress']
			);
			return $result;
		}

		if ( !$this->_authCookie ) {
			
			// Redirect to Collash login
			$loginUrl = isset( $this->_config['login']['url'] ) ? trim($this->_config['login']['url']) : null;
			$params   = isset( $this->_config['login']['params']) ? $this->_config['login']['params'] : null;
			
			// Validation
			if ( !$loginUrl ) {
				throw new Exception('Empty Collash Connect login url');
			}else if(!$params){
				throw new Exception('Empty Collash Connect login params');
			}
			
			// Adding mandatory params to request url
			$message = '';
			$loginUrl .= '?';
			if ( isset($this->_config['appId']) ) {
				$loginUrl .= self::APP_ID . '='. $this->_config['appId'];
			} else {
				$message = 'appId';
			}

			if ( isset($params['appKey']) ) {
				$loginUrl .= '&'. self::APP_KEY . '=' . $params['appKey'];
				unset($params['appKey']);
			} else {
				$message = ', appKey';
			}

			if ( $message ) {
				throw new Exception('Missing following information to login: ' . $message);
			}

			// Adding optional params to request url
			$loginUrl = $this->_getUrl($loginUrl, $params);
			header("Location: {$loginUrl}");
			
			// Make sure exit after redirect and avoid further Fatal errors
			exit;

		} else {
			
			// Validate the existing cookie and make sure the user is loggedin
			$result = $this->validate($this->_userInfoList);
		}

		return $result;
	}

	/**
	* For validating a user, the user need should be authenticated
	* and hold a valid AuthCookie. Else this method will throw 
	* exception.
	* 
	* For a valid Auth Cookie , return user information
	* For in valid Cookie, return error message from server
	* 
	* Provides a fluent interface and return the Collash Connect
	* server errors
	* 
	* Mandatory params for Collash Connect Authentication
	* App Id
	* Login Url
	* Base Url to redirect
	* App Id Key
	* 
	* 
	* @access public
	* @param {array} $userInfoList , List of user information to be retrieved from
	* 								  validation server
	* @throws Exception if there is no Auth cookie
	* @return {Noobh_Auth_Result}
	*/
	public function validate ( $userInfoList = array() ) {
		
		// If bypass status is true then return dummy result
		if ( APPLICATION_ENV == 'development' && $this->_isBypassed && isset($this->_config['data']['emailAddress']) ) {
			$result = new Noobh_Auth_Result(Noobh_Auth_Result::SUCCESS,
				$this->_config['data']['emailAddress']
			);
			
			// Set user data
			$this->_userInfo = $this->_config['data'];
			return $result;
		}
		
		if ( !$this->_authCookie ) {
			throw new Exception('Need to authenticate user before validation');
		}
		
		// Validate user and get all information
		$validateUrl = isset( $this->_config['validate']['url'] ) ? trim($this->_config['validate']['url']) : null;
		$params      = isset( $this->_config['validate']['params'] ) ? $this->_config['validate']['params'] : null;
		
		// Validation
		if ( !$validateUrl ) {
			throw new Exception('Empty Collash Connect validate url');
		} elseif ( !$params ) {
			throw new Exception('Empty Collash Connect validate params');
		}
		
		// Adding mandatory params to request url
		$message = '';
		$validateUrl .= '?' . self::COOKIE .'='. $this->_authCookie;
		if ( isset($this->_config['appId']) ) {
			$validateUrl .= '&'. self::APP_ID . '='. $this->_config['appId'];
		} else {
			$message = 'appId';
		}

		if ( isset($params['appAdminPassword']) ) {
			$validateUrl .= '&'. self::APP_ADMIN_PASSWORD . '=' . $params['appAdminPassword'];
			unset($params['appAdminPassword']);
		} else {
			$message .= ', appAdminPassword';
		}

		if ( $this->getUserIp() ) {
			$validateUrl .= '&'. self::IP . '=' . $this->getUserIp();
		} else {
			$message .= ', '.self::IP;
		}

		if ( $message ) {
			throw new Exception('Missing following information to validate: ' . $message);
		}

		// Get user identity
		unset($userInfoList[$this->_identity]);
		$params['func'] = $this->_identity;
		
		// Format url params for user information
		if ( count($userInfoList) > 0 ) {
			foreach ( $userInfoList as $info ) {
				$params['func'] .= ';' . $info;
			}
		}

		// Adding optional params to request url and make server call
		$result = $this->_getResponse($this->_getUrl($validateUrl, $params));
		return $result;

	}

	/**
	* When ever we make a server call make sure you get User Identity for
	* success response. Because it is used in the result class
	* @param {string} $url
	* @throws Exception
	*/
	protected function _getResponse ( $url ) {
		
		$this->_serverResponse = file($url);
		// print "<pre>url: </pre>"; var_dump($url);
		// print "<pre>response: </pre>"; var_dump($this->_serverResponse);
		// Parse response
		if ( count($this->_serverResponse) > 0 ) {
			
			// Validate response
			if ( !isset($this->_serverResponse[0]) || !strstr($this->_serverResponse[0], 'status=') ) {
				throw new Exception ('Status field not present in response from Collash Connect Validation Server. Validate URL: [' . $url . '], Response returned: [' . trim($this->_serverResponse[0]) . ']');
			}

			$status = explode('=', $this->_serverResponse[0]);
			if ( $status[1] == self::STATUS_SUCCESS ) {
				$this->_userInfo = $this->_formatReponse($this->_serverResponse);

				// Get Email Address as Identity 
				$emailAddress = isset($this->_userInfo[$this->_identity])? $this->_userInfo[$this->_identity] : null;

				// Get person type
				$prsTypeCode = isset($this->_userInfo['prsTypeCode'])? $this->_userInfo['prsTypeCode'] : null;
				
				// Check if user prsTypeCode is <= 4 for internal user. All other user types are blocked
				$prsTypeCodeAllowedList = array(
					1, // Collash Employee
					2, // Collash Contractor
					3, // Independent Contractor
					4  //On-site Vendor
				);
				
				// Add exception for mail tools. Allow prsTypeCode=6 (Vendor) to be able to access it.
				if ( !empty($this->_appIdentifier) && $this->_appIdentifier == 'MAIL_TOOLS' ) {
					$prsTypeCodeAllowedList[] = 6; // Vendor
				}

				if ( !empty($prsTypeCode) && !in_array($prsTypeCode, $prsTypeCodeAllowedList) ) {
					
					// block users
					$result = new Noobh_Auth_Result(Noobh_Auth_Result::FAILURE, null, array('Invalid person type'));
					$info = array(
						'name'        => $this->_userInfo['firstName'] . ' ' . $this->_userInfo['lastName'],
						'email'       => $this->_userInfo['emailAddress'],
						'person_type' => $this->_personTypes[$prsTypeCode],
						'referrer'    => $_SERVER['REQUEST_URI']
					);

					$qryStr = base64_encode(serialize($info));
					
					// redirect to generic page
					header('Location: ' . self::INVALID_PERSON_TYPE_REDIRECT_URL . '?q=' . $qryStr);

					exit;

				} elseif ( !empty($prsTypeCode) && in_array($prsTypeCode, $prsTypeCodeAllowedList) ) {
					
					// allow these users
					$result = new Noobh_Auth_Result(Noobh_Auth_Result::SUCCESS, $emailAddress);

				} else {
					
					$result = new Noobh_Auth_Result(Noobh_Auth_Result::FAILURE, null, array('Invalid person type'));
				}
			} elseif ( $status[1] == self::STATUS_NOT_IN_AUTHORIZED_GROUP ) {
				
				// redirect to generic page
				//header('Location: ' . self::INVALID_PERSON_TYPE_REDIRECT_URL);
				$result = new Noobh_Auth_Result(Noobh_Auth_Result::UNAUTHORIZED, -1);
				//exit;
			} else {
				
				// Validating Reason
				if( !isset($this->_serverResponse[1]) || !strstr($this->_serverResponse[1], 'reason=') ) {
					throw new Exception ('Failure reason not present in response from Collash Connect Validation Server');
				}
				/**
				* Due to many reasons server validation can fail and we 
				* map failures to the Auth result object to get a common
				* fluid interface for all Noobh auth results
				*/
				$reason = explode('=',$this->_serverResponse[1]);
				$result = new Noobh_Auth_Result(Noobh_Auth_Result::FAILURE_ADAPTER_SPECIFIC, NULL, array($reason[1]));
			}
		} else {
			//There is no response from the validation server and something goes wrong
			throw new Exception('No response from validation server');
		}
		return $result;
	}
	
	/**
	* Format authenticated user information
	*
	* @access protected
	* @param {array}
	* @return {array}
	*/
	protected function _formatReponse ( $serverResponse ) {
		$result = array();
		if ( count($serverResponse) ) {
			foreach ( $serverResponse as $element ) {
				$explode = explode('=',$element);
				$result[$explode[0]] = trim($explode[1]);
			}
		}
		return $result;
	}
	
	/**
	* Return response from the server as array. 
	* This is a handy method for users to get direct
	* response from server
	*
	* @access public
	* @param void
	* @return {array}
	*/
	public function getServerResponse () {
		$this->_serverResponse;
	}

	/**
	* Return user ip address
	*
	* @access protected
	* @param void
	* @return {string}
	*/
	public function getUserIp() {

		if ( getenv("HTTP_X_FORWARDED_FOR") ) {
			return getenv("HTTP_X_FORWARDED_FOR");
		} elseif ( getenv("REMOTE_ADDR") ) {
			return getenv("REMOTE_ADDR");
		} elseif ( getenv("SERVER_ADDR") ) {
			return getenv("SERVER_ADDR");
		} elseif ( getenv("HTTP_PC_REMOTE_ADDR") ) {
			return getenv("HTTP_PC_REMOTE_ADDR");
		} else {
			return null;
		}
	
	}

	/**
	* Return user information from Collash Connect server
	* 
	* @access public
	* @param void
	* @return {array}
	*/
	public function getUserInfo () {
		return $this->_userInfo;
	}

	/**
	* Create Collash connect login and validation url for authentication
	* 
	* @param protected
	* @param {string} $url to add params dynamically
	* @return {string}
	*/
	protected function _getUrl ( $url, $params ) {
		if ( $url && count($params) > 0 ) {
			foreach ( $params as $key => $value ) {
				if ( $value ) {
					if($key == 'baseUrl'){
						$key = self::BASE_URL;
					}
					$url .= '&' . trim($key) . '=' . trim($value);
				}
			}
		}
		return $url;
	}

} // class
