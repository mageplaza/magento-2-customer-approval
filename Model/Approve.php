<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_CustomerApproval
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\CustomerApproval\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon;
use Magento\SalesRule\Model\RuleRepository;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Api\ApproveInterface;
use Mageplaza\CustomerApproval\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Approve
 * @package Mageplaza\CustomerApproval\Model
 */
class Approve implements ApproveInterface
{
    /**
     * @var Coupon
     */
    protected $_couponResource;

    /**
     * @var CouponFactory
     */
    protected $_couponFactory;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var Random
     */
    protected $_mathRandom;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Approve constructor.
     *
     * @param Data                               $helper
     * @param Coupon                             $couponResource
     * @param CouponFactory                      $couponFactory
     * @param RuleRepository                     $ruleRepository
     * @param Random                             $mathRandom
     * @param DateTime                           $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param TransportBuilder                   $transportBuilder
     * @param LoggerInterface                    $logger
     * @param StoreManagerInterface              $storeManager
     */
    public function __construct(
        Data $helper,
        Coupon $couponResource,
        CouponFactory $couponFactory,
        RuleRepository $ruleRepository,
        Random $mathRandom,
        DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
        $this->_couponResource = $couponResource;
        $this->_couponFactory = $couponFactory;
        $this->ruleRepository = $ruleRepository;
        $this->_mathRandom = $mathRandom;
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->_transportBuilder = $transportBuilder;
        $this->_logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function generateByKey($accessKey, $qty = 1, $inclMessage = false)
    {
        if (!$this->helper->isEnabled()) {
            throw new NoSuchEntityException(__('Module Mageplaza_CustomerApproval is not enabled.'));
        }

        try {
            $generateResult = $this->quickGenerateCoupon($accessKey, ['qty' => $qty], !$inclMessage);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Something went wrong while generated coupons.'));
        }

        return $generateResult;
    }

    /**
     * @param $accessKey
     * @param array $data
     * @param bool $couponOnly
     * @return array
     * @throws LocalizedException
     * @throws \Exception
     * @throws \Zend_Serializer_Exception
     */
    public function quickGenerateCoupon($accessKey, $data = [], $couponOnly = false)
    {
        $quickItems = $this->helper->unserialize($this->helper->getLinkGeneratorConfig('link'));
        $quickItems = array_filter($quickItems, function ($item) use ($accessKey) {
            return $item['access_key'] == $accessKey;
        });

        if (!sizeof($quickItems)) {
            return [
                'codes' => null,
                'messages' => [
                    [
                        'success' => false,
                        'message' => __('Access key is not valid. Please contact your store owner.')
                    ]
                ]
            ];
        }

        $data = array_merge(array_shift($quickItems), $data);

        return $this->generateCoupon($data, $couponOnly);
    }

    /**
     * @param $data
     * @param bool $couponOnly
     * @return array
     * @throws LocalizedException
     * @throws \Exception
     */
    public function generateCoupon($data, $couponOnly = false)
    {
        if (!isset($data['rule_id'])) {
            throw new LocalizedException(__('Rule is not valid'));
        }

        try {
            $rule = $this->ruleRepository->getById($data['rule_id']);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Rule is not valid'));
        }

        $qty = (isset($data['qty']) && $data['qty']) ? (int)$data['qty'] : 1;
        $pattern = (isset($data['pattern']) && $data['pattern']) ? $data['pattern'] : '[12AN]';
        $code = $pattern = strtoupper(str_replace(' ', '', $pattern));

        /** @var $coupon \Magento\SalesRule\Model\Coupon */
        $coupon = $this->_couponFactory->create();
        $generatedCodes = [];
        $nowTimestamp = $this->dateTime->formatDate($this->date->gmtTimestamp());

        for ($i = 0; $i < $qty; $i++) {
            $attempt = 10;
            $exist = false;
            do {
                if ($attempt-- <= 0) {
                    break;
                }

                $patternString = '#\[([0-9]+)([AN]{1,2})\]#';
                if (preg_match($patternString, $pattern)) {
                    $code = preg_replace_callback($patternString, function ($param) {
                        $pool = (strpos($param[2], 'A')) === false ? '' : Random::CHARS_UPPERS;
                        $pool .= (strpos($param[2], 'N')) === false ? '' : Random::CHARS_DIGITS;

                        return $this->_mathRandom->getRandomString($param[1], $pool);
                    }, $pattern);
                }
            } while ($exist = $this->_couponResource->exists($code));
            if ($exist) {
                break;
            }

            $expirationDate = $rule->getToDate();
            if ($expirationDate instanceof \DateTimeInterface) {
                $expirationDate = $expirationDate->format('Y-m-d H:i:s');
            }

            $coupon->setId(null)
                ->setRuleId($rule->getRuleId())
                ->setUsageLimit($rule->getUsesPerCoupon())
                ->setUsagePerCustomer($rule->getUsesPerCustomer())
                ->setExpirationDate($expirationDate)
                ->setCreatedAt($nowTimestamp)
                ->setType(\Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                ->setCode($code)
                ->save();

            $generatedCodes[] = $code;
        }

        $isSent = false;
        if (isset($data['email']) && $data['email'] && sizeof($generatedCodes)) {
            $emailAddresses = array_map('trim', explode(',', $data['email']));
            $template = isset($data['email_template']) ? $data['email_template'] : $this->helper->getGeneratorConfig('email_template');
            $storeId = isset($data['store_id']) ? $data['store_id'] : (
                $this->helper->getGeneratorConfig('send_email_from') ?: $this->storeManager->getDefaultStoreView()->getId()
            );

            $isSent = $this->sendMail($emailAddresses, $generatedCodes, $template, $storeId);
        }

        return $couponOnly ? $generatedCodes : [
            'codes' => $generatedCodes,
            'messages' => $this->getGenerateMessage($generatedCodes, $isSent, $data)
        ];
    }

    /**
     * @param $sendTo
     * @param $generatedCodes
     * @param $emailTemplate
     * @param $storeId
     * @return bool
     */
    public function sendMail($sendTo, $generatedCodes, $emailTemplate, $storeId)
    {
        try {
            $this->_transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'couponCodes' => implode(', ', $generatedCodes)
                ])
                ->setFrom('general')
                ->addTo($sendTo);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            return true;
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical($e->getLogMessage());
        }

        return false;
    }

    /**
     * @param $coupons
     * @param $isSent
     * @param $data
     * @return array
     */
    public function getGenerateMessage($coupons, $isSent, $data)
    {
        $result = [];

        $couponQty = sizeof($coupons);
        if (!$couponQty) {
            $result[] = [
                'success' => false,
                'message' => __('No coupon is generated.')
            ];
        } else if ($couponQty > 1) {
            if ($isSent) {
                $result[] = [
                    'success' => true,
                    'message' => __('%1 Coupons: %2 have been generated and sent successfully!', $couponQty, '<strong>' . implode(', ', $coupons) . '</strong>')
                ];
            } else {
                $result[] = [
                    'success' => true,
                    'message' => __('%1 Coupons: %2 have been generated successfully!', $couponQty, '<strong>' . implode(', ', $coupons) . '</strong>')
                ];
            }
        } else {
            if ($isSent) {
                $result[] = [
                    'success' => true,
                    'message' => __('Coupon: %1 have been generated and sent successfully!', '<strong>' . implode(',', $coupons) . '</strong>')
                ];
            } else {
                $result[] = [
                    'success' => true,
                    'message' => __('Coupon: %1 have been generated successfully!', '<strong>' . implode(',', $coupons) . '</strong>')
                ];
            }
        }

        if (isset($data['qty']) && $couponQty && ($data['qty'] > $couponQty)) {
            $result[] = [
                'success' => false,
                'message' => __('Cannot create more than %1 coupon(s) for this pattern.', $couponQty)
            ];
        }

        if (isset($data['email']) && $data['email'] && $couponQty && !$isSent) {
            $result[] = [
                'success' => false,
                'message' => __('An error occurred while sending email(s). Please try again later.')
            ];
        }

        return $result;
    }
}