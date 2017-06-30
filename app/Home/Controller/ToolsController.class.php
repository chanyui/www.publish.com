<?php

namespace Home\Controller;

use Think\Controller;

import('Vendor.tcpdf.tcpdf'); //引入pdf类
import('Vendor.PHPExcel.PHPExcel'); //引入PHPExcel类

class ToolsController extends Controller
{
    /**
     * 导出pdf文件
     * +------------------------------------------------------------------
     * @functionName : exportPdf
     * +------------------------------------------------------------------
     * @return array
     * +------------------------------------------------------------------
     * @author yucheng
     * +------------------------------------------------------------------
     */
    public function exportPdf()
    {
        //实例化
        $pdf = new \tcpdf('P', 'mm', 'A4', true, 'UTF-8', false);
        // 设置文档信息
        $pdf->SetCreator('无畏则刚！');
        $pdf->SetAuthor('yc');
        $pdf->SetTitle('胜天半子!');
        $pdf->SetSubject('创业');
        $pdf->SetKeywords('胜天, PDF, PHP');

        // 设置页眉和页脚信息
        $pdf->SetHeaderData('', 70, '', '胜天半子欢迎您', array(0, 64, 255), array(0, 64, 128));
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
     * 导出Excel
     * +------------------------------------------------------------------
     * @functionName : exportExcel
     * +------------------------------------------------------------------
     * @return array
     * +------------------------------------------------------------------
     * @author yucheng
     * +------------------------------------------------------------------
     */
    public function exportExcel()
    {
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
}