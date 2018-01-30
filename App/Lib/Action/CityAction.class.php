<?php

class CityAction extends ApiAction
{
    public function citylist()
    {
        // 查询城市
        $sql = 'SELECT first,id,name FROM pchotel_area WHERE level=2 ORDER BY first ASC';
        $sql_hot = 'SELECT id,name FROM pchotel_area WHERE ishot=1 LIMIT 10';
        $model = M('area');
        $data = [];
        $res = $model->query($sql);
        // 按城市名首字母归类
        $res_hot = $model->query($sql_hot);
        foreach($res as $key => $value) {
            $data[$value['first']][] = $value;
            unset($res[$key]);
        }
        if($data) {
            $result = [
                'msg' => 'ok',
                'code' => 200,
                'data' => [
                    'hot' => $res_hot,
                    'list' => $data
                ]
            ];
            $this->response($result, 200);
        } else {
            $this->response(['msg' => $model], 500);
        }
    }
    /**
     * 酒店所在地区  酒店类型、品牌
     */
    public function hotelparams()
    {
        $area = $this->arealist();
        $type = $this->hoteltype();
        $label = $this->label();
        $data = [
            'msg' => 'ok',
            'code' => 200,
            'data' => [
                'area' => $area,
                'type' => $type,
                'label' => $label
            ]
        ];
        $this->response($data, $data['code']);
    }
    /**
     * 查询区域
     */
    public function arealist()
    {
        if(isset($_GET['cityid']) && !empty($_GET['cityid']) && intval($_GET['cityid'])) {
            $pid = intval($_GET['cityid']);
            // 查询城市下的区
            $sql = 'SELECT id,name FROM pchotel_area WHERE pid='.$pid;
            $model = M('area');
            $res = $model->query($sql);
            $error = [];
            if($res) {
                return $res;
            } else {
                $error = [
                    'msg' => 'error',
                    'code' => 500,
                    'message' => '未找到该城市对应的区域'
                ];
            }
        } else {
            $error = [
                'msg' => 'error',
                'code' => 400,
                'message' => '缺少对应的参数cityid'
            ];
        }
        $this->response($error, $result['code']);
    }
    /**
     * 酒店类型
     * 
    */
    private function hoteltype(){
        $sql = 'SELECT id,name FROM pchotel_xingji';
        $model = M('xingji');
        $res = $model->query($sql);
        if($res) {
            return $res;
        } else {
            $error = [
                'msg' => 'error',
                'code' => 500,
                'message' => '未找到星级数据' 
            ];
            $this->response($error, $error['code']);
        }
    }
    /**
     * 酒店品牌
     */
    private function label()
    {
        $sql = 'SELECT id,name FROM pchotel_liansuo';
        $model = M('liansuo');
        $res = $model->query($sql);
        if($res) {
            return $res;
        } else {
            $error = [
                'msg' => 'error',
                'code' => 500,
                'message' => '未找到酒店品牌数据'
            ];
            $this->response($error, $error['code']);
        }
    }

}