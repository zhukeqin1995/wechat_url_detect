# wechat_url_detect
### 某app域名检测用
### 使用时引入该文件后即可使用
实例化类时需要传入uri参数以及cookie参数

具体使用方法如下
```
/**
*第一个参数为需要检测的url
*第二个参数为检测模式  1为检测顶级域名 2为不检测顶级域名
*第三个参数为每次检测请求次数  为防止拥堵  建议为2
**/
$nowstatus=$wechat_url_delect->get_status($url,1,2);
```
