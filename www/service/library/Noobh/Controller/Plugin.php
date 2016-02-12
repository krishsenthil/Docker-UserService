<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    Noobh
 * @subpackage Controller
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 *
 * Collash Inc Internal
 * 
 * Abstract class should be extended in all the plugins for
 * getting a hook in the following states
 *     
 * 
 * 
 *   1. routerStartup - before the current route starts (@todo)
 *   2. routeShutdown Ð after the completion of routing (@todo)
 *   3. dispatchLoopStartup Ð before the dispatch loop is entered (@todo)
 *   4. preDispatch Ð before the current action is dispatched
 *   5. postDispatch Ð after the current action is dispatched
 *   6. dispatchLoopShutdown Ð after the dispatch loop is completed. (@todo) 
 * 
 *  
 *  Request object parameters required for the plugins are injected
 *  by the router
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package    Noobh
 * @subpackage Controller
 * @since   0.1
 * @date Nov 28, 2012
 *
 */
 abstract class Noobh_Controller_Plugin{

    /**
     * @var Noobh_Application_Resource_Request
     */
    protected $_request;

    /**
     * @var Noobh_Application_Resource_Response
     */
    protected $_response;

    /**
     * Set request object
     *
     * @param Noobh_Application_Resource_Request $request
     * @return Noobh_Application_Resource_Request
     */
    public function setRequest(Noobh_Application_Resource_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Get request object
     *
     * @return Noobh_Application_Resource_Request $request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set response object
     *
     * @param Noobh_Application_Resource_Response $response
     * @return Noobh_Application_Resource_Response
     */
    public function setResponse(Noobh_Application_Resource_Response $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Get response object
     *
     * @return Noobh_Application_Resource_Response $response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Called before an action is dispatched
     *
     * @param  Noobh_Application_Resource_Response $request
     * @return void
     */
    public function preDispatch(Noobh_Application_Resource_Request $request)
    {}

    /**
     * Called after an action is dispatched
     * 
     * @param  Noobh_Application_Resource_Request $request
     * @return void
     */
    public function postDispatch(Noobh_Application_Resource_Request $request)
    {}
  
 }