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
 * XLite_Module_WholesaleTrading_View_CategorySelect
 *
 * @package    XLite
 * @subpackage View
 * @since      3.0.0
 */
class XLite_Module_WholesaleTrading_View_CategorySelect extends XLite_View_CategorySelect
{
    /**
     * setFieldName 
     * 
     * @param string $name
     *  
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function setFieldName($name)
    {
        $this->formField = $name;
        $this->field = $name;
    }

	/**
	 * Check if category is in the bulk_categories list 
	 * 
	 * @param XLite_Model_Category $category
	 *  
	 * @return bool
	 * @access protected
	 * @see    ____func_see____
	 * @since  3.0.0
	 */
	protected function isCategorySelected(XLite_Model_Category $category)
	{
		return in_array($category->get('category_id'), explode(";", $this->config->WholesaleTrading->bulk_categories));
	}
}
