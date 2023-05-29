<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// include ('google-translate-php/Tradutor.php');

//LINK PRODUCT PAGE CODE
function kia_custom_option() {
    global $product;
    global $wpdb;
    // $current_language = ICL_LANGUAGE_CODE;
    // $tradutor = new Tradutor ();

    $id = $product->get_id();

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = " . $id . "", OBJECT);
    $product_data = json_decode(json_encode($results), True);
    foreach ($product_data as $row => $values):
        $product_details[$values['meta_key']] = $values['meta_value'];
    endforeach;
    $link_lbl = tsl_lg($product_details['_link_label']);
    $enable_smm = tsl_lg($product_details['_enable_smm']);
    $service_holder = tsl_lg($product_details['_service_holder']);
    $service_with = tsl_lg($product_details['_service_with']);
    $service_with_holder = tsl_lg($product_details['_service_with_holder']);
    $service_without = tsl_lg($product_details['_service_without']);
    $service_without_holder = tsl_lg($product_details['_service_without_holder']);
    if ($enable_smm == 1) {
        $help_text = tsl_lg(get_post_meta($id, '_service_help_text', true));
        $help_label = '';
        if ($help_text) {
            $help_label = '<span id="service_help_text" title="' . $help_text . '"><i class="fa fa-question-circle" aria-hidden="true"></i></span>';
        }

        // DEFAULT
        $value = isset($_POST['_custom_option']) ? sanitize_text_field($_POST['_custom_option']) : '';
        printf('<div id="wccf_product_field_master_container"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s %s </label><div><input name="_custom_option" id="custom_option" value="%s" class="link_txtbox" placeholder="' . $service_holder . '" /></div></div></div>', __($link_lbl . ' ', 'kia-plugin-textdomain'), $help_label, esc_attr($value));
        print_r('<input type="hidden" name="_service_with" id="service_with" value="' . $service_with . '">');
        print_r('<input type="hidden" name="_service_with_holder" id="service_with_holder" value="' . $service_with_holder . '">');
        print_r('<input type="hidden" name="_service_without" id="service_without" value="' . $service_without . '">');
        print_r('<input type="hidden" name="_service_without_holder" id="service_without_holder" value="' . $service_without_holder . '">');

        if (get_service_type($id) == 'custom_comments') {
            $value2 = isset($_POST['_custom_comment']) ? sanitize_text_field($_POST['_custom_comment']) : '';
            printf('<div id="wccf_product_field_master_container"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">Tekst reacties</label></div><div class="col-md-6 col-sm-6 col-xs-6"><textarea name="_custom_comment" value="Tekst reacties" class="link_txtbox" id="custom_comment"></textarea><span id="comment_quantity" style="display:none">1</span></div></div>', __($link_lbl . ' ', 'kia-plugin-textdomains'), esc_attr($value2));
        }

        //MENTION CUSTOM LIST
        if (get_service_type($id) == 'mention_custom_list') {
            $value2 = isset($_POST['_mention_custom_list']) ? sanitize_text_field($_POST['_mention_custom_list']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label></div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_mention_custom_list" value="%s" class="link_txtbox" /></div></div>', __('MentionCustomList', '
  '), esc_attr($value2));
        }

        //MENTION USER FOLLOWER
        if (get_service_type($id) == 'mention_user_follower') {
            $value2 = isset($_POST['_mention_user_follower']) ? sanitize_text_field($_POST['_mention_user_follower']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_mention_user_follower" value="%s" class="link_txtbox"  /></div></div>', __('Username', 'plugin-mention-user-follower'), esc_attr($value2));
        }

        //COMMENT LIKES
        if (get_service_type($id) == 'comment_likes') {
            $value2 = isset($_POST['_comment_likes']) ? sanitize_text_field($_POST['_comment_likes']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_comment_likes" value="%s" class="link_txtbox" /></div></div>', __('Username', 'plugin-comment-likes'), esc_attr($value2));
        }

        //DRIPFEED
        if (get_service_type($id) == 'drip_feed') {
            $value2 = isset($_POST['_runs']) ? sanitize_text_field($_POST['_runs']) : '';
            printf('<div id="wccf_product_field_master_container"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label></div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_runs" type="number" value="%s" class="link_txtbox" /></div></div>', __('Runs', 'plugin-runs'), esc_attr($value2));

            $value3 = isset($_POST['_interval']) ? sanitize_text_field($_POST['_interval']) : '';
            printf('<div id="wccf_product_field_master_container"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label></div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_interval" type="number" value="%s" class="link_txtbox" /></div></div>', __('Interval', 'plugin-interval'), esc_attr($value3));
        }

        //SUBSCRIPTION
        if (get_service_type($id) == 'subscription') {
            $value2 = isset($_POST['_username']) ? sanitize_text_field($_POST['_username']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_username" type="text" value="%s"  class="link_txtbox"/></div></div>', __('Username', 'plugin-username'), esc_attr($value2));

            $value3 = isset($_POST['_min']) ? sanitize_text_field($_POST['_min']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_min" type="number" value="%s"class="link_txtbox"/></div></div>', __('Min', 'plugin-min'), esc_attr($value3));

            $value4 = isset($_POST['_max']) ? sanitize_text_field($_POST['_max']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_max" type="number" value="%s" class="link_txtbox"/></div></div>', __('Max', 'plugin-max'), esc_attr($value4));

            $value5 = isset($_POST['_posts']) ? sanitize_text_field($_POST['_posts']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_posts" type="text" value="%s" class="link_txtbox"/></div></div>', __('Posts', 'plugin-posts'), esc_attr($value5));

            $value6 = isset($_POST['_delay']) ? sanitize_text_field($_POST['_delay']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_delay" type="text" value="%s" class="link_txtbox"/></div></div>', __('Delay', 'plugin-delay'), esc_attr($value6));

            $value7 = isset($_POST['_expiry']) ? sanitize_text_field($_POST['_expiry']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_expiry" type="date" value="%s" class="link_txtbox"/></div></div>', __('Expiry', 'plugin-expiry'), esc_attr($value7));
        }
        //PACKAGE
        if (get_service_type($id) == 'mention_package') {
            $value2 = isset($_POST['_package']) ? sanitize_text_field($_POST['_package']) : '';
            printf('<div class="row"><div class="col-md-6 col-sm-6 col-xs-6"><label class="link_lbl">%s</label>&nbsp;&nbsp;</div><div class="col-md-6 col-sm-6 col-xs-6"><input name="_package" type="text" value="%s"  class="link_txtbox"/></div></div>', __('Package', 'plugin-package'), esc_attr($value2));
        }
    }
}

add_action('wp_head', 'smm_check_product_type');

function smm_check_product_type() {
    global $product;
    if (is_a($product, 'WC_Product')) {
        wp_enqueue_script('currenct-formater', '//cdnjs.cloudflare.com/ajax/libs/accounting.js/0.4.1/accounting.min.js');
        wp_enqueue_script('tippy-js', 'https://cdnjs.cloudflare.com/ajax/libs/tippy.js/0.3.0/tippy.js');
        wp_enqueue_style('tippy-css', 'https://cdnjs.cloudflare.com/ajax/libs/tippy.js/0.3.0/tippy.css');

        if ($product->is_type('variable')) {
            add_action('woocommerce_before_variations_form', 'kia_custom_option', 9);
            add_action('woocommerce_before_add_to_cart_button', 'smm_add_extra_product', 10);
        } else {
            add_action('woocommerce_before_add_to_cart_button', 'kia_custom_option', 9);
        }
    }
}

function smm_add_extra_product() {
    global $product;
    global $wpdb;
    $product_id = $product->get_id();

    $smm_upsell_product = tsl_lg(get_post_meta($product_id, '_smm_upsell_product', true));
    $smm_upsell_text = tsl_lg(get_post_meta($product_id, '_smm_upsell_text', true));
    $smm_upsell_help_text = tsl_lg(get_post_meta($product_id, '_smm_upsell_help_text', true));

    $smm_upsell_product2 = tsl_lg(get_post_meta($product_id, '_smm_upsell_product2', true));
    $smm_upsell_text2 = tsl_lg(get_post_meta($product_id, '_smm_upsell2_text', true));
    $smm_upsell_help_text2 = tsl_lg(get_post_meta($product_id, '_smm_upsell2_help_text', true));

    if (!empty($smm_upsell_product) && !empty($smm_upsell_text)) {

        $_product = wc_get_product($smm_upsell_product);
        if ($_product && $_product->is_type('variable')) {
            $variations = $_product->get_available_variations();
            $product_title = $_product->get_title();
            $options = array();
            if ($variations) {
                foreach ($variations as $variation) {
                    $_variation = new WC_Product_Variation($variation['variation_id']);
                    $price = ' (' . wp_kses(woocommerce_price($_variation->get_price()), array()) . ')';
                    $title = get_the_title($variation['variation_id']);
                    $title = str_replace($product_title . ' - ', '', $title);
                    $title = str_replace($product_title, '', $title);
                    $title = str_replace('&#8211;', '', $title);
                    $options[] = array('variation_id' => $variation['variation_id'], 'title' => trim($title) . ' ' . $price, 'price' => $variation['display_price'], 'regular_price' => $variation['display_regular_price']);
                }

                array_multisort(array_column($options, 'regular_price'), SORT_DESC, $options);
                ?>
                <div id="wccf_product_field_master_container" class="smm_upsell_option_div" style="display:none">
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <label class="link_lbl"><?php echo __($smm_upsell_text . ' ', 'kia-plugin-textdomain'); ?>
                            <?php if (!empty($smm_upsell_help_text)) { ?>
                                <span id="upsell_help_text" title="<?php echo $smm_upsell_help_text; ?>" ><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                            <?php } ?>
                        </label>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <select name="_smm_upsell_option" id="_smm_upsell_option">
                            <option value="">Een optie kiezen</option>
                            <?php foreach ($options as $k => $v) { ?>
                                <option data-price="<?php echo $v['price']; ?>" data-regular_price="<?php echo $v['regular_price']; ?>" value="<?php echo $v['variation_id']; ?>"><?php echo $v['title']; ?></option>
                            <?php } ?>
                            <option value="">Nee, bedankt</option>
                        </select>
                        <input type="hidden" name="_smm_upsell_product_id" value="<?php echo $smm_upsell_product; ?>" />
                    </div>
                </div>
                <?php
                if (!empty($smm_upsell_product2) && !empty($smm_upsell_text2)) {
                    $_product2 = wc_get_product($smm_upsell_product2);
                    if ($_product2 && $_product2->is_type('variable')) {
                        $variations2 = $_product2->get_available_variations();
                        $product_title2 = $_product2->get_title();
                        $options2 = array();
                        if ($variations2) {
                            foreach ($variations2 as $variation2) {
                                $_variation2 = new WC_Product_Variation($variation2['variation_id']);
                                $price2 = ' (' . wp_kses(woocommerce_price($_variation2->get_price()), array()) . ')';
                                $title2 = get_the_title($variation2['variation_id']);
                                $title2 = str_replace($product_title2 . ' - ', '', $title2);
                                $title2 = str_replace($product_title2, '', $title2);
                                $title2 = str_replace('&#8211;', '', $title2);
                                $options2[] = array('variation_id' => $variation2['variation_id'], 'title' => trim($title2) . ' ' . $price2, 'price' => $variation2['display_price'], 'regular_price' => $variation2['display_regular_price']);
                            }

                            array_multisort(array_column($options2, 'regular_price'), SORT_DESC, $options2);
                            ?>

                            <div id="wccf_product_field_master_container" class="smm_upsell_option_div2" style="display:none">
                                <div class="col-md-6 col-sm-6 col-xs-6">
                                    <label class="link_lbl"><?php echo __($smm_upsell_text2 . ' ', 'kia-plugin-textdomain'); ?>
                                        <?php if (!empty($smm_upsell_help_text2)) { ?>
                                            <span id="upsell_help_text2" title="<?php echo $smm_upsell_help_text2; ?>" ><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                                        <?php } ?>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6">
                                    <select name="_smm_upsell_option2" id="_smm_upsell_option2">
                                        <option value="">Een optie kiezen</option>
                                        <?php foreach ($options2 as $k => $v) { ?>
                                            <option data-price="<?php echo $v['price']; ?>" data-regular_price="<?php echo $v['regular_price']; ?>" value="<?php echo $v['variation_id']; ?>"><?php echo $v['title']; ?></option>
                                        <?php } ?>
                                        <option value="">Nee, bedankt</option>
                                    </select>
                                    <input type="hidden" name="_smm_upsell_product_id2" value="<?php echo $smm_upsell_product2; ?>" />
                                </div>
                            </div>

                            <?php
                        }
                    }
                }
                ?>

                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        var $selected_variation = '';
                <?php if (!empty($smm_upsell_help_text2)) { ?>
                            new Tippy('#upsell_help_text2', {
                                position: 'top',
                                animation: 'scale',
                                arrow: 'true'
                            });
                <?php } ?>

                        new Tippy('#upsell_help_text', {
                            position: 'top',
                            animation: 'scale',
                            arrow: 'true'
                        });

                        $(".single_variation_wrap").on("hide_variation", function (event) {
                            $('.smm_upsell_option_div').hide();
                            $("#_smm_upsell_option").val("").attr("selected", "selected");

                            $('.smm_upsell_option_div2').hide();
                            $("#_smm_upsell_option2").val("").attr("selected", "selected");

                        });

                        $(".single_variation_wrap").on("show_variation", function (event, variation) {
                            $('.smm_upsell_option_div').show();
                            $selected_variation = variation;
                            setTimeout(function () {
                                update_smm_prices(variation);
                            }, 10);
                        });

                        $(document).on('change', '#_smm_upsell_option', function () {

                            var $selected_upsell = $('#_smm_upsell_option option:selected');
                            if ($selected_upsell.val() != '') {
                                if ($('.smm_upsell_option_div2').length > 0) {
                                    $('.smm_upsell_option_div2').show();
                                }
                            } else {
                                if ($('.smm_upsell_option_div2').length > 0) {
                                    $('.smm_upsell_option_div2').hide();
                                }
                            }

                            var $product_variations = $('form.variations_form').data('product_variations');
                            var $variation_id = $('.variation_id').val();
                            if ($variation_id) {
                                var variation = '';
                                $.each($product_variations, function (index, value) {
                                    if ($variation_id == value.variation_id) {
                                        variation = value;
                                    }
                                });
                                $selected_variation = variation;
                                update_smm_prices(variation);
                            }
                        });

                        $(document).on('change', '#_smm_upsell_option2', function () {
                            var $product_variations = $('form.variations_form').data('product_variations');
                            var $variation_id = $('.variation_id').val();
                            if ($variation_id) {
                                var variation = '';
                                $.each($product_variations, function (index, value) {
                                    if ($variation_id == value.variation_id) {
                                        variation = value;
                                    }
                                });
                                $selected_variation = variation;
                                update_smm_prices(variation);
                            }
                        });

                        function update_smm_prices(variation) {
                            var $v_price = variation.display_price;
                            var $v_price1 = variation.display_regular_price;

                            var $upsell_price = 0;
                            var $upsell_price1 = 0;

                            var $selected_upsell = $('#_smm_upsell_option option:selected');
                            if ($selected_upsell.val() != '') {
                                var $upsell_price = $selected_upsell.data('price');
                                var $upsell_price1 = $selected_upsell.data('regular_price');
                            }


                            var $price = 0;
                            var $price1 = 0;


                            if ($('#_smm_upsell_option2').length > 0) {

                                var $upsell2_price = 0;
                                var $upsell2_price1 = 0;

                                var $selected_upsell2 = $('#_smm_upsell_option2 option:selected');
                                if ($selected_upsell2.val() != '') {
                                    var $upsell2_price = $selected_upsell2.data('price');
                                    var $upsell2_price1 = $selected_upsell2.data('regular_price');
                                }

                                $price = $v_price + $upsell_price + $upsell2_price;
                                $price1 = $v_price1 + $upsell_price1 + $upsell2_price1;

                            } else {
                                $price = $v_price + $upsell_price;
                                $price1 = $v_price1 + $upsell_price1;
                            }

                            var $final_price = 0;
                            var $diff = 0;
                            var $percentage = 0;


                            $diff = $price1 - $price;

                            if ($diff != '') {
                                $percentage = ($diff * 100) / $price1;
                            }

                            $price = $price.toFixed(2);
                            $price1 = $price1.toFixed(2);




                            //if($price != $price1){
                            var $d = '<span class="price"><del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi>' + accounting.formatMoney($price1, '<span class="woocommerce-Price-currencySymbol"><?php echo get_woocommerce_currency_symbol(); ?></span>', <?php echo wc_get_price_decimals(); ?>, "<?php echo wc_get_price_thousand_separator(); ?>", "<?php echo wc_get_price_decimal_separator(); ?>") + '</bdi></span></del> <ins><span class="woocommerce-Price-amount amount"><bdi>' + accounting.formatMoney($price, '<span class="woocommerce-Price-currencySymbol"><?php echo get_woocommerce_currency_symbol(); ?></span>', <?php echo wc_get_price_decimals(); ?>, "<?php echo wc_get_price_thousand_separator(); ?>", "<?php echo wc_get_price_decimal_separator(); ?>") + '</bdi></span></ins></span>';
                            $('.woocommerce-variation-price').html($d);

                            if ($percentage != 0) {
                                $diff = $diff.toFixed(2);
                                $percentage = $percentage.toFixed(2);
                                var $d = 'Je bespaart: <span class="you_save_value_percentage">' + accounting.formatMoney($diff, '<span class="woocommerce-Price-currencySymbol"><?php echo get_woocommerce_currency_symbol(); ?></span>', <?php echo wc_get_price_decimals(); ?>, "<?php echo wc_get_price_thousand_separator(); ?>", "<?php echo wc_get_price_decimal_separator(); ?>") + ' (' + accounting.formatMoney($percentage, '', <?php echo wc_get_price_decimals(); ?>, "<?php echo wc_get_price_thousand_separator(); ?>", "<?php echo wc_get_price_decimal_separator(); ?>") + '%)</span>';

                                $('.woocommerce-variation.single_variation').find('.wcst_savings_variation').remove();
                                $('.woocommerce-variation.single_variation .woocommerce-variation-price').after('<div class="wcst_savings_variation">' + $d + '</div>');
                            }






                            //}

                        }
                    });
                </script>

                <?php
            }
        }
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            if ($('#service_help_text').length > 0) {
                new Tippy('#service_help_text', {
                    position: 'top',
                    animation: 'scale',
                    arrow: 'true'
                });
            }
        });
    </script>
    <?php
}

function kia_add_to_cart_validation($passed, $product_id, $qty) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = " . $product_id . "", OBJECT);
    $product_data = json_decode(json_encode($results), True);
    foreach ($product_data as $row => $values):
        $product_details[$values['meta_key']] = $values['meta_value'];
    endforeach;
    $link_lbl = (isset($product_details['_link_label'])) ? $product_details['_link_label'] : 'Link';

    if (isset($_POST['_custom_option']) && sanitize_text_field($_POST['_custom_option']) == '') {
        $product = wc_get_product($product_id);

        wc_add_notice(sprintf(__($link_lbl . ' cannot be added to the cart until you enter Link.', 'kia-plugin-textdomain'), $product->get_title()), 'error');
        return false;
    }
    // Custom Comment
    if (get_service_type($product_id) == 'custom_comments') {
        if (isset($_POST['_custom_comment']) && sanitize_text_field($_POST['_custom_comment']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__($link_lbl . ' cannot be added to the cart until you enter Comment.', 'kia-plugin-textdomains'), $product->get_title()), 'error');
            return false;
        }
    }
    // Mention Custom List
    if (get_service_type($product_id) == 'mention_custom_list') {
        if (isset($_POST['_mention_custom_list']) && sanitize_text_field($_POST['_mention_custom_list']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Mention Custom list.', 'plugin-mention-custom-list'), $product->get_title()), 'error');
            return false;
        }
    }

    //Mention User Follower
    if (get_service_type($product_id) == 'mention_user_follower') {
        if (isset($_POST['_mention_user_follower']) && sanitize_text_field($_POST['_mention_user_follower']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Mention User Follower.', 'plugin-mention-user-follower'), $product->get_title()), 'error');
            return false;
        }
    }
    //Mention Comment Likes
    if (get_service_type($product_id) == 'comment_likes') {
        if (isset($_POST['_comment_likes']) && sanitize_text_field($_POST['_comment_likes']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Comment Likes.', 'plugin-comment-likes'), $product->get_title()), 'error');
            return false;
        }
    }



    //DripFeed
    if (get_service_type($product_id) == 'drip_feed') {
        if (isset($_POST['_runs']) && sanitize_text_field($_POST['_runs']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Runs.', 'plugin-runs'), $product->get_title()), 'error');
            return false;
        }
        if (isset($_POST['_interval']) && sanitize_text_field($_POST['_interval']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Interval.', 'plugin-interval'), $product->get_title()), 'error');
            return false;
        }
    }

    //Subscription
    if (get_service_type($product_id) == 'subscription') {
        if (isset($_POST['_username']) && sanitize_text_field($_POST['_username']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Username.', 'plugin-username'), $product->get_title()), 'error');
            return false;
        }
        if (isset($_POST['_min']) && sanitize_text_field($_POST['_min']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Minimum.', 'plugin-min'), $product->get_title()), 'error');
            return false;
        }
        if (isset($_POST['_max']) && sanitize_text_field($_POST['_max']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Maximum.', 'plugin-max'), $product->get_title()), 'error');
            return false;
        }

        if (isset($_POST['_posts']) && sanitize_text_field($_POST['_posts']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Posts.', 'plugin-posts'), $product->get_title()), 'error');
            return false;
        }

        if (isset($_POST['_delay']) && sanitize_text_field($_POST['_delay']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Delay.', 'plugin-delay'), $product->get_title()), 'error');
            return false;
        }

        if (isset($_POST['_expiry']) && sanitize_text_field($_POST['_expiry']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Expiry.', 'plugin-expiry'), $product->get_title()), 'error');
            return false;
        }
    }

    //Package
    if (get_service_type($product_id) == 'mention_package') {
        if (isset($_POST['_package']) && sanitize_text_field($_POST['_package']) == '') {
            $product = wc_get_product($product_id);
            wc_add_notice(sprintf(__('%s cannot be added to the cart until you enter Package.', 'plugin-package'), $product->get_title()), 'error');
            return false;
        }
    }
    return $passed;
}

add_filter('woocommerce_add_to_cart_validation', 'kia_add_to_cart_validation', 10, 3);

function kia_add_cart_item_data($cart_item, $product_id) {

    if (isset($_POST['_custom_option'])) {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = " . $product_id . "", OBJECT);
        $product_data = json_decode(json_encode($results), True);
        $product_details = [];
        foreach ($product_data as $row => $values):
            $product_details[$values['meta_key']] = $values['meta_value'];
        endforeach;
        $link_lbl = (isset($product_details['_link_type']) && $product_details['_link_type'] != 'no_value') ? $product_details['_link_type'] . sanitize_text_field($_POST['_custom_option']) : sanitize_text_field($_POST['_custom_option']);

        $cart_item['custom_option'] = $link_lbl;
    }
    // CUSTOM COMMENT
    if (get_service_type($product_id) == 'custom_comments') {
        if (isset($_POST['_custom_comment'])) {
            $cart_item['custom_comment'] = $_POST['_custom_comment'];
        }
    }
    //MENTION CUSTOM LIST
    if (get_service_type($product_id) == 'mention_custom_list') {
        if (isset($_POST['_mention_custom_list'])) {
            $cart_item['mention_custom_list'] = sanitize_text_field($_POST['_mention_custom_list']);
        }
    }
    // MENTION USER FOLLOWER
    if (get_service_type($product_id) == 'mention_user_follower') {
        if (isset($_POST['_mention_user_follower'])) {
            $cart_item['mention_user_follower'] = sanitize_text_field($_POST['_mention_user_follower']);
        }
    }
    //COMMENT LIKES
    if (get_service_type($product_id) == 'comment_likes') {
        if (isset($_POST['_comment_likes'])) {
            $cart_item['comment_likes'] = sanitize_text_field($_POST['_comment_likes']);
        }
    }
    // DRIP FEED
    if (get_service_type($product_id) == 'drip_feed') {
        if (isset($_POST['_runs'])) {
            $cart_item['runs'] = sanitize_text_field($_POST['_runs']);
        }
        if (isset($_POST['_interval'])) {
            $cart_item['interval'] = sanitize_text_field($_POST['_interval']);
        }
    }

    //SUBSCRIPTION

    if (get_service_type($product_id) == 'subscription') {
        if (isset($_POST['_username'])) {
            $cart_item['username'] = sanitize_text_field($_POST['_username']);
        }
        if (isset($_POST['_min'])) {
            $cart_item['min'] = sanitize_text_field($_POST['_min']);
        }
        if (isset($_POST['_max'])) {
            $cart_item['max'] = sanitize_text_field($_POST['_max']);
        }
        if (isset($_POST['_posts'])) {
            $cart_item['posts'] = sanitize_text_field($_POST['_posts']);
        }
        if (isset($_POST['_delay'])) {
            $cart_item['delay'] = sanitize_text_field($_POST['_delay']);
        }
        if (isset($_POST['_expiry'])) {
            $cart_item['expiry'] = sanitize_text_field($_POST['_expiry']);
        }
    }

    //PACKAGE
    if (get_service_type($product_id) == 'comment_likes') {
        if (isset($_POST['_package'])) {
            $cart_item['package'] = sanitize_text_field($_POST['_package']);
        }
    }
    return $cart_item;
}

add_filter('woocommerce_add_cart_item_data', 'kia_add_cart_item_data', 10, 2);

function kia_get_cart_item_from_session($cart_item, $values) {
    // DEFAULT
    if (isset($values['custom_option'])) {
        $cart_item['custom_option'] = $values['custom_option'];
    }
    // CUSTOM COMMENT
    if (isset($values['custom_comment'])) {
        $cart_item['custom_comment'] = $values['custom_comment'];
    }
    // COMMENT LIST
    if (isset($values['mention_custom_list'])) {
        $cart_item['mention_custom_list'] = $values['mention_custom_list'];
    }
    // MENTION USER FOLLOWER
    if (isset($values['mention_user_follower'])) {
        $cart_item['mention_user_follower'] = $values['mention_user_follower'];
    }
    // COMMENT LIKES
    if (isset($values['comment_likes'])) {
        $cart_item['comment_likes'] = $values['comment_likes'];
    }
    // DRIPFEED
    if (isset($values['runs'])) {
        $cart_item['runs'] = $values['runs'];
    }

    if (isset($values['interval'])) {
        $cart_item['interval'] = $values['interval'];
    }

    // SUBSCRIPTION

    if (isset($values['username'])) {
        $cart_item['username'] = $values['username'];
    }

    if (isset($values['min'])) {
        $cart_item['min'] = $values['min'];
    }

    if (isset($values['max'])) {
        $cart_item['max'] = $values['max'];
    }

    if (isset($values['posts'])) {
        $cart_item['posts'] = $values['posts'];
    }

    if (isset($values['delay'])) {
        $cart_item['delay'] = $values['delay'];
    }

    if (isset($values['expiry'])) {
        $cart_item['expiry'] = $values['expiry'];
    }
    // PACKAGE
    if (isset($values['package'])) {
        $cart_item['package'] = $values['package'];
    }
    return $cart_item;
}

add_filter('woocommerce_get_cart_item_from_session', 'kia_get_cart_item_from_session', 20, 2);

function kia_add_order_item_meta($item_id, $values) {
    // DEFAULT
    if (!empty($values['custom_option'])) {
        woocommerce_add_order_item_meta($item_id, 'custom_option', $values['custom_option']);
    }
    // CUSTOM COMMENT
    if (!empty($values['custom_comment'])) {
        woocommerce_add_order_item_meta($item_id, 'custom_comment', $values['custom_comment']);
    }
    // MENTION CUSTOM LIST
    if (!empty($values['mention_custom_list'])) {
        woocommerce_add_order_item_meta($item_id, 'mention_custom_list', $values['mention_custom_list']);
    }
    // MENTION USER FOLLOWER
    if (!empty($values['mention_user_follower'])) {
        woocommerce_add_order_item_meta($item_id, 'mention_user_follower', $values['mention_user_follower']);
    }
    // COMMENT LIKES
    if (!empty($values['comment_likes'])) {
        woocommerce_add_order_item_meta($item_id, 'comment_likes', $values['comment_likes']);
    }
    //DRIPFEED
    if (!empty($values['runs'])) {
        woocommerce_add_order_item_meta($item_id, 'runs', $values['runs']);
    }

    if (!empty($values['interval'])) {
        woocommerce_add_order_item_meta($item_id, 'interval', $values['interval']);
    }


    //SUBSCRIPTION
    if (!empty($values['username'])) {
        woocommerce_add_order_item_meta($item_id, 'username', $values['username']);
    }

    if (!empty($values['min'])) {
        woocommerce_add_order_item_meta($item_id, 'min', $values['min']);
    }

    if (!empty($values['max'])) {
        woocommerce_add_order_item_meta($item_id, 'max', $values['max']);
    }

    if (!empty($values['posts'])) {
        woocommerce_add_order_item_meta($item_id, 'posts', $values['posts']);
    }

    if (!empty($values['delay'])) {
        woocommerce_add_order_item_meta($item_id, 'delay', $values['delay']);
    }

    if (!empty($values['expiry'])) {
        woocommerce_add_order_item_meta($item_id, 'expiry', $values['expiry']);
    }
    // PACKAGE
    if (!empty($values['package'])) {
        woocommerce_add_order_item_meta($item_id, 'package', $values['package']);
    }
}

add_action('woocommerce_add_order_item_meta', 'kia_add_order_item_meta', 10, 2);

function kia_get_item_data($other_data, $cart_item) {
    // DEFAULT
    if (isset($cart_item['custom_option'])) {

        $other_data[] = array(
            'name' => __('Link', 'kia-plugin-textdomain'),
            'value' => sanitize_text_field($cart_item['custom_option'])
        );
    }
    // CUSTOM COMMENT
    if (isset($cart_item['custom_comment'])) {
        $other_data[] = array(
            'name' => __('Comment', 'kia-plugin-textdomains'),
            'value' => sanitize_text_field($cart_item['custom_comment'])
        );
    }
    // MENTION CUSTOM LIST   
    if (isset($cart_item['mention_custom_list'])) {
        $other_data[] = array(
            'name' => __('MentionCustomList', 'plugin-mention-custom-list'),
            'value' => sanitize_text_field($cart_item['mention_custom_list'])
        );
    }

    //MENTION USER FOLLOWER   
    if (isset($cart_item['mention_user_follower'])) {
        $other_data[] = array(
            'name' => __('Username', 'plugin-mention-user-follower'),
            'value' => sanitize_text_field($cart_item['mention_user_follower'])
        );
    }
    //COMMENT LIKES  
    if (isset($cart_item['comment_likes'])) {
        $other_data[] = array(
            'name' => __('Username', 'plugin-comment-likes'),
            'value' => sanitize_text_field($cart_item['comment_likes'])
        );
    }
    //DRIPFEED
    if (isset($cart_item['runs'])) {
        $other_data[] = array(
            'name' => __('Runs', 'plugin-runs'),
            'value' => sanitize_text_field($cart_item['runs'])
        );
    }

    if (isset($cart_item['interval'])) {
        $other_data[] = array(
            'name' => __('Interval', 'plugin-interval'),
            'value' => sanitize_text_field($cart_item['interval'])
        );
    }

    //SUBSCRIPTION
    if (isset($cart_item['username'])) {
        $other_data[] = array(
            'name' => __('Username', 'plugin-username'),
            'value' => sanitize_text_field($cart_item['username'])
        );
    }

    if (isset($cart_item['min'])) {
        $other_data[] = array(
            'name' => __('Min', 'plugin-min'),
            'value' => sanitize_text_field($cart_item['min'])
        );
    }

    if (isset($cart_item['max'])) {
        $other_data[] = array(
            'name' => __('Max', 'plugin-max'),
            'value' => sanitize_text_field($cart_item['max'])
        );
    }

    if (isset($cart_item['posts'])) {
        $other_data[] = array(
            'name' => __('Posts', 'plugin-posts'),
            'value' => sanitize_text_field($cart_item['posts'])
        );
    }

    if (isset($cart_item['delay'])) {
        $other_data[] = array(
            'name' => __('Delay', 'plugin-delay'),
            'value' => sanitize_text_field($cart_item['delay'])
        );
    }

    if (isset($cart_item['expiry'])) {
        $other_data[] = array(
            'name' => __('Expiry', 'plugin-expiry'),
            'value' => sanitize_text_field($cart_item['expiry'])
        );
    }
    //PACKAGE
    if (isset($cart_item['package'])) {
        $other_data[] = array(
            'name' => __('Package', 'plugin-package'),
            'value' => sanitize_text_field($cart_item['package'])
        );
    }


    return $other_data;
}

add_filter('woocommerce_get_item_data', 'kia_get_item_data', 10, 2);

function kia_order_item_product($cart_item, $order_item) {
    //DEFAULT
    if (isset($order_item['custom_option'])) {
        $cart_item_meta['custom_option'] = $order_item['custom_option'];
    }
    // CUSTOM COMMENT
    if (isset($order_item['custom_comment'])) {
        $cart_item_meta['custom_comment'] = $order_item['custom_comment'];
    }
    // MENTION CUSTOM LIST
    if (isset($order_item['mention_custom_list'])) {
        $cart_item_meta['mention_custom_list'] = $order_item['mention_custom_list'];
    }
    // MENTION USER FOLLOWER
    if (isset($order_item['mention_user_follower'])) {
        $cart_item_meta['mention_user_follower'] = $order_item['mention_user_follower'];
    }
    // COMMENT LIKES
    if (isset($order_item['comment_likes'])) {
        $cart_item_meta['comment_likes'] = $order_item['comment_likes'];
    }
    //DRIPFEED
    if (isset($order_item['runs'])) {
        $cart_item_meta['runs'] = $order_item['runs'];
    }

    if (isset($order_item['interval'])) {
        $cart_item_meta['interval'] = $order_item['interval'];
    }

    //SUBSCRIPTION
    if (isset($order_item['username'])) {
        $cart_item_meta['username'] = $order_item['username'];
    }

    if (isset($order_item['min'])) {
        $cart_item_meta['min'] = $order_item['min'];
    }
    if (isset($order_item['max'])) {
        $cart_item_meta['max'] = $order_item['max'];
    }

    if (isset($order_item['posts'])) {
        $cart_item_meta['posts'] = $order_item['posts'];
    }
    if (isset($order_item['delay'])) {
        $cart_item_meta['delay'] = $order_item['delay'];
    }

    if (isset($order_item['expiry'])) {
        $cart_item_meta['expiry'] = $order_item['expiry'];
    }
    // PACKAGE
    if (isset($order_item['package'])) {
        $cart_item_meta['package'] = $order_item['package'];
    }

    return $cart_item;
}

add_filter('woocommerce_order_item_product', 'kia_order_item_product', 10, 2);

function kia_order_again_cart_item_data($cart_item, $order_item, $order) {
    // DEFAULT
    if (isset($order_item['custom_option'])) {
        $cart_item_meta['custom_option'] = $order_item['custom_option'];
    }
    // CUSTOM COMMENT
    if (isset($order_item['custom_comment'])) {
        $cart_item_meta['custom_comment'] = $order_item['custom_comment'];
    }
    // MENTIION CUSTOM LIST
    if (isset($order_item['mention_custom_list'])) {
        $cart_item_meta['mention_custom_list'] = $order_item['mention_custom_list'];
    }
    // MENTION USER FOLLOWER
    if (isset($order_item['mention_user_follower'])) {
        $cart_item_meta['mention_user_follower'] = $order_item['mention_user_follower'];
    }
    // COMMENT LIKES
    if (isset($order_item['comment_likes'])) {
        $cart_item_meta['comment_likes'] = $order_item['comment_likes'];
    }
    //DRIPFEED
    if (isset($order_item['runs'])) {
        $cart_item_meta['runs'] = $order_item['runs'];
    }
    if (isset($order_item['interval'])) {
        $cart_item_meta['interval'] = $order_item['interval'];
    }

    //SUBSCRIPTION
    if (isset($order_item['username'])) {
        $cart_item_meta['username'] = $order_item['username'];
    }
    if (isset($order_item['min'])) {
        $cart_item_meta['min'] = $order_item['min'];
    }
    if (isset($order_item['max'])) {
        $cart_item_meta['max'] = $order_item['max'];
    }
    if (isset($order_item['posts'])) {
        $cart_item_meta['posts'] = $order_item['posts'];
    }
    if (isset($order_item['delay'])) {
        $cart_item_meta['delay'] = $order_item['delay'];
    }
    if (isset($order_item['expiry'])) {
        $cart_item_meta['expiry'] = $order_item['expiry'];
    }

    // PACKAGE
    if (isset($order_item['package'])) {
        $cart_item_meta['package'] = $order_item['package'];
    }
    return $cart_item;
}

add_filter('woocommerce_order_again_cart_item_data', 'kia_order_again_cart_item_data', 10, 3);

add_action('woocommerce_add_to_cart', 'smm_add_upsell_product_to_cart', 10, 6);

function smm_add_upsell_product_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {

    remove_action('woocommerce_add_to_cart', 'smm_add_upsell_product_to_cart', 10, 6);

    if (isset($_POST['_smm_upsell_product_id']) && isset($_POST['_smm_upsell_option']) && !empty($_POST['_smm_upsell_option'])) {
        $product_upsell_product = get_post_meta($product_id, '_smm_upsell_product', true);
        if (!empty($product_upsell_product) && $product_upsell_product === $_POST['_smm_upsell_product_id']) {
            $upsell_product_variation = absint($_POST['_smm_upsell_option']);
            if (!empty($upsell_product_variation)) {
                $quantity = 1;
                $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_upsell_product, $quantity);
                $product_status = get_post_status($product_upsell_product);
                $upsell_product = wc_get_product($product_upsell_product);

                WC()->cart->add_to_cart($product_upsell_product, $quantity, $upsell_product_variation);
            }
        }
    }

    if (isset($_POST['_smm_upsell_product_id2']) && isset($_POST['_smm_upsell_option2']) && !empty($_POST['_smm_upsell_option2'])) {
        $product_upsell_product2 = get_post_meta($product_id, '_smm_upsell_product2', true);
        if (!empty($product_upsell_product2) && $product_upsell_product2 === $_POST['_smm_upsell_product_id2']) {
            $upsell_product_variation2 = absint($_POST['_smm_upsell_option2']);
            if (!empty($upsell_product_variation2)) {
                $quantity = 1;
                $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_upsell_product2, $quantity);
                $product_status = get_post_status($product_upsell_product2);
                $upsell_product = wc_get_product($product_upsell_product2);

                WC()->cart->add_to_cart($product_upsell_product2, $quantity, $upsell_product_variation2);
            }
        }
    }
}

add_filter('woocommerce_add_cart_item_data', 'order_bump_add_cart_item_data', 99, 2);

function order_bump_add_cart_item_data($_cart_item, $_product_id) {
    if (!isset($_cart_item['_wfob_product']))
        return $_cart_item;
    $cart_product = array();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $_product_id) {
            $cart_product = $cart_item;
        }
    }

    if (!empty($cart_product)) {
        $keys_to_check = array('custom_option', 'custom_comment', 'mention_custom_list', 'mention_user_follower', 'comment_likes', 'runs', 'runs', 'username', 'min', 'max', 'posts', 'expiry', 'package');
        foreach ($keys_to_check as $key) {
            if (isset($cart_product[$key])) {
                $_cart_item[$key] = $cart_product[$key];
            }
        }
    }

    return $_cart_item;
}

// disable klaviyo plugin updates.

function alt_filter_plugin_updates($value) {

    // Add references to plugins you want to disable update notices for in the $plugins array
    $plugins = array(
        'klaviyo/klaviyo.php'
    );

    foreach ($plugins as $plugin) {
        if (isset($value->response[$plugin])) {
            unset($value->response[$plugin]);
        }
    }

    return $value;
}

add_filter('site_transient_update_plugins', 'alt_filter_plugin_updates');

add_shortcode('smm-order-status', 'smm_order_status_shortcode_fn');

function smm_order_status_shortcode_fn() {
    ob_start();
    wp_enqueue_script('jquery-blockui');
    ?>
    <style>
        .smm_order_status_form .progress-bar-wraper{
            text-align: center;
        }
        .smm_order_status_form table img {
            max-width: 75px;
            max-height: 75px;
            width: auto;
            height: auto;
        }

        .progress-bar {
            display: inline-block;
            margin: 16px auto 32px;
            max-width: 480px;
            width: 100%
        }

        .progress-bar .icon-container {
            margin: 0 auto;
            width: 87%
        }

        .progress-bar .icon {
            border: 4px solid #a4a4a4;
            border-radius: 50%;
            float: left;
            width: 13%
        }

        .progress-bar .icon img {
            float: left;
            height: auto;
            padding: 25%
        }

        .progress-bar .line {
            background-color: #a4a4a4;
            border-radius: 5px;
            float: left;
            height: 4px;
            margin: 6% 1%;
            width: 14%
        }

        .progress-bar .text-icon-container {
            display: block;
            float: left;
            margin: 4px auto 0;
            width: 100%
        }

        .progress-bar .text-icon {
            color: #a4a4a4;
            float: left;
            font-size: 14px;
            font-weight: 700;
            width: 25%;
            text-align: center
        }

        .progress-bar .success {
            background: #30a647
        }

        .progress-bar .active,
        .progress-bar .success {
            border-color: #30a647;
            color: #30a647
        }

    </style>
    <form method="post" class="smm_order_status_form" >
        <div class="form-field">
            <label for="smm-order-id">Order Number:</label>
            <input type="text" id="orderNumber" name="orderNumber" value="" placeholder="Order Number:" />
        </div>
        <div class="form-field">
            <label for="smm-order-email">E-Mail:</label>
            <input type="email" id="email" name="email" value="" placeholder="E-Mail:" />
        </div>
        <div class="form-field">
            <?php wp_nonce_field('smm_order_status_nonce', 'smm_order_status_nonce'); ?>
            <input type="submit" id="check_smm_order" name="check_smm_order" value="Check it">
        </div>
        <div class="form-messages">
        </div>
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $(document).on('submit', '.smm_order_status_form', function (event) {
                event.preventDefault();
                var $form = $(this);
                if ($form.find('#orderNumber').val() == '' || $form.find('#email').val() == '') {
                    $form.find('.form-messages').html('<p class="form-error-message">Please fill out all of the fields.</p>');
                    return;
                }

                $form.addClass('processing').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                var $order_number = $form.find('#orderNumber').val();
                var $order_email = $form.find('#email').val();
                var $security = $form.find('#smm_order_status_nonce').val();
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {'action': 'get_user_smm_order_details', 'order_id': $order_number, 'email': $order_email, 'security': $security}, function ($data) {
                    $form.unblock();
                    if ($data.status == 'error') {
                        $form.find('.form-messages').html('<p class="form-error-message">' + $data.message + '</p>');
                    } else {
                        $form.find('.form-messages').html('');
                        $form.html($data.table);
                    }

                });

                var formdata = $(this).serialize();
                console.log(formdata);
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_get_user_smm_order_details', 'get_user_smm_order_details_fn');
add_action('wp_ajax_nopriv_get_user_smm_order_details', 'get_user_smm_order_details_fn');

function get_user_smm_order_details_fn() {
    check_ajax_referer('smm_order_status_nonce', 'security');
    $email = wc_clean($_POST['email']);
    $order_id = wc_clean($_POST['order_id']);

    if (empty($email) || empty($order_id)) {
        wp_send_json(array('status' => 'error', 'message' => 'Please fill out all of the fields.'));
    }

    if (!is_email($email)) {
        wp_send_json(array('status' => 'error', 'message' => 'Please enter valid email is.'));
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json(array('status' => 'error', 'message' => 'Order details not found.'));
    }

    $order_email = $order->get_billing_email();
    if ($order_email != $email) {
        wp_send_json(array('status' => 'error', 'message' => 'Order details not found.'));
    }

    global $wpdb;
    $table = $wpdb->prefix . "api_order_detail";
    $res = $wpdb->get_results('Select * from ' . $table . ' where woo_order_id = "' . $order_id . '"');

    if (empty($res)) {
        wp_send_json(array('status' => 'error', 'message' => 'Order details not found.'));
    } else {
        $data = array();
        $completed = 0;
        $inprocess = 0;
        foreach ($res as $r) {
            $_product = array();
            $smm_order_id = $r->order_id;
            $product_id = $r->product_id;
            $product = wc_get_product($product_id);
            $product_image = $product->get_image();
            $_product['product_id'] = $product_id;
            $_product['image'] = $product_image;
            $_product['link'] = $r->link;
            $_product['product_title'] = $product->get_title();
            $_product['piece'] = $r->quantity;
            $_product['status'] = $r->mesg;
            $_product['product_link'] = get_permalink($product_id);

            $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = " . $product_id . " and meta_key like '%_service_parent%'", OBJECT);
            $service_val = json_decode(json_encode($results), True);
            $parent_id = $service_val[0]['meta_value'];

            $tablename = $wpdb->prefix . "api_credentials";
            $api_data = $wpdb->get_row("SELECT * FROM $tablename where api_id=" . $parent_id . "");
            $api_data = json_decode(json_encode($api_data), true);
            $api = new Api();
            $api->api_url = $api_data['api_url'];
            $api->api_key = $api_data['api_key'];
            $order_status = $api->status($smm_order_id);
            //print_r($order_status);
            if ($order_status) {
                $_product['status'] = $order_status->status;
                $_product['start_count'] = $order_status->start_count;
            }
            $data[] = $_product;
            if($order_status->status == 'In progress'){
                $inprocess++;
            }
            if($order_status->status == 'Completed'){
                $completed++;
            }
        }
    }

    ob_start();
    if (!empty($data)) {
        
        
        
        if(count($data) == $completed){
            $is_completed = 'success';
            $is_process = 'success';
        }elseif(count($data) >= $inprocess){
            $is_completed = '';
            $is_process = 'success';
        }else{
            $is_completed = '';
            $is_process = '';
        }
        
        ?>
        <div class="progress-bar-wraper">
            <div class="progress-bar" data-completed="<?php echo $completed;?>" data-progress="<?php echo $inprocess;?>" data-total="<?php echo count($data);?>">
            <div class="icon-container">
                <div class="icon success">
                    <img src="<?php echo plugin_dir_url( __FILE__ );?>images/check.png" class="img-fluid">
                </div>
                <div class="line success"></div>

                <div class="icon success">
                    <img src="<?php echo plugin_dir_url( __FILE__ );?>images/check.png" class="img-fluid">
                </div>
                <div class="line <?php echo $is_process;?>"></div>

                <div class="icon <?php echo $is_process;?>">
                    <img src="<?php echo plugin_dir_url( __FILE__ );?>images/check.png" class="img-fluid">
                </div>
                
                <div class="line <?php echo $is_completed;?>"></div>
                <div class="icon <?php echo $is_completed;?>">
                    <img src="<?php echo plugin_dir_url( __FILE__ );?>images/check.png" class="img-fluid">
                </div>
                
            </div>
            <div class="text-icon-container">
                <div class="text-icon text-success">Order Created</div>
                <div class="text-icon text-success">Paid</div>
                <div class="text-icon text-success">In Process</div>
                <div class="text-icon text-success">Completed</div>
            </div>
        </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Link</th>
                    <th>Start Count</th>
                    <th>Piece</th>
                    <th>Order Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row) { ?>
                    <tr>
                        <td><a href="<?php echo $row['product_link']; ?>" target="_blank"><?php echo $row['image']; ?></a></td>
                        <td><a href="<?php echo $row['link']; ?>" target="_blank"><?php echo $row['link']; ?></a></td>
                        <td><?php echo $row['start_count']; ?></td>
                        <td><?php echo $row['piece']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
    }
    $table = ob_get_clean();

    wp_send_json(array('status' => 'success', 'message' => 'Order details found.', 'table' => $table));

    die();
}
