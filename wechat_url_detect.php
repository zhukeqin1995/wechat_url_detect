<?php
/**
 * Created by PhpStorm.
 * User: zhukeqin
 * Date: 2018/2/17
 * Time: 14:28
 */

/**
 * Class wechat_url_detect
 * wechat被封域名检测
 */
class wechat_url_detect{
    protected $wechaturl='https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl=';//通信地址

    /**
     * wechat_url_detect constructor.
     * @param $uri wechaturl
     * @param $cookie wechat cookie
     * @param string $wechaturl
     *
     */
    function __construct($uri,$cookie,$wechaturl='')
    {
           $this->uri=$uri;
           $this->cookie=$cookie;
           if(!empty($wechaturl)) $this->wechaturl=$wechaturl;
    }

    /**
     * @param string $url
     * @param string $modal 1为检查顶级域名  2为不检查顶级域名
     * @param int $count 每次访问次数
     * @return array('status'=>int,'remark'=>string)
     * 检查域名当前状态
     *status可能值列表
     * 0域名正常
     * 1域名被封
     * 2顶级域名未被封，当前域名已被封
     * 3账号登陆问题
     * 4未知错误，具体见remark值
     */
    private function get_status($url='',$modal='1',$count=2){
        if(empty($url)) return array('status'=>4,'remark'=>'请传入要检测的url');
        if($count<1) return array('status'=>4,'remark'=>'域名检测次数不能小于1');
        $now_status=$this->check_wechat_count($url,$count);
        switch ($now_status){
            case 0:return array('status'=>0,'remark'=>'域名访问正常');break;
            case 1:if($modal==2) return array('status'=>1,'remark'=>'该域名已被封');break;
            case 2:return array('status'=>3,'remark'=>'微信账号没有登陆');break;
            default: return array('status'=>4,'remark'=>'未知错误');
        }
        //检查顶级域名是否被封
        if($modal==1){
            $host=$this->get_host($url);
            $host_status=$this->check_wechat_count($host,$count);

            switch ($host_status){
                case 0:return array('status'=>2,'remark'=>'顶级域名未被封');break;
                case 1:return array('status'=>1,'remark'=>'该域名已被封');break;
                case 2:return array('status'=>3,'remark'=>'微信账号没有登陆');break;
                default: return array('status'=>4,'remark'=>'未知错误');
            }
        }
        return array('status'=>4,'remark'=>'未知错误');
    }

    private function check_wechat_count($url,$count){
        for ($i=1;$i<=$count;$i++){
            $status=$this->check_wechat($url);
            if($status!=1){
                break;
            }
        }
        return $status;
    }
    /**
     * @param $url
     * @return int
     */
    private function check_wechat($url){

        $api= $this->wechaturl.$url."&".$this->uri;

        $curl = curl_init();



        curl_setopt($curl,CURLOPT_COOKIE,$this->cookie);

        curl_setopt($curl,CURLOPT_URL,$api);

        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);

        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);

        curl_setopt($curl,CURLOPT_TIMEOUT,30);

        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);

        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

        curl_setopt($curl, CURLOPT_HEADER, true);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);

        curl_setopt($curl,CURLOPT_MAXREDIRS,1);

        $res=curl_exec($curl);

        curl_close($curl);

        $url1=urldecode($url);

        if(strpos($res,"网络错误,请稍后再试")===false){

            $pattern="#Location:(.*?)\n#";

            preg_match($pattern,$res,$matches);

            $location=$matches[1];

            if((strpos($res,"weixin110")!==false||strpos($res,"szsupport")!==false)&&strpos($res,$url)!==false){

                return 1;

            }elseif (strpos($res,$url1)===false) {

                return 2;

            }else{

                return 0;

            }

        }else{

            return 0;

        }

    }

    /**
     * @param $url
     * @return string
     * 获取顶级域名
     */
    private function get_host($url){

        $data = explode('.', $url);
        $co_ta = count($data);

        //判断是否是双后缀
        $zi_tow = true;
        $host_cn = 'com.cn,net.cn,org.cn,gov.cn';
        $host_cn = explode(',', $host_cn);
        foreach($host_cn as $host){
            if(strpos($url,$host)){
                $zi_tow = false;
            }
        }

        //如果是返回FALSE ，如果不是返回true
        if($zi_tow == true){

            // 是否为当前域名
            if($url == 'localhost'){
                $host = $data[$co_ta-1];
            }
            else{
                $host = $data[$co_ta-2].'.'.$data[$co_ta-1];
            }

        }
        else{
            $host = $data[$co_ta-3].'.'.$data[$co_ta-2].'.'.$data[$co_ta-1];
        }

        return $host;
    }
}