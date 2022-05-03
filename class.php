<?php
class Api_Trade_Payeer
{
    private $arParams = array();
    private $arError = array();


    public function __construct($params = array())
    {
        $this->arParams = $params;
    }


    private function Request($req = array())
    {
        $msec = round(microtime(true) * 1000);
        $req['post']['ts'] = $msec;

        $post = json_encode($req['post']);

        $sign = hash_hmac('sha256', $req['method'].$post, $this->arParams['key']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://payeer.com/api/trade/".$req['method']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "API-ID: ".$this->arParams['id'],
            "API-SIGN: ".$sign
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $arResponse = json_decode($response, true);

        if ($arResponse['success'] !== true)
        {
            $this->arError = $arResponse['error'];
            throw new Exception($arResponse['error']['code']);
        }

        return $arResponse;
    }


    public function GetError()
    {
        return $this->arError;
    }


    public function Info()
    {
        $res = $this->Request(array(
            'method' => 'info',
        ));

        return $res;
    }


    public function Orders($pair = 'BTC_USDT')
    {
        $res = $this->Request(array(
            'method' => 'orders',
            'post' => array(
                'pair' => $pair,
            ),
        ));

        return $res['pairs'];
    }


    public function Account()
    {
        $res = $this->Request(array(
            'method' => 'account',
        ));

        return $res['balances'];
    }


    public function OrderCreate($req = array())
    {
        $res = $this->Request(array(
            'method' => 'order_create',
            'post' => $req,
        ));

        return $res;
    }


    public function OrderStatus($req = array())
    {
        $res = $this->Request(array(
            'method' => 'order_status',
            'post' => $req,
        ));

        return $res['order'];
    }


    public function MyOrders($req = array())
    {
        $res = $this->Request(array(
            'method' => 'my_orders',
            'post' => $req,
        ));

        return $res['items'];
    }
}
