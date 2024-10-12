<?php
$yamlHelper = new YamlHelper();
$searchConfig = $yamlHelper->load(__DIR__ . '/../../../plugins/barnet-products/Config/searches.yml');
unset($searchConfig['tools']);

$prefix = "barnet_opt_";
$searchSetting = $searchConfig['setting'];
$searchAdvance = $searchConfig['advance'];
$searchExtra = $searchConfig['extra'];
$searchRelationship = $searchConfig['relationship'];

$listField = array();

$formatDisplay = function ($key) {
    return DataHelper::camel2Display(DataHelper::snake2CamelCase($key));
};

$getOptionValue = function ($key, $defaultValue) use ($prefix) {
    return get_option("$prefix$key") ? get_option("$prefix$key") : $defaultValue;
};

?>
<div>
    <form method="post">
        <?php wp_nonce_field( 'barnet_search_setting' ) ?>
        <h1>Search Setting</h1>
        <hr>
        <?php
        foreach ($searchSetting as $key => $_setting) {
            $suffix = "ss_" . DataHelper::compactString($key) . "_";
            ?>
            <table class="form-table"><?php
            echo "<h3>" . $formatDisplay($key) . "</h3>";
            foreach ($_setting as $field => $point) {
                if ($field == 'include') {
                    foreach ($point as $relationShipKey) {
                        $fieldName = DataHelper::compactString($relationShipKey, '_');
                        $displayName = ucfirst(explode('_', $relationShipKey)[0]);
                        if (!isset($searchRelationship[$key][$relationShipKey])) {
                            continue;
                        }

                        $listFieldName = array();
                        foreach ($searchRelationship[$key][$relationShipKey] as $postType => $postPointList) {
                            if ($postType == 'advance') {
                                foreach ($postPointList as $advanceKey => $advanceValue) {
                                    if (!$advanceValue) {
                                        continue;
                                    }

                                    $searchAdvance[$advanceKey]['filter'] = array_merge($searchAdvance[$advanceKey]['filter'], $listFieldName);
                                }
                            } else {
                                if (!isset($postPointList['relation_key'])) {
                                    continue;
                                }

                                $fieldName .= '_' . DataHelper::compactString($postType);
                                if (isset($postPointList['taxonomy'])) {
                                    $fieldName .= '_tax';
                                    foreach ($postPointList['taxonomy'] as $taxType => $taxPointList) {
                                        $fieldName .= '_' . DataHelper::compactString($taxType);
                                        foreach ($taxPointList as $taxField => $taxPoint) {
                                            $k = $fieldName . "_" . $taxField;
                                            ?>
                                            <tr valign="top">
                                                <th scope="row"><label for="<?php echo "$prefix$suffix$k"; ?>"><?php echo $displayName . " " . ucfirst($taxField); ?></label></th>
                                                <td><input type="number" id="<?php echo "$prefix$suffix$k"; ?>" name="<?php echo "$prefix$suffix$k"; ?>"
                                                           value="<?php echo $getOptionValue("$suffix$k", $taxPoint); ?>"/></td>
                                            </tr>
                                            <?php
                                            $fField = lcfirst($k == 'post_title' ? explode(' ', $formatDisplay($key))[1] . '_' . $k : $k);
                                            $listField[$fField] = $displayName . " " . ucfirst($taxField);
                                            $listFieldName[] = $fField;
                                        }
                                    }
                                } else {
                                    foreach ($postPointList as $_key => $_point) {
                                        $k = $fieldName . '_' . DataHelper::compactString($_key, '_');
                                        ?>
                                        <tr valign="top">
                                            <th scope="row"><label for="<?php echo "$prefix$suffix$k"; ?>"><?php echo $displayName . " " . ucfirst($_key); ?></label></th>
                                            <td><input type="number" id="<?php echo "$prefix$suffix$k"; ?>" name="<?php echo "$prefix$suffix$k"; ?>"
                                                       value="<?php echo $getOptionValue("$suffix$k", $_point); ?>"/></td>
                                        </tr>
                                        <?php
                                        $fField = lcfirst($k == 'post_title' ? explode(' ', $formatDisplay($key))[1] . '_' . $k : $k);
                                        $listField[$fField] = $displayName . " " . ucfirst($_key);
                                        $listFieldName[] = $fField;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    ?>
                    <tr valign="top">
                        <th scope="row"><label for="<?php echo "$prefix$suffix$field"; ?>"><?php echo $formatDisplay($field); ?></label></th>
                        <td><input type="number" id="<?php echo "$prefix$suffix$field"; ?>" name="<?php echo "$prefix$suffix$field"; ?>"
                                   value="<?php echo $getOptionValue("$suffix$field", $point); ?>"/></td>
                    </tr>
                    <?php
                    $fField = lcfirst($field == 'post_title' ? explode(' ', $formatDisplay($key))[1] . '_' . $field : $field);
                    $listField[$fField] = $fField;
                }
            }
            ?></table><?php
        }
        ?>

        <h1>Advance</h1>
        <hr>
        <table class="form-table">
        <?php
        foreach ($searchAdvance as $key => $_advance) {
            $suffix = "sa_" . DataHelper::compactString($key, '_') . "_";
            ?>
            <tr valign="top">
                <th scope="row"><label for="<?php echo "$prefix$suffix$field"; ?>"><?php echo $formatDisplay($key); ?></label></th>
                <td><select name="<?php echo "$prefix$suffix" . "option"; ?>[]" multiple="multiple">
            <?php
            foreach ($listField as $_kPoint => $point) {
                $fField = lcfirst($_kPoint == 'post_title' ? explode(' ', $formatDisplay($key))[1] . '_' . $_kPoint : $_kPoint);
                $isSelected = in_array($fField, $_advance['filter']) ? "selected" : "";
                ?>
                    <option value="<?php echo "$_kPoint"; ?>" <?php echo $isSelected; ?>><?php echo $formatDisplay($point); ?></option>
                <?php
            }
            ?></td></select></tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo "$prefix$suffix" . "active"; ?>"><?php echo $formatDisplay($key) . " Active"; ?></label></th>
                <td><input type="checkbox" id="<?php echo "$prefix$suffix" . "active"; ?>" name="<?php echo "$prefix$suffix" . "active"; ?>"
                           <?php echo false !== get_option("$prefix$suffix" . "active") ? (get_option("$prefix$suffix" . "active") == 1 ? "checked" : "") : ($_advance['active'] ? "checked" : ""); ?>></td>
            </tr>
            <?php
        }
        ?>
        </table>

        <?php
        if (isset($searchExtra['modified_date'])) {
            ?>
        <h1>Extra</h1>
        <hr>
        <table class="form-table">
            <?php
            $suffix = "se_md_";
            ?>
            <tr valign="top">
                <th scope="row"><label for="<?php echo "$prefix$suffix" . "modified_date"; ?>">Post Modified Rate</label></th>
                <td>
                    <input type="number" id="<?php echo "$prefix$suffix" . "point"; ?>" name="<?php echo "$prefix$suffix" . "point"; ?>"
                           value="<?php echo $getOptionValue("$suffix" . "point", $searchExtra['modified_date']['point']); ?>"/> /
                    <input type="number" id="<?php echo "$prefix$suffix" . "value"; ?>" name="<?php echo "$prefix$suffix" . "value"; ?>"
                           value="<?php echo $getOptionValue("$suffix" . "value", $searchExtra['modified_date']['value']); ?>"/> seconds
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo "$prefix$suffix" . "active"; ?>">Modified Date Active</label></th>
                <td><input type="checkbox" id="<?php echo "$prefix$suffix" . "active"; ?>" name="<?php echo "$prefix$suffix" . "active"; ?>"
                        <?php echo false !== get_option("$prefix$suffix" . "active") ? (get_option("$prefix$suffix" . "active") == 1 ? "checked" : "") : ($searchExtra['modified_date']['active'] ? "checked" : ""); ?>></td>
            </tr>
            <?php
        }
        ?>
        </table>
        <table>
            <tr valign="top">
                <th scope="row"></th>
                <td><?php submit_button(); ?></td>
                <td><?php submit_button("Revert To Default", 'primary', 'revert'); ?></td>
            </tr>
        </table>
    </form>
</div>