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

namespace Magento\Customer\Model;

use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;
use Magento\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\Dto\Customer as CustomerDto;
use Magento\Customer\Service\V1\Dto\CustomerBuilder as CustomerDtoBuilder;

/**
 * Customer Model converter.
 *
 * Converts a Customer Model to a DTO or vice versa.
 */
class Converter
{
    /**
     * @var CustomerDtoBuilder
     */
    protected $_customerBuilder;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param CustomerFactory $customerFactory
     * @param CustomerDtoBuilder $customerBuilder
     */
    public function __construct(CustomerDtoBuilder $customerBuilder, CustomerFactory $customerFactory)
    {
        $this->_customerBuilder = $customerBuilder;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Convert a customer model to a customer entity
     *
     * @param Customer $customerModel
     * @return CustomerDto
     */
    public function createCustomerFromModel(Customer $customerModel)
    {
        $customerBuilder = $this->_populateBuilderWithAttributes($customerModel);
        $customerBuilder->setCustomerId($customerModel->getId());
        $customerBuilder->setFirstname($customerModel->getFirstname());
        $customerBuilder->setLastname($customerModel->getLastname());
        $customerBuilder->setEmail($customerModel->getEmail());
        return $customerBuilder->create();
    }


    /**
     * @param int $customerId
     * @throws NoSuchEntityException If customer with customerId is not found.
     * @return Customer
     */
    public function getCustomerModel($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);

        if (!$customer->getId()) {
            // customer does not exist
            throw new NoSuchEntityException('customerId', $customerId);
        } else {
            return $customer;
        }
    }


    /**
     * Creates a customer model from a customer entity.
     *
     * @param CustomerDto $customer
     * @return Customer
     */
    public function createCustomerModel(CustomerDto $customer)
    {
        $customerModel = $this->_customerFactory->create();

        $attributes = $customer->getAttributes();
        foreach ($attributes as $attributeCode => $attributeValue) {
            // avoid setting password through set attribute
            if ($attributeCode == 'password') {
                continue;
            } else {
                $customerModel->setData($attributeCode, $attributeValue);
            }
        }

        $customerId = $customer->getCustomerId();
        if ($customerId) {
            $customerModel->setId($customerId);
        }

        // Need to use attribute set or future updates can cause data loss
        if (!$customerModel->getAttributeSetId()) {
            $customerModel->setAttributeSetId(CustomerMetadataServiceInterface::CUSTOMER_ATTRIBUTE_SET_ID);
            return $customerModel;
        }

        return $customerModel;
    }

    /**
     * Loads the values from a customer model
     *
     * @param Customer $customerModel
     * @return CustomerDtoBuilder
     */
    protected function _populateBuilderWithAttributes(Customer $customerModel)
    {
        $attributes = [];
        foreach ($customerModel->getAttributes() as $attribute) {
            $attrCode = $attribute->getAttributeCode();
            $value = $customerModel->getData($attrCode);
            if (null == $value) {
                continue;
            }
            $attributes[$attrCode] = $value;
        }

        return $this->_customerBuilder->populateWithArray($attributes);
    }

}
