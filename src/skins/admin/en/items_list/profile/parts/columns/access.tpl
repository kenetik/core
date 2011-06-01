{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * Item name
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 * @since     1.0.0
 * @ListChild (list="itemsList.profile.search.columns", weight="40")
 *}

<td class="access">

  {if:profile.access_level=0}
  Customer
  {if:profile.membership}
  <br /><b>membership:</b> {profile.membership.getName()}
  {end:}
  {if:profile.pending_membership}
  <br /><b>requested for membership:</b> {profile.pending_membership.getName()}
  {end:}
  {else:}
  Administrator
  {end:}

</td>
