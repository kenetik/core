{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * Checkout : payment step : selected state : button
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *
 * @ListChild (list="checkout.payment.selected", weight="30")
 *}
<div class="button-row">
  <widget class="\XLite\View\Button\Link" label="Continue" location="{buildURL(#checkout#)}" style="bright disabled" />
</div>
