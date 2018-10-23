<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/7/20
 * Time: 14:12
 */
namespace Utils\Controller;

use Think\Controller;

class SearchController extends Controller
{

    /**
     * 使用 xunsearch 获取搜索建议
     */
    public function getSuggestQuery()
    {
        $keyword = I('get.keyword');
        if (!$keyword) {
            $res = ['code' => 400, 'msg' => '请输入搜索词！'];
        } else {
            import('vendor/hightman/xunsearch/lib/XS', ROOT_PATH);
            $xs = new \XS('shop');
            // 获取搜索对象
            $search = $xs->getSearch();
            $search->setCharset('UTF-8');
            $result = $search->getExpandedQuery($keyword);
            $res = ['code' => 200, 'msg' => $result];
        }
        $this->ajaxReturn($res);
    }
}