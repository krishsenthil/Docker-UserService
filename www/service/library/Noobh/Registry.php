<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    Noobh
 * @subpackage    Noobh
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 *
 * Collash Inc Internal
 *
 * Generic storage class helps to manage global data.
 *
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh
 * @since   0.1
 * @date Aug 28, 2012
 */
class Noobh_Registry extends ArrayObject
{
    /**
     * Class name of the singleton registry object.
     * @var string
     */
    private static $_registryClassName = 'Noobh_Registry';

    /**
     * Registry object provides storage for shared objects.
     * @var Noobh_Registry
     */
    private static $_registry = null;

    /**
     * Retrieves the default registry instance.
     *
     * @return Noobh_Registry
     */
    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }
    /**
     * Constructs a parent ArrayObject with default
     * ARRAY_AS_PROPS to allow acces as an object
     *
     * @param array $array data array
     * @param integer $flags ArrayObject flags
     */
    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS)
    {
        parent::__construct($array, $flags);
    }
    /**
     * Set the default registry instance to a specified instance.
     *
     * @param Noobh_Registry $registry An object instance of type Noobh_Registry,
     *   or a subclass.
     * @return void
     * @throws Exception if registry is already initialized.
     */
    public static function setInstance(Noobh_Registry $registry)
    {
        if (self::$_registry !== null) {
            throw new Exception('Registry is already initialized');
        }

        self::setClassName(get_class($registry));
        self::$_registry = $registry;
    }

    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init()
    {
        self::setInstance(new self::$_registryClassName());
    }

    /**
     * Set the class name to use for the default registry instance.
     * Does not affect the currently initialized instance, it only applies
     * for the next time you instantiate.
     *
     * @param string $registryClassName
     * @return void
     * @throws Exception if the registry is initialized or if the
     *   class name is not valid.
     */
    public static function setClassName($registryClassName = 'Noobh_Registry')
    {
        if (self::$_registry !== null) {
            throw new Exception('Registry is already initialized');
        }

        if (!is_string($registryClassName)) {
            throw new Exception("Argument is not a class name");
        }
        if (!class_exists($registryClassName)) {
            Noobh_Loader::loadClass($registryClassName);
        }
        self::$_registryClassName = $registryClassName;
    }

    /**
     * Unset the default registry instance.
     * Primarily used in tearDown() in unit tests.
     * 
     * When you use _unsetInstance(), all data in the static 
     * registry are discarded and cannot be recovered.
     * 
     * @returns void
     */
    public static function _unsetInstance()
    {
        self::$_registry = null;
    }

    /**
     * 
     * IMPORTANT : WILL BE DEPRICATE IN FUTURE RELEASE
     * 
     * getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type Noobh_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     * @return mixed
     * @throws Exception if no entry is registerd for $index.
     */
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new Exception("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    /**
     * 
     * IMPORTANT : WILL BE DEPRICATE IN FUTURE RELEASE
     * 
     * setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type Noobh_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *   the value.
     * @param mixed $value The object to store in the ArrayObject.
     * @return void
     */
    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    
    /**
     * getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type Noobh_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     * @return mixed
     */
    public static function getValue($index)
    {
    	$instance = self::getInstance();
    
    	if (!$instance->offsetExists($index)) {
    		return NULL;
    	}
    
    	return $instance->offsetGet($index);
    }
    
    /**
     * setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type Noobh_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     * 
     * Equalent to set() for consistency
     *
     * @param string $index The location in the ArrayObject in which to store
     *   the value.
     * @param mixed $value The object to store in the ArrayObject.
     * @return void
     */
    public static function setValue($index, $value)
    {
    	$instance = self::getInstance();
    	$instance->offsetSet($index, $value);
    }
    
    /**
     * Returns TRUE if the $index is a named value in the registry,
     * or FALSE if $index was not found in the registry.
     *
     * @param  string $index
     * @return boolean
     */
    public static function isRegistered($index)
    {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }

    /**
     * Offset exist in array list
     * 
     * @param string $index
     * @returns mixed
     */
    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }

}
