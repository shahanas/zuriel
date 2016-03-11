<?php

class Magestore_Ajaxcart_Block_Ajaxcart extends Mage_Core_Block_Template {

    /**
     * get Helper for Ajaxcart module
     * 
     * @return Magestore_Ajaxcart_Helper_Data
     */
    public function getAjaxcartHelper() {
        return Mage::helper('ajaxcart');
    }

    public function _prepareLayout() {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return parent::_prepareLayout();
        }
        if ($this->getAjaxcartHelper()->getConfig('enable') && !$this->getIsCartPage()) {
		$cookie = Mage::getSingleton('core/cookie');
		
		$key=$cookie->get('onestepcheckout_admin_key');
            $this->setTemplate('ajaxcart/ajaxcart.phtml');
			if(!$key){
            $this->getLayout()->getBlock('head')->addJs('magestore/ajaxcart.js');
			}
            $this->getLayout()->getBlock('head')->addCss('css/magestore/ajaxcart/style.css');
            if ($folder = $this->getAjaxcartHelper()->getTemplateFolder()) {
                $this->getLayout()->getBlock('head')->addCss("css/magestore/ajaxcart/$folder/style.css");
            }
            $this->addProductJs();
        }
        if ( $this->getAjaxcartHelper()->getConfig('enable')
            && $this->getAjaxcartHelper()->getConfig('cart')
            && $this->getIsCartPage()
        ) {
            $this->setTemplate('ajaxcart/ajaxcartpage.phtml');
            $this->getLayout()->getBlock('head')->addJs('magestore/ajaxcart.js');
            $this->getLayout()->getBlock('head')->addJs('magestore/ajaxcartpage.js');
            $this->getLayout()->getBlock('head')->addCss('css/magestore/ajaxcart/style.css');
            if ($folder = $this->getAjaxcartHelper()->getTemplateFolder()) {
                $this->getLayout()->getBlock('head')->addCss("css/magestore/ajaxcart/$folder/style.css");
            }
            $this->addProductJs();
        }
        return parent::_prepareLayout();
    }

    /**
     * Add JS for preload
     * 
     * @return Magestore_Ajaxcart_Block_Ajaxcart
     */
    public function addProductJs() {
        if (!$this->getIsPreloadAjax()) {
            return $this;
        }
        $productJsFiles = array(
            'js' => array(
                'varien/product.js',
                // 'varien/configurable.js',
                'calendar/calendar.js',
                'calendar/calendar-setup.js'
            ),
            'skin_js' => array(
                'js/bundle.js'
            )
        );
        $headBlock = $this->getLayout()->getBlock('head');
        if (version_compare(Mage::getVersion(), '1.5.0.0', '>=')) {
            $headBlock->addJs('varien/configurable.js');
        }
        foreach ($productJsFiles['js'] as $jsFile) {
            $headBlock->addJs($jsFile);
        }
        foreach ($productJsFiles['skin_js'] as $skinJsFile) {
            $headBlock->addItem('skin_js', $skinJsFile);
        }
        return $this;
    }

    /**
     * Check config is Preload Ajax file
     * 
     * @return type
     */
    public function getIsPreloadAjax() {
        return $this->getAjaxcartHelper()->getConfig('pre_load_ajax');
    }

    public function getIsCartPage() {
        if (!$this->hasData('is_cart_page')) {
            $isCartPage = ($this->getFullActionName() == 'checkout_cart_index');
            $this->setData('is_cart_page', $isCartPage);
        }
        return $this->getData('is_cart_page');
    }

    public function getIsShowConfirmation() {
        return $this->getAjaxcartHelper()->getConfirmConfig('enable');
    }
    
    public function getAutoScroll() {
        return $this->getAjaxcartHelper()->getConfig('auto_scroll');
    }

    public function getTimeOut() {
        if (!$this->hasData('time_out')) {
            $timeOut = (int) $this->getAjaxcartHelper()->getConfirmConfig('count_down');
            $this->setData('time_out', $timeOut);
        }
        return $this->getData('time_out');
    }

    public function getFullActionName($delimiter = '_') {
        return $this->getRequest()->getRequestedRouteName() . $delimiter .
            $this->getRequest()->getRequestedControllerName() . $delimiter .
            $this->getRequest()->getRequestedActionName();
    }

    public function getUrlImage() {
        $config = Mage::helper('ajaxcart')->getBaseUrlImage();
        if ($config) {
            $imgSrc = Mage::getBaseUrl('media') . 'ajaxcart/animation/' . $config;
            return $imgSrc;
        } else {
            return $this->getSkinUrl('images/ajaxcart/loading.gif');
            // return $this->getSkinUrl('images/opc-ajax-loader.gif');
        }
    }

        
}
