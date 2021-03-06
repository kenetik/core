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

namespace XLite\Model;

/**
 * Product multilingual data
 *
 *
 * @Entity
 *
 * @Table (name="product_translations",
 *         indexes={
 *              @Index (name="ci", columns={"code","id"}),
 *              @Index (name="id", columns={"id"}),
 *              @Index (name="name", columns={"name"})
 *         }
 * )
 */
class ProductTranslation extends \XLite\Model\Base\Translation
{
    /**
     * Product name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $name;

    /**
     * Product description
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $description = '';

    /**
     * Product brief description
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $brief_description = '';

    /**
     * Meta tags
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $meta_tags = '';

    /**
     * Product meta description
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $meta_desc = '';

    /**
     * Meta title
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $meta_title = '';
}
