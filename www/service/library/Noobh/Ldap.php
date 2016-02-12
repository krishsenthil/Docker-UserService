<?php

/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * @category   Noobh
 * @package    Noobh_Ldap
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap
{
    const SEARCH_SCOPE_SUB  = 1;
    const SEARCH_SCOPE_ONE  = 2;
    const SEARCH_SCOPE_BASE = 3;

    const ACCTNAME_FORM_DN        = 1;
    const ACCTNAME_FORM_USERNAME  = 2;
    const ACCTNAME_FORM_BACKSLASH = 3;
    const ACCTNAME_FORM_PRINCIPAL = 4;

    /**
     * String used with ldap_connect for error handling purposes.
     *
     * @var string
     */
    private $_connectString;

    /**
     * The options used in connecting, binding, etc.
     *
     * @var array
     */
    protected $_options = null;

    /**
     * The raw LDAP extension resource.
     *
     * @var resource
     */
    protected $_resource = null;

    /**
     * FALSE if no user is bound to the LDAP resource
     * NULL if there has been an anonymous bind
     * username of the currently bound user
     *
     * @var boolean|null|string
     */
    protected $_boundUser = false;

    /**
     * Caches the RootDSE
     *
     * @var Noobh_Ldap_Node
     */
    protected $_rootDse = null;

    /**
     * Caches the schema
     *
     * @var Noobh_Ldap_Node
     */
    protected $_schema = null;

    /**
     * @deprecated will be removed, use {@see Noobh_Ldap_Filter_Abstract::escapeValue()}
     * @param  string $str The string to escape.
     * @return string The escaped string
     */
    public static function filterEscape($str)
    {
        /**
         * @see Noobh_Ldap_Filter_Abstract
         */
        require_once 'Noobh/Ldap/Filter/Abstract.php';
        return Noobh_Ldap_Filter_Abstract::escapeValue($str);
    }

    /**
     * @deprecated will be removed, use {@see Noobh_Ldap_Dn::checkDn()}
     * @param  string $dn   The DN to parse
     * @param  array  $keys An optional array to receive DN keys (e.g. CN, OU, DC, ...)
     * @param  array  $vals An optional array to receive DN values
     * @return boolean True if the DN was successfully parsed or false if the string is
     * not a valid DN.
     */
    public static function explodeDn($dn, array &$keys = null, array &$vals = null)
    {
        /**
         * @see Noobh_Ldap_Dn
         */
        require_once 'Noobh/Ldap/Dn.php';
        return Noobh_Ldap_Dn::checkDn($dn, $keys, $vals);
    }

    /**
     * Constructor.
     *
     * @param  array|Noobh_Config $options Options used in connecting, binding, etc.
     * @return void
     * @throws Noobh_Ldap_Exception if ext/ldap is not installed
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('ldap')) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception(null, 'LDAP extension not loaded',
                Noobh_Ldap_Exception::LDAP_X_EXTENSION_NOT_LOADED);
        }
        $this->setOptions($options);
    }

    /**
     * Destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @return resource The raw LDAP extension resource.
     */
    public function getResource()
    {
        if (!is_resource($this->_resource) || $this->_boundUser === false) {
            $this->bind();
        }
        return $this->_resource;
    }

    /**
     * Return the LDAP error number of the last LDAP command
     *
     * @return int
     */
    public function getLastErrorCode()
    {
        $ret = @ldap_get_option($this->_resource, LDAP_OPT_ERROR_NUMBER, $err);
        if ($ret === true) {
            if ($err <= -1 && $err >= -17) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                /* For some reason draft-ietf-ldapext-ldap-c-api-xx.txt error
                 * codes in OpenLDAP are negative values from -1 to -17.
                 */
                $err = Noobh_Ldap_Exception::LDAP_SERVER_DOWN + (-$err - 1);
            }
            return $err;
        }
        return 0;
    }

    /**
     * Return the LDAP error message of the last LDAP command
     *
     * @param  int   $errorCode
     * @param  array $errorMessages
     * @return string
     */
    public function getLastError(&$errorCode = null, array &$errorMessages = null)
    {
        $errorCode = $this->getLastErrorCode();
        $errorMessages = array();

        /* The various error retrieval functions can return
         * different things so we just try to collect what we
         * can and eliminate dupes.
         */
        $estr1 = @ldap_error($this->_resource);
        if ($errorCode !== 0 && $estr1 === 'Success') {
            $estr1 = @ldap_err2str($errorCode);
        }
        if (!empty($estr1)) {
            $errorMessages[] = $estr1;
        }

        @ldap_get_option($this->_resource, LDAP_OPT_ERROR_STRING, $estr2);
        if (!empty($estr2) && !in_array($estr2, $errorMessages)) {
            $errorMessages[] = $estr2;
        }

        $message = '';
        if ($errorCode > 0) {
            $message = '0x' . dechex($errorCode) . ' ';
        } else {
            $message = '';
        }
        if (count($errorMessages) > 0) {
            $message .= '(' . implode('; ', $errorMessages) . ')';
        } else {
            $message .= '(no error message from LDAP)';
        }
        return $message;
    }

    /**
     * Get the currently bound user
     *
     * FALSE if no user is bound to the LDAP resource
     * NULL if there has been an anonymous bind
     * username of the currently bound user
     *
     * @return false|null|string
     */
    public function getBoundUser()
    {
        return $this->_boundUser;
    }

    /**
     * Sets the options used in connecting, binding, etc.
     *
     * Valid option keys:
     *  host
     *  port
     *  useSsl
     *  username
     *  password
     *  bindRequiresDn
     *  baseDn
     *  accountCanonicalForm
     *  accountDomainName
     *  accountDomainNameShort
     *  accountFilterFormat
     *  allowEmptyPassword
     *  useStartTls
     *  optRefferals
     *  tryUsernameSplit
     *
     * @param  array|Noobh_Config $options Options used in connecting, binding, etc.
     * @return Noobh_Ldap Provides a fluent interface
     * @throws Noobh_Ldap_Exception
     */
    public function setOptions($options)
    {
        $permittedOptions = array(
            'host'                   => null,
            'port'                   => 0,
            'useSsl'                 => false,
            'username'               => null,
            'password'               => null,
            'bindRequiresDn'         => false,
            'baseDn'                 => null,
            'accountCanonicalForm'   => null,
            'accountDomainName'      => null,
            'accountDomainNameShort' => null,
            'accountFilterFormat'    => null,
            'allowEmptyPassword'     => false,
            'useStartTls'            => false,
            'optReferrals'           => false,
            'tryUsernameSplit'       => true,
        );

        foreach ($permittedOptions as $key => $val) {
            if (array_key_exists($key, $options)) {
                $val = $options[$key];
                unset($options[$key]);
                switch ($key) {
                    case 'port':
                    case 'accountCanonicalForm':
                        $permittedOptions[$key] = (int)$val;
                        break;
                    case 'useSsl':
                    case 'bindRequiresDn':
                    case 'allowEmptyPassword':
                    case 'useStartTls':
                    case 'optReferrals':
                    case 'tryUsernameSplit':
                        $permittedOptions[$key] = ($val === true ||
                                $val === '1' || strcasecmp($val, 'true') == 0);
                        break;
                    default:
                        $permittedOptions[$key] = trim($val);
                        break;
                }
            }
        }
        if (count($options) > 0) {
            $key = key($options);
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception(null, "Unknown Noobh_Ldap option: $key");
        }
        $this->_options = $permittedOptions;
        return $this;
    }

    /**
     * @return array The current options.
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @return string The hostname of the LDAP server being used to authenticate accounts
     */
    protected function _getHost()
    {
        return $this->_options['host'];
    }

    /**
     * @return int The port of the LDAP server or 0 to indicate that no port value is set
     */
    protected function _getPort()
    {
        return $this->_options['port'];
    }

    /**
     * @return boolean The default SSL / TLS encrypted transport control
     */
    protected function _getUseSsl()
    {
        return $this->_options['useSsl'];
    }

    /**
     * @return string The default acctname for binding
     */
    protected function _getUsername()
    {
        return $this->_options['username'];
    }

    /**
     * @return string The default password for binding
     */
    protected function _getPassword()
    {
        return $this->_options['password'];
    }

    /**
     * @return boolean Bind requires DN
     */
    protected function _getBindRequiresDn()
    {
        return $this->_options['bindRequiresDn'];
    }

    /**
     * Gets the base DN under which objects of interest are located
     *
     * @return string
     */
    public function getBaseDn()
    {
        return $this->_options['baseDn'];
    }

    /**
     * @return integer Either ACCTNAME_FORM_BACKSLASH, ACCTNAME_FORM_PRINCIPAL or
     * ACCTNAME_FORM_USERNAME indicating the form usernames should be canonicalized to.
     */
    protected function _getAccountCanonicalForm()
    {
        /* Account names should always be qualified with a domain. In some scenarios
         * using non-qualified account names can lead to security vulnerabilities. If
         * no account canonical form is specified, we guess based in what domain
         * names have been supplied.
         */

        $accountCanonicalForm = $this->_options['accountCanonicalForm'];
        if (!$accountCanonicalForm) {
            $accountDomainName = $this->_getAccountDomainName();
            $accountDomainNameShort = $this->_getAccountDomainNameShort();
            if ($accountDomainNameShort) {
                $accountCanonicalForm = Noobh_Ldap::ACCTNAME_FORM_BACKSLASH;
            } else if ($accountDomainName) {
                $accountCanonicalForm = Noobh_Ldap::ACCTNAME_FORM_PRINCIPAL;
            } else {
                $accountCanonicalForm = Noobh_Ldap::ACCTNAME_FORM_USERNAME;
            }
        }

        return $accountCanonicalForm;
    }

    /**
     * @return string The account domain name
     */
    protected function _getAccountDomainName()
    {
        return $this->_options['accountDomainName'];
    }

    /**
     * @return string The short account domain name
     */
    protected function _getAccountDomainNameShort()
    {
        return $this->_options['accountDomainNameShort'];
    }

    /**
     * @return string A format string for building an LDAP search filter to match
     * an account
     */
    protected function _getAccountFilterFormat()
    {
        return $this->_options['accountFilterFormat'];
    }

    /**
     * @return boolean Allow empty passwords
     */
    protected function _getAllowEmptyPassword()
    {
        return $this->_options['allowEmptyPassword'];
    }

    /**
     * @return boolean The default SSL / TLS encrypted transport control
     */
    protected function _getUseStartTls()
    {
        return $this->_options['useStartTls'];
    }

    /**
     * @return boolean Opt. Referrals
     */
    protected function _getOptReferrals()
    {
        return $this->_options['optReferrals'];
    }

    /**
     * @return boolean Try splitting the username into username and domain
     */
    protected function _getTryUsernameSplit()
    {
        return $this->_options['tryUsernameSplit'];
    }

    /**
     * @return string The LDAP search filter for matching directory accounts
     */
    protected function _getAccountFilter($acctname)
    {
        /**
         * @see Noobh_Ldap_Filter_Abstract
         */
        require_once 'Noobh/Ldap/Filter/Abstract.php';
        $this->_splitName($acctname, $dname, $aname);
        $accountFilterFormat = $this->_getAccountFilterFormat();
        $aname = Noobh_Ldap_Filter_Abstract::escapeValue($aname);
        if ($accountFilterFormat) {
            return sprintf($accountFilterFormat, $aname);
        }
        if (!$this->_getBindRequiresDn()) {
            // is there a better way to detect this?
            return sprintf("(&(objectClass=user)(sAMAccountName=%s))", $aname);
        }
        return sprintf("(&(objectClass=posixAccount)(uid=%s))", $aname);
    }

    /**
     * @param string $name  The name to split
     * @param string $dname The resulting domain name (this is an out parameter)
     * @param string $aname The resulting account name (this is an out parameter)
     * @return void
     */
    protected function _splitName($name, &$dname, &$aname)
    {
        $dname = null;
        $aname = $name;

        if (!$this->_getTryUsernameSplit()) {
            return;
        }

        $pos = strpos($name, '@');
        if ($pos) {
            $dname = substr($name, $pos + 1);
            $aname = substr($name, 0, $pos);
        } else {
            $pos = strpos($name, '\\');
            if ($pos) {
                $dname = substr($name, 0, $pos);
                $aname = substr($name, $pos + 1);
            }
        }
    }

    /**
     * @param  string $acctname The name of the account
     * @return string The DN of the specified account
     * @throws Noobh_Ldap_Exception
     */
    protected function _getAccountDn($acctname)
    {
        /**
         * @see Noobh_Ldap_Dn
         */
        require_once 'Noobh/Ldap/Dn.php';
        if (Noobh_Ldap_Dn::checkDn($acctname)) return $acctname;
        $acctname = $this->getCanonicalAccountName($acctname, Noobh_Ldap::ACCTNAME_FORM_USERNAME);
        $acct = $this->_getAccount($acctname, array('dn'));
        return $acct['dn'];
    }

    /**
     * @param  string $dname The domain name to check
     * @return boolean
     */
    protected function _isPossibleAuthority($dname)
    {
        if ($dname === null) {
            return true;
        }
        $accountDomainName = $this->_getAccountDomainName();
        $accountDomainNameShort = $this->_getAccountDomainNameShort();
        if ($accountDomainName === null && $accountDomainNameShort === null) {
            return true;
        }
        if (strcasecmp($dname, $accountDomainName) == 0) {
            return true;
        }
        if (strcasecmp($dname, $accountDomainNameShort) == 0) {
            return true;
        }
        return false;
    }

    /**
     * @param  string $acctname The name to canonicalize
     * @param  int    $type     The desired form of canonicalization
     * @return string The canonicalized name in the desired form
     * @throws Noobh_Ldap_Exception
     */
    public function getCanonicalAccountName($acctname, $form = 0)
    {
        $this->_splitName($acctname, $dname, $uname);

        if (!$this->_isPossibleAuthority($dname)) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception(null,
                "Binding domain is not an authority for user: $acctname",
                Noobh_Ldap_Exception::LDAP_X_DOMAIN_MISMATCH);
        }

        if (!$uname) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception(null, "Invalid account name syntax: $acctname");
        }

        if (function_exists('mb_strtolower')) {
            $uname = mb_strtolower($uname, 'UTF-8');
        } else {
            $uname = strtolower($uname);
        }

        if ($form === 0) {
            $form = $this->_getAccountCanonicalForm();
        }

        switch ($form) {
            case Noobh_Ldap::ACCTNAME_FORM_DN:
                return $this->_getAccountDn($acctname);
            case Noobh_Ldap::ACCTNAME_FORM_USERNAME:
                return $uname;
            case Noobh_Ldap::ACCTNAME_FORM_BACKSLASH:
                $accountDomainNameShort = $this->_getAccountDomainNameShort();
                if (!$accountDomainNameShort) {
                    /**
                     * @see Noobh_Ldap_Exception
                     */
                    require_once 'Noobh/Ldap/Exception.php';
                    throw new Noobh_Ldap_Exception(null, 'Option required: accountDomainNameShort');
                }
                return "$accountDomainNameShort\\$uname";
            case Noobh_Ldap::ACCTNAME_FORM_PRINCIPAL:
                $accountDomainName = $this->_getAccountDomainName();
                if (!$accountDomainName) {
                    /**
                     * @see Noobh_Ldap_Exception
                     */
                    require_once 'Noobh/Ldap/Exception.php';
                    throw new Noobh_Ldap_Exception(null, 'Option required: accountDomainName');
                }
                return "$uname@$accountDomainName";
            default:
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception(null, "Unknown canonical name form: $form");
        }
    }

    /**
     * @param  array $attrs An array of names of desired attributes
     * @return array An array of the attributes representing the account
     * @throws Noobh_Ldap_Exception
     */
    protected function _getAccount($acctname, array $attrs = null)
    {
        $baseDn = $this->getBaseDn();
        if (!$baseDn) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception(null, 'Base DN not set');
        }

        $accountFilter = $this->_getAccountFilter($acctname);
        if (!$accountFilter) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception(null, 'Invalid account filter');
        }

        if (!is_resource($this->getResource())) {
            $this->bind();
        }

        $accounts = $this->search($accountFilter, $baseDn, self::SEARCH_SCOPE_SUB, $attrs);
        $count = $accounts->count();
        if ($count === 1) {
            $acct = $accounts->getFirst();
            $accounts->close();
            return $acct;
        } else if ($count === 0) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            $code = Noobh_Ldap_Exception::LDAP_NO_SUCH_OBJECT;
            $str = "No object found for: $accountFilter";
        } else {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            $code = Noobh_Ldap_Exception::LDAP_OPERATIONS_ERROR;
            $str = "Unexpected result count ($count) for: $accountFilter";
        }
        $accounts->close();
        /**
         * @see Noobh_Ldap_Exception
         */
        require_once 'Noobh/Ldap/Exception.php';
        throw new Noobh_Ldap_Exception($this, $str, $code);
    }

    /**
     * @return Noobh_Ldap Provides a fluent interface
     */
    public function disconnect()
    {
        if (is_resource($this->_resource)) {
            @ldap_unbind($this->_resource);
        }
        $this->_resource = null;
        $this->_boundUser = false;
        return $this;
    }

    /**
     * To connect using SSL it seems the client tries to verify the server
     * certificate by default. One way to disable this behavior is to set
     * 'TLS_REQCERT never' in OpenLDAP's ldap.conf and restarting Apache. Or,
     * if you really care about the server's cert you can put a cert on the
     * web server.
     *
     * @param  string  $host        The hostname of the LDAP server to connect to
     * @param  int     $port        The port number of the LDAP server to connect to
     * @param  boolean $useSsl      Use SSL
     * @param  boolean $useStartTls Use STARTTLS
     * @return Noobh_Ldap Provides a fluent interface
     * @throws Noobh_Ldap_Exception
     */
    public function connect($host = null, $port = null, $useSsl = null, $useStartTls = null)
    {
        if ($host === null) {
            $host = $this->_getHost();
        }
        if ($port === null) {
            $port = $this->_getPort();
        } else {
            $port = (int)$port;
        }
        if ($useSsl === null) {
            $useSsl = $this->_getUseSsl();
        } else {
            $useSsl = (bool)$useSsl;
        }
        if ($useStartTls === null) {
            $useStartTls = $this->_getUseStartTls();
        } else {
            $useStartTls = (bool)$useStartTls;
        }

        if (!$host) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception(null, 'A host parameter is required');
        }

        $useUri = false;
        /* Because ldap_connect doesn't really try to connect, any connect error
         * will actually occur during the ldap_bind call. Therefore, we save the
         * connect string here for reporting it in error handling in bind().
         */
        $hosts = array();
        if (preg_match_all('~ldap(?:i|s)?://~', $host, $hosts, PREG_SET_ORDER) > 0) {
            $this->_connectString = $host;
            $useUri = true;
            $useSsl = false;
        } else {
            if ($useSsl) {
                $this->_connectString = 'ldaps://' . $host;
                $useUri = true;
            } else {
                $this->_connectString = 'ldap://' . $host;
            }
            if ($port) {
                $this->_connectString .= ':' . $port;
            }
        }

        $this->disconnect();

        /* Only OpenLDAP 2.2 + supports URLs so if SSL is not requested, just
         * use the old form.
         */
        $resource = ($useUri) ? @ldap_connect($this->_connectString) : @ldap_connect($host, $port);

        if (is_resource($resource) === true) {
            $this->_resource = $resource;
            $this->_boundUser = false;

            $optReferrals = ($this->_getOptReferrals()) ? 1 : 0;
            if (@ldap_set_option($resource, LDAP_OPT_PROTOCOL_VERSION, 3) &&
                        @ldap_set_option($resource, LDAP_OPT_REFERRALS, $optReferrals)) {
                if ($useSsl || !$useStartTls || @ldap_start_tls($resource)) {
                    return $this;
                }
            }

            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            $zle = new Noobh_Ldap_Exception($this, "$host:$port");
            $this->disconnect();
            throw $zle;
        }
        /**
         * @see Noobh_Ldap_Exception
         */
        require_once 'Noobh/Ldap/Exception.php';
        throw new Noobh_Ldap_Exception(null, "Failed to connect to LDAP server: $host:$port");
    }

    /**
     * @param  string $username The username for authenticating the bind
     * @param  string $password The password for authenticating the bind
     * @return Noobh_Ldap Provides a fluent interface
     * @throws Noobh_Ldap_Exception
     */
    public function bind($username = null, $password = null)
    {
        $moreCreds = true;

        if ($username === null) {
            $username = $this->_getUsername();
            $password = $this->_getPassword();
            $moreCreds = false;
        }

        if (empty($username)) {
            /* Perform anonymous bind
             */
            $username = null;
            $password = null;
        } else {
            /* Check to make sure the username is in DN form.
             */
            /**
             * @see Noobh_Ldap_Dn
             */
            require_once 'Noobh/Ldap/Dn.php';
            if (!Noobh_Ldap_Dn::checkDn($username)) {
                if ($this->_getBindRequiresDn()) {
                    /* moreCreds stops an infinite loop if _getUsername does not
                     * return a DN and the bind requires it
                     */
                    if ($moreCreds) {
                        try {
                            $username = $this->_getAccountDn($username);
                        } catch (Noobh_Ldap_Exception $zle) {
                            switch ($zle->getCode()) {
                                case Noobh_Ldap_Exception::LDAP_NO_SUCH_OBJECT:
                                case Noobh_Ldap_Exception::LDAP_X_DOMAIN_MISMATCH:
                                case Noobh_Ldap_Exception::LDAP_X_EXTENSION_NOT_LOADED:
                                    throw $zle;
                            }
                            throw new Noobh_Ldap_Exception(null,
                                'Failed to retrieve DN for account: ' . $username .
                                ' [' . $zle->getMessage() . ']',
                                Noobh_Ldap_Exception::LDAP_OPERATIONS_ERROR);
                        }
                    } else {
                        /**
                         * @see Noobh_Ldap_Exception
                         */
                        require_once 'Noobh/Ldap/Exception.php';
                        throw new Noobh_Ldap_Exception(null, 'Binding requires username in DN form');
                    }
                } else {
                    $username = $this->getCanonicalAccountName($username,
                        $this->_getAccountCanonicalForm());
                }
            }
        }

        if (!is_resource($this->_resource)) {
            $this->connect();
        }

        if ($username !== null && $password === '' && $this->_getAllowEmptyPassword() !== true) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            $zle = new Noobh_Ldap_Exception(null,
                'Empty password not allowed - see allowEmptyPassword option.');
        } else {
            if (@ldap_bind($this->_resource, $username, $password)) {
                $this->_boundUser = $username;
                return $this;
            }

            $message = ($username === null) ? $this->_connectString : $username;
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            switch ($this->getLastErrorCode()) {
                case Noobh_Ldap_Exception::LDAP_SERVER_DOWN:
                    /* If the error is related to establishing a connection rather than binding,
                     * the connect string is more informative than the username.
                     */
                    $message = $this->_connectString;
            }

            $zle = new Noobh_Ldap_Exception($this, $message);
        }
        $this->disconnect();
        throw $zle;
    }

    /**
     * A global LDAP search routine for finding information.
     *
     * Options can be either passed as single parameters according to the
     * method signature or as an array with one or more of the following keys
     * - filter
     * - baseDn
     * - scope
     * - attributes
     * - sort
     * - collectionClass
     * - sizelimit
     * - timelimit
     *
     * @param  string|Noobh_Ldap_Filter_Abstract|array $filter
     * @param  string|Noobh_Ldap_Dn|null               $basedn
     * @param  integer                                $scope
     * @param  array                                  $attributes
     * @param  string|null                            $sort
     * @param  string|null                            $collectionClass
     * @param  integer                                  $sizelimit
     * @param  integer                                  $timelimit
     * @return Noobh_Ldap_Collection
     * @throws Noobh_Ldap_Exception
     */
    public function search($filter, $basedn = null, $scope = self::SEARCH_SCOPE_SUB, array $attributes = array(),
        $sort = null, $collectionClass = null, $sizelimit = 0, $timelimit = 0)
    {
        if (is_array($filter)) {
            $options = array_change_key_case($filter, CASE_LOWER);
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'filter':
                    case 'basedn':
                    case 'scope':
                    case 'sort':
                        $$key = $value;
                        break;
                    case 'attributes':
                        if (is_array($value)) {
                            $attributes = $value;
                        }
                        break;
                    case 'collectionclass':
                        $collectionClass = $value;
                        break;
                    case 'sizelimit':
                    case 'timelimit':
                        $$key = (int)$value;
                }
            }
        }

        if ($basedn === null) {
            $basedn = $this->getBaseDn();
        }
        else if ($basedn instanceof Noobh_Ldap_Dn) {
            $basedn = $basedn->toString();
        }

        if ($filter instanceof Noobh_Ldap_Filter_Abstract) {
            $filter = $filter->toString();
        }

        switch ($scope) {
            case self::SEARCH_SCOPE_ONE:
                $search = @ldap_list($this->getResource(), $basedn, $filter, $attributes, 0, $sizelimit, $timelimit);
                break;
            case self::SEARCH_SCOPE_BASE:
                $search = @ldap_read($this->getResource(), $basedn, $filter, $attributes, 0, $sizelimit, $timelimit);
                break;
            case self::SEARCH_SCOPE_SUB:
            default:
                $search = @ldap_search($this->getResource(), $basedn, $filter, $attributes, 0, $sizelimit, $timelimit);
                break;
        }

        if($search === false) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception($this, 'searching: ' . $filter);
        }
        if ($sort !== null && is_string($sort)) {
            $isSorted = @ldap_sort($this->getResource(), $search, $sort);
            if($isSorted === false) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception($this, 'sorting: ' . $sort);
            }
        }

        /**
         * Noobh_Ldap_Collection_Iterator_Default
         */
        require_once 'Noobh/Ldap/Collection/Iterator/Default.php';
        $iterator = new Noobh_Ldap_Collection_Iterator_Default($this, $search);
        return $this->_createCollection($iterator, $collectionClass);
    }

    /**
     * Extension point for collection creation
     *
     * @param  Noobh_Ldap_Collection_Iterator_Default    $iterator
     * @param  string|null                                $collectionClass
     * @return Noobh_Ldap_Collection
     * @throws Noobh_Ldap_Exception
     */
    protected function _createCollection(Noobh_Ldap_Collection_Iterator_Default $iterator, $collectionClass)
    {
        if ($collectionClass === null) {
            /**
             * Noobh_Ldap_Collection
             */
            require_once 'Noobh/Ldap/Collection.php';
            return new Noobh_Ldap_Collection($iterator);
        } else {
            $collectionClass = (string)$collectionClass;
            if (!class_exists($collectionClass)) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception(null,
                    "Class '$collectionClass' can not be found");
            }
            if (!is_subclass_of($collectionClass, 'Noobh_Ldap_Collection')) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception(null,
                    "Class '$collectionClass' must subclass 'Noobh_Ldap_Collection'");
            }
            return new $collectionClass($iterator);
        }
    }

    /**
     * Count items found by given filter.
     *
     * @param  string|Noobh_Ldap_Filter_Abstract $filter
     * @param  string|Noobh_Ldap_Dn|null         $basedn
     * @param  integer                          $scope
     * @return integer
     * @throws Noobh_Ldap_Exception
     */
    public function count($filter, $basedn = null, $scope = self::SEARCH_SCOPE_SUB)
    {
        try {
            $result = $this->search($filter, $basedn, $scope, array('dn'), null);
        } catch (Noobh_Ldap_Exception $e) {
            if ($e->getCode() === Noobh_Ldap_Exception::LDAP_NO_SUCH_OBJECT) return 0;
            else throw $e;
        }
        return $result->count();
    }

    /**
     * Count children for a given DN.
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @return integer
     * @throws Noobh_Ldap_Exception
     */
    public function countChildren($dn)
    {
        return $this->count('(objectClass=*)', $dn, self::SEARCH_SCOPE_ONE);
    }

    /**
     * Check if a given DN exists.
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @return boolean
     * @throws Noobh_Ldap_Exception
     */
    public function exists($dn)
    {
        return ($this->count('(objectClass=*)', $dn, self::SEARCH_SCOPE_BASE) == 1);
    }

    /**
     * Search LDAP registry for entries matching filter and optional attributes
     *
     * Options can be either passed as single parameters according to the
     * method signature or as an array with one or more of the following keys
     * - filter
     * - baseDn
     * - scope
     * - attributes
     * - sort
     * - reverseSort
     * - sizelimit
     * - timelimit
     *
     * @param  string|Noobh_Ldap_Filter_Abstract|array $filter
     * @param  string|Noobh_Ldap_Dn|null               $basedn
     * @param  integer                                $scope
     * @param  array                                  $attributes
     * @param  string|null                            $sort
     * @param  boolean                                $reverseSort
     * @param  integer                                  $sizelimit
     * @param  integer                                  $timelimit
     * @return array
     * @throws Noobh_Ldap_Exception
     */
    public function searchEntries($filter, $basedn = null, $scope = self::SEARCH_SCOPE_SUB,
        array $attributes = array(), $sort = null, $reverseSort = false, $sizelimit = 0, $timelimit = 0)
    {
        if (is_array($filter)) {
            $filter = array_change_key_case($filter, CASE_LOWER);
            if (isset($filter['collectionclass'])) {
                unset($filter['collectionclass']);
            }
            if (isset($filter['reversesort'])) {
                $reverseSort = $filter['reversesort'];
                unset($filter['reversesort']);
            }
        }
        $result = $this->search($filter, $basedn, $scope, $attributes, $sort, null, $sizelimit, $timelimit);
        $items = $result->toArray();
        if ((bool)$reverseSort === true) {
            $items = array_reverse($items, false);
        }
        return $items;
    }

    /**
     * Get LDAP entry by DN
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @param  array               $attributes
     * @param  boolean             $throwOnNotFound
     * @return array
     * @throws Noobh_Ldap_Exception
     */
    public function getEntry($dn, array $attributes = array(), $throwOnNotFound = false)
    {
        try {
            $result = $this->search("(objectClass=*)", $dn, self::SEARCH_SCOPE_BASE,
                $attributes, null);
            return $result->getFirst();
        } catch (Noobh_Ldap_Exception $e){
            if ($throwOnNotFound !== false) throw $e;
        }
        return null;
    }

    /**
     * Prepares an ldap data entry array for insert/update operation
     *
     * @param  array $entry
     * @return void
     * @throws InvalidArgumentException
     */
    public static function prepareLdapEntryArray(array &$entry)
    {
        if (array_key_exists('dn', $entry)) unset($entry['dn']);
        foreach ($entry as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $i => $v) {
                    if ($v === null) unset($value[$i]);
                    else if (!is_scalar($v)) {
                        throw new InvalidArgumentException('Only scalar values allowed in LDAP data');
                    } else {
                        $v = (string)$v;
                        if (strlen($v) == 0) {
                            unset($value[$i]);
                        } else {
                            $value[$i] = $v;
                        }
                    }
                }
                $entry[$key] = array_values($value);
            } else {
                if ($value === null) $entry[$key] = array();
                else if (!is_scalar($value)) {
                    throw new InvalidArgumentException('Only scalar values allowed in LDAP data');
                } else {
                    $value = (string)$value;
                    if (strlen($value) == 0) {
                        $entry[$key] = array();
                    } else {
                        $entry[$key] = array($value);
                    }
                }
            }
        }
        $entry = array_change_key_case($entry, CASE_LOWER);
    }

    /**
     * Add new information to the LDAP repository
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @param  array               $entry
     * @return Noobh_Ldap                  Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function add($dn, array $entry)
    {
        if (!($dn instanceof Noobh_Ldap_Dn)) {
            $dn = Noobh_Ldap_Dn::factory($dn, null);
        }
        self::prepareLdapEntryArray($entry);
        foreach ($entry as $key => $value) {
            if (is_array($value) && count($value) === 0) {
                unset($entry[$key]);
            }
        }

        $rdnParts = $dn->getRdn(Noobh_Ldap_Dn::ATTR_CASEFOLD_LOWER);
        foreach ($rdnParts as $key => $value) {
            $value = Noobh_Ldap_Dn::unescapeValue($value);
            if (!array_key_exists($key, $entry)) {
                $entry[$key] = array($value);
            } else if (!in_array($value, $entry[$key])) {
                $entry[$key] = array_merge(array($value), $entry[$key]);
            }
        }
        $adAttributes = array('distinguishedname', 'instancetype', 'name', 'objectcategory',
            'objectguid', 'usnchanged', 'usncreated', 'whenchanged', 'whencreated');
        foreach ($adAttributes as $attr) {
            if (array_key_exists($attr, $entry)) {
                unset($entry[$attr]);
            }
        }
        print_r($entry);
        $isAdded = @ldap_add($this->getResource(), $dn->toString(), $entry);
        if($isAdded === false) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception($this, 'adding: ' . $dn->toString());
        }
        return $this;
    }

    /**
     * Update LDAP registry
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @param  array               $entry
     * @return Noobh_Ldap                  Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function update($dn, array $entry)
    {
        if (!($dn instanceof Noobh_Ldap_Dn)) {
            $dn = Noobh_Ldap_Dn::factory($dn, null);
        }
        self::prepareLdapEntryArray($entry);

        $rdnParts = $dn->getRdn(Noobh_Ldap_Dn::ATTR_CASEFOLD_LOWER);
        foreach ($rdnParts as $key => $value) {
            $value = Noobh_Ldap_Dn::unescapeValue($value);
            if (array_key_exists($key, $entry) && !in_array($value, $entry[$key])) {
                $entry[$key] = array_merge(array($value), $entry[$key]);
            }
        }

        $adAttributes = array('distinguishedname', 'instancetype', 'name', 'objectcategory',
            'objectguid', 'usnchanged', 'usncreated', 'whenchanged', 'whencreated');
        foreach ($adAttributes as $attr) {
            if (array_key_exists($attr, $entry)) {
                unset($entry[$attr]);
            }
        }

        if (count($entry) > 0) {
            $isModified = @ldap_modify($this->getResource(), $dn->toString(), $entry);
            if($isModified === false) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception($this, 'updating: ' . $dn->toString());
            }
        }
        return $this;
    }

    /**
     * Add attribute values to current attributes
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @param  array               $entry
     * @return Noobh_Ldap                  Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function modadd($dn, array $entry)
    {
        if (!($dn instanceof Noobh_Ldap_Dn)) {
            $dn = Noobh_Ldap_Dn::factory($dn, null);
        }
        self::prepareLdapEntryArray($entry);

        $rdnParts = $dn->getRdn(Noobh_Ldap_Dn::ATTR_CASEFOLD_LOWER);
        foreach ($rdnParts as $key => $value) {
            $value = Noobh_Ldap_Dn::unescapeValue($value);
            if (array_key_exists($key, $entry) && !in_array($value, $entry[$key])) {
                $entry[$key] = array_merge(array($value), $entry[$key]);
            }
        }

        $adAttributes = array('distinguishedname', 'instancetype', 'name', 'objectcategory',
            'objectguid', 'usnchanged', 'usncreated', 'whenchanged', 'whencreated');
        foreach ($adAttributes as $attr) {
            if (array_key_exists($attr, $entry)) {
                unset($entry[$attr]);
            }
        }

        if (count($entry) > 0) {
            $isModified = @ldap_mod_add($this->getResource(), $dn->toString(), $entry);
            if($isModified === false) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception($this, 'updating: ' . $dn->toString());
            }
        }
        return $this;
    }

    /**
     * Add attribute values to current attributes
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @param  array               $entry
     * @return Noobh_Ldap                  Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function moddel($dn, array $entry)
    {
        if (!($dn instanceof Noobh_Ldap_Dn)) {
            $dn = Noobh_Ldap_Dn::factory($dn, null);
        }
        self::prepareLdapEntryArray($entry);

        $rdnParts = $dn->getRdn(Noobh_Ldap_Dn::ATTR_CASEFOLD_LOWER);
        foreach ($rdnParts as $key => $value) {
            $value = Noobh_Ldap_Dn::unescapeValue($value);
            if (array_key_exists($key, $entry) && !in_array($value, $entry[$key])) {
                $entry[$key] = array_merge(array($value), $entry[$key]);
            }
        }

        $adAttributes = array('distinguishedname', 'instancetype', 'name', 'objectcategory',
            'objectguid', 'usnchanged', 'usncreated', 'whenchanged', 'whencreated');
        foreach ($adAttributes as $attr) {
            if (array_key_exists($attr, $entry)) {
                unset($entry[$attr]);
            }
        }

        if (count($entry) > 0) {
            $isModified = @ldap_mod_del($this->getResource(), $dn->toString(), $entry);
            if($isModified === false) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception($this, 'updating: ' . $dn->toString());
            }
        }
        return $this;
    }

    /**
     * Save entry to LDAP registry.
     *
     * Internally decides if entry will be updated to added by calling
     * {@link exists()}.
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @param  array               $entry
     * @return Noobh_Ldap Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function save($dn, array $entry)
    {
        if ($dn instanceof Noobh_Ldap_Dn) {
            $dn = $dn->toString();
        }
        if ($this->exists($dn)) $this->update($dn, $entry);
        else $this->add($dn, $entry);
        return $this;
    }

    /**
     * Delete an LDAP entry
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @param  boolean             $recursively
     * @return Noobh_Ldap Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function delete($dn, $recursively = false)
    {
        if ($dn instanceof Noobh_Ldap_Dn) {
            $dn = $dn->toString();
        }
        if ($recursively === true) {
            if ($this->countChildren($dn)>0) {
                $children = $this->_getChildrenDns($dn);
                foreach ($children as $c) {
                    $this->delete($c, true);
                }
            }
        }
        $isDeleted = @ldap_delete($this->getResource(), $dn);
        if($isDeleted === false) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception($this, 'deleting: ' . $dn);
        }
        return $this;
    }

    /**
     * Retrieve the immediate children DNs of the given $parentDn
     *
     * This method is used in recursive methods like {@see delete()}
     * or {@see copy()}
     *
     * @param  string|Noobh_Ldap_Dn $parentDn
     * @return array of DNs
     */
    protected function _getChildrenDns($parentDn)
    {
        if ($parentDn instanceof Noobh_Ldap_Dn) {
            $parentDn = $parentDn->toString();
        }
        $children = array();
        $search = @ldap_list($this->getResource(), $parentDn, '(objectClass=*)', array('dn'));
        for ($entry = @ldap_first_entry($this->getResource(), $search);
                $entry !== false;
                $entry = @ldap_next_entry($this->getResource(), $entry)) {
            $childDn = @ldap_get_dn($this->getResource(), $entry);
            if ($childDn === false) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception($this, 'getting dn');
            }
            $children[] = $childDn;
        }
        @ldap_free_result($search);
        return $children;
    }

    /**
     * Moves a LDAP entry from one DN to another subtree.
     *
     * @param  string|Noobh_Ldap_Dn $from
     * @param  string|Noobh_Ldap_Dn $to
     * @param  boolean             $recursively
     * @param  boolean             $alwaysEmulate
     * @return Noobh_Ldap Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function moveToSubtree($from, $to, $recursively = false, $alwaysEmulate = false)
    {
        if ($from instanceof Noobh_Ldap_Dn) {
            $orgDnParts = $from->toArray();
        } else {
            $orgDnParts = Noobh_Ldap_Dn::explodeDn($from);
        }

        if ($to instanceof Noobh_Ldap_Dn) {
            $newParentDnParts = $to->toArray();
        } else {
            $newParentDnParts = Noobh_Ldap_Dn::explodeDn($to);
        }

        $newDnParts = array_merge(array(array_shift($orgDnParts)), $newParentDnParts);
        $newDn = Noobh_Ldap_Dn::fromArray($newDnParts);
        return $this->rename($from, $newDn, $recursively, $alwaysEmulate);
    }

    /**
     * Moves a LDAP entry from one DN to another DN.
     *
     * This is an alias for {@link rename()}
     *
     * @param  string|Noobh_Ldap_Dn $from
     * @param  string|Noobh_Ldap_Dn $to
     * @param  boolean             $recursively
     * @param  boolean             $alwaysEmulate
     * @return Noobh_Ldap Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function move($from, $to, $recursively = false, $alwaysEmulate = false)
    {
        return $this->rename($from, $to, $recursively, $alwaysEmulate);
    }

    /**
     * Renames a LDAP entry from one DN to another DN.
     *
     * This method implicitely moves the entry to another location within the tree.
     *
     * @param  string|Noobh_Ldap_Dn $from
     * @param  string|Noobh_Ldap_Dn $to
     * @param  boolean             $recursively
     * @param  boolean             $alwaysEmulate
     * @return Noobh_Ldap Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function rename($from, $to, $recursively = false, $alwaysEmulate = false)
    {
        $emulate = (bool)$alwaysEmulate;
        if (!function_exists('ldap_rename')) $emulate = true;
        else if ($recursively) $emulate = true;

        if ($emulate === false) {
            if ($from instanceof Noobh_Ldap_Dn) {
                $from = $from->toString();
            }

            if ($to instanceof Noobh_Ldap_Dn) {
                $newDnParts = $to->toArray();
            } else {
                $newDnParts = Noobh_Ldap_Dn::explodeDn($to);
            }

            $newRdn = Noobh_Ldap_Dn::implodeRdn(array_shift($newDnParts));
            $newParent = Noobh_Ldap_Dn::implodeDn($newDnParts);
            $isOK = @ldap_rename($this->getResource(), $from, $newRdn, $newParent, true);
            if($isOK === false) {
                /**
                 * @see Noobh_Ldap_Exception
                 */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception($this, 'renaming ' . $from . ' to ' . $to);
            }
            else if (!$this->exists($to)) $emulate = true;
        }
        if ($emulate) {
            $this->copy($from, $to, $recursively);
            $this->delete($from, $recursively);
        }
        return $this;
    }

    /**
     * Copies a LDAP entry from one DN to another subtree.
     *
     * @param  string|Noobh_Ldap_Dn $from
     * @param  string|Noobh_Ldap_Dn $to
     * @param  boolean             $recursively
     * @return Noobh_Ldap Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function copyToSubtree($from, $to, $recursively = false)
    {
        if ($from instanceof Noobh_Ldap_Dn) {
            $orgDnParts = $from->toArray();
        } else {
            $orgDnParts = Noobh_Ldap_Dn::explodeDn($from);
        }

        if ($to instanceof Noobh_Ldap_Dn) {
            $newParentDnParts = $to->toArray();
        } else {
            $newParentDnParts = Noobh_Ldap_Dn::explodeDn($to);
        }

        $newDnParts = array_merge(array(array_shift($orgDnParts)), $newParentDnParts);
        $newDn = Noobh_Ldap_Dn::fromArray($newDnParts);
        return $this->copy($from, $newDn, $recursively);
    }

    /**
     * Copies a LDAP entry from one DN to another DN.
     *
     * @param  string|Noobh_Ldap_Dn $from
     * @param  string|Noobh_Ldap_Dn $to
     * @param  boolean             $recursively
     * @return Noobh_Ldap Provides a fluid interface
     * @throws Noobh_Ldap_Exception
     */
    public function copy($from, $to, $recursively = false)
    {
        $entry = $this->getEntry($from, array(), true);

        if ($to instanceof Noobh_Ldap_Dn) {
            $toDnParts = $to->toArray();
        } else {
            $toDnParts = Noobh_Ldap_Dn::explodeDn($to);
        }
        $this->add($to, $entry);

        if ($recursively === true && $this->countChildren($from)>0) {
            $children = $this->_getChildrenDns($from);
            foreach ($children as $c) {
                $cDnParts = Noobh_Ldap_Dn::explodeDn($c);
                $newChildParts = array_merge(array(array_shift($cDnParts)), $toDnParts);
                $newChild = Noobh_Ldap_Dn::implodeDn($newChildParts);
                $this->copy($c, $newChild, true);
            }
        }
        return $this;
    }

    /**
     * Returns the specified DN as a Noobh_Ldap_Node
     *
     * @param  string|Noobh_Ldap_Dn $dn
     * @return Noobh_Ldap_Node|null
     * @throws Noobh_Ldap_Exception
     */
    public function getNode($dn)
    {
        /**
         * Noobh_Ldap_Node
         */
        require_once 'Noobh/Ldap/Node.php';
        return Noobh_Ldap_Node::fromLdap($dn, $this);
    }

    /**
     * Returns the base node as a Noobh_Ldap_Node
     *
     * @return Noobh_Ldap_Node
     * @throws Noobh_Ldap_Exception
     */
    public function getBaseNode()
    {
        return $this->getNode($this->getBaseDn(), $this);
    }

    /**
     * Returns the RootDSE
     *
     * @return Noobh_Ldap_Node_RootDse
     * @throws Noobh_Ldap_Exception
     */
    public function getRootDse()
    {
        if ($this->_rootDse === null) {
            /**
             * @see Noobh_Ldap_Node_Schema
             */
            require_once 'Noobh/Ldap/Node/RootDse.php';
            $this->_rootDse = Noobh_Ldap_Node_RootDse::create($this);
        }
        return $this->_rootDse;
    }

    /**
     * Returns the schema
     *
     * @return Noobh_Ldap_Node_Schema
     * @throws Noobh_Ldap_Exception
     */
    public function getSchema()
    {
        if ($this->_schema === null) {
            /**
             * @see Noobh_Ldap_Node_Schema
             */
            require_once 'Noobh/Ldap/Node/Schema.php';
            $this->_schema = Noobh_Ldap_Node_Schema::create($this);
        }
        return $this->_schema;
    }
}
