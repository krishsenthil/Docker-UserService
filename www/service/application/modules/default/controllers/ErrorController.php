<?php
/**
 * Error controller is a fall back controller which
 * will be executed for handling application as well
 * as framework errors like 404,500 etc.
 * 
 * @author Senthilraj K
 *
 */
class ErrorController extends Noobh_Controller
{
    /**
     * This is the fall back action called by the
     * Noobh framework when some error occur. This
     * is a default behavior
     */
    public function indexAction()
    {  

    	//Get the current request url and pass to view
         $this->view->error = 'Sorry Somthing went wrong. Please go back to home page'; 
    }
}