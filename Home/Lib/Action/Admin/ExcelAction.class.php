<?php
class ExcelAction extends CommonAction{

    private $limit = 10000;

    public function download($data, $fileName){
        $fileName = $this->_charset($fileName);
        header("Content-Type: application/vnd.ms-excel; charset=gbk");
        header("Content-Disposition: inline; filename=\"" . $fileName . ".xls\"");
        echo "<?xml version=\"1.0\" encoding=\"gbk\"?>\n
                    <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
                    xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
                    xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
                    xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
        echo "\n<Worksheet ss:Name=\"" . $fileName . "\">\n<Table>\n";
        $guard = 0;
        foreach($data as $v) {
            $guard++;
            if($guard==$this->limit) {
                ob_flush();
                flush();
                $guard = 0;
            }
            echo $this->_addRow($this->_charset($v));
        }
        echo "</Table>\n</Worksheet>\n</Workbook>";
    }

    private function _addRow($row){
        $cells = "";
        foreach ($row as $k => $v) {
            $cells .= "<Cell><Data ss:Type=\"String\">" . $v . "</Data></Cell>\n";
        }
        return "<Row>\n" . $cells . "</Row>\n";
    }

    private function _charset($data){
        if(!$data) {
            return false;
        }
        if(is_array($data)) {
            foreach($data as $k=>$v) {
                $data[$k] = $this->_charset($v);
            }
            return $data;
        }
        return iconv('utf-8', 'gbk', $data);
    }

    public function download_excel($table){
        $file_type = "vnd.ms-excel";  // excel表头固定写法
        $file_ending = "xls"; // excel表的后缀名
        header("Content-Type: application/$file_type");
        header("Content-Disposition: attachment; filename=TeamIn".time().".$file_ending"); // agentfile导出的表名
        header("Pragma: no-cache"); // 缓存
        header("Expires: 0");
        ?>
        <html xmlns:o='urn:schemas-microsoft-com:office:office'
              xmlns:x='urn:schemas-microsoft-com:office:excel'
              xmlns='http://www.w3.org/TR/REC-html40'>
        <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html>
        <head>
            <meta http-equiv='Content-type' content='text/html;charset=utf-8'/>
            <style id='Classeur1_16681_Styles'></style>
        </head>
        <body>
        <div id='Classeur1_16681' align=center x:publishsource='Excel'>
        <?php echo $table ?>
        </div></body></html>
        <?php
    }

}