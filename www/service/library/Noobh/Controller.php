<?php 
/**
 * This is Noobh base controller which is extended by 
 * all other controllers. All handy methods for handelling the
 * request over different application will be encapsulated into
 * this controller
 */
abstract class Noobh_Controller{
    /**
     * View propery name
     * @var{string} 
     */
    const VIEW = '_view';
    
    
    
    /**
     * Store is a POST OR GET Request
     * @var array
     */
    private $_params = array();    
    /**
     * Store Noobh_Request object
     * @var {Noobh_Application_Resource_Request}
     */
    protected $_request;
    /**
     * Store view object
     * @var {Object}
     */
    public $view;
    
    /**
     * Initilaze controller elements while loading a controller
     */
    public function __construct($request){
        $this->_request = $request;
        $this->view = $request->getView();
        $this->init();
        
    }
    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        
    }
    /**
     * Get request
     * @access public
     * @param {void} $url
     * @return {Noobh_Application_Resource_Request}
     */
    public function getRequest(){
        return $this->_request;
    }
    
    /**
     * Set new view for rendering. This method is
     * wrapping request's setViewName method
     * @todo: Need to validate file name
     * @access public
     * @param {string} $fileName, view file name
     * @return void
     */
    public function render($fileName){
        $this->_request->setViewName($fileName);
    }   
    /**
     * Redirect to another controller/action from current
     * controller
     * @todo: Need to validate the url
     * @access protected
     * @param {string} $url
     * @throws Throw exception if there is no url to redirect
     * @return {void}
     */
    protected function _forward($url){
    	//Update response object and set redirect flag
    	if($url){
    		$this->_request->setUrl($url);
    		$this->_request->setRedirect(TRUE);
    	}else{
    		throw new Exception('Empty url for controller redirect');
    	}
    }    
    
    /**
     * Disable view
     * @access protected
     * @param {void}
     * @return void
     */
    protected function _disableView(){
        $this->_request->disableview();
    }
    /**
     * Disable view
     * @access protected
     * @param {void}
     * @return void
     */
    protected function _disableLayout(){
        $this->_request->disableLayout();
    }    
    /**
     * Set list of params posted for current request
     * @access protected
     * @param array
     * @return void
     */
    protected function _setParams(array $params){
        $this->_params = $params;
    }

    /**
     * Set headlink for a view page
     * @access protected
     * @param array
     * @return void
     */
    protected function headLink(){
     return $this->_request->getViewHelper();
    }
    
    
}