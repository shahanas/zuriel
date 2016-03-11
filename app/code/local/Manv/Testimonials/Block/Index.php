<?php   

class Manv_Testimonials_Block_Index extends Mage_Core_Block_Template{   


	
public function ShowTestimonialsRecords()
{
  $w = Mage::getSingleton('core/resource')->getConnection('core_write');
    $results = $w->query('SELECT * FROM manv_testimonials');
   return $results;

}

public function getTestimonialsEnabled()
    {
        return Mage::getStoreConfig('manv/manv_group/manv_select',Mage::app()->getStore());
    }
public function getTestimonialsImageWidth()
    {
        return Mage::getStoreConfig('manv/manv_group/manv_width',Mage::app()->getStore());
    }
public function getTestimonialsImageHeight()
    {
        return Mage::getStoreConfig('manv/manv_group/manv_height',Mage::app()->getStore());
    }
public function getTestimonialsView()
    {
        return Mage::getStoreConfig('manv/manv_group/manv_view',Mage::app()->getStore());
    }
public function getTestimonialsImages()
    {
        return Mage::getStoreConfig('manv/manv_group/manv_image',Mage::app()->getStore());
    }
public function getTestimonialsStyle()
    {
        return Mage::getStoreConfig('manv/manv_group/manv_style',Mage::app()->getStore());
    }

}
