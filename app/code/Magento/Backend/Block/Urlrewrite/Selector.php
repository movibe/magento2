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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Urlrewrite;

/**
 * Modes selector for URL rewrites modes
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Selector extends \Magento\View\Element\Template
{
    /**
     * List of available modes from source model
     * key => label
     *
     * @var array
     */
    protected $_modes;

    /**
     * @var string
     */
    protected $_template = 'urlrewrite/selector.phtml';

    /**
     * Set block template and get available modes
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_modes = array(
            'category' => __('For category'),
            'product' => __('For product'),
            'cms_page' => __('For CMS page'),
            'id' => __('Custom'),
        );
    }

    /**
     * Available modes getter
     *
     * @return array
     */
    public function getModes()
    {
        return $this->_modes;
    }

    /**
     * Label getter
     *
     * @return string
     */
    public function getSelectorLabel()
    {
        return __('Create URL Rewrite:');
    }

    /**
     * Check whether selection is in specified mode
     *
     * @param string $mode
     * @return bool
     */
    public function isMode($mode)
    {
        return $this->getRequest()->has($mode);
    }

    /**
     * Get default mode
     *
     * @return string
     */
    public function getDefaultMode()
    {
        $keys = array_keys($this->_modes);
        return array_shift($keys);
    }
}
