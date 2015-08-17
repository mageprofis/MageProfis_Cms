<?php

class MageProfis_Cms_Model_Observer
extends Mage_Core_Model_Abstract
{
    /**
     * @mageEvent cms_page_render
     * @param Varien_Event_Observer $event
     */
    public function onCmsPagePreDispatch (Varien_Event_Observer $event)
    {
        $page = $event->getPage();
        /* @var $page Mage_Cms_Model_Page */
        if ($page->getIdentifier() != 'home'
                && strlen($page->getGroupname()) > 1
                && in_array('0', $page->getStoreId()))
        {
            $redirect = Mage::getModel('cms/page')->getCollection()
                ->addFieldToSelect(array('groupname', 'identifier'))
                ->addFieldToFilter('groupname', $page->getGroupname())
                ->addFieldToFilter('is_active', 1)
                ->addStoreFilter((int) Mage::app()->getStore()->getStoreId(), false)
                ->setPageSize(1)
                ->setOrder('store_id', 'DESC')
                ->getFirstItem()
            ;
            /* @var $redirect Mage_Cms_Model_Page */
            if ($redirect && $redirect->getId() &&
                    $redirect->getIdentifier() != $page->getIdentifier())
            {
                Mage::app()->getFrontController()->getResponse()
                    ->setRedirect(Mage::getUrl($redirect->getIdentifier()), 301)
                    ->sendResponse();
                exit;
            }
        }
    }

    /**
     * @mageEvent adminhtml_cms_page_edit_tab_main_prepare_form
     * @param type $event
     */
    public function addFieldToMainTab (Varien_Event_Observer $event)
    {
        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = true;
        if (Mage::getSingleton('admin/session')->isAllowed('cms/page/save')) {
            $isElementDisabled = false;
        }

        $form = $event->getForm();
        /* @var $form Varien_Data_Form */
        $fieldset = $form->getElement('base_fieldset');
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset->addField('groupname', 'text', array(
            'name'      => 'groupname',
            'label'     => Mage::helper('mpcms')->__('Group Name'),
            'title'     => Mage::helper('mpcms')->__('Group Name'),
            'required'  => true,
            'class'     => 'validate-identifier',
            'note'      => Mage::helper('cms')->__('For multilanguage pages'),
            'disabled'  => $isElementDisabled
        ), 'identifier');
    }

    /**
     * 
     * @mageEvent controller_action_layout_generate_blocks_after
     * @param type $event
     */
    public function addFieldToGrid (Varien_Event_Observer $event)
    {
        $action = $event->getEvent()->getAction();
        /* @var $action Mage_Adminhtml_Cms_PageController */
        if ($action instanceof Mage_Adminhtml_Cms_PageController && in_array($action->getFullActionName(), array('adminhtml_cms_page_index')))
        {
            $block = $action->getLayout()->getBlock('cms_page.grid');
            /* @var $block Mage_Adminhtml_Block_Cms_Page_Grid */
            if ($block)
            {
                $block->addColumnAfter('groupname', array(
                        'header'    => Mage::helper('mpcms')->__('Group Name'),
                        'width'     => '50px',
                        'align'     => 'left',
                        'type'      => 'text',
                        'index'     => 'groupname'
                    ), 'identifier');
            }
        }
    }
}