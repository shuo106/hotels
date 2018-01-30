<?php

class IndexAction extends ApiAction
{
    public function index()
    {
        $this->response(['msg'=>'error', 'message'=>'请求地址不存在'], 404);
    }
}