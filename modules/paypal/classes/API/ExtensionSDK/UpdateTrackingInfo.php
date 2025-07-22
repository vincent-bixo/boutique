<?php
/*
 * Since 2007 PayPal
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author Since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */

namespace PaypalAddons\classes\API\ExtensionSDK;

use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\HttpResponse;
use PaypalAddons\classes\API\Request\HttpRequestInterface;
use PaypalAddons\classes\API\WrapperInterface;
use PaypalAddons\services\Builder\BuilderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateTrackingInfo implements HttpRequestInterface, WrapperInterface
{
    /**
     * @var BuilderInterface
     */
    protected $builder;

    protected $headers = [];
    /**
     * @var string
     */
    protected $id;

    public function __construct(BuilderInterface $builder)
    {
        $this->headers['Content-Type'] = 'application/json';
        $this->builder = $builder;

        $body = $builder->build();
        $id = '';

        if (false === empty($body['transaction_id'])) {
            $id .= $body['transaction_id'];
        }

        if (false === empty($body['tracking_number'])) {
            $id .= '-' . $body['tracking_number'];
        }

        $this->id = $id;
    }

    public function getPath()
    {
        return sprintf('/v1/shipping/trackers/%s', $this->id);
    }

    /** @return array*/
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     *
     * @return self
     */
    public function setHeaders($headers)
    {
        if (is_array($headers)) {
            $this->headers = $headers;
        }

        return $this;
    }

    public function getBody()
    {
        $body = $this->builder->build();

        if (is_array($body)) {
            $body = json_encode($body);
        }

        return $body;
    }

    public function getMethod()
    {
        return 'PUT';
    }

    public function wrap($object)
    {
        if ($object instanceof HttpResponse) {
            return new HttpAdoptedResponse($object);
        }

        return $object;
    }
}
