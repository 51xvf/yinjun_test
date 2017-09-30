<?php
/**
 * Created by 小雨在线.
 * User: 飛天
 * Date: 2017/9/8 0008
 * Time: 10:51
 */


//sleep(1);
//include('./Requests/library/Requests.php');

session_start();
include "../vendor/autoload.php";


Requests::register_autoloader();


//请求头部
$headers = array('Content-Type' => 'application/json');


if ($_POST["do"] === "getchapterlist") {  //获取章节列表


//请求参数
    //subjectid 年级  majorid 科目 vol 上下册 0上册[优先] 下册1 textBook 教材版本 2【苏课版】

    /*      初中 210
            初一	211
            初二	212【有数据】
            初三	213
            中考	231
    */
//{"subMaj":{"subjectid":211,"majorid":16,"vol":0},"textBook":{"id":3}}

    $data = array('subMaj' => array('subjectid' => 212, 'majorid' => 16, 'vol' => 0), 'textBook' => array('id' => 2));

//请求地址
    $url = "http://api.91xiaoyu.com/rs/chapter/chapterList";


    $request = Requests::post($url, $headers, json_encode($data));

    $str_res = $request->body;


    echo $str_res;


} elseif ($_POST["do"] === "getsectionlist") {  //获取某个章节下的所有例题列表

    $chap_id = intval($_POST["chap_id"]);

    $data = array('userId' => $_SESSION["cusId"], 'id' => $chap_id);

    //请求地址
    $url = "http://api.91xiaoyu.com/rs/exam/cp/q/exam";

    $request = Requests::post($url, $headers, json_encode($data));

    $str_res = $request->body;

    echo $str_res;

} elseif ($_POST["do"] === "addSummary") {  //添加某个章节下的总结

    //请求总结接口地址
    $url = "http://api.91xiaoyu.com/rs/exam/addSummary";

    $yourFind = filter($_POST["yourFind"]);
    $yourStudy = filter($_POST["yourStudy"]);
    $yourInspiration = filter($_POST["yourInspiration"]);
    $examId = intval($_POST["examId"]); //例题id
    $cusId = intval($_SESSION["cusId"]); //user id

    //请求参数
    $data = array('userId' => $cusId, 'examId' => $examId, 'yourFind' => $yourFind, 'yourStudy' => $yourStudy, 'yourInspiration' => $yourInspiration);

    $request = Requests::post($url, $headers, json_encode($data));

    $str_res = $request->body;

    echo $str_res;


} elseif ($_POST["do"] === "getExamDetails") { //获取例题播放详情


    //请求例题播放详情接口地址
    $url = "http://api.91xiaoyu.com/rs/exam/q";
    $userId = 34101;
    $id = 194;

    //请求参数
    $data = array('userId' => $userId, 'id' => $id);


    $request = Requests::post($url, $headers, json_encode($data));

    $str_res = $request->body;

    echo $str_res;


}


/**
 * @param $pram
 * @return string
 */

function filter($pram)
{

    return !empty($pram) ? htmlspecialchars(trim($pram)) : null;
}

 
 