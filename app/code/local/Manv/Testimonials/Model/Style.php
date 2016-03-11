<?php

class Manv_Testimonials_Model_Style
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('testimonials')->__('Fade')),
            array('value'=>2, 'label'=>Mage::helper('testimonials')->__('Shuffle')),
 	   array('value'=>3, 'label'=>Mage::helper('testimonials')->__('scrollUp')),
            array('value'=>4, 'label'=>Mage::helper('testimonials')->__('scrollRight')),
       );
    }

}
	 
