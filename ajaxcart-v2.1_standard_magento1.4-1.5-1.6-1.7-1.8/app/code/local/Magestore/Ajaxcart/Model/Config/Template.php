<?php


class Magestore_Ajaxcart_Model_Config_Template
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $templateConfig = Mage::getStoreConfig('ajaxcart_template');
        $options = array();
        foreach ($templateConfig as $code => $config) {
            $options[] = array(
                'value' => $code,
                'label' => Mage::helper('ajaxcart')->__($config['label']),
            );
        }
        return $options;
    }
}
