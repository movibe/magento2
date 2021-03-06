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
 * @category    Magento
 * @package     Magento_Email
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Email\Model;

/**
 * Common sender
 *
 * @category   Magento
 * @package    Magento_Email
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Sender
{
    /**
     * @var \Magento\Email\Model\Template\Mailer
     */
    protected $_mailer;

    /**
     * @var \Magento\Email\Model\Info
     */
    protected $_emailInfo;

    /**
     * @var \Magento\Core\Model\Store
     */
    protected $_store;

    /**
     * @param \Magento\Email\Model\Template\Mailer $mailer
     * @param \Magento\Email\Model\Info $info
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Email\Model\Template\Mailer $mailer,
        \Magento\Email\Model\Info $info,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_mailer = $mailer;
        $this->_emailInfo = $info;
        $this->_store = $storeManager->getStore();
    }

    /**
     * @param string $email
     * @param string $name
     * @param string $template
     * @param string $sender
     * @param array $templateParams
     * @param int $storeId
     * @return $this
     */
    public function send($email, $name, $template, $sender, $templateParams, $storeId)
    {
        $this->_store->load($storeId);
        $this->_emailInfo->addTo($email, $name);
        $this->_mailer->addEmailInfo($this->_emailInfo);
        $this->_mailer->setSender($this->_store->getConfig($sender, $this->_store->getId()));
        $this->_mailer->setStoreId($this->_store->getId());
        $this->_mailer->setTemplateId($this->_store->getConfig($template, $this->_store->getId()));
        $this->_mailer->setTemplateParams($templateParams);
        $this->_mailer->send();
        return $this;
    }
}
