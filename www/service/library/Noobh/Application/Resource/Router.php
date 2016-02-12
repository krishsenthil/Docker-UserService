<?php

/**
 * Collash Inc Internal
 * 
 * Noobh Framework
 *
 * @category   Noobh
 * @package    Noobh
 * @subpackage Noobh_Application_Resources
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 * Collash Inc Internal
 * 
 * Core routing functions are defined in this class. Where
 * request params are extracted and routed to the correct
 * controller and action. It performs the redirect from one
 * contoller to another as well. Once all the redirect loops are
 * executed successfully then the layout / view session will be
 * loaded
 * 
 * In the routing stage we can have hooks in the following stages
 * 
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh
 * @subpackage Noobh_Application_Resources
 * @since   0.1
 * @date Mar 28, 2012
 *
 */
class Noobh_Application_Resource_Router
{
    /**
     * Error controller file name for avoiding
     * infinite loop.
     * @var {string}
     */
    const DEFAULT_ERROR_CONTROLLER_NAME = 'ErrorController';
    const DEFAULT_LAYOUT = 'layout';
    /**
     * Store default plugin path
     * @access private
     * @var {string}
     */
    private $_defaultPluginPath; 
    /**
     * Store error page url for 404,505 page
     * requests
     * @access private
     * @var {string}
     */
    private $_errorPageUrl = 'error';
    /**
     * Srore request object
     * @access private
     * @var {Noobh_Application_Resource_Request}
     */
    private $_request;
    /**
     * Store current url
     * @access private
     * @var {string}
     */
    private $_url;
    /**
     * Set folder path for loading
     * Controllers & Views
     * @var {string}
     */
    private $_folderPath;
    /**
     * Set layout path for loading layout
     * @var {string}
     */
    private $_layoutPath;
    /**
     * Store view suffix
     * @var {string}
     */
    private $_viewSuffix = 'phtml';
    /**
     * The whole framework routing and redirects are performed in this
     * constructor. This follow is called inernally by boostrap class for
     * Noobh Frame work
     * 
     * Application path should be defined as APPLICATION_PATH, Before using this
     * class
     * 
     * @todo: For performence currently we are only supporting single plugin,
     * need to support multiple plugins
     * 
     * Following are the different points in which we are performing dispatch hooks
     * 
     * 
     *   1. routerStartup - before the current route starts (@todo)
     *   2. routeShutdown - after the completion of routing (@todo)
     *   3. dispatchLoopStartup - before the dispatch loop is entered (@todo)
     *   4. preDispatch - before the current action is dispatched
     *   5. postDispatch - after the current action is dispatched
     *   6. dispatchLoopShutdown - after the dispatch loop is completed. (@todo) 
     * 
     * 
     * @access public
     * @param {Noobh_Config}
     * @return void
     */
    public function __construct ($config = NULL)
    {
       //Check application path is set before using it
       if(!defined('APPLICATION_PATH')){
         throw new Exception("You should define APPLICATION_PATH"); 
       }
       $this->_folderPath = $this->_layoutPath = APPLICATION_PATH;
       $this->_defaultPluginPath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'plugins';
       // Security check
       $this->_removeMagicQuotes();
       //Create Request and Response object
       $this->_url = $_SERVER['REQUEST_URI'];
       $this->_request = Noobh_Application_Resource_Request::getInstance();
       if($config){
           $this->_request->setConfig($config);
       }
       $this->_request->setUrl($this->_url);
       
       /**
        * Check for plugins, if present then we need
        * to inject preDispatch code at this point
        * of execution
        * 
        * Default plugin path is APPLICATION_PATH."/plugins"
        */
       if(isset($config['application']['resource']['plugins'])){
         if(count($config['application']['resource']['plugins']) > 0){
          $pluginPath = ($config['application']['resource']['pluginDirectory'])? $config['application']['resource']['pluginDirectory'] : $this->_defaultPluginPath;
          //@todo: Need to loop plugins and avoid autoloader with require_once in future
          $autoloader = Noobh_Loader_Autoloader::getInstance();
          $autoloader->registerNamespace('Plugins');
         }
       }
       /**
        * Call front controller and pass request
        */
       do{
          $this->_request->processRequest();
          /**
           * @todo: Need to change it to support multiple plugins
           */
          //Perfom preDispatch
          if(isset($pluginPath)){
          	$plugin = new $config['application']['resource']['plugins'][0];
          	if(method_exists($plugin , 'preDispatch')){
          		$plugin->preDispatch($this->_request);
          	}
          }
           /**
            * After creating controller name and action name, we reset the
            * redirect flag to make sure we are done with redirection
            */
           $this->_request->setRedirect(FALSE);
       	   /**
            * Not using auto loader to avoid runnig extra code, if
            * it is not true then we can use auto loader here
            * Including controllers dynamically
            * 
            * check if the controller file exist else redirect to the
            * error controller, it is a default property
            * @todo:
            * Need to implement error stack
            */
           $controllerName = $this->_request->getControllerName();
           //Check if the application is having modules
           if($this->_request->getModulePath()){
               //Set Controller path
               $this->_folderPath = $this->_request->getModulePath();
               //Set layout path
               if(isset($config['application']['resource']['layoutDirectory'])){
                   $this->_layoutPath = $config['application']['resource']['layoutDirectory'];
               }else{
                   $this->_layoutPath = $this->_folderPath;
               }
           }
           if(file_exists($this->_folderPath.DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR."{$controllerName}.php")){
               //Check if action exist 
               require_once $this->_folderPath.DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR."{$controllerName}.php";
               if($this->_request->getModuleName()){
                   if($this->_request->getModuleName() != Noobh_Application_Resource_Request::DEFAULT_MODULE){
                       $controllerName = ucfirst($this->_request->getModuleName()).'_'.$controllerName;
                   }
               }
               $controller = new $controllerName($this->_request);
               if(method_exists($controller, $this->_request->getActionName())){
                // Make controller action call and dispatch it
                call_user_func(array($controller,$this->_request->getActionName()));
               }else{
                   //Redirect to error controller
                   $this->_redirectToErrorController();
               }
           }else{
               /**
                * This code will be excuted when error controller is not present
                * Which will lead to infinite loop, to aviode that we need to 
                * throw exception.
                */
               if($controllerName == self::DEFAULT_ERROR_CONTROLLER_NAME){
                  throw new Exception("Error Controller is not specified");
               }
               //Redirect to error controller
               $this->_redirectToErrorController();
           }
           /**
            * @todo: Need to change it to support multiple plugins
            */
           //Perfom postDispatch
           if(isset($pluginPath)){
           	$plugin = new $config['application']['resource']['plugins'][0];
           	if(method_exists($plugin , 'postDispatch')){
           		$plugin->postDispatch($this->_request);
           	}
           }
           /**
            * Update request with view and unset controller object, some time
            * it can be too big
            */
           unset($controller);
       }while ($this->_request->isRedirected() === TRUE); 
              
		/**
		 * Calling the base layout and unload all other big objects if present
		 * in the destructor
		 */
       if($this->_request->layoutEnabled()){
           /**
            * Check default layout is over ridden by controller
            */
           if($this->_request->getLayout()){
            $layoutFile = $this->_request->getLayout();
           }else{
            $layoutFile = self::DEFAULT_LAYOUT .'.' .$this->_viewSuffix;
           }
           /**
            * By default framework look layout in the module else from
            * the location specified in the application.ini
            */
           require_once $this->_layoutPath .DIRECTORY_SEPARATOR."layout".DIRECTORY_SEPARATOR."{$layoutFile}";
          
       }else if($this->_request->viewEnabled()){
           $this->getContent();
       }
    }
    /**
     * Set view suffix, it default to 'phtml'
     *
     * @access public
     * @param {string} $suffix
     * @return void
     */
    public function setViewSuffix($suffix){
        $this->_viewSuffix = $suffix;
    }
    /**
     * Get view suffix, it default to 'phtml'
     *
     * @access public
     * @param void
     * @return {string} $suffix
     */
    public function getViewSuffix(){
        return $this->_viewSuffix;
    }
    /**
     * Get view content form the view file and render
     * 
     * @access public
     * @param void
     * @return void
     */
    public function getContent(){
        if($this->_request->viewEnabled()){
          //Passing view object to real view scope  
          $this->view = $this->_request->view;
          require_once $this->_folderPath.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."{$this->_request->getController()}".DIRECTORY_SEPARATOR."{$this->_request->getViewName()}.{$this->_viewSuffix}";
        }
        return;
    }
    /**
     * Set site header, if there is no header file
     * in the default application then also application
     * will not throw any exception
     * 
     * @access public
     * @param {string} $headerPath, file path for header
     * @return void
     */
    public function header($headerPath = NULL){
        //Check for default header path
        if(!$headerPath){
           $headerPath = $this->_layoutPath."/layout/header.{$this->_viewSuffix}";
           if(!file_exists($headerPath)){
              return;
           }
        }
        $this->render($headerPath);
        return;
    }
    
    /**
     * Set site footer, if there is no footer file
     * in the default application then also application
     * will not throw any exception
     * 
     * @access public
     * @param {string} $footerPath, file path for header
     * @return void
     */
    public function footer($footerPath = NULL){
        //Check for default footer path
        if(!$footerPath){
            $footerPath =  $this->_layoutPath.DIRECTORY_SEPARATOR."layout".DIRECTORY_SEPARATOR."footer.{$this->_viewSuffix}";
            if(!file_exists($footerPath)){
                return;
            }
           
        }
        $this->render($footerPath);
        return;
    }
    
    /**
     * Render the file from layout folder.
     * We can give flexibility over here but for code optimization
     * we are only doing includes
     * 
     * @access public
     * @param {string} $filePath, path of the file to be included
     * @return void
     */
    public function render($filePath){
        require_once $filePath;
        return;
    }  
    /**
     * This is a fuild interface for getting
     * headscripts for a view page
     * 
     * @access public
     * @param void
     * @return {string} 
     */
    public function headScript(){
      return $this->_request->getViewHelper()->getScripts(); 
    }
    /**
     * This is a fuild interface for getting
     * head style sheets for a view page
     * 
     * @access public
     * @param void
     * @return {string} 
     */
    public function headStyleSheet(){
        return $this->_request->getViewHelper()->getStyleSheets();
    }

    /**
     * This is a fuild interface for getting
     * head style sheets for a view page
     *
     * @access public
     * @param void
     * @return {string}
     */
    protected function _getViewHelper(){
     return $this->_request->getViewHelper();
    }
    
    /**
     * Get baseurl for the application
     * @access public
     * @param {string} $baseUrl
     * @return void
     */
    public function baseUrl(){
        return $this->_request->baseUrl;
    }
   /**
    * strip deep slashes
    * 
    * @access private
    * @param {string} $value
    * @return {string} $value
    */
   private function _stripSlashesDeep($value) {
       $value = is_array($value) ? array_map(array($this, '_stripSlashesDeep'), $value) : stripslashes($value);
       return $value;
   }
   /** 
    * Check for Magic Quotes and remove them
    * @access private
    * @param void
    * @return void
    */
   private function _removeMagicQuotes() {
       if ( get_magic_quotes_gpc() ) {
            $_GET    = $this->_stripSlashesDeep($_GET);
            $_POST   = $this->_stripSlashesDeep($_POST);
            $_COOKIE = $this->_stripSlashesDeep($_COOKIE);
        }
   }
   
   /**
    * Redirect request to error controller
    * @access protected
    * @param void
    * @return void
    */
   protected function _redirectToErrorController(){
     //Rewrite request object
	   $this->_request->setUrl($this->_errorPageUrl);
     $this->_request->setRedirect(TRUE);
   }
}