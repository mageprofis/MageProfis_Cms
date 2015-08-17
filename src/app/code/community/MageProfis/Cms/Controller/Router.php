<?php

class MageProfis_Cms_Controller_Router
extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();
        $front->addRouter('mpcms', $this);
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        //checking before even try to find out that current module
        //should use this router
        if (!$this->_beforeModuleMatch()) {
            return false;
        }
        
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'), 301)
                ->sendResponse();
            exit;
        }

        $this->fetchDefault();

        $identifier = trim($request->getPathInfo(), '/');
        
        $collection = Mage::getModel('cms/page')->getCollection()
                ->addFieldToSelect(array('groupname', 'identifier'))
                ->addFieldToFilter('identifier', $identifier)
                ->addFieldToFilter('groupname', array('notnull' => true))
                ->addFieldToFilter('groupname', array('neq' => ''))
                ->addFieldToFilter('is_active', 1)
                ->setPageSize(1)
        ;
        $collection->getSelect()->join(
                array('cms_store' => $collection->getTable('cms/page_store')),
                'main_table.page_id = cms_store.page_id',
                array('store_id')
        )
        ->where('cms_store.store_id NOT IN (?)', array(
            0,
            (int) Mage::app()->getStore()->getStoreId()
        ));
        
        $cms = $collection->getFirstItem();
        /* @var $cms Mage_Cms_Model_Page */
        if($cms && $cms->getId() && strlen($cms->getGroupname()) > 1)
        {
            $redirect = Mage::getModel('cms/page')->getCollection()
                ->addFieldToSelect(array('groupname', 'identifier'))
                ->addFieldToFilter('groupname', $cms->getGroupname())
                ->addFieldToFilter('is_active', 1)
                ->addStoreFilter((int) Mage::app()->getStore()->getStoreId(), true)
                ->setPageSize(1)
                ->setOrder('store_id', 'DESC')
                ->getFirstItem()
            ;
            /* @var $redirect Mage_Cms_Model_Page */
            if ($redirect && $redirect->getId())
            {
                Mage::app()->getFrontController()->getResponse()
                    ->setRedirect(Mage::getUrl($redirect->getIdentifier()), 301)
                    ->sendResponse();
                exit;
            }
        }
    }
}