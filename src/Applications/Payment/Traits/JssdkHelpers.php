<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Applications\Payment\Traits;

use EasyWeChat\Support;
use Overtrue\Socialite\AccessTokenInterface;

/**
 * Trait JssdkHelpers.
 *
 * @author overtrue <i@overtrue.me>
 */
trait JssdkHelpers
{
    /**
     * [WeixinJSBridge] Generate js config for payment.
     *
     * <pre>
     * WeixinJSBridge.invoke(
     *  'getBrandWCPayRequest',
     *  ...
     * );
     * </pre>
     *
     * @param string $prepayId
     * @param bool   $json
     *
     * @return string|array
     */
    public function configForPayment($prepayId, $json = true)
    {
        $params = [
            'appId' => $this->app['merchant']->app_id,
            'timeStamp' => strval(time()),
            'nonceStr' => uniqid(),
            'package' => "prepay_id=$prepayId",
            'signType' => 'MD5',
        ];

        $params['paySign'] = Support\generate_sign($params, $this->app['merchant']->key, 'md5');

        return $json ? json_encode($params) : $params;
    }

    /**
     * [JSSDK] Generate js config for payment.
     *
     * <pre>
     * wx.chooseWXPay({...});
     * </pre>
     *
     * @param string $prepayId
     *
     * @return array|string
     */
    public function configForJSSDKPayment($prepayId)
    {
        $config = $this->configForPayment($prepayId, false);

        $config['timestamp'] = $config['timeStamp'];
        unset($config['timeStamp']);

        return $config;
    }

    /**
     * Generate app payment parameters.
     *
     * @param string $prepayId
     *
     * @return array
     */
    public function configForAppPayment($prepayId)
    {
        $params = [
            'appid' => $this->app['merchant']->app_id,
            'partnerid' => $this->app['merchant']->merchant_id,
            'prepayid' => $prepayId,
            'noncestr' => uniqid(),
            'timestamp' => time(),
            'package' => 'Sign=WXPay',
        ];

        $params['sign'] = Support\generate_sign($params, $this->app['merchant']->key);

        return $params;
    }

    /**
     * Generate js config for share user address.
     *
     * @param string|\Overtrue\Socialite\AccessTokenInterface $accessToken
     * @param bool                                            $json
     *
     * @return string|array
     */
    public function configForShareAddress($accessToken, $json = true)
    {
        if ($accessToken instanceof AccessTokenInterface) {
            $accessToken = $accessToken->getToken();
        }

        $params = [
            'appId' => $this->app['merchant']->app_id,
            'scope' => 'jsapi_address',
            'timeStamp' => strval(time()),
            'nonceStr' => uniqid(),
            'signType' => 'SHA1',
        ];

        $signParams = [
            'appid' => $params['appId'],
            'url' => Support\Url::current(),
            'timestamp' => $params['timeStamp'],
            'noncestr' => $params['nonceStr'],
            'accesstoken' => strval($accessToken),
        ];

        ksort($signParams);

        $params['addrSign'] = sha1(urldecode(http_build_query($signParams)));

        return $json ? json_encode($params) : $params;
    }
}