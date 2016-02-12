<?php
/**
 * This is a singleton class which is a
 * single instance which serves single
 * response
 * @author Vijay
 *
 */
class Noobh_Application_Resource_Response{
    
    private static $_response;
    
    /**
     * This is a private constructor to avoid
     * multiple object creation
     */
    private function __construct(){
        
    }
    /**
     * Get request instance
     */
    public static function getInstance()
    {
        if (!self::$_response)
        {
            self::$_response = new self();
        }
    
        return self::$_response;
    }
    
}