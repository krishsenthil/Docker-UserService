<?php
/** 
 * This is the bootstrap file for this application
 * which extends the base Noobh bootstrap. All _init{Methodname}
 * will be executed before application starts loading
 * @author SenthilrajK
 */
class Bootstrap extends Noobh_Bootstrap{
  /**
   * Load models.
   * 
   * If your application doesn't need
   * models to be autoloader in all the
   * request then you need to move the following
   * code to some other location for performance optimization
   *
   * @access public
   * @param void
   * @return void
   */
  public function _initAutoloadModels(){
      $autoloader = Noobh_Loader_Autoloader::getInstance();
      $autoloader->registerNamespace('Models');
      $autoloader->registerNamespace('Services');
      $autoloader->registerNamespace('ElasticSearch');
  }

  public function _initLoadSetup(){
    Noobh_Registry::set('errorStack', Noobh_ErrorStackSingleton::getInstance());
    session_start();
  }
}