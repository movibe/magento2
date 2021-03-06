<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml billing agreement info tab
 */
namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement\View\Tab;

class Info extends \Magento\Backend\Block\Template
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'billing/agreement/view/tab/info.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /** @var \Magento\Customer\Service\V1\CustomerServiceInterface */
    protected $_customerService;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Customer\Service\V1\CustomerServiceInterface $customerService
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Customer\Service\V1\CustomerServiceInterface $customerService,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_customerService = $customerService;
        parent::__construct($context, $data);
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General Information');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve billing agreement model
     *
     * @return \Magento\Paypal\Model\Billing\Agreement
     */
    protected function _getBillingAgreement()
    {
        return $this->_coreRegistry->registry('current_billing_agreement');
    }

    /**
     * Set data to block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $agreement = $this->_getBillingAgreement();
        $this->setReferenceId($agreement->getReferenceId());
        $customerId = $agreement->getCustomerId();
        $customer = $this->_customerService->getCustomer($customerId);

        $this->setCustomerEmail($customer->getEmail());
        $this->setCustomerUrl(
            $this->getUrl('customer/index/edit', array('id' => $customerId))
        );
        $this->setStatus($agreement->getStatusLabel());
        $this->setCreatedAt(
            $this->formatDate($agreement->getCreatedAt(), 'short', true)
        );
        $this->setUpdatedAt(
            ($agreement->getUpdatedAt())
                ? $this->formatDate($agreement->getUpdatedAt(), 'short', true)
                : __('N/A')
        );

        return parent::_toHtml();
    }
}
