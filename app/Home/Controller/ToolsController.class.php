<?php

namespace Home\Controller;

class ToolsController extends ActionController
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * jquery-qrcode二维码/时间动画flash
     * +------------------------------------------------------------------
     * @functionName : deskQrcode
     * +------------------------------------------------------------------
     * @author yc
     * +------------------------------------------------------------------
     */
    public function deskQrcode()
    {
        $this->display();
    }

    /**
     * 生成二维码图片
     * +------------------------------------------------------------------
     * @functionName : qrcode
     * +------------------------------------------------------------------
     * @return array
     * +------------------------------------------------------------------
     * @author yc
     * +------------------------------------------------------------------
     */
    public function qrcode()
    {
        vendor('phpQrCode.phpqrcode'); //引入phpqrcode类
        $qrcode = new \QRcode();

        //方式一、直接输出
        $url = 'https://github.com/chanyui/jquery-qrcode';
        $errorCorrectionLevel = "L"; //纠错级别：L、M、Q、H
        $matrixPointSize = "4";      //点的大小：1到10
        $qrcode->png($url, false, $errorCorrectionLevel, $matrixPointSize); //$qrcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);

        //方式二、图片文件输出
        $data = 'http://blog.dzdress.com';
        $filename = 'qrcode/useryc1.png'; //生成的文件及路径
        $errorCorrectionLevel = 'L';      //纠错级别：L、M、Q、H
        $matrixPointSize = 4;             //点的大小：1到10
        $qrcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

        //方式三、生成中间带logo的二维码
        $value = 'http://blog.dzdress.com';
        $logo = ROOT_PATH . "/Public/css/img/logo1.png"; //中间的logo
        if (!is_dir(C('QRCODE_DIR'))) {
            if (!mkdir(C('QRCODE_DIR'), 0755)) {
                E("路径'" . C('QRCODE_DIR') . "'创建失败！");
            }
        }
        $QRpath = "qrcode/base.png";                       //自定义生成的。结束后可以删除
        $last = "qrcode/last.png";                     //最终生成的图片
        $errorCorrectionLevel = 'L';
        $matrixPointSize = 10;
        $qrcode::png($value, $QRpath, $errorCorrectionLevel, $matrixPointSize, 2);
        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QRpath));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);       //获取二维码图片宽度
            $QR_height = imagesy($QR);      //获取二维码图片高度
            $logo_width = imagesx($logo);   //获取logo图片宽度
            $logo_height = imagesy($logo);  //获取logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        }
        unlink($QRpath);
        imagepng($QR, $last); //生成最终的文件
    }

    /**
     * 导出pdf文件
     * +------------------------------------------------------------------
     * @functionName : exportPdf
     * +------------------------------------------------------------------
     * @return array
     * +------------------------------------------------------------------
     * @author yc
     * +------------------------------------------------------------------
     */
    public function exportPdf()
    {
        import('Vendor.tcpdf.tcpdf'); //引入pdf类
        //实例化
        $pdf = new \tcpdf('P', 'mm', 'A4', true, 'UTF-8', false);
        // 设置文档信息
        $pdf->SetCreator('无畏则刚！');
        $pdf->SetAuthor('yc');
        $pdf->SetTitle('胜天半子!');
        $pdf->SetSubject('创业');
        $pdf->SetKeywords('胜天, PDF, PHP');

        // 设置页眉和页脚信息
        $pdf->SetHeaderData('', 70, '', '胜天半子', array(0, 0, 255), array(0, 64, 128));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // 设置页眉和页脚字体
        $pdf->setHeaderFont(Array('stsongstdlight', '', '10'));
        $pdf->setFooterFont(Array('helvetica', '', '8'));

        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // 设置分页
        $pdf->SetAutoPageBreak(TRUE, 25);

        // set image scale factor
        $pdf->setImageScale(1.25);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        //设置字体
        $pdf->SetFont('stsongstdlight', '', 14);

        //增加一个页面
        $pdf->AddPage();

        //设置单行单元格
        $pdf->Cell('180', '10', '单元格标题', 1, 0, 'C');
        $pdf->Ln();
        $pdf->Cell('40', '10', '单行单元格', 1, 0, 'C');
        $pdf->Cell('140', '10', '我是单行单元格的值', 1, 0, 'C');

        //换行
        $pdf->Ln();

        //设置多行单元格。注意跟Cell的参数位置有些差别，Cell是用来输出单行文本的，MultiCell就能用来输出多行文本
        $pdf->MultiCell('40', '10', '多行单元格', '1', 'C', false, '0', '', '', true, '0', false, true, 20, 'M', true);
        $pdf->MultiCell('140', '10', '我是多行单元格的值', '1', 'C', false, '0', '', '', true, '0', false, true, 20, 'M', true);

        $pdf->Ln();

        $str1 = '欢迎来到tcpdf生成的PDF表格内容';
        $pdf->Write(0, $str1, '', 0, 'L', true, 0, false, false, 0);

        //输出PDF
        //$pdf->Output('t.pdf', 'I');

        //直接下载pdf
        $pdf->Output('yc.pdf', 'D');
    }

    /**
     * 配置header执行简单的导出Excel
     * +-----------------------------------------------------------
     * @functionName : exportExcel1
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function exportExcel1()
    {
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=" . str_replace(' ', '', "yc.xls"));
    }

    /**
     * 导出Excel
     * +------------------------------------------------------------------
     * @functionName : exportExcel
     * +------------------------------------------------------------------
     * @return array
     * +------------------------------------------------------------------
     * @author yc
     * +------------------------------------------------------------------
     */
    public function exportExcel()
    {
        import('Vendor.PHPExcel.PHPExcel'); //引入PHPExcel类
        $data = array(
            0 => array(
                'id' => 1001,
                'username' => '张飞',
                'password' => '123456',
                'address' => '三国时高老庄250巷101室'
            ),
            1 => array(
                'id' => 1002,
                'username' => '关羽',
                'password' => '123456',
                'address' => '三国时花果山'
            ),
            2 => array(
                'id' => 1003,
                'username' => '曹操',
                'password' => '123456',
                'address' => '延安西路2055弄3号'
            ),
            3 => array(
                'id' => 1004,
                'username' => '刘备',
                'password' => '654321',
                'address' => '愚园路188号3309室'
            )
        );

        //实例化
        $objPHPExcel = new \PHPExcel();

        //设置文档基本属性
        $objPHPExcel->getProperties()->setCreator('http://www.jb51.net')
            ->setLastModifiedBy('http://www.jb51.net')
            ->setTitle('Office 2007 XLSX Document')
            ->setSubject('Office 2007 XLSX Document')
            ->setDescription('Document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Result file');

        //Set properties  设置文件属性
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', '用户名')
            ->setCellValue('C1', '密码')
            ->setCellValue('D1', '地址');

        $i = 2;
        foreach ($data as $k => $v) {
            //set table header content  设置表头名称
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $v['id'])
                ->setCellValue('B' . $i, $v['username'])
                ->setCellValue('C' . $i, $v['password'])
                ->setCellValue('D' . $i, $v['address']);
            $i++;
        }

        //设置第一个sheet的标题
        $objPHPExcel->getActiveSheet()->setTitle('三年级2班');
        $objPHPExcel->setActiveSheetIndex(0);
        $filename = urlencode('学生信息统计表') . '_' . date('Y-m-dHis');

        //生成xlsx文件
        /*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');*/

        //生成xls文件
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('php://output');
        exit();
    }

    /**
     * 导入excel
     * +-----------------------------------------------------------
     * @functionName : import
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function import()
    {
        $file = I('post.excel');
        if (IS_POST && $file) {
            $res = readExcel($file);
            $tableHeader = array('姓名', '性别', '手机号', '固定电话', '地址');
            if ($res['row'] > 101) {
                $this->error('最多能导入100条数据');
                exit();
            }
            if ($res['data'][0] !== $tableHeader) {
                $this->error('请勿修改模板表头');
                exit();
            }
            unset($res['data'][0]);
            if ($res['data']) {
                $data = array();
                foreach ($res['data'] as $key => $value) {
                    if (!$value[0] || $value[0] === null && !$value[1] || $value[1] === null && !$value[2] || $value[2] === null) {
                        unset($res['data'][$key]);
                    } else {
                        $data[] = array(
                            'name' => $value[0],
                            'sex' => $value[1] == '男' ? 1 : 0,
                            'telephone' => $value[2],
                            'fixedphone' => $value[3],
                            'address' => $value[4]
                        );
                    }
                }
            }
            M('user_profile')->addAll($data);
            unlink($file);
            $this->success('导入成功');
        } else {
            $this->display();
        }
    }

    /**
     * 发送邮件(PHPmailer)
     * +-----------------------------------------------------------
     * @functionName : phpmailer
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function phpmailer()
    {
        $mailCofig = C('SENDMAIL');
        if (IS_POST) {
            $defaulttitle = '愿得一人心，白首不相离。';
            $body = <<<EOF
            <p align="center">
                皑如山上雪，皎若云间月。<br>
                闻君有两意，故来相决绝。<br>
                今日斗酒会，明旦沟水头。<br>
                躞蹀御沟上，沟水东西流。<br>
                凄凄复凄凄，嫁娶不须啼。<br>
                愿得一人心，白首不相离。<br>
                竹竿何袅袅，鱼尾何簁簁！<br>
                男儿重意气，何用钱刀为！</p>
EOF;
            if ($_FILES['uploadfile']['tmp_name']) {
                $config = array(
                    'maxSize' => 3145728,
                    'rootPath' => './Uploads/',
                    'savePath' => '',
                    'saveName' => array(),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg', 'xls', 'xlsx', 'pdf', 'doc', 'docx'),
                    'autoSub' => true,
                    'subName' => array('date', 'Y-m-d'),
                );
                $upload = new \Think\Upload($config);
                $info = $upload->upload();
                if (!$info) {
                    $this->error($upload->getError(), U('tools/phpmailer'));
                } else {
                    $filePath = $upload->rootPath . $info['uploadfile']['savepath'] . $info['uploadfile']['savename'];
                }
            }

            $toemail = I('post.toemail');
            $title = I('post.title') ?: $defaulttitle;
            $content = I('post.content') ? htmlspecialchars_decode(I('post.content')) : $body;
            $res = sendPHPMail($toemail, $title, $content, $mailCofig, $filePath);
            if ($filePath) {
                unlink($filePath);
            }
            if ($res) {
                $this->success('发送成功', U('tools/phpmailer'));
            } else {
                $this->error('发送失败');
            }
        } else {
            $this->display();
        }
    }

    /**
     * 发送邮件(swiftMailer)
     * +-----------------------------------------------------------
     * @functionName : swiftMailer
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function swiftMailer()
    {
        $mailCofig = C('SENDMAIL');
        if (IS_POST) {
            $defaulsubject = '愿得一人心，白首不相离。';
            $body = <<<EOF
            <p align="center">
                皑如山上雪，皎若云间月。<br>
                闻君有两意，故来相决绝。<br>
                今日斗酒会，明旦沟水头。<br>
                躞蹀御沟上，沟水东西流。<br>
                凄凄复凄凄，嫁娶不须啼。<br>
                愿得一人心，白首不相离。<br>
                竹竿何袅袅，鱼尾何簁簁！<br>
                男儿重意气，何用钱刀为！</p>
EOF;
            if ($_FILES['uploadfile']['tmp_name']) {
                $config = array(
                    'maxSize' => 3145728,
                    'rootPath' => './Uploads/',
                    'savePath' => '',
                    'saveName' => array(),
                    'exts' => array('jpg', 'gif', 'png', 'jpeg', 'xls', 'xlsx', 'pdf', 'doc', 'docx'),
                    'autoSub' => true,
                    'subName' => array('date', 'Y-m-d'),
                );
                $upload = new \Think\Upload($config);
                $info = $upload->upload();
                if (!$info) {
                    $this->error($upload->getError(), U('tools/phpmailer'));
                } else {
                    $filePath = $upload->rootPath . $info['uploadfile']['savepath'] . $info['uploadfile']['savename'];
                }
            }

            $toemail = I('post.toemail');
            $subject = I('post.title') ?: $defaulsubject;
            $content = I('post.content') ? htmlspecialchars_decode(I('post.content')) : $body;
            $res = sendSwiftMailer($toemail, $subject, $content, $mailCofig, $filePath);
            if ($filePath) {
                unlink($filePath);
            }
            if ($res) {
                $this->success('发送成功', U('tools/phpmailer'));
            } else {
                $this->error('发送失败');
            }
        } else {
            $this->display('phpmailer');
        }
    }

    /**
     * 百度富文本编辑器
     * +-----------------------------------------------------------
     * @functionName : uedit
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function ueditor()
    {
        if (IS_POST) {
            dump(I(''));
            die;
        }
        $this->display();
    }

    /**
     * 百度图表
     * +-----------------------------------------------------------
     * @functionName : echarts
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function echarts()
    {
        $this->display();
    }

    /**
     * js打印控件Lodop
     * +-----------------------------------------------------------
     * @functionName : print_a4
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function print_a4()
    {
        $this->display();
    }

    /**
     * 获取拼音的首字母字符串(支持utf-8, gb2312)默认utf-8
     * +-----------------------------------------------------------
     * @functionName : pinyinchar
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function pinyinchar()
    {
        /*vendor('PinyinChar.PinyinChar');   //引入拼音首字母类
        $pinyin = new \PinyinChar();*/       //没有引入命名空间时需要手动引入类库(文件名为 .php而不是 .class.php)
        $pinyin = new \Vendor\PinyinChar\PinyinChar();
        $str = '这个是测试';
        $res = $pinyin->getInitials($str);
        echo '输入为：' . $str . "<br/>";
        echo '输出为：' . $res;
    }

    /**
     * 获取中文的全拼、带声标的全拼、首拼字符串、首字母
     * +-----------------------------------------------------------
     * @functionName : pinyin
     * +-----------------------------------------------------------
     * @author yc
     * +-----------------------------------------------------------
     */
    public function pinyin()
    {
        vendor('Pinyin.vendor.autoload');
        $pinyin = new \Overtrue\Pinyin\Pinyin();

        $str1 = '带着希望去旅行，比到达终点更美好!';
        $name = '单某某';

        $a = $pinyin->convert($str1);
        // ["dai", "zhe", "xi", "wang", "qu", "lu", "xing", "bi", "dao", "da", "zhong", "dian", "geng", "mei", "hao"]

        $b = $pinyin->convert($str1, PINYIN_UNICODE);
        // ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]

        $c = $pinyin->convert($str1, PINYIN_ASCII);
        //["dai4","zhe","xi1","wang4","qu4","lv3","xing2","bi3","dao4","da2","zhong1","dian3","geng4","mei3","hao3"]

        $d = $pinyin->permalink($str1); //dai-zhe-xi-wang-qu-lv-xing-bi-dao-da-zhong-dian-geng-mei-hao

        $e = $pinyin->permalink($str1, '.'); //dai.zhe.xi.wang.qu.lv.xing.bi.dao.da.zhong.dian.geng.mei.hao

        $f = $pinyin->abbr($str1); //dzxwqlxbddzdgmh

        $g = $pinyin->abbr($str1, '-'); //d-z-x-w-q-l-x-b-d-d-z-d-g-m-h

        $h = $pinyin->sentence($str1);
        // dai zhe xi wang qu lv xing, bi dao da zhong dian geng mei hao!

        $i = $pinyin->sentence($str1, true);
        // dài zhe xī wàng qù lǚ xíng, bǐ dào dá zhōng diǎn gèng měi hǎo!

        $name1 = $pinyin->name($name); // ['shan', 'mou', 'mou']

        $name2 = $pinyin->name($name, PINYIN_UNICODE); // ["shàn","mǒu","mǒu"]


        echo '输入中文为：' . $str1 . "<br/><br/>";

        echo '输出拼音为(不带音调输出):' . implode($a, ' ') . "<br/><br/>";

        echo '输出拼音为(UNICODE式音调):' . implode($b, ' ') . "<br/><br/>";

        echo '输出拼音为(带数字式音调):' . implode($c, ' ') . "<br/><br/>";

        echo '带链接的拼音字符串为:' . $d . "<br/><br/>";

        echo '带链接的拼音字符串为:' . $e . "<br/><br/>";

        echo '输出首字符串为:' . $f . "<br/><br/>";

        echo '输出首字符串为:' . $g . "<br/><br/>";

        echo '保留中文字符:' . $h . "<br/><br/>";

        echo '保留中文字符带音标:' . $i . "<br/><br/>";

        echo '输出翻译姓名为：' . implode($name1, ' ') . "<br/><br/>";

        echo '输出翻译姓名为：' . implode($name2, ' ') . "<br/><br/>";
    }

    /**
     * 验证事务操作
     * +------------------------------------------------------------------
     * @functionName : routine
     * +------------------------------------------------------------------
     * @author yucheng
     * +------------------------------------------------------------------
     */
    public function routine()
    {
        $news = D('News');

        //开启事务
        $news->startTrans();

        $data1 = array(
            'title' => '新增1',
            'content' => 'add new data1',
            'status' => 0,
            'create_time' => time(),
            'update_time' => time(),
        );
        $data2 = array(
            'title' => '新增2',
            'content' => 'add new data2',
            'status' => 0,
            'create_time' => time(),
            'update_time' => time(),
        );
        $res1 = $news->insert($data1);
        $res2 = $news->insert($data2);

        if ($res1 && $res2) {
            //提交事务
            $news->commit();
        } else {
            //回滚事务
            $news->rollback();
        }

        /*try{
            $data = array(
                'title' => '新增1',
                'content' => 'add new data1',
                'status' => 0,
                'create_time' => time(),
                'update_time' => time(),
            );
            $res1 = $news->insert($data);
            $res2 = $news->insert($data);

            //提交事务
            $news->commit();
        } catch (\Exception $e) {
            //回滚事务
            $news->rollback();
        }*/
    }

    public function markdown()
    {
        vendor('HyperDown.Parser');
        if (IS_AJAX) {
            $post = I('post.content');
            $parser = new \HyperDown\Parser();
            $html = $parser->makeHtml($post);
            $this->ajaxReturn(array('code' => 0, 'data' => $html));
        } else {
            $this->display();
        }
    }

}