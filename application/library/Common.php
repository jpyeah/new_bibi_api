<?php

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

class Common {

    public static function encodeToUtf8($string) {
        return mb_convert_encoding ( $string, "UTF-8", mb_detect_encoding ( $string, "UTF-8, ISO-8859-1, ISO-8859-15", true ) );
    }

    /**
     * 将数组转换成json
     * @param array $arr 数组
     * @return string  json字符串
     */
    public static function arrToJson($arr) {
        $json = json_encode ( $arr );
        // $json = preg_replace('/\"(\d+)\"/', '$1', $json);
        $json = preg_replace('/\"(\d+)\.(\d+)"/', '$1.$2', $json);
        return $json;
    }

    /**
     * 将信息写入日志
     * @param string $title   标题
     * @param string $content 内容
     */
    public static function globalLogRecord($title, $content) {
        // if (! IS_LOG) {
        // return false;
        // }
        $date = Date ( 'Y-m-d', time () );

        $path = APPPATH . '/log/' . $date . '.txt';

        if (! file_exists ( $path )) {

            $fh = fopen ( $path, "w+" );
            fwrite ( $fh, '******************' . $date . ' record' . "\n" . "\n" );
            fclose ( $fh );
        }

        $time = self::microtime_format ( 'Y-m-d  H:i:m:x', self::microtime_float () );
        $str = $time . ":  " . $title . "  " . $content . "\n";
        $handle = fopen ( $path, 'a' );
        fwrite ( $handle, $str );
        fclose ( $handle );
    }

    /**
     * 返回当前时间（毫秒级）
     * @return type
     */
    public static function microtime_float() {
        list ( $usec, $sec ) = explode ( " ", microtime () );
        return (( float ) $usec + ( float ) $sec);
    }

    /**
     * 格式化时间戳，精确到毫秒，x代表毫秒
     * @param type $tag
     * @param type $time
     * @return type
     */
    public static function microtime_format($tag, $time) {
       @ list ( $usec, $sec ) = explode ( ".", $time );
        $date = date ( $tag, $usec );

        $num = strlen($sec);
        for ($i=$num; $i<4; $i++){
            $sec .= '0';
        }

        return str_replace ( 'x', $sec, $date );
    }

    public static function getBeforeTime($last_time, $now_time=0){
        if ($now_time == 0) {
            $now_time = time();
        }

        $time_period = $now_time - $last_time;

        if ($time_period < 60) {

            $before_time = abs($time_period).'秒前';

        }elseif ($time_period >=60 && $time_period<3600) {

            $before_time = floor($time_period/60).'分钟前';

        }elseif ($time_period >=3600 && $time_period<86400) {

            $before_time = floor($time_period/3600).'小时前';

        }elseif ($time_period >=86400 && $time_period<2592000) {

            $before_time = floor($time_period/86400).'天前';

        }else{
            $before_time = floor($time_period/2592000).'个月前';
        }
        return $before_time;
    }
    
    //临时改发布时间
     public static function getBeforeTimes($last_time, $now_time=0){
        if ($now_time == 0) {
            $now_time = time();
        }

        $time_period = $now_time - $last_time;

        if ($time_period < 60) {

            $before_time = abs($time_period).'秒前';

        }elseif ($time_period >=60 && $time_period<3600) {

            $before_time = floor($time_period/60).'分钟前';

        }elseif ($time_period >=3600 && $time_period<86400) {

            $before_time = floor($time_period/3600).'小时前';

        }elseif ($time_period >=86400 && $time_period<2592000) {

            $before_time = floor($time_period/86400).'天前';

        }else{
            $before_time = floor(($now_time-(floor($time_period/2592000)*2592000))/86400000).'天前';
        }
        return $before_time;
    }

    /**
     * 判断邮箱地址是否正确
     * @param string $email 邮箱地址
     * @return boolean
     */
    public static function isEmail($email){
        if(preg_match("^\\w+([-+.']\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*$^", $email, $matches)){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 判断是否为手机号
     * @param int $mobile  手机号码
     * @return boolean
     */
    public static function isMobile($mobile) {
        // /^(((d{3}))|(d{3}-))?((0d{2,3})|0d{2,3}-)?[1-9]d{6,8}$/
        if (preg_match ( "/^1[3458][0-9]{9}$/", $mobile )) {
            return true; // 验证通过
        } else {
            return false; // 手机号码格式不对
        }
    }

    /**
     * 获取输出类型
     * @param string $file  文件名
     * @return string 类型
     */
    public static function getMimeType($file) {
        // our list of mime types
        $mime_types = array (
            "pdf" => "application/pdf",
            "exe" => "application/octet-stream",
            "zip" => "application/zip",
            "docx" => "application/msword",
            "doc" => "application/msword",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "gif" => "image/gif",
            "png" => "image/png",
            "jpeg" => "image/jpg",
            "jpg" => "image/jpg",
            "mp3" => "audio/mpeg",
            "wav" => "audio/x-wav",
            "mpeg" => "video/mpeg",
            "mp4" => "video/mp4",
            "mpg" => "video/mpeg",
            "mpe" => "video/mpeg",
            "mov" => "video/quicktime",
            "avi" => "video/x-msvideo",
            "3gp" => "video/3gpp",
            "css" => "text/css",
            "jsc" => "application/javascript",
            "js" => "application/javascript",
            "php" => "text/html",
            "htm" => "text/html",
            "html" => "text/html"
        );

        $extension = strtolower ( end ( explode ( '.', $file ) ) );

        return $mime_types [$extension];
    }

    /**
     * 根据错误码输出错误提示
     * @param int $code 错误码
     * @return string  错误提示
     */
    public static function errorCodeMsg($code){
        $msgs = require APPPATH . '/configs/Error_Code_Msg.php';

        if(isset($msgs[$code])){
            $msg = $msgs[$code];
        }else{
            $msg = '未知错误';
        }

        return $msg;
    }

    /**
     * 根据经纬度，算出附近N米内的最大经度和最大纬度，以及最小纬度和最小纬度
     * @param float $latitude   纬度
     * @param float $longitude  经度
     * @param int $raidusMile   范围（单位：米）
     * @return array 最小最大经纬度
     */
    public static function getAround($latitude, $longitude, $raidusMile) {
        $PI = 3.14159265;
        $degree = (24901 * 1609) / 360.0;
        $dpmLat = 1 / $degree;
        $radiusLat = $dpmLat * $raidusMile;
        $minLat = $latitude - $radiusLat;
        $maxLat = $latitude + $radiusLat;
        $mpdLng = $degree * cos ( $latitude * ($PI / 180) );
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng * $raidusMile;
        $minLng = $longitude - $radiusLng;
        $maxLng = $longitude + $radiusLng;
        $res = array (
            'minlat' => $minLat,
            'maxlat' => $maxLat,
            'minlng' => $minLng,
            'maxlng' => $maxLng
        );
        return $res;
        // echo $minLat."#".$maxLat."@".$minLng."#".$maxLng;
    }

    /**
     * 求两个已知经纬度之间的距离,单位为米
     * @param float $lng1  经度1
     * @param float $lat1  纬度1
     * @param float $lng2  经度2
     * @param float $lat2  纬度2
     * @return float 距离（单位米）
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2) { // 根据经纬度计算距离
        // 将角度转为狐度
        $radLat1 = deg2rad ( $lat1 );
        $radLat2 = deg2rad ( $lat2 );
        $radLng1 = deg2rad ( $lng1 );
        $radLng2 = deg2rad ( $lng2 );
        $a = $radLat1 - $radLat2; // 两纬度之差,纬度<90
        $b = $radLng1 - $radLng2; // 两经度之差纬度<180
        $s = 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $radLat1 ) * cos ( $radLat2 ) * pow ( sin ( $b / 2 ), 2 ) ) ) * 6378.137 * 1000;
        return $s;
    }

    public static function sortLetter($list, $type=0){
        $arr = array();

        for($i=65; $i<91; $i++){
            $t = chr($i);
            $arr[$t] = array();
        }
        $arr['#'] = array();

        $empty_letter = $not_empty_letter = array();

        foreach ($list as &$l){
            switch ($type){
                case 1 :
                    $l = self::sortLetterSchoolData($l);
                    break;
                default :
                    break;
            }

            $initials = $l['initials'];
            unset($l['initials']);

            if($initials == ''){
                $arr['#'][] = $l;
            }else{
                $arr[$initials][] = $l;
            }
        }

        foreach ($arr as $k=>$v){
            if(empty($v)){
                $empty_letter[] = $k;
            }else{
                $not_empty_letter[] = $k;
            }
        }

        $list = $arr;
        $list['empty_letter'] = implode(',', $empty_letter);
        $list['not_empty_letter'] = implode(',', $not_empty_letter);

        return $list;
    }



    /**
     * 对数组进行排序
     * @param array  $arr   原始数组
     * @param string $keys  根据该值进行排序
     * @param string $type  升序/降序
     * @return array 排序后的数组
     */
    public static function arraySort($arr, $keys, $type = 'asc') {
        $keysvalue = $new_array = array ();
        foreach ( $arr as $k => $v ) {
            $keysvalue [$k] = $v [$keys];
        }
        if ($type == 'asc') {
            asort ( $keysvalue );
        } else {
            arsort ( $keysvalue );
        }
        reset ( $keysvalue );
        foreach ( $keysvalue as $k => $v ) {
            $new_array [] = $arr [$k];
        }
        return $new_array;
    }

    /**
     * 对数组排序，支持多级排序
     * sortArr($Array,"Key1","SORT_ASC","SORT_RETULAR","Key2"……)
     * @param  array   $ArrayData   the array to sort.
     * @param  string  $KeyName1    the first item to sort by.
     * @param  string  $SortOrder1  the order to sort by("SORT_ASC"|"SORT_DESC")
     * @param  string  $SortType1   the sort type("SORT_REGULAR"|"SORT_NUMERIC"|"SORT_STRING")
     * @return array                sorted array.
     */
    public static function sortArr($ArrayData,$KeyName1,$SortOrder1 = "SORT_ASC",$SortType1 = "SORT_REGULAR"){
        if(!is_array($ArrayData)){
            return $ArrayData;
        }

        // Get args number.
        $ArgCount = func_num_args();
        // Get keys to sort by and put them to SortRule array.
        for($I = 1; $I < $ArgCount; $I ++) {
            $Arg = func_get_arg($I);
            if (!eregi("SORT",$Arg)) {
                $KeyNameList[] = $Arg;
                $SortRule[] = '$'.$Arg;
            }else{
                $SortRule[] = $Arg;
            }
        }
        // Get the values according to the keys and put them to array.
        foreach($ArrayData AS $Key => $Info){
            foreach($KeyNameList AS $KeyName){
                ${$KeyName}[$Key] = strtolower($Info[$KeyName]);
            }
        }

        // Create the eval string and eval it.
        $EvalString = 'array_multisort('.join(",",$SortRule).',$ArrayData);';
        eval ($EvalString);
        return $ArrayData;
    }

    public static function getFirstCharter($str){
        if(empty($str)){return '';}

        $a = mb_substr($str, 0,3);
        switch ($a){
            case "衢": return 'Q'; break;
            case "暨": return 'J'; break;
            case "泸": return 'L'; break;
            case "亳": return 'B'; break;
            case "濮": return 'P'; break;
            case "漯": return 'L'; break;
            case "重": return 'C'; break;
            case "闵": return 'M'; break;
            default : break;
        }

        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return '';
    }

    public static function getSplitTableName($id, $table = 'xyh_chat_msg_'){
        $sign = $id % 10;

        if($sign < 10){
            $sign = "0".$sign;
        }

        return $table.$sign;
    }

    /**
     * 对URL发送请求
     * @param string $url    请求路径
     * @param array  $args   请求参数（POST用）
     * @param string $method 请求方式（默认GET）
     * @return string 返回的信息
     */
    public static function curlRequest($url, $args = array(), $method = '') {
        $ch = curl_init (); // 初始化curl
        curl_setopt ( $ch, CURLOPT_URL, $url ); // 设置链接
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 设置是否返回信息
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
        if ($method == 'post') {
            curl_setopt ( $ch, CURLOPT_POST, 1 ); // 设置为POST方式
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $args ); // POST数据
        }

        $response = curl_exec ( $ch ); // 接收返回信息
        curl_close ( $ch );
        return $response;
    }

    /**
     * 上传文件
     * @param array  $file         文件数据
     * @param int    $user_id      用户ID
     * @param string $upload_path  存放主目录
     * @return string 图片相对路径
     */
    public static function uploadFile($file, $user_id, $upload_path = AVATAR_PATH) {
        if ($file ["error"] != 0) {
            return false;
        }

        $type = strrchr ( $file ["name"], '.' );
        if($type == ''&&$file['type']!=''){
            if($file['type']!=''){
                $type = '.'.$file['type'];
            }else{
                $type = '.jpg';
            }
        }

        // 存放路径、文件名处理
        $dir = WWW_PATH . $upload_path;
        $date = date ( 'Ym' );

        if (! is_dir ( $dir . $user_id )) {
            mkdir ( $dir . $user_id, 0777 );
        }

        if (! is_dir ( $dir . $user_id . '/' . $date )) {
            mkdir ( $dir . $user_id . '/' . $date, 0777 );
        }

        $name = time () . rand ( 0, 99 );
        $url = '/' . $date . '/' . $name . $type;
        $destination = $dir . $user_id . $url;

        $res = rename ( $file ["tmp_name"], $destination );

        if ($res) {
            chmod ( $destination, 0755 );
            return $url;
        } else {
            return false;
        }
    }

    public static function imgMerge($imgs, $user_id, $upload_path = CIRCLE_IMG_PATH){
        $pic_list	 = array_slice($imgs, 0, 9); // 只操作前9个图片

        $bg_w	 = 300;	// 背景图片宽度
        $bg_h	 = 300;	// 背景图片高度

        $background	= imagecreatetruecolor($bg_w,$bg_h); // 背景图片
        $color	= imagecolorallocate($background, 255, 255, 255); // 为真彩色画布创建白色背景，再设置为透明
        imagefill($background, 0, 0, $color);
        imageColorTransparent($background, $color);

        $pic_count	= count($pic_list);
        $lineArr	= array();	// 需要换行的位置
        $space_x	= 3;
        $space_y	= 3;
        $line_x	 = 0;
        switch($pic_count) {
            case 1:	// 正中间
                $start_x	= 0;	// 开始位置X
                $start_y	= 0;	// 开始位置Y
                $pic_w	 = $bg_w;	// 宽度
                $pic_h	 = $bg_h;	// 高度
                break;
            case 2:	// 中间位置并排
                $start_x	= 2;
                $start_y	= intval($bg_h/4) + 3;
                $pic_w	 = intval($bg_w/2) - 5;
                $pic_h	 = intval($bg_h/2) - 5;
                $space_x	= 5;
                break;
            case 3:
                //$start_x	= 40;	// 开始位置X
                $start_y	= 5;	// 开始位置Y
                $pic_w	 = intval($bg_w/2) - 5;	// 宽度
                $pic_h	 = intval($bg_h/2) - 5;	// 高度
                $start_x = ($bg_w-$pic_w)/2;
                $lineArr	= array(2);
                $line_x	 = 4;
                break;
            case 4:
                $start_x	= 4;	// 开始位置X
                $start_y	= 5;	// 开始位置Y
                $pic_w	 = intval($bg_w/2) - 5;	// 宽度
                $pic_h	 = intval($bg_h/2) - 5;	// 高度
                $lineArr	= array(3);
                $line_x	 = 4;
                break;
            case 5:
                //$start_x	= 30;	// 开始位置X
                $start_y	= 30;	// 开始位置Y
                $pic_w	 = intval($bg_w/3) - 5;	// 宽度
                $pic_h	 = intval($bg_h/3) - 5;	// 高度
                $lineArr	= array(3);
                $line_x	 = 5;
                $start_x = ($bg_w - $pic_w*2 - $line_x)/2;
                break;
            case 6:
                $start_x	= 5;	// 开始位置X
                $start_y	= 30;	// 开始位置Y
                $pic_w	 = intval($bg_w/3) - 5;	// 宽度
                $pic_h	 = intval($bg_h/3) - 5;	// 高度
                $lineArr	= array(4);
                $line_x	 = 5;
                break;
            case 7:
                //$start_x	= 53;	// 开始位置X
                $start_y	= 5;	// 开始位置Y
                $pic_w	 = intval($bg_w/3) - 5;	// 宽度
                $pic_h	 = intval($bg_h/3) - 5;	// 高度
                $start_x = ($bg_w - $pic_w)/2;
                $lineArr	= array(2,5);
                $line_x	 = 5;
                break;
            case 8:
                //$start_x	= 30;	// 开始位置X
                $start_y	= 5;	// 开始位置Y
                $pic_w	 = intval($bg_w/3) - 5;	// 宽度
                $pic_h	 = intval($bg_h/3) - 5;	// 高度
                $lineArr	= array(3,6);
                $line_x	 = 5;
                $start_x = ($bg_w - $pic_w*2 - $line_x)/2;
                break;
            case 9:
                $start_x	= 5;	// 开始位置X
                $start_y	= 5;	// 开始位置Y
                $pic_w	 = intval($bg_w/3) - 5;	// 宽度
                $pic_h	 = intval($bg_h/3) - 5;	// 高度
                $lineArr	= array(4,7);
                $line_x	 = 5;
                break;
        }

        foreach( $pic_list as $k=>$pic_path ) {
            $kk = $k + 1;
            if ( in_array($kk, $lineArr) ) {
                $start_x = $line_x;
                $start_y = $start_y + $pic_h + $space_y;
            }
            $pathInfo	 = pathinfo($pic_path);
            switch( strtolower($pathInfo['extension']) ) {
                case 'jpg': case 'jpeg':
                $imagecreatefromjpeg = 'imagecreatefromjpeg';
                break;
                case 'png':
                    $imagecreatefromjpeg = 'imagecreatefrompng';
                    break;
                case 'gif':
                default:
                    $imagecreatefromjpeg = 'imagecreatefromstring';
                    $pic_path = file_get_contents($pic_path);
                    break;
            }
            $resource = $imagecreatefromjpeg($pic_path);
            // $start_x,$start_y copy图片在背景中的位置
            // 0,0 被copy图片的位置
            // $pic_w,$pic_h copy后的高度和宽度
            imagecopyresized($background,$resource,$start_x,$start_y,0,0,$pic_w,$pic_h,imagesx($resource),imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $start_x = $start_x + $pic_w + $space_x;
        }

        $dir = WWW_PATH . $upload_path;
        $date = date ( 'Ym' );

        if (! is_dir ( $dir . $user_id )) {
            mkdir ( $dir . $user_id, 0777 );
        }

        if (! is_dir ( $dir . $user_id . '/' . $date )) {
            mkdir ( $dir . $user_id . '/' . $date, 0777 );
        }

        $name = time () . rand ( 0, 99 ) . '_merge.jpg';
        $url = '/' . $date . '/' . $name;
        $destination = $dir . $user_id . $url;

        imagejpeg($background, $destination);

        chmod ( $destination, 0755 );
        return $url;
    }

    /**
     * 获取版本更新地址
     * @param type $version      版本号
     * @param type $phone_type   手机类型
     * @return string  地址
     */
    public static function getUpdateInfo($version, $phone_type = '1') {
        switch ($phone_type) {
            case "1" : default :
            $update_url = "https://itunes.apple.com/us/app/zhao-xiao-you/id933720119?l=zh&ls=1&mt=8";
            break;
            case "2" :
                $update_url = "http://www.211xyh.com/download/xyh.apk";
                $update_url .= "xyh.apk";
//				$update_url .= $version.'.apk';
                break;
        }

        return $update_url;
    }


    /**
     * 获取成功返回的json数据
     * @param array 返回的数组
     * @return string json字符串
     */
    public static function getSuccessRes($data){
        if(empty($data)){
            $data = new \stdClass();
        }

        $response = array (
            'status' => STATUS_SUCCESS,
            'code' => SUCCESS,
            'data' => $data
        );

        $response = self::arrToJson ( $response );
        self::globalLogRecord ( 'success_res', $response );
        return $response;
    }

    /**
     * 获取失败返回的json数据
     * @param int $errorCode    错误码
     * @param int $errorStatus  错误状态码
     * @return string json字符串
     */
    public static function getFailRes($errorCode, $errorStatus){
        $response = array (
            'status' => $errorStatus,
            'code' => $errorCode,
            'data' => array(
                'msg' => self::errorCodeMsg($errorCode)
            )
        );

        $response = self::arrToJson ( $response );
        self::globalLogRecord ( 'fail_res', $response );
        return $response;
    }

    public static  function randomkeys($length)
    {
        $key = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
    }

    public static function nick(){

        $tou=array('快乐','冷静','醉熏','潇洒','糊涂','积极','冷酷','深情','粗暴','温柔','可爱','愉快','义气','认真','威武','帅气','传统','潇洒','漂亮','自然','专一','听话','昏睡','狂野','等待','搞怪','幽默','魁梧','活泼','开心','高兴','超帅','留胡子','坦率','直率','轻松','痴情','完美','精明','无聊','有魅力','丰富','繁荣','饱满','炙热','暴躁','碧蓝','俊逸','英勇','健忘','故意','无心','土豪','朴实','兴奋','幸福','淡定','不安','阔达','孤独','独特','疯狂','时尚','落后','风趣','忧伤','大胆','爱笑','矮小','健康','合适','玩命','沉默','斯文','香蕉','苹果','鲤鱼','鳗鱼','任性','细心','粗心','大意','甜甜','酷酷','健壮','英俊','霸气','阳光','默默','大力','孝顺','忧虑','着急','紧张','善良','凶狠','害怕','重要','危机','欢喜','欣慰','满意','跳跃','诚心','称心','如意','怡然','娇气','无奈','无语','激动','愤怒','美好','感动','激情','激昂','震动','虚拟','超级','寒冷','精明','明理','犹豫','忧郁','寂寞','奋斗','勤奋','现代','过时','稳重','热情','含蓄','开放','无辜','多情','纯真','拉长','热心','从容','体贴','风中','曾经','追寻','儒雅','优雅','开朗','外向','内向','清爽','文艺','长情','平常','单身','伶俐','高大','懦弱','柔弱','爱笑','乐观','耍酷','酷炫','神勇','年轻','唠叨','瘦瘦','无情','包容','顺心','畅快','舒适','靓丽','负责','背后','简单','谦让','彩色','缥缈','欢呼','生动','复杂','慈祥','仁爱','魔幻','虚幻','淡然','受伤','雪白','高高','糟糕','顺利','闪闪','羞涩','缓慢','迅速','优秀','聪明','含糊','俏皮','淡淡','坚强','平淡','欣喜','能干','灵巧','友好','机智','机灵','正直','谨慎','俭朴','殷勤','虚心','辛勤','自觉','无私','无限','踏实','老实','现实','可靠','务实','拼搏','个性','粗犷','活力','成就','勤劳','单纯','落寞','朴素','悲凉','忧心','洁净','清秀','自由','小巧','单薄','贪玩','刻苦','干净','壮观','和谐','文静','调皮','害羞','安详','自信','端庄','坚定','美满','舒心','温暖','专注','勤恳','美丽','腼腆','优美','甜美','甜蜜','整齐','动人','典雅','尊敬','舒服','妩媚','秀丽','喜悦','甜美','彪壮','强健','大方','俊秀','聪慧','迷人','陶醉','悦耳','动听','明亮','结实','魁梧','标致','清脆','敏感','光亮','大气','老迟到','知性','冷傲','呆萌','野性','隐形','笑点低','微笑','笨笨','难过','沉静','火星上','失眠','安静','纯情','要减肥','迷路','烂漫','哭泣','贤惠','苗条','温婉','发嗲','会撒娇','贪玩','执着','眯眯眼','花痴','想人陪','眼睛大','高贵','傲娇','心灵美','爱撒娇','细腻','天真','怕黑','感性','飘逸','怕孤独','忐忑','高挑','傻傻','冷艳','爱听歌','还单身','怕孤单','懵懂');
        $do = array("的","爱","","与","给","扯","和","用","方","打","就","迎","向","踢","笑","闻","有","等于","保卫","演变");
        $wei=array('嚓茶','凉面','便当','毛豆','花生','可乐','灯泡','哈密瓜','野狼','背包','眼神','缘分','雪碧','人生','牛排','蚂蚁','飞鸟','灰狼','斑马','汉堡','悟空','巨人','绿茶','自行车','保温杯','大碗','墨镜','魔镜','煎饼','月饼','月亮','星星','芝麻','啤酒','玫瑰','大叔','小伙','哈密瓜，数据线','太阳','树叶','芹菜','黄蜂','蜜粉','蜜蜂','信封','西装','外套','裙子','大象','猫咪','母鸡','路灯','蓝天','白云','星月','彩虹','微笑','摩托','板栗','高山','大地','大树','电灯胆','砖头','楼房','水池','鸡翅','蜻蜓','红牛','咖啡','机器猫','枕头','大船','诺言','钢笔','刺猬','天空','飞机','大炮','冬天','洋葱','春天','夏天','秋天','冬日','航空','毛衣','豌豆','黑米','玉米','眼睛','老鼠','白羊','帅哥','美女','季节','鲜花','服饰','裙子','白开水','秀发','大山','火车','汽车','歌曲','舞蹈','老师','导师','方盒','大米','麦片','水杯','水壶','手套','鞋子','自行车','鼠标','手机','电脑','书本','奇迹','身影','香烟','夕阳','台灯','宝贝','未来','皮带','钥匙','心锁','故事','花瓣','滑板','画笔','画板','学姐','店员','电源','饼干','宝马','过客','大白','时光','石头','钻石','河马','犀牛','西牛','绿草','抽屉','柜子','往事','寒风','路人','橘子','耳机','鸵鸟','朋友','苗条','铅笔','钢笔','硬币','热狗','大侠','御姐','萝莉','毛巾','期待','盼望','白昼','黑夜','大门','黑裤','钢铁侠','哑铃','板凳','枫叶','荷花','乌龟','仙人掌','衬衫','大神','草丛','早晨','心情','茉莉','流沙','蜗牛','战斗机','冥王星','猎豹','棒球','篮球','乐曲','电话','网络','世界','中心','鱼','鸡','狗','老虎','鸭子','雨','羽毛','翅膀','外套','火','丝袜','书包','钢笔','冷风','八宝粥','烤鸡','大雁','音响','招牌','胡萝卜','冰棍','帽子','菠萝','蛋挞','香水','泥猴桃','吐司','溪流','黄豆','樱桃','小鸽子','小蝴蝶','爆米花','花卷','小鸭子','小海豚','日记本','小熊猫','小懒猪','小懒虫','荔枝','镜子','曲奇','金针菇','小松鼠','小虾米','酒窝','紫菜','金鱼','柚子','果汁','百褶裙','项链','帆布鞋','火龙果','奇异果','煎蛋','唇彩','小土豆','高跟鞋','戒指','雪糕','睫毛','铃铛','手链','香氛','红酒','月光','酸奶','银耳汤','咖啡豆','小蜜蜂','小蚂蚁','蜡烛','棉花糖','向日葵','水蜜桃','小蝴蝶','小刺猬','小丸子','指甲油','康乃馨','糖豆','薯片','口红','超短裙','乌冬面','冰淇淋','棒棒糖','长颈鹿','豆芽','发箍','发卡','发夹','发带','铃铛','小馒头','小笼包','小甜瓜','冬瓜','香菇','小兔子','含羞草','短靴','睫毛膏','小蘑菇','跳跳糖','小白菜','草莓','柠檬','月饼','百合','纸鹤','小天鹅','云朵','芒果','面包','海燕','小猫咪','龙猫','唇膏','鞋垫','羊','黑猫','白猫','万宝路','金毛','山水','音响','尊云','西安');
        $tou_num=rand(0,331);
        $do_num=rand(0,19);
        $wei_num=rand(0,327);
        $type = rand(0,1);
        if($type==0){
            $username=$tou[$tou_num].$do[$do_num].$wei[$wei_num];
        }else{
            $username=$wei[$wei_num].$tou[$tou_num];
        }
        return $username;
    }

    /**
     *  计算.星座
     *
     * @param int $month 月份
     * @param int $day 日期
     * @return str
     */
    public static function get_constellation($month, $day){
        $signs = array(
            array('20'=>'水瓶座'), array('19'=>'双鱼座'),
            array('21'=>'白羊座'), array('20'=>'金牛座'),
            array('21'=>'双子座'), array('22'=>'巨蟹座'),
            array('23'=>'狮子座'), array('23'=>'处女座'),
            array('23'=>'天秤座'), array('24'=>'天蝎座'),
            array('22'=>'射手座'), array('22'=>'摩羯座')
        );
        $key = (int)$month - 1;
        list($startSign, $signName) = each($signs[$key]);
        if( $day < $startSign ){
            $key = $month - 2 < 0 ? $month = 11 : $month -= 2;
            list($startSign, $signName) = each($signs[$key]);
        }
        return $signName;
    }

    public static function birthday($mydate){
        $birth=$mydate;
        list($by,$bm,$bd)=explode('-',$birth);
        $cm=date('n');
        $cd=date('j');
        $age=date('Y')-$by-1;
        if ($cm>$bm || $cm==$bm && $cd>$bd) $age++;
        return $age;
        //echo "生日:$birthn年龄:$agen";
    }


    /**
     * 发送模板短信
     *
     * @param
     * to 手机号码集合,用英文逗号分开
     * @param
     * datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
     * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
     */
    public static function sendSMS($to, $datas, $tempId) {

        $sms_setting = Yaf_Registry::get('config')->sms;

        $rest = new Rest($sms_setting->serverIP, $sms_setting->serverPort, $sms_setting->softVersion);
        $rest->setAccount($sms_setting->accountSid, $sms_setting->accountToken);
        $rest->setAppId($sms_setting->appId);

        Common::globalLogRecord("Sending TemplateSMS to $to ...", "sms");

        $result = $rest->sendTemplateSMS($to, $datas, $tempId); // 发送模板短信
        if ($result == NULL) {
            Common::globalLogRecord("Sending TemplateSMS failed, result error!", "sms");
            return false;
        }

        if ($result->statusCode != 0) {
            Common::globalLogRecord("Sending TemplateSMS failed, error code is " . $result->statusCode . ", error msg is " . $result->statusMsg . "!", "sms");
            return false;
        } else {
            Common::globalLogRecord("Sending TemplateSMS success, the dateCreated is " . $result->TemplateSMS->dateCreated . ", the smsMessageSid is " . $result->TemplateSMS->smsMessageSid . "!", "sms");
            return true;
        }
    }

    //七牛获取文件类型
     public static function checktype($key){
  
          $accessKey = QI_NIU_AK;
          $secretKey = QI_NIU_SK;
          // 构建鉴权对象
          $auth = new Auth($accessKey, $secretKey);
          //初始化BucketManager
          $bucketMgr = new BucketManager($auth);
          //你要测试的空间， 并且这个key在你空间中存在
          $bucket = 'bibi';
          $key =$key;
          //获取文件的状态信息
          list($ret, $err) = $bucketMgr->stat($bucket, $key);
          $str=explode("/",$ret['mimeType']);
          return $str[1];
        
    }

   //同步推正
    public static function shiwan($device_identifier){
         
          $sql = 'SELECT `device_id` FROM `bibi_device_info` WHERE `device_identifier` = "'.$device_identifier.'"';
          $pdo = new PdoDb();
          $id= $pdo->query($sql);
          if($id){
            return $id[0]['device_id'];
          }
    }



}
