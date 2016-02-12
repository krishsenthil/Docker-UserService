<?php
/**
 * This is a singleton class which is a
 * single instance which serves single
 * request. Avoid using controller and view object
 * for optimization. Currently controller name
 * action name , view object and view name are the
 * properties of this class
 * @author Vijay
 *
 */
class Noobh_Application_Resource_Request {    
    /**
     * Following are the list of http
     * request types supported
     * @var string
     */
    const POST = 'POST';
    const GET = 'GET';
    /**
     * Default module name for all applications
     * 
     * @var string
     */
    const DEFAULT_MODULE = 'default';
        
    /**
     * To store view object
     * @access public
     * @var {stdObject}
     */
    public $view;
    
    /**
     * Store view helper
     * @access public
     * @var {string}
     */
    private $_viewHelper;
    
    /**
     * To store current request's base url
     * @access public
     * @var {string}
     */    
    public $baseUrl;
    /**
     * To store current request object
     * @access public
     * @var {Noobh_Application_Resource_Request}
     */
    private static $_request;
    /**
     * Contain list of urls requested and redirected by 
     * the applicaition
     * @access private
     * @var {array}
     */
    private $_requestStack = array();
    /**
     * Store current Url served by the request
     * @access private
     * @var {string}
     */
    private $_currentUrl = NULL;
    /**
     * Store config object
     * @access private
     * @var {Noobh_Config}
     */
    private $_config = NULL;
    /**
     * Store current controller from url
     * @access private
     * @var {string}
     */
    private $_controller;
    /**
     * Store current action from url
     * @access private
     * @var {string}
     */
    private $_action;    
    /**
     * Store current module name from url
     * @access private
     * @var {string}
     */
    private $_moduleName;
    /**
     * Store current module path from url
     * @access private
     * @var {string}
     */
    private $_modulePath;
    /**
     * Store current controller name,
     * actual controller file name
     * @access private
     * @var {string}
     */
    private $_controllerName;
    /**
     * Store current action name,
     * actual action function name
     * @access private
     * @var {string}
     */
    private $_actionName;
    /**
     * Store current view name
     * @var {string}
     */
    private $_viewName;
    
    /**
     * Check current request is rediected or not
     * @access private
     * @var {boolean}
     */
    private $_isRedirected = FALSE;
    /**
     * Check current request enables view 
     * @access private
     * @var {boolean}
     */
    private $_displayView = TRUE;
    /**
     * Check current request enables layout
     * @access private
     * @var {boolean}
     */
    private $_displayLayout = TRUE;
    /**
     * Set new layout from controller.By default framework will be loading
     * layout.phtml.But from the controller we can over ride default layout
     * @access private
     * @var {string}
     */
    private $_layout = '';
    /**
     * Store list of params for current request
     * @access private
     * @var {array}
     */
    private $_params = array();
    /**
     * Store request type
     * @access private
     * @var {string}
     */
    private $_type;
    /**
     * This is a private constructor to avoid
     * multiple object creation
     */
    private function __construct(){
        $this->getView();
    }
    /**
     * Get request instance
     */
    public static function getInstance()
    {
        if (!self::$_request)
        {
            self::$_request = new self();
        }
    
        return self::$_request;
    }
    
    /**
     * Set current config file
     * @param config file path
     */
    public function setConfig($config){
        $this->_config = $config;
    }
    /**
     * Return a stdClass object for passing view
     * from controller to view
     * 
     * @access public
     * @param void
     * @return {stdClass}
     */    
    public function getView(){
       $this->view = new stdClass();
       return $this->view; 
    }
    
    /**
     * Return current serving url
     */
    public function getUrl(){
        return $this->_currentUrl;
    }
    /**
     * Set new url to request object
     */
    public function setUrl($url){
       $this->_currentUrl = $url; 
       $this->_requestStack[] = $this->_currentUrl;
    }
    /**
     * Get action for the current
     * request
     */
    public function getAction(){
        return $this->_action;
    }
    
    /**
     * Get controller for the current
     * request
     */
    public function getController(){
        return $this->_controller;
    }
    /**
     * Get action name for the current
     * request
     */
    public function getActionName(){
        return $this->_actionName;
    }
    
    /**
     * Get controller name for the current
     * request
     */
    public function getControllerName(){
        return $this->_controllerName;
    }
    /**
     * Get view name for the current
     * request
     */
    public function getViewName(){
        return $this->_viewName;
    }
    /**
     * Set view name for the current
     * request
     * @access public
     * @param {string} $viewName
     * @return void
     */
    public function setViewName($viewName){
        if($viewName){
            $this->_viewName = $viewName;
        }
    }

    /**
     * Set module name for the current
     * request
     * @access public
     * @param {string} $moduleName
     * @return void
     */
    public function setModuleName($moduleName){
    	if($moduleName){
    		$this->_moduleName = $moduleName;
    	}
    }
    
    /**
     * Set controller name for the current
     * request
     * @access public
     * @param {string} $controllerName
     * @return void
     */
    public function setControllerName($controllerName){
    	if($controllerName){
    		$this->_controllerName = $controllerName;
    	}
    }
    /**
     * Set action name for the current
     * request
     * @access public
     * @param {string} $controllerName
     * @return void
     */
    public function setActionName($actionName){
    	if($actionName){
    		$this->_actionName = $actionName;
    	}
    } 

    /**
     * Set action for the current
     * request
     * 
     * This method is used for overridding
     * an existing request from plugins and
     * action. For internal routing
     * 
     * If passed null then default to indexAction
     */
    public function setAction($action){
     if($action){
      $this->_action = $action;
     }else{
      $this->_action = 'index';
     }
     $this->_actionName = $this->_capitalizeUrl($this->_action.'Action');
     return $this;
    }
    
    /**
     * Set controller for the current
     * request
     * 
     * This method is used for overridding
     * an existing request from plugins and
     * controller. For internal routing
     * 
     * If passed null then default to indexController
     */
    public function setController($controller){
     if($controller){
      $this->_controller = $controller;
     }else{
      $this->_controller = 'index';
     }
     $this->_controllerName = $this->_capitalizeUrl($this->_controller.'Controller');
     return $this;
    }
    
    /**
     * Set view for the current
     * request
     *
     * This method is used for overridding
     * an existing request from plugins and
     * view. For internal routing
     *
     * If passed null then default to indexController
     */
    public function setView($view){
    	if($view){
    		$this->_view = $view;
    	}else{
    		$this->_view = 'index';
    	}
    	$this->_viewName = $this->_view;
    	return $this;
    }
    
    /**
     * Set Module for the current
     * request
     *
     * This method is used for overridding
     * an existing request from plugins and
     * module. For internal routing
     *
     * If passed null then default to indexController
     */
    public function setModule($module){
    	if($controller){
    		$this->_moduleName = $module;
    	}else{
    		$this->_moduleName = 'default';
    	}
    	return $this;
    }
    
    /**
     * Set new layout for the request
     * @access public
     * @param {string} $layout
     * @return void
     */
    public function setLayout($layout){
    	$this->_layout = $layout;
    }
    /**
     * Get current request's layout
     * @access public
     * @param {boolean} by default the value is true
     * @return void
     */
    public function getLayout(){
    	return $this->_layout;
    }
    
    /**
     * Get module from current url
     * @access public
     * @param void
     * @return {string} $module
     */
    public function getModuleName(){
        return $this->_moduleName;
    }
    /**
     * Get module path from current url
     * @access public
     * @param void
     * @return {string} $modulePath
     */
    public function getModulePath(){
        return $this->_modulePath;
    }
    /**
     * Get all params for the current request
     * @access public
     * @param void
     * @return {array} list of params for this request
     */
    public function getParams(){
        return $this->_params;
    }
    /**
     * Get param value for specific element from
     * current request
     * @access public
     * @param void
     * @return {mixed} 
     */
    public function getParam($key){
        return isset($this->_params[$key]) ? $this->_params[$key] : NULL;
    
    }    
    /**
     * Check layout enabled
     * @access public
     * @param void
     * @return {boolean} by default the value is true
     */
    public function layoutEnabled(){
        return $this->_displayLayout;
    }
    
    /**
     * Check view enabled
     * @access public
     * @param void
     * @return {boolean} by default the value is true
     */
    public function viewEnabled(){
        return $this->_displayView;
    }
    /**
     * Disable layout
     * @access public
     * @param {boolean} by default the value is true
     * @return void
     */
    public function disableLayout(){
        $this->_displayLayout = FALSE;
    }   
    
    /**
     * Disable view
     * @access public
     * @param {boolean} by default the value is true
     * @return void
     */
    public function disableView(){
        $this->_displayView = FALSE;
    }    
    /**
     * To set the request is redirected or not
     * @access public
     * @param {boolean} by default the value is false
     * @return void
     */
    public function setRedirect($status = FALSE){
       $this->_isRedirected = $status; 
    }
    /**
     * Check is a get request
     * @access public
     * @param {void}
     * @return {boolean}
     */
    public function isGet(){
        if($this->_type == self::GET){
            return TRUE;
        }
        return FALSE;
    }  
    /**
     * Check is a POST request
     * @access public
     * @param {void}
     * @return {boolean}
     */
    public function isPOST(){
        if($this->_type == self::POST){
            return TRUE;
        }
        return FALSE;
    }  
    
    /**
     * Get view helper
     * @access public
     * @param {void}
     * @return {Noobh_View_Helper}
     */
    public function getViewHelper(){
     if(!$this->_viewHelper){
      $this->_viewHelper = new Noobh_View_Helper();
     }
     return $this->_viewHelper;
    }
    
    /**
     * Check current request is ajax request
     * @access public
     * @param {void}
     * @return {boolean}
     */    
    public function isXmlHttpRequest(){
        return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }
    
    /**
     * Get controller name for the current
     * request
     * @access public
     * @param {void}
     * @return {void}
     */
    public function isRedirected(){
        return $this->_isRedirected;
    }
    
    /**
     * Redirect to a new url, equalent to 
     * php header redirect
     * 
     * Note: Only pass url part after baseUrl
     * 
     * @access public
     * @param {string} $url, url to be redirected
     * @return {void}
     */
    public function redirectUrl($url){
     header('Location: '. $this->_currentHost().$this->baseUrl.$url);
     //Exist after redirect to stop further processing
     exit(0);
    }
    
    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @access public
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     */
    public function getHeader($header)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }    
        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$header])) {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $header) {
                    return $value;
                }
            }
        }
    
        return false;
    }
    
    /**
     * Return request ip address
     *
     * @access public
     * @param void
     * @return {string}
     */
    public function getIpaddress(){
    	if(getenv("REMOTE_ADDR")) {
    		$remoteIP = getenv("REMOTE_ADDR");
    	} elseif(getenv("HTTP_PC_REMOTE_ADDR")) {
    		$remoteIP = getenv("HTTP_PC_REMOTE_ADDR");
    	} elseif(getenv("HTTP_X_FORWARDED_FOR")) {
    		$remoteIP = getenv("HTTP_X_FORWARDED_FOR");
    	} else {
    		return NULL;
    	}
    	return $remoteIP;
    }
    
    /**
     * Procress request to controller/action
     * @access public
     * @param {void}
     * @return {void}
     */
    public function processRequest(){
        if ($this->_currentUrl) {
            $explodeGet = explode('?',$this->_currentUrl);
            $this->_currentUrl = $explodeGet[0];
            $this->_currentUrl = trim($this->_currentUrl, '/');
            $this->baseUrl = ($this->baseUrl)? $this->_config['application']['resource']['baseUrl']: null;
            if (! $this->_isRedirected) {
                if (isset($this->_config['application']['resource']['baseUrl'])) {
                    /**
                     * Even if the base url is dynamically changed in the request flow
                     * we need to get the active beaseurl while processing the request
                     */
                    $this->baseUrl = $this->_config['application']['resource']['baseUrl'];
                    $baseUrl = trim($this->baseUrl,'/');
                    $explode = explode($baseUrl, $this->_currentUrl);
                    /**
                     * If explode[0] contain null then the baseurl specified in
                     * the
                     * application.ini is present in the url and we will
                     * consider the
                     * rest of the url.
                     * Else the application base url is not in match
                     * the requested url
                     */
                    if (count($explode) == 2) {
                        // Base url match with requested url
                        $this->_currentUrl = $explode[1];
                    } else {
                        // Base url is not having any match with current requested URL
                        throw new Exception(
                                'Base url specified in application.ini is not having any match with requested URL');
                    }
                
                }
                /**
                 * Check for loaded modules and set current module.
                 * If module is enabled and not refered in the url it will
                 * assign 'default' module as the current module
                 */
                if (isset($this->_config['application']['resource']['moduleDirectory'])) {
                	//Adding default module to module list
                	$this->_config['application']['resource']['modules'][] = self::DEFAULT_MODULE;
                	//Clean up module path and re-add slashes
                	//$this->_modulePath = trim($this->_config['application']['resource']['moduleDirectory'],DIRECTORY_SEPARATOR);
                	$this->_modulePath = $this->_config['application']['resource']['moduleDirectory'] . DIRECTORY_SEPARATOR;
                	//Get module for current request
                	$url = $this->_currentUrl;
                	if($this->_currentUrl){
                		//Get first portion from the url and make sure it is module name or not
                		$explode = explode('/',ltrim($this->_currentUrl,'/'),2);
                		$this->_moduleName = $explode[0];
                	}
                	/**
                	 * Check for current module name in the loaded module list, if not present
                	 * then module name will be 'default'
                	 */
                	if(isset($this->_config['application']['resource']['modules'])){
                		if(in_array($this->_moduleName, $this->_config['application']['resource']['modules'])){
                			//Set new current URL
                			$this->_currentUrl = isset($explode[1])? $explode[1] : "";
                		}else{
                			$this->_moduleName = self::DEFAULT_MODULE;
                		}
                	}
                	$this->_modulePath = $this->_modulePath . $this->_moduleName;
                }
            }
            
            /**
             * Get controller,action and params
             */
            if(!$this->_currentUrl){
                //Redirect to index controller and index action
                $this->_currentUrl = 'index/index';
            }            
            $this->_currentUrl = trim($this->_currentUrl, '/');
            //Explode url
            $urlArray = explode("/", $this->_currentUrl);
            /**
             * Get Controller and action name
             * At this point there should be a controller
             * atleast with the fallback logic to IndexController
             */
            $this->_controller = $urlArray[0];
            $this->_action = (isset($urlArray[1]))? $urlArray[1] : 'index';
            //Convert url '-' to capital letters
            $this->_controllerName = $this->_capitalizeUrl($this->_controller).'Controller';
            $this->_actionName = $this->_capitalizeUrl($this->_action). 'Action';
            $this->_viewName = $this->_action;
            unset($urlArray[0]);
            unset($urlArray[1]);
            $this->_getRequestParams($urlArray);
        }
    }
    /**
     * Convert '-' to capitalized word for controller name
     * and action name
     * @access protected
     * @param {string} $string
     * @return {$string} $formattedString
     */
    protected function _capitalizeUrl($string){
        $formattedString = '';
        if($string){
            $explode = explode('-',$string);
            $string = NULL;
            if(count($explode) > 0){
                foreach($explode as $element){
                    $string = ($string) ? $string.ucfirst($element) : ucfirst($element);
                }
                $formattedString = $string;
            }else{
                $formattedString = ucfirst($explode[0]);
            }
            
        }
        return $formattedString;
    }
    /**
     * Get current requested host
     * @access protected
     * @param {void} void
     * @return {string} $currentHost
     */
    protected function _currentHost() {
    	$currentHost = 'http';
    	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$currentHost .= "s";}
    	$currentHost .= "://";
    	if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
    		$currentHost .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
    	} else {
    		$currentHost .= $_SERVER["SERVER_NAME"];
    	}
    	return $currentHost;
    }
    
    /**
     * Extract request params from php global
     * request Variables and clean url format
     * @access protected
     * @param {array} $params
     * @return {void}
     */
    protected function _getRequestParams($params = array()){
        /**
         * This values are url params passed as part of clean url like
         * /key1/value1/key2/value2/...
         * Create associative array from params
         */
        if(count($params) > 0){
            $params = array_values($params);
            $count = 0;
            do{
                //Support clean url
                $this->_params[$params[$count]] = isset($params[$count + 1])? $params[$count + 1] : '';
                $count = $count + 2;
            }while(count($params) > $count);
            $this->_type = self::GET;
        }
        //Get Params from POST and GET request
        if($_GET){
             $this->_type = self::GET;
             $this->_params = array_merge($this->_params,$_GET); 
        }else if($_POST){
             $this->_type = self::POST;
             $this->_params = array_merge($this->_params,$_POST);             
        }
    }
}