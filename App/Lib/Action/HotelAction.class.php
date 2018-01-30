<?php

class HotelAction extends ApiAction
{
    /**
     * 查询酒店列表
     * @param $cityid  必填
     * @param $type  酒店类型  可选
     * @param $starid  星级  可选
     * @param $keyword 搜索关键字  可选
     */  
    public function hotellist()
    {
        // 城市id
        if(isset($_GET['cityid']) && !empty($_GET['cityid']) && intval($_GET['cityid'])) {
            $cityid = $_GET['cityid'];
            // 酒店品牌
            $sql = "SELECT h.hotelid,h.hotelname,h.address,p.src FROM pchotel_member_hotel AS h 
                    LEFT JOIN pchotel_photo AS p 
                    ON h.hotelid=p.hotelid 
                    WHERE p.isdefault=1 
                    AND h.city LIKE '%{$cityid}%' ";

            if(isset($_GET['type']) && !empty($_GET['type'])) {
                $type = $_GET['type'];
                $sql .= "AND h.lspp LIKE '%{$type}%' ";
            }
            // 星级
            if(isset($_GET['starid']) && !empty($_GET['starid']) && intval($_GET['starid'])) {
                $starid = intval($_GET['starid']);
                $sql .= "AND h.xingji={$starid} ";
            }
            // 搜索关键字
            if(isset($_GET['keywords']) && !empty($_GET['keywords'])) {
                $keywords = $_GET['keywords'];
                $sql = "SELECT * FROM ($sql) a WHERE a.hotelname LIKE '%{$keywords}%' OR a.address LIKE '%{$keywords}%' ";
            }
            // 分页
            if(isset($_GET['pagesize']) && !empty($_GET['pagesize']) && intval($_GET['pagesize'])) {
                $pagesize = $_GET['pagesize'];
            } else {
                $pagesize = 10;
            }
            if(isset($_GET['pagenum']) && !empty($_GET['pagenum']) && intval($_GET['pagenum'])) {
                $pagenum = $_GET['pagenum'];
            } else {
                $pagenum = 1;
            }
            $sql .= "LIMIT $pagesize, $pagenum";
            $model = M('member_hotel');
            $res = $model->query($sql);
            if($res) {
                $result = [
                    'msg' => 'ok',
                    'code' => $sql,
                    'data' => $res
                ];
            } else {
                $result = [
                    'msg' => 'error',
                    'code' => $sql,
                    'message' => '未查询到酒店数据'
                ];
            }
        } else {
            $result = [
                'msg' => 'error',
                'code' => 404,
                'message' => '城市id必填'
            ];
        }
        $this->response($result, $result['code']);
    }

    public function detail()
    {
        if(isset($_GET['hotelid']) && !empty($_GET['hotelid']) && intval($_GET['hotelid'])) {
            $hotelid = intval($_GET['hotelid']);
            // $sql
        }
        $this->response(['msg'=>'ok'], 501);
    }
}