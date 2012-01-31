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
 * PHP version 5.3.0
 * 
 * @category  LiteCommerce
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 * @see       ____file_see____
 * @since     1.0.16
 */

namespace XLite\View\ProductClasses;

/**
 * Main controller widget
 *
 * @see   ____class_see____
 * @since 1.0.16
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class Book extends \XLite\View\Dialog
{
    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.14
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'product_classes';

        return $result;
    }

    /**
     * Register CSS files
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.16
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        // For popups
        $list = array_merge(
            $list,
            $this->getWidget(array(), '\XLite\View\ProductClasses\Book\AssignAttributes')->getCSSFiles()
        );

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.16
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/controller.js';

        // For popups
        $list = array_merge(
            $list,
            $this->getWidget(array(), '\XLite\View\ProductClasses\Book\AssignAttributes')->getJSFiles()
        );

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getDir()
    {
        return 'product_classes/book';
    }

    /**
     * Return full list of product classes
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.16
     */
    protected function getProductClasses()
    {
        // FIXME [DOCTRINE 2.1]
        return \XLite\Core\Database::getRepo('\XLite\Model\ProductClass')->findBy(array(), array('pos' => 'ASC'));
    }

    /**
     * Return list of product widgets
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.16
     */
    protected function getProductClassWidgets()
    {
        $result = array();
        $class  = '\XLite\View\ProductClasses\Book\Row';

        foreach ($this->getProductClasses() as $object) {
            $result[] = $this->getWidget(array($class::PARAM_CLASS => $object), $class);
        }

        return $result;
    }
}
