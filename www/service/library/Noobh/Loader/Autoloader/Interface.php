<?php

/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Noobh
 * @package    Noobh
 * @subpackage Autoloader
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 * Autoloader interface.
 *
 * @package    Noobh_Loader
 * @subpackage Autoloader
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
interface Noobh_Loader_Autoloader_Interface
{
    /**
     * Autoload a class
     *
     * @abstract
     * @param   string $class
     * @return  mixed
     *          False [if unable to load $class]
     *          get_class($class) [if $class is successfully loaded]
     */
    public function autoload($class);
}
