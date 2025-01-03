<?php
/**
 * @package   stockablecustomfield
 * @copyright Copyright (C) 2014-2025 breakdesigns.net . All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$options = $viewData->options;
$field_name = 'customProductData[' . $viewData->product->virtuemart_product_id . '][' . $viewData->virtuemart_custom_id . '][' . $viewData->virtuemart_customfield_id . '][stockable]';
$select_id = 'stockableselect' . $viewData->product->virtuemart_product_id . $viewData->virtuemart_customfield_id . $viewData->custom->virtuemart_custom_id;
$wrapper_id = 'stockablecustomfields_field_wrapper_' . $viewData->virtuemart_customfield_id;
?>

<div class="stockablecustomfields_field_wrapper control-group mb-4 mt-0" id="<?php
echo $wrapper_id ?>">
    <?php

    if (!empty($options)) {
        ?>
        <div>
            <select name="<?php
            echo $field_name ?>" id="<?php
            echo $select_id ?>" class="form-select">
                <?php
                //empty options should exist only on the parent product loading
                if ($viewData->product->product_parent_id == 0 && !$viewData->isderived):?>
                    <option value="0"><?php echo Text::_('PLG_STOCKABLECUSTOMFIELDS_SELECT_OPTION'); ?></option>
                <?php
                endif; ?>

                <?php
                foreach ($options as $key => $v) {
                    $selected = '';
                    if (!empty($v->selected)) {
                        $selected = 'selected="selected"';
                    } ?>
                    <option value="<?php echo $v->id ?>" <?php echo $selected; ?>>
                        <?php echo Text::_($v->value) ?>
                    </option>
                    <?php
                } ?>
            </select>
        </div>
        <?php
    } ?>
</div>
