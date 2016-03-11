<?php

class Magestore_Ajaxcart_Block_Adminhtml_Field_Template extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setData('onchange', 'ajaxcartChangeTemplate(this);');
        $html  = "<div id='ajaxcart-template-preview' style='display: none;'>";
        $html .= '<p class="note">' . Mage::helper('ajaxcart')->__('Preview') . '</p>';
        $templateConfig = Mage::getStoreConfig('ajaxcart_template');
        foreach ($templateConfig as $code => $config) {
            if (isset($config['screenshot']) && $config['screenshot']) {
                $imgSrc = Mage::getBaseUrl('media') . 'ajaxcart/template/' . $config['screenshot'];
                $imgAlt = Mage::helper('ajaxcart')->__($config['label']);
                $html .= "<img src='$imgSrc' alt='$imgAlt' title='$imgAlt' id='ajaxcart-template-preview-$code' />";
            }
        }
        $html .= "</div>
<script type='text/javascript'>
    function ajaxcartChangeTemplate(el) {
        var imgEl = $('ajaxcart-template-preview-'+el.value);
        $$('#ajaxcart-template-preview img').each(function(el){el.hide();});
        if (imgEl) {
            $('ajaxcart-template-preview').show();
            imgEl.show();
        } else {
            $('ajaxcart-template-preview').hide();
        }
    }
    ajaxcartChangeTemplate($('ajaxcart_general_template'));
</script>";
        return parent::_getElementHtml($element) . $html;
    }
}
