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
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

namespace XLite\View\Button;

/**
 * Switch state button
 *
 */
class SwitchState extends \XLite\View\Button\Regular
{
    /**
     * Widget parameter names
     */
    const PARAM_ENABLED = 'enabled';

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'button/switch-state.tpl';
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_ENABLED => new \XLite\Model\WidgetParam\Bool('Enabled', true),
        );
    }

    /**
     * Get style 
     * 
     * @return string
     */
    protected function  getStyle()
    {
        return 'switch-state '
            . ($this->getParam(self::PARAM_ENABLED) ? 'enabled-state' : 'disabled-state')
            . ($this->getParam(self::PARAM_STYLE) ? ' ' . $this->getParam(self::PARAM_STYLE) : '');
    }
}
