<?php
class Manv_Testimonials_Block_Adminhtml_Testimonials_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("testimonials_form", array("legend"=>Mage::helper("testimonials")->__("Item information")));

				
						$fieldset->addField("name", "text", array(
						"label" => Mage::helper("testimonials")->__("name"),
						"name" => "name",
						));
									
						$fieldset->addField('pic', 'image', array(
						'label' => Mage::helper('testimonials')->__('picture'),
						'name' => 'pic',
						'note' => '(*.jpg, *.png, *.gif)',
						));
						$fieldset->addField("message", "textarea", array(
						"label" => Mage::helper("testimonials")->__("message"),
						"name" => "message",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getTestimonialsData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getTestimonialsData());
					Mage::getSingleton("adminhtml/session")->setTestimonialsData(null);
				} 
				elseif(Mage::registry("testimonials_data")) {
				    $form->setValues(Mage::registry("testimonials_data")->getData());
				}
				return parent::_prepareForm();
		}
}
