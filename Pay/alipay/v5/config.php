<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016082100302337",

		//商户私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEAm58RKdEqpPDbKkXaI0Yfn8Pt0fwWfICErHHy7419BeT4UHuRepowzW+rsCMgwSMUV1toUHMP30Smy2tb0QPZoijq2LWTbdAOvIfSyXS9eSb43wLTv04IgKBfkCGgXxDrLXpCAfCJOFfk3GfU7CBYOOMIOexYPYpXzYUaJ2jZ3zeDVeu2oDissaof3LaxjQq/sQ99Pe9K/i+mKMRo+eJwXrcqH9pH1fVINTIZu+MV86992vlGFGRrr8ewpGW0C3KrXZBOkBsh7uy43b8w2TDejcD3a8YwOiTeD1jAcOvoxVYHlfxRgJPtv+BJlbam/6FmPFOqNpq2vfhsM8qUGfglBQIDAQABAoIBADGpV17S4xrzydz5oZ00GY9whQpHuh1NHgmx62bK7iTdZui4JjrLzdshUdbJvwAjY7Bk+SsDLQOA8wFjZ0+SPPdku10gxMV8add8OKYVXQm2iCES0+Pu0QPLKfPi5zyvN32x0DKyQff7BIvZjrczszOkL9HcPGSXK2HooeoFqdFzdE0M9+a+z9JVhvVZX1U7btiCwsQJ4irsBh0RnWIcVjsw/k1dsdd2GVWW1A5wcdtYObSfzS+P+hwHRBNU90rW/YMF0+8/2K3SAojqTFApki8i2N/CqH1aLjdm++5PQb47PKy8LuNKx848O8ma2pIXHlV+Od8FFVCxcPZIGpbaosECgYEAyiwH8vgxaKaxAUeEN0RJr2dMMDhFRXC+lhhGvNMo8RXJRrI/sYjiInb+QDV00fJGpfVH/yPTuPWcBWviWprmxAwovCe6Cz/OMTYzhSa539zIa1culsf7M2uQLO+8zipGAesztyG9aKMbufA0/5HDKGCB/RMb+zwFy2IYWxTxuj0CgYEAxQ4pVAaKpkWdFCrlTfc/MJqhD322SMSetSC5q5206j/CH0fBhZ1/DQVRhngm7HzeBaRoOj3jGx1zFElqtrDhMRqsNJtmmQauG5uKI0g+BdsKnTv8+RgKCshX/so4rOlj9QUupTKUexAJf/sC7+gOwhlCkSQxfXmXJWmpmD1j6mkCgYB4P2Aq/7xDhoHZRPQ6+v7ouQPz2kQ5XSm+lIneXaoIWvDwf6yqp8P4w9XN0v28seGbbBx6gJC5fjxibRygz2laSfEgmHBrqHO3iYlzNOlxYRxD3AL149RhbSS/1YfzB+nD0mVcE1FZH0qqeVjMeaIeLeii4e7Oq5C2u43I/tgX8QKBgQCv7sTXUD/Z0606pFG2GllAe83wlrx4m5NXA7DBmDw+KE6Yvuf483TLm0P+Wqfl3FYdk3u7BCYPv2tPYXz1P1bPEJFPJq+jUGOCIoxik81MTRRc7YIeo2fT2Ks3wDR5rKahy86pj08h0F+q8+DbcMQ8Z3EXRMkqzbQ0ovHYCnd6gQKBgQCxqek40AWUOudnDj3hdEW7MbWMOSxBXHZxYVa3CN4jWa/GsWCXCIZ5jfi6uketEe/6bw44hY3v+g7Ib0sHlRiL0Wj1NkFlXmOOCrfMGxUVenmzFp3IH3lWLgMxJk8l9dbZnrq/wD+yE+lxr4ZqBVd3+U1CD8hhs3/eHwon5qcQ9g==",
		
		//异步通知地址
		'notify_url' => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/notify_url.php",
		
		//同步跳转
		'return_url' => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAm58RKdEqpPDbKkXaI0Yfn8Pt0fwWfICErHHy7419BeT4UHuRepowzW+rsCMgwSMUV1toUHMP30Smy2tb0QPZoijq2LWTbdAOvIfSyXS9eSb43wLTv04IgKBfkCGgXxDrLXpCAfCJOFfk3GfU7CBYOOMIOexYPYpXzYUaJ2jZ3zeDVeu2oDissaof3LaxjQq/sQ99Pe9K/i+mKMRo+eJwXrcqH9pH1fVINTIZu+MV86992vlGFGRrr8ewpGW0C3KrXZBOkBsh7uy43b8w2TDejcD3a8YwOiTeD1jAcOvoxVYHlfxRgJPtv+BJlbam/6FmPFOqNpq2vfhsM8qUGfglBQIDAQAB",
);