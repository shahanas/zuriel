<?php

class Magestore_Ajaxcart_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_cache = array();
    public function getBaseUrlImage()
    {
        $imgSrc = Mage::getStoreConfig("ajaxcart/general/animation_img");

        return $imgSrc;
    }
	public function getConfig($code, $store = null){
		return Mage::getStoreConfig("ajaxcart/general/$code",$store);
	}
    
    public function getConfirmConfig($code, $store = null) {
        return Mage::getStoreConfig("ajaxcart/confirmation/$code", $store);
    }
    
    /**
     * get Mini cart block class
     * 
     * @return string
     */
    public function getMiniCartClass() {
        if (!isset($this->_cache['mini_cart_class'])) {
            $minicartSelect = '';
            if ($minicartBlock = Mage::app()->getLayout()->getBlock('cart_sidebar')) {
                $xmlMinicart = simplexml_load_string($this->toXMLElement($minicartBlock->toHtml()));
                $attributes = $xmlMinicart->attributes();
                if ($id = (string)$attributes->id) {
                    $minicartSelect = "#$id";
                } elseif ($class = (string)$attributes->class) {
                    $minicartSelect = '[class="' . $class . '"]';
                }
            }
            $this->_cache['mini_cart_class'] = $minicartSelect;
        }
        return $this->_cache['mini_cart_class'];
    }
    
    public function getMiniCompareClass(){
        if(!isset($this->_cache['mini_compare_class'])){
            $miniCompareSelect = '';
            if($miniCompareBlock = Mage::app()->getLayout()->getBlock('catalog.compare.sidebar')){
                $xmlMiniCompare = simplexml_load_string($this->toXMLElement($miniCompareBlock->toHtml()));
                $attributes = $xmlMiniCompare->attributes();
                if ($id = (string)$attributes->id) {
                    $miniCompareSelect = "#$id";
                } elseif ($class = (string)$attributes->class) {
                    $miniCompareSelect = '[class="' . $class . '"]';
                }
            }
            $this->_cache['mini_compare_class'] = $miniCompareSelect;
        }
        return $this->_cache['mini_compare_class'];
    }
    
    public function getLinkClass() {
        if (!isset($this->_cache['link_class'])) {
            $linkSelect = '';
            if ($linkBlock = Mage::app()->getLayout()->getBlock('top.links')) {
                $xmlLink = simplexml_load_string($this->toXMLElement($linkBlock->toHtml()));
                $attributes = $xmlLink->attributes();
                if ($id = (string)$attributes->id) {
                    $linkSelect = "#$id";
                } elseif ($class = (string)$attributes->class) {
                    $linkSelect = '[class="' . $class . '"]';
                }
            }
            $this->_cache['link_class'] = $linkSelect;
        }
        return $this->_cache['link_class'];
    }
    
    public function toXMLElement($html) {
        $open = trim(substr($html, 0, strpos($html, '>')+1));
        $close = '</' . substr($open, 1, strpos($open, ' ')-1) . '>';
        if ($xml = $open . $close) {
            return $xml;
        }
        return '<div></div>';
    }
    
    public function getTemplateFolder() {
        $template = $this->getConfig('template');
        return Mage::getStoreConfig("ajaxcart_template/$template/folder");
    }
}
