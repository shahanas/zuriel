<?php

class Manv_Testimonials_Block_Adminhtml_Testimonials_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("testimonialsGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("ASC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("testimonials/testimonials")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("testimonials")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("name", array(
				"header" => Mage::helper("testimonials")->__("name"),
				"index" => "name",
				));
				$this->addColumn("message", array(
				"header" => Mage::helper("testimonials")->__("message"),
				"index" => "message",
				));
				$this->addColumn("pic", array(
				"header" => Mage::helper("testimonials")->__("pic"),
				"index" => "pic",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_testimonials', array(
					 'label'=> Mage::helper('testimonials')->__('Remove Testimonials'),
					 'url'  => $this->getUrl('*/adminhtml_testimonials/massRemove'),
					 'confirm' => Mage::helper('testimonials')->__('Are you sure?')
				));
			return $this;
		}
			

}
