<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * LiteCommerce
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@litecommerce.com so we can send you a copy immediately.
 * 
 * @category   LiteCommerce
 * @package    XLite
 * @subpackage ____sub_package____
 * @author     Creative Development LLC <info@cdev.ru> 
 * @copyright  Copyright (c) 2010 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version    SVN: $Id$
 * @link       http://www.litecommerce.com/
 * @see        ____file_see____
 * @since      3.0.0
 */

/**
 * Application singleton
 * 
 * @package    Litecommerce
 * @subpackage XLite
 * @since      3.0.0 EE
 */
class XLite extends XLite_Base implements XLite_Base_ISingleton
{
    /**
     * Endpoints
     */

    const CART_SELF  = 'cart.php';
    const ADMIN_SELF = 'admin.php';

    /**
     * This target will be used if the "target" params is not passed in the request
     */

    const TARGET_DEFAULT = 'main';


    /**
     * Flag; determines if we need to cleanup (and, as a result, to rebuild) classes and templates cache
     *
     * @var    bool
     * @access protected
     * @since  3.0
     */
    protected $isNeedToCleanupCache = false;

    /**
     * Config options hash
     * 
     * @var    array
     * @access protected
     * @since  3.0.0 EE
     */
    protected $options = null;

    /**
     * Current area flag
     *
     * @var    bool
     * @access public
     * @since  3.0.0 EE
     */
    public $adminZone = false;

    /**
     * TODO - check if it's realy needed 
     * 
     * @var    mixed
     * @access public
     * @since  3.0.0 EE
     */
    public $_xlite_form_id = null;

    /**
     * Called controller 
     * 
     * @var    XLite_Controller_Abstract
     * @access public
     * @since  3.0.0 EE
     */
    public static $controller = null;


    /**
     * It's not possible to instantiate this class using the "new" operator 
     * 
     * @return void
     * @access protected
     * @since  3.0.0 EE
     */
    protected function __construct()
    {
    }

    /**
     * Parse config file and return options list
     * 
     * @return array
     * @access protected
     * @since  3.0.0 EE
     */
    protected function parseConfigFile()
    {
        $options = parse_ini_file(LC_ROOT_DIR . 'etc' . LC_DS . 'config.php', true);

        if (is_array($options)) {
            if (file_exists(LC_ROOT_DIR . 'etc' . LC_DS . 'config.local.php')) {
                $optionsLocal = parse_ini_file(LC_ROOT_DIR . 'etc' . LC_DS . 'config.local.php', true);
                if (is_array($optionsLocal)) {
                    $options = array_merge($options, $optionsLocal);
                }
            }
        } else {
            $this->_die('Unable to read/parse configuration file(s)');
        }

        return $options;
    }

    /**
     * Return current target 
     * 
     * @return string
     * @access protected
     * @since  3.0.0 EE
     */
    protected function getTarget()
    {
        $target = XLite_Core_Request::getInstance()->target;

        if (empty($target)) {
            // FIXME - "_REQUEST" should be removed
            $_REQUEST['target'] = XLite_Core_Request::getInstance()->target = $target = self::TARGET_DEFAULT;
        }

        return $target;
    }

    /**
     * Return current action 
     * 
     * @return mixed
     * @access protected
     * @since  3.0.0 EE
     */
    protected function getAction()
    {
        return XLite_Core_Request::getInstance()->action;
    }
    

    /**
     * Return specified (or the whole list) options 
     * 
     * @param mixed $names list (or single value) of option names
     *  
     * @return mixed
     * @access public
     * @since  3.0.0 EE
     */
    public function getOptions($names = null)
    {
        if (is_null($this->options)) {
            $this->options = $this->parseConfigFile();
            $this->options['host_details']['web_dir_wo_slash'] = rtrim($this->options['host_details']['web_dir'], '/');
        }

        $result = $this->options;

        if (!is_null($names)) {
            if (is_array($names)) {
                $names = array_reverse($names);
                while (!empty($names) && !is_null($result)) {
                    if (is_null($key = array_pop($names))) break;
                    $result = isset($result[$key]) ? $result[$key] : null;
                }
            } else {
                $result = isset($result[$names]) ? $result[$names] : null;
            }
        }

        return $result;
    }

    /**
     * Use this function to get a reference to this class object
     * 
     * @return XLite
     * @access public
     * @since  3.0.0 EE
     */
    public static function getInstance()
    {
        return self::_getInstance(__CLASS__);
    }

    /**
     * Clean up classes cache (if needed) 
     * 
     * @return void
     * @access public
     * @since  3.0.0 EE
     */
    public function __destruct()
    {
        if ($this->isNeedToCleanupCache) {
            XLite_Model_ModulesManager::getInstance()->cleanupCache();
        }
    }

    /**
     * Ability to provoke cache cleanup (or to prevent it)
     * 
     * @param bool $flag if it's needed to cleanup cache or not
     *  
     * @return void
     * @access public
     * @since  3.0.0 EE
     */
    public function setCleanUpCacheFlag($flag)
    {
        $this->isNeedToCleanupCache = (true === $flag);
    }

    /**
     * Return current endpoint script
     * 
     * @return string
     * @access public
     * @since  3.0.0 EE
     */
    public function getScript()
    {
        return $this->adminZone ? self::ADMIN_SELF : self::CART_SELF;
    }

    /**
     * Return full URL for the resource 
     * 
     * @param string $url    resource relative URL
     * @param bool   $secure HTTP/HTTPS flag
     *  
     * @return string
     * @access public
     * @since  3.0.0 EE
     */
    public function shopUrl($url, $secure = false)
    {
        $proto   = 'http' . ($secure ? 's' : '') . '://';
        $host    = $this->getOptions(array('host_details', 'http' . ($secure ? 's' : '') . '_host'));
        $web_dir = rtrim($this->getOptions(array('host_details', 'web_dir')), '/') . '/';

        if ($secure) {
            $session = XLite_Model_Session::getInstance();
            $url .= (strpos($url, '?') ? '&' : '?') . $session->getName() . '=' . $session->getID();
        }

        return $proto . $host . $web_dir . $url;
    }

    /**
     * Return instance of the abstract factory sigleton 
     * 
     * @return XLite_Model_Factory
     * @access public
     * @since  3.0.0 EE
     */
    public static function getFactory()
    {
        return XLite_Model_Factory::getInstance();
    }

    /**
     * Get controller
     *
     * @return XLite_Controller_Abstract
     * @access public
     * @since  3.0.0
     */
    public function getController()
    {
        return self::$controller;
    }

    /**
     * Initialize all active modules 
     * 
     * @return void
     * @access public
     * @since  3.0.0 EE
     */
    public function initModules()
    {
        XLite_Model_ModulesManager::getInstance()->init();
    }

    /**
     * Instantiate and initialize a controller
     * 
     * @return void
     * @access public
     * @since  3.0.0 EE
     */
    public function initController()
    {
        $controllerClass = XLite_Core_Converter::getControllerClass($this->getTarget());

        self::$controller = new $controllerClass();
        self::$controller->init();
    }

    /**
     * Wrapper 
     * 
     * @return void
     * @access public
     * @since  3.0.0 EE
     */
    public function init()
    {
        $this->initModules();
        $this->initController();
    }

    /**
     * Perform an action and redirect
     * 
     * @return XLite_View_Controller
     * @access public
     * @since  3.0.0 EE
     */
    public function runController()
    {
        self::$controller->handleRequest();

        return self::$controller->getViewer();
    }

    /**
     * Display current page 
     * 
     * @param XLite_View_Abstract $viewer instance of the current viewer
     *  
     * @return void
     * @access public
     * @since  3.0.0 EE
     */
    public function runViewer(XLite_View_Abstract $viewer)
    {
        $viewer->display();
    }

    /**
     * Runs the cart 
     * 
     * @param bool $adminZone area flag
     *  
     * @return void
     * @access public
     * @since  3.0.0 EE
     */
    public function run($adminZone = false)
    {
        // Set current area
        $this->adminZone = $adminZone;

        // Initialize modules amd create controller instance
        $this->init();

        // Handle action (if needed)
        $viewer = $this->runController();
    
        // Display page
        $this->runViewer($viewer);
    }
}

