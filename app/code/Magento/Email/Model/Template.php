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

use Magento\Core\Exception;
use Magento\Email\Model\Template\Filter;
use Magento\Filter\Template as FilterTemplate;

/**
 * Template model
 *
 * Example:
 *
 * // Loading of template
 * \Magento\Email\Model\TemplateFactory $templateFactory
 * $templateFactory->create()->load($this->_coreStoreConfig->getConfig('path_to_email_template_id_config'));
 * $variables = array(
 *    'someObject' => $this->_coreResourceEmailTemplate
 *    'someString' => 'Some string value'
 * );
 * $emailTemplate->send('some@domain.com', 'Name Of User', $variables);
 *
 * @method \Magento\Email\Model\Resource\Template _getResource()
 * @method \Magento\Email\Model\Resource\Template getResource()
 * @method string getTemplateCode()
 * @method \Magento\Email\Model\Template setTemplateCode(string $value)
 * @method string getTemplateText()
 * @method \Magento\Email\Model\Template setTemplateText(string $value)
 * @method string getTemplateStyles()
 * @method \Magento\Email\Model\Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method \Magento\Email\Model\Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method \Magento\Email\Model\Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method \Magento\Email\Model\Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method \Magento\Email\Model\Template setTemplateSenderEmail(string $value)
 * @method string getAddedAt()
 * @method \Magento\Email\Model\Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method \Magento\Email\Model\Template setModifiedAt(string $value)
 * @method string getOrigTemplateCode()
 * @method \Magento\Email\Model\Template setOrigTemplateCode(string $value)
 * @method string getOrigTemplateVariables()
 * @method \Magento\Email\Model\Template setOrigTemplateVariables(string $value)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Template extends \Magento\Core\Model\Template
{
    /**
     * Configuration path for default email templates
     */
    const XML_PATH_SENDING_SET_RETURN_PATH      = 'system/smtp/set_return_path';
    const XML_PATH_SENDING_RETURN_PATH_EMAIL    = 'system/smtp/return_path_email';
    const XML_PATH_DESIGN_EMAIL_LOGO            = 'design/email/logo';
    const XML_PATH_DESIGN_EMAIL_LOGO_ALT        = 'design/email/logo_alt';

    /**
     * Config path to mail sending setting that shows if email communications are disabled
     */
    const XML_PATH_SYSTEM_SMTP_DISABLE = 'system/smtp/disable';

    /**
     * Email template filter
     *
     * @var FilterTemplate
     */
    protected $_templateFilter;

    /**
     * Email template preprocessed flag
     *
     * @var bool
     */
    protected $_preprocessFlag = false;

    /**
     * BCC list
     *
     * @var array
     */
    protected $_bcc = array();

    /**
     * Return path
     *
     * @var string
     */
    protected $_returnPath = '';

    /**
     * Reply address
     *
     * @var string
     */
    protected $_replyTo = '';

    /**
     * @var \Exception|null
     */
    protected $_sendingException = null;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\View\Url
     */
    protected $_viewUrl;

    /**
     * @var \Magento\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;

    /**
     * Constructor
     *
     * Email filter factory
     *
     * @var \Magento\Email\Model\Template\FilterFactory
     */
    protected $_emailFilterFactory;

    /**
     * Types of template
     */
    const TYPE_TEXT = 1;
    const TYPE_HTML = 2;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\App\Emulation $appEmulation
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\App\ConfigInterface $coreConfig
     * @param \Magento\Email\Model\Template\FilterFactory $emailFilterFactory
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\View\DesignInterface $design,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\App\Emulation $appEmulation,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\Filesystem $filesystem,
        \Magento\View\Url $viewUrl,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\App\ConfigInterface $coreConfig,
        \Magento\Email\Model\Template\FilterFactory $emailFilterFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = array()
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_filesystem = $filesystem;
        $this->_viewUrl = $viewUrl;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_coreConfig = $coreConfig;
        $this->_emailFilterFactory = $emailFilterFactory;
        $this->_emailConfig = $emailConfig;
        parent::__construct($context, $design, $registry, $appEmulation, $storeManager, $data);
    }

    /**
     * Initialize email template model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Email\Model\Resource\Template');
    }

    /**
     * Return logo URL for emails. Take logo from theme if custom logo is undefined
     *
     * @param  \Magento\Core\Model\Store|int|string $store
     * @return string
     */
    protected function _getLogoUrl($store)
    {
        $store = $this->_storeManager->getStore($store);
        $fileName = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO);
        if ($fileName) {
            $uploadDir = \Magento\Backend\Model\Config\Backend\Email\Logo::UPLOAD_DIR;
            $mediaDirectory = $this->_filesystem->getDirectoryRead(\Magento\App\Filesystem::MEDIA_DIR);
            if ($mediaDirectory->isFile($uploadDir . '/' . $fileName)) {
                return $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\UrlInterface::URL_TYPE_MEDIA) . $uploadDir . '/' . $fileName;
            }
        }
        return $this->getDefaultEmailLogo();
    }

    /**
     * Get default email logo image
     *
     * @return string
     */
    public function getDefaultEmailLogo()
    {
        return $this->_viewUrl->getViewFileUrl(
            'Magento_Email::logo_email.gif',
            array('area' => \Magento\Core\Model\App\Area::AREA_FRONTEND)
        );
    }

    /**
     * Return logo alt for emails
     *
     * @param  \Magento\Core\Model\Store|int|string $store
     * @return string
     */
    protected function _getLogoAlt($store)
    {
        $store = $this->_storeManager->getStore($store);
        $alt = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO_ALT);
        if ($alt) {
            return $alt;
        }
        return $store->getFrontendName();
    }

    /**
     * Retrieve mail object instance
     *
     * @return \Zend_Mail
     */
    protected function _getMail()
    {
        return new \Zend_Mail('utf-8');
    }

    /**
     * Declare template processing filter
     *
     * @param FilterTemplate $filter
     * @return $this
     */
    public function setTemplateFilter(FilterTemplate $filter)
    {
        $this->_templateFilter = $filter;
        return $this;
    }

    /**
     * Get filter object for template processing log
     *
     * @return Filter
     */
    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = $this->_emailFilterFactory->create();
            $this->_templateFilter->setUseAbsoluteLinks($this->getUseAbsoluteLinks())
                ->setStoreId($this->getDesignConfig()->getStore());
        }
        return $this->_templateFilter;
    }

    /**
     * Load template by code
     *
     * @param string $templateCode
     * @return $this
     */
    public function loadByCode($templateCode)
    {
        $this->addData($this->getResource()->loadByCode($templateCode));
        return $this;
    }

    /**
     * Load default email template
     *
     * @param string $templateId
     * @return $this
     */
    public function loadDefault($templateId)
    {
        $templateFile = $this->_emailConfig->getTemplateFilename($templateId);
        $templateType = $this->_emailConfig->getTemplateType($templateId);
        $templateTypeCode = $templateType == 'html' ? self::TYPE_HTML : self::TYPE_TEXT;
        $this->setTemplateType($templateTypeCode);

        $modulesDirectory = $this->_filesystem->getDirectoryRead(\Magento\App\Filesystem::MODULES_DIR);
        $templateText = $modulesDirectory->readFile($modulesDirectory->getRelativePath($templateFile));

        if (preg_match('/<!--@subject\s*(.*?)\s*@-->/u', $templateText, $matches)) {
            $this->setTemplateSubject($matches[1]);
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@vars\s*((?:.)*?)\s*@-->/us', $templateText, $matches)) {
            $this->setData('orig_template_variables', str_replace("\n", '', $matches[1]));
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@styles\s*(.*?)\s*@-->/s', $templateText, $matches)) {
            $this->setTemplateStyles($matches[1]);
            $templateText = str_replace($matches[0], '', $templateText);
        }

        /**
         * Remove comment lines
         */
        $templateText = preg_replace('#\{\*.*\*\}#suU', '', $templateText);

        $this->setTemplateText($templateText);
        $this->setId($templateId);

        return $this;
    }

    /**
     * Return template id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getTemplateId();
    }

    /**
     * Set id of template
     *
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->setTemplateId($value);
    }

    /**
     * Return true if this template can be used for sending queue as main template
     *
     * @return boolean
     */
    public function isValidForSend()
    {
        return !$this->_coreStoreConfig->getConfigFlag('system/smtp/disable')
            && $this->getSenderName()
            && $this->getSenderEmail()
            && $this->getTemplateSubject();
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    public function getType()
    {
        return $this->getTemplateType();
    }

    /**
     * Process email template code
     *
     * @param array $variables
     * @return string
     * @throws \Exception
     */
    public function getProcessedTemplate(array $variables = array())
    {
        $processor = $this->getTemplateFilter();
        $processor->setUseSessionInUrl(false)
            ->setPlainTemplateMode($this->isPlain());

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        if (isset($variables['subscriber'])) {
            $processor->setStoreId($variables['subscriber']->getStoreId());
        }

        if (!isset($variables['logo_url'])) {
            $variables['logo_url'] = $this->_getLogoUrl($processor->getStoreId());
        }
        if (!isset($variables['logo_alt'])) {
            $variables['logo_alt'] = $this->_getLogoAlt($processor->getStoreId());
        }

        $processor->setIncludeProcessor(array($this, 'getInclude'))
            ->setVariables($variables);

        $this->_applyDesignConfig();
        $storeId = $this->getDesignConfig()->getStore();
        try {
            $processedResult = $processor->setStoreId($storeId)
                ->filter($this->getPreparedTemplateText());
        } catch (\Exception $e) {
            $this->_cancelDesignConfig();
            throw $e;
        }
        $this->_cancelDesignConfig();
        return $processedResult;
    }

    /**
     * Makes additional text preparations for HTML templates
     *
     * @return string
     */
    public function getPreparedTemplateText()
    {
        if ($this->isPlain() || !$this->getTemplateStyles()) {
            return $this->getTemplateText();
        }
        // wrap styles into style tag
        $html = "<style type=\"text/css\">\n%s\n</style>\n%s";
        return sprintf($html, $this->getTemplateStyles(), $this->getTemplateText());
    }

    /**
     * Get template code for include directive
     *
     * @param string $template
     * @param array $variables
     * @return string
     */
    public function getInclude($template, array $variables)
    {
        $thisClass = __CLASS__;
        $includeTemplate = new $thisClass();

        $includeTemplate->loadByCode($template);

        return $includeTemplate->getProcessedTemplate($variables);
    }

    /**
     * Send mail to recipient
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param array|string $email E-mail(s)
     * @param array|string|null $name receiver name(s)
     * @param array $variables template variables
     * @return bool
     */
    public function send($email, $name = null, array $variables = array())
    {
        if (!$this->isValidForSend()) {
            $this->_logger->logException(
                new \Exception('This letter cannot be sent.') // translation is intentionally omitted
            );
            return false;
        }

        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', $this->_coreStoreConfig->getConfig('system/smtp/host'));
        ini_set('smtp_port', $this->_coreStoreConfig->getConfig('system/smtp/port'));

        $mail = $this->_getMail();
        foreach ($this->_bcc as $bcc) {
            $mail->addBcc($bcc);
        }
        if ($this->_returnPath) {
            $mail->setReturnPath($this->_returnPath);
        }
        if ($this->_replyTo) {
            $mail->setReplyTo($this->_replyTo);
        }

        $setReturnPath = $this->_coreStoreConfig->getConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = $this->_coreStoreConfig->getConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mailTransport = new \Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
            \Zend_Mail::setDefaultTransport($mailTransport);
        }

        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        $result = false;
        $this->_sendingException = null;
        try {
            $mail->send();
            $result = true;
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            $this->_sendingException = $e;
        }
        $this->_bcc = array();
        $this->_returnPath = '';
        $this->_replyTo = '';

        return $result;
    }

    /**
     * Get exception, generated during send() method
     *
     * @return \Exception|null
     */
    public function getSendingException()
    {
        return $this->_sendingException;
    }

    /**
     * Send transactional email to recipient
     *
     * @param int|string $templateId
     * @param string|array $sender sender information, can be declared as part of config path
     * @param string $email recipient email
     * @param string $name recipient name
     * @param array $vars variables which can be used in template
     * @param int|null $storeId
     * @return $this
     * @throws Exception
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars = array(), $storeId = null)
    {
        $this->setSentSuccess(false);
        if (($storeId === null) && $this->getDesignConfig()->getStore()) {
            $storeId = $this->getDesignConfig()->getStore();
        }

        if (is_numeric($templateId)) {
            $this->load($templateId);
        } else {
            $this->loadDefault($templateId);
        }

        if (!$this->getId()) {
            throw new Exception(__('Invalid transactional email code: %1', $templateId));
        }

        if (!is_array($sender)) {
            $this->setSenderName(
                $this->_coreStoreConfig->getConfig('trans_email/ident_' . $sender . '/name', $storeId)
            );
            $this->setSenderEmail(
                $this->_coreStoreConfig->getConfig('trans_email/ident_' . $sender . '/email', $storeId)
            );
        } else {
            $this->setSenderName($sender['name']);
            $this->setSenderEmail($sender['email']);
        }

        if (!isset($vars['store'])) {
            $vars['store'] = $this->_storeManager->getStore($storeId);
        }
        $this->setSentSuccess($this->send($email, $name, $vars));
        return $this;
    }

    /**
     * Process email subject
     *
     * @param array $variables
     * @return string
     * @throws \Exception
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $processor = $this->getTemplateFilter();

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        $processor->setVariables($variables);

        $this->_applyDesignConfig();
        $storeId = $this->getDesignConfig()->getStore();
        try {
            $processedResult = $processor->setStoreId($storeId)
                ->filter($this->getTemplateSubject());
        } catch (\Exception $e) {
            $this->_cancelDesignConfig();
            throw $e;
        }
        $this->_cancelDesignConfig();
        return $processedResult;
    }

    /**
     * Add email BCC
     *
     * @param string|array $bcc
     * @return $this
     */
    public function addBcc($bcc)
    {
        $this->_bcc[] = $bcc;
        return $this;
    }

    /**
     * Set Return Path
     *
     * @param string $email
     * @return $this
     */
    public function setReturnPath($email)
    {
        $this->_returnPath = $email;
        return $this;
    }

    /**
     * Add Reply-To header
     *
     * @param string $email
     * @return $this
     */
    public function setReplyTo($email)
    {
        $this->_replyTo = $email;
        return $this;
    }

    /**
     * Parse variables string into array of variables
     *
     * @param string $variablesString
     * @return array
     */
    protected function _parseVariablesString($variablesString)
    {
        $variables = array();
        if ($variablesString && is_string($variablesString)) {
            $variablesString = str_replace("\n", '', $variablesString);
            $variables = \Zend_Json::decode($variablesString);
        }
        return $variables;
    }

    /**
     * Retrieve option array of variables
     *
     * @param boolean $withGroup if true wrap variable options in group
     * @return array
     */
    public function getVariablesOptionArray($withGroup = false)
    {
        $optionArray = array();
        $variables = $this->_parseVariablesString($this->getData('orig_template_variables'));
        if ($variables) {
            foreach ($variables as $value => $label) {
                $optionArray[] = array(
                    'value' => '{{' . $value . '}}',
                    'label' => __('%1', $label)
                );
            }
            if ($withGroup) {
                $optionArray = array(
                    'label' => __('Template Variables'),
                    'value' => $optionArray
                );
            }
        }
        return $optionArray;
    }

    /**
     * Validate email template code
     *
     * @throws Exception
     * @return $this
     */
    protected function _beforeSave()
    {
        $code = $this->getTemplateCode();
        if (empty($code)) {
            throw new Exception(__('The template Name must not be empty.'));
        }
        if ($this->_getResource()->checkCodeUsage($this)) {
            throw new Exception(__('Duplicate Of Template Name'));
        }
        return parent::_beforeSave();
    }
}
