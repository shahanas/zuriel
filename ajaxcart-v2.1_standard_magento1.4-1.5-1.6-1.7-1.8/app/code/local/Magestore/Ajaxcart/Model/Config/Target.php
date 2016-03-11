<?php


class Magestore_Ajaxcart_Model_Config_Target
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('ajaxcart')->__('Mini Cart')),
            array('value' => 1, 'label'=>Mage::helper('ajaxcart')->__('Top Link')),
        );
    }
}
