<?php
/**
 * Created By : Rohan Hapani
 */
namespace Feedaty\Badge\Block\Adminhtml;

use Feedaty\Badge\Model\Config\Source\WebService;

class BadgeSelect extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Retrieve Element HTML fragment
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $merchant = $this->_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);

        $element_id = $element->getId();

        $script = "<script>

                require([
                    'jquery'
                ], function ($) {

                    $(document).ready(function($) {

                        // COMMON VARS

                        var size = '';

                        var element_id = '". $element_id ."';

                        var feedaty_config_form = $('#config-edit-form');

                        // SELECTORS

                        var fdt_store_style = $('#feedaty_badge_options_widget_store_merch_style');

                        var fdt_product_style = $('#feedaty_badge_options_widget_products_prod_style');

                        var fdt_store_variant = $('#feedaty_badge_options_widget_store_merch_variant');

                        var fdt_product_variant = $('#feedaty_badge_options_widget_products_prod_variant');

                        var fdt_store_preview = $('#row_feedaty_badge_options_widget_store_preview>td.value');

                        var fdt_product_preview =  $('#row_feedaty_badge_options_widget_products_prod_preview>td.value');

                        // INIT VALUES

                        var style_store_selected = $('option:selected', fdt_store_style).text();

                        var style_prod_selected = $('option:selected', fdt_product_style).text();

                        var variant_store_selected = $('option:selected', fdt_store_variant).text();

                        var variant_prod_selected = $('option:selected', fdt_product_variant).text();

                        var fdt_product_preview_html =  '<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/' + style_prod_selected + '_' + variant_prod_selected + '_it-IT.png\" />';

                        var fdt_store_preview_html =  '<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/' + style_store_selected + '_' + variant_store_selected + '_it-IT.png\" />';  

                        fdt_store_preview.html('<div class=\"\" >' + fdt_store_preview_html + '</div>');
                        fdt_product_preview.html('<div class=\"\" >' + fdt_product_preview_html + '</div>');


                        /*
                        * EVENTS
                        *
                        */

                        // Store Badge Style change event

                        fdt_store_style.change( function (element) {

                            style_store_selected = $('option:selected', fdt_store_style).text();

                            fdt_store_variant.val('0');

                            variant_store_selected = $('option:selected', fdt_store_variant).text();

                            // Submit

                            feedaty_config_form.submit();

                        });

                        // Product Badge Style change event

                        fdt_product_style.change( function (element) {

                            style_prod_selected = $('option:selected', fdt_product_style).text();

                            fdt_product_variant.val('0');

                            variant_prod_selected = $('option:selected', fdt_product_variant).text();

                            // Submit

                            feedaty_config_form.submit();

                        });

                        // Store Badge variant change event

                        fdt_store_variant.change( function (element) {

                            style_store_selected = $('option:selected', fdt_store_style).text();

                            variant_store_selected = $('option:selected', fdt_store_variant).text();

                            if (  element_id.match(/dynamic/g) ) {

                                fdt_store_preview_html =  '<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/' + style_store_selected + '_' + variant_store_selected + '_it-IT.png\" />';

                            }

                            else {

                                fdt_store_preview_html =  '<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/' + style_store_selected + '_' + variant_store_selected + '_it-IT.png\" />';
                            }

                            fdt_store_preview.html('<div class=\"\" >' + fdt_store_preview_html + '</div>');

                        });

                        // Product Badge variant change event

                        fdt_product_variant.change( function (element) {

                            style_prod_selected = $('option:selected', fdt_product_style).text();

                            variant_prod_selected = $('option:selected', fdt_product_variant).text();

                            if (  element_id.match(/dynamic/g) ) {

                                fdt_product_preview_html =  '<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/' + style_prod_selected + '_' + variant_prod_selected + '_it-IT.png\" />';

                            }

                            else {

                                fdt_product_preview_html =  '<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/' + style_prod_selected + '_' + variant_prod_selected + '_it-IT.png\" />';

                            }

                            fdt_product_preview.html('<div class=\"\" >' + fdt_product_preview_html + '</div>');

                        });
                    });
                })

            </script>";

        return parent::_getElementHtml($element) . $script;

    }
}