<?php
/**
 * Api 基类
 */

class ApiAction extends Action
{
    // success
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    // client error
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;

    // server error
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * List of allowed HTTP methods
     * @param array
     */
    protected $allowed_http_methods = ['get','delete','post','put','patch'];
    
    /**
     * HTTP status codes and their respective description
     * @param array
     * @link 
     */
    protected $http_status_code = [
        self::HTTP_OK => 'OK',
        self::HTTP_CREATED => 'CREATED'
    ];

    public function __construct()
    {
        $this->parse_request();
        return ;
    }

    private function parse_url()
    {
        // 获取url请求
        $url = trim($_SERVER['REQUEST_URI'], '/');

    }
    /**
     * 过滤不合法的请求
     */
    private function parse_request()
    {
        // 获取控制器名称
        // $this->parse_url();
        $error = [];
        $result = [];
        $module_name = ucfirst(MODULE_NAME);
        // 获取方法名
        $action_name = ACTION_NAME;
        // 读取路由配置
        $url_conf = C('URL_ROUTE_RULES_USER');
        if(!isset($url_conf[$module_name]) || empty($url_conf[$module_name])){
            $error = [
                'code' => 404,
                'msg' => $url_conf,
                'message' => '不存在对应的路由'
            ];
        } else {
            $method_conf = $url_conf[$module_name];
            // 获取请求方法
            $req_method = $_SERVER['REQUEST_METHOD'];
            // 拼接请求方法 和路由 【GET index】
            $request_url = $req_method.' '.$action_name;
            if(!isset($method_conf[$request_url]) || empty($method_conf[$request_url])){
                $error = [
                    'code' => 404,
                    'msg' => 'error',
                    'message' => '请求方法未定义'
                ];
            } else {
                try {
                    $action = $method_conf[$request_url];
                    // 通过反射获取对应的类
                    $class = new ReflectionClass($module_name.'Action');
                    // 查看类中是否存在对应方法
                    $method = $class->getMethod($action);
                } catch (ReflectionException $e){
                    header('Content-Type: application/json;UTF-8');
                    http_response_code(404);
                    echo json_encode([
                        'msg' => 'error',
                        'code' => 404,
                        'message' => '请求方法不存在'
                    ]);
                    exit;
                }
            }
        }
        if(!empty($error)){
            $this->response($error, $error['code']);
            // echo json_encode($error);
        }
    }
    protected function response($data, $http_code = 200)
    {
        header('Content-Type: application/json; charset=UTF-8');
        if (empty($data)) {
            $http_code = self::HTTP_NOT_FOUND;
            $data = [
                'code' => $http_code,
                'msg' => 'error',
                'message' => '未找到对应数据'
            ];
        } elseif(!is_array($data)) {
            $http_code = self::HTTP_INTERNAL_SERVER_ERROR;
            $data = [
                'code' => $http_code,
                'msg' => 'error'
            ];
        }
        $this->set_status_header($http_code);
        echo json_encode($data);
        exit;
    }

    private function set_status_header($http_code)
    {
        http_response_code($http_code);
    }
}