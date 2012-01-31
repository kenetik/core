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
 * @since     1.0.0
 */

namespace XLite\View\Button;

/**
 * Delete user button widget. Customer area.
 *
 * @see   ____class_see____
 * @since 1.0.0
 */
class DeleteUser extends \XLite\View\Button\Popup\Link
{
    /**
     * Return default value for widget param
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.16
     */
    protected function getDefaultTarget()
    {
        return 'profile';
    }

    /**
     * Return default value for widget param
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.16
     */
    protected function getDefaultWidget()
    {
        return '\XLite\View\Account\Delete';
    }

    /**
     * Return URL parameters to use in AJAX popup
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getAdditionalURLParams()
    {
        $list = parent::getAdditionalURLParams();
        $list['mode'] = 'delete';

        return $list;
    }

    /**
     * Return default button label
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getDefaultLabel()
    {
        return 'Delete profile';
    }

    /**
     * Return CSS classes
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getClass()
    {
        return 'delete-user-button ' . ($this->getParam(self::PARAM_STYLE) ?: '');
    }
}
