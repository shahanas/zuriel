<?php


class Manv_Testimonials_Block_Adminhtml_Testimonials extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_testimonials";
	$this->_blockGroup = "testimonials";
	$this->_headerText = Mage::helper("testimonials")->__("Testimonials Manager");
	$this->_addButtonLabel = Mage::helper("testimonials")->__("Add New Item");
	parent::__construct();
	
	}

}