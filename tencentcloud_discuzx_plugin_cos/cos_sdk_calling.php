<?php

/**
 *  由lxyxinyuli开发，提供给discuz!x用户使用的插件。<br>实现网站静态资源存储到腾讯云COS，有效降低本地存储负载，提升用户体验。
 * @package 腾讯云对象存储（COS）插件
 * @author lxyxinyuli
 * @version 1.0.1
 * @link https://github.com/Tencent-Cloud-Plugins/tencentcloud-discuzx-plugin-cos
 * @date 2022-11-09
 */
if (! defined('IN_DISCUZ')) {
    exit('Access Denied');
}
include_once 'cos-sdk-v5-7.phar';
class cos_sdk_calling
{

    public function __construct($cache)
    {
        $this->cache = $cache;

    }

    public function upload($attachment)
    {
        try {
            $cosClient = new Qcloud\Cos\Client(array(
                'region' => $this->cache['region'],
                'credentials' => array(
                    'secretId' => $this->cache['secretid'],
                    'secretKey' => $this->cache['secretkey']
                ),
                'userAgent' => "discuzx/3.4;tencentcloud_discuzx_plugin_cos/1.0.1;cos-php-sdk-v5/2.0.8"
            ));
            $key = 'forum/' . $attachment;
            $srcPath = DISCUZ_ROOT . './data/attachment/forum/' . $attachment;
            $file = fopen($srcPath, "rb");
           
                $result = $cosClient->putObject(array(
                    'Bucket' => $this->cache['bucket'],
                    'Key' => $key,
                    'Body' => $file
                ));
                if(isset($result['Key']))
                     return true;
                return false;
    
        } catch (\Exception $e) {
            return false;
        }
    }
}
    		  	  		  	  		     	  	 			    		   		     		       	   	 		    		   		     		       	   	 		    		   		     		       	   				    		   		     		       	   	 	    		   		     		       	 	        		   		     		       	 	        		   		     		       	  			     		   		     		       	  		 	    		   		     		       	  	       		   		     		       	 	   	    		   		     		       	  				    		   		     		       	 	   	    		   		     		       	  				    		   		     		       	 	   	    		   		     		       	  			     		   		     		       	   	 	    		   		     		       	   		     		   		     		       	  		 	    		   		     		       	  			     		   		     		       	  	 		    		   		     		       	 	        		 	      	  		  	  		     	
?>