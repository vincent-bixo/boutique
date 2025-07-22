<?php
/**
 * Utility class for SendCloud module.
 *
 * PHP version 7.4
 *
 *  @author    SendCloud Global B.V. <contact@sendcloud.eu>
 *  @copyright 2023 SendCloud Global B.V.
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *  @category  Shipping
 *
 *  @see      https://sendcloud.eu
 */

use Sendcloud\PrestaShop\Classes\Repositories\OrderRepository;
use Sendcloud\PrestaShop\Classes\Repositories\ServicePointRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class WebserviceSpecificServicePoint
 *
 * @package Sendcloud\PrestaShop\Classes\webservice
 */
class WebserviceSpecificManagementServicePoint implements WebserviceSpecificManagementInterface
{
    /**
     * @var WebserviceOutputBuilder
     */
    private $webserviceOutput;
    /**
     * @var WebserviceRequest
     */
    private $wsObject;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var ServicePointRepository
     */
    private $servicePointRepository;

    /**
     * @return WebserviceOutputBuilder
     */
    public function getObjectOutput()
    {
        return $this->webserviceOutput;
    }

    /**
     * Set the webservice output
     *
     * @param WebserviceOutputBuilder $obj
     */
    public function setObjectOutput(WebserviceOutputBuilderCore $obj)
    {
        $this->webserviceOutput = $obj;
        return $this;
    }

    /**
     * @return WebserviceRequest
     */
    public function getWsObject()
    {
        return $this->wsObject;
    }

    /**
     * @param WebserviceRequest $obj
     */
    public function setWsObject(WebserviceRequestCore $obj)
    {
        $this->wsObject = $obj;
    }

    /**
     * Validate if the request is properly configured
     *
     * @return bool
     */
    public function manage(): bool
    {
        return true;
    }

    /**
     * @return string
     *
     * @throws WebserviceException
     */
    public function getContent()
    {
        $orderId = $this->wsObject->urlSegment[1] ?? null;

        if (!$orderId) {
            return 'Order ID not found in the URL.';
        }
        $cartId = $this->getOrderRepository()->getCartByOrderId($orderId);
        $servicePoint = $this->getServicePointRepository()->getByCartId($cartId);
        if (!$servicePoint || !$servicePoint['details']) {
            return 'Service point not found.';
        }
        $servicePointDetails =  json_decode($servicePoint['details'], true);

        $this->webserviceOutput->setHeaderParams('Content-Type', 'application/xml; charset=utf-8');
        $output = $this->webserviceOutput->getObjectRender()->renderNodeHeader('id', []);
        $output .= htmlspecialchars($servicePointDetails['id'], ENT_XML1, 'UTF-8') . "\n";
        $output .= $this->webserviceOutput->getObjectRender()->renderNodeFooter('id', []);

        return $output;
    }

    /**
     * @return OrderRepository
     */
    private function getOrderRepository()
    {
        if (!$this->orderRepository) {
            $this->orderRepository = new OrderRepository();
        }

        return $this->orderRepository;
    }

    /**
     * @return ServicePointRepository
     */
    private function getServicePointRepository()
    {
        if (!$this->servicePointRepository) {
            $this->servicePointRepository = new ServicePointRepository();
        }

        return $this->servicePointRepository;
    }
}
