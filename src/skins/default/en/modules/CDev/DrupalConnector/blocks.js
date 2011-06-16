/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * Blocks settings controller
 *  
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 * @since     1.0.0
 */

jQuery().ready(
  function() {

    var processRecommendedSizes = function(form) {
      var widgetType = jQuery('select', form).filter(
        function() {
          return -1 != this.name.search(/lc_widget.lc_block_XLite_.+_ItemsList_Product_Customer_.+..widgetType./);
        }
      );
      var displayMode = jQuery('select', form).filter(
        function() {
          return -1 != this.name.search(/lc_widget.lc_block_XLite_.+_ItemsList_Product_Customer_.+..displayMode./);
        }
      );
      var key = widgetType.get(0).value + '.' + displayMode.get(0).value;
      widgetType = widgetType.get(0);

      var width = jQuery('input[name="' + widgetType.name.replace(/widgetType/, 'iconWidth') + '"]', this.form);
      var descWidth = width.parents('.form-item').find('.description').eq(0);
      var height = jQuery('input[name="' + widgetType.name.replace(/widgetType/, 'iconHeight') + '"]', this.form);
      var descHeight = height.parents('.form-item').find('.description').eq(0);

      if (typeof(window.lcConnectorRecommendedIconSizes) != 'undefined' && typeof(lcConnectorRecommendedIconSizes[key]) != 'undefined') {
        var size = lcConnectorRecommendedIconSizes[key];

        descWidth.show().html(lcConnectorRecommendedLabel.replace(/!size/, size[0]));
        descHeight.show().html(lcConnectorRecommendedLabel.replace(/!size/, size[1]));
        width.removeAttr('disabled', 'disabled');
        height.removeAttr('disabled', 'disabled');

      } else {
        descWidth.hide();
        descHeight.hide();
        width.attr('disabled', 'disabled');
        height.attr('disabled', 'disabled');
      }

    }

    jQuery('form select').filter(

      function() {
        return -1 != this.name.search(/lc_widget.lc_block_XLite_.+_ItemsList_Product_Customer_.+..widgetType./);
      }

    ).change(

      function() {

        // Detect related select-box with display modes
        if (!this.displayMode) {
          var select = this.name.replace(/widgetType/, 'displayMode');
          this.displayMode = jQuery('select', this.form).filter(
            function() {
              return this.name == select;
            }
          ).get(0);
        }

        // Remove old options
        while (0 < this.displayMode.options.length) {
          this.displayMode.options[0] = null;
        }

        // Add new options
        var displayMode = this.displayMode;
        jQuery.each(
          lcConnectorBlocks[this.options[this.selectedIndex].value],
          function(k, v) {
            displayMode.options[displayMode.options.length] = new Option(v, k);
          }
        );

        jQuery(displayMode).change();

        // Show / hide some settings
        var isShow = 'sidebar' != this.options[this.selectedIndex].value;
        var elements = [
          jQuery('input[name="' + this.name.replace(/widgetType/, 'showDisplayModeSelector') + '"]', this.form).parents('.form-item').eq(0),
          jQuery('select[name="' + this.name.replace(/widgetType/, 'gridColumns') + '"]', this.form).parents('.form-item').eq(0),
          jQuery('input[name="' + this.name.replace(/widgetType/, 'showItemsPerPageSelector') + '"]', this.form).parents('.form-item').eq(0),
          jQuery('input[name="' + this.name.replace(/widgetType/, 'showSortBySelector') + '"]', this.form).parents('.form-item').eq(0)
        ];

        for (var i = 0; i < elements.length; i++) {
          var elm = elements[i];
          if (isShow) {
            elm.show();

          } else {
            elm.hide();
          }
        }

      }

    );

    jQuery('form select').filter(

      function() {
        return -1 != this.name.search(/lc_widget.lc_block_XLite_.+_ItemsList_Product_Customer_.+..displayMode./);
      }

    ).change(

      function() {

        var name = this.name.replace(/displayMode/, 'gridColumns');

        var select = jQuery('select', this.form).filter(
          function() {
            return this.name == name;
          }
        ).eq(0);

        if ('grid' == this.options[this.selectedIndex].value) {
          select.removeAttr('disabled');

        } else {
          select.attr('disabled', 'disabled');
        }

        processRecommendedSizes(this.form);
      }
    );

    jQuery('form select').filter(
      function() {
        return -1 != this.name.search(/lc_widget.lc_block_XLite_.+_ItemsList_Product_Customer_.+..widgetType./);
      }
    ).change();
  }
);

