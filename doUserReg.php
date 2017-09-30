<?php
/**
 * Created by 小雨在线.
 * User: 飛天
 * Date: 2017/9/16 0016
 * Time: 14:52
 */
session_start();


$type = htmlspecialchars(trim($_POST["type"]));
$phone_no = htmlspecialchars($_POST["phone_no"]);
$vc = htmlspecialchars(trim($_POST["vc"]));


//include('./Requests/library/Requests.php');

include "../vendor/autoload.php";


Requests::register_autoloader();

//请求头部
$headers = array('Content-Type' => 'application/json');


//是否点击发送短信验证码按钮

if ($type === "phone_code") {

    $extra = htmlspecialchars(trim($_POST["extra"]));
    validate($vc, $phone_no, $headers, $type);

} elseif ($type === "register") {

    $psw = htmlspecialchars(trim($_POST["password"]));
    $cod = htmlspecialchars(trim($_POST["code"]));

    !empty($psw) ? $password = $psw : $password = "";
    !empty($cod) ? $code = $cod : $code = "";

    validate($vc, $phone_no, $headers, $type, $code);


}


/**
 * @param $vc
 * @param $phone_no
 * @param $headers
 * @param $type
 * @param null $mob_code
 * @return bool
 */


function validate($vc, $phone_no, $headers, $type, $mob_code = null)
{


    //是手机 1、验证是否注册，2、发短信验证码

    if (preg_match("/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/", $phone_no)) {


        //再次验证验证码是否正确
        if ($vc != "" && $vc == $_SESSION['phrase']) {
            //验证码正确执行

            //---------- 检测手机号是否注册 start---------------------------||

            $data = array('mobile' => $phone_no);

            //查询是否有注册信息
            $url = "http://api.91xiaoyu.com/rs/user/info";
            $request = Requests::post($url, $headers, json_encode($data));
            $is_reg = json_decode($request->body, true);


            //验证是否注册
            if ($is_reg["code"] === 0) {
                //已经注册
                $arr = ["error" => "手机号已经注册", "data" => [], "result" => []];
                echo json_encode($arr);
                return false;

            } else {

                //与发送验证和立即注册区别开来，不然会有重复短信

                if ($type === "phone_code") {
                    //没有注册 发送验证码
                    //请求地址 -获取手机验证码
                    $url1 = "http://api.91xiaoyu.com/rs/user/getCode";
                    $request1 = Requests::post($url1, $headers, json_encode($data));
                    $get_code = json_decode($request1->body, true);

                    //保存手机验证码进会话
                    $_SESSION["code"] = $get_code["data"];
                    //echo $get_code;  //{"code":0,"message":"","data":"554310","totalCount":0,"type":0} //获取手机验证码
                    //todo 把验证码保存进会话 --这里还需要验证

                    //{"error":false,"data":[],"result":{"success":1,"msg":""}}
                    $arr = ["error" => false, "data" => [], "result" => ["success" => 1, "msg" => ""]];
                    echo json_encode($arr);
                    return false;

                } else {

                    //当点击注册的时候，验证手机获取的验证码是否正确
                    if ($mob_code != $_SESSION["code"]) {
                        $arr = ["error" => "请输入有效手机验证码", "data" => [], "result" => []];
                        echo json_encode($arr);
                        return false;
                    } else {

                        //todo 正式注册

                        global $password;


                        $req_data = array('mobile' => $phone_no, 'password' => $password);
                        //注册接口
                        $reg_url = "http://api.91xiaoyu.com/rs/user/ckin";
                        $request = Requests::post($reg_url, $headers, json_encode($req_data));

                        $ok_reg = json_decode($request->body, true);

                        if ($ok_reg["code"] === 0) {

                            //获取用户信息接口
                            $get_info_url = "http://api.91xiaoyu.com/rs/user/info";
                            //请求参数
                            $info_data = array('mobile' => $phone_no);
                            $info_request = Requests::post($get_info_url, $headers, json_encode($info_data));
                            $user_info = json_decode($info_request->body, true);

                            //保存会话，跳转



                            !empty($user_info["data"]["nickname"]) ? $_SESSION["nickname"] = $user_info["data"]["nickname"] : $_SESSION["nickname"] = "小雨点"; //用户昵称
                            !empty($user_info["data"]["realname"]) ? $_SESSION["realname"] = $user_info["data"]["realname"] : $_SESSION["realname"] = null; //真实姓名

                            !empty($user_info["data"]["birthday"]) ? $_SESSION["birthday"] = $user_info["data"]["birthday"] : $_SESSION["birthday"] = null; //真实姓名

                            !empty($user_info["data"]["avatar"]) ? $_SESSION["avatar"] = $user_info["data"]["avatar"] : $_SESSION["avatar"] = "http://ai.91xiaoyu.com/xiaoyu_pc/images/defpic.jpg"; //头像
                            !empty($user_info["data"]["grade"]) ? $_SESSION["grade"] = $user_info["data"]["grade"] : $_SESSION["grade"] = null; //年级

                            !empty($user_info["data"]["ext"]["exp"]) ? $_SESSION["exp"] = $user_info["data"]["ext"]["exp"] : $_SESSION["exp"] = 0; //经验值
                            !empty($user_info["data"]["ext"]["learned"]) ? $_SESSION["learned"] = $user_info["data"]["ext"]["learned"] : $_SESSION["learned"] = 0; //累计学习天数
                            !empty($user_info["data"]["ext"]["gc"]) ? $_SESSION["gc"] = $user_info["data"]["ext"]["gc"] : $_SESSION["gc"] = 0; //金币数
                            !empty($user_info["data"]["ext"]["sc"]) ? $_SESSION["sc"] = $user_info["data"]["ext"]["sc"] : $_SESSION["sc"] = 0; //星数



/*
                            $_SESSION["nickname"] = $user_info["data"]["nickname"];
                            $_SESSION["realname"] = $user_info["data"]["realname"];
                            $_SESSION["avatar"] = $user_info["data"]["avatar"];
                            $_SESSION["grade"] = $user_info["data"]["grade"];
                            $_SESSION["exp"] = $user_info["data"]["ext"]["exp"];
                            $_SESSION["learned"] = $user_info["data"]["ext"]["learned"];
                            $_SESSION["gc"] = $user_info["data"]["ext"]["gc"];
                            $_SESSION["sc"] = $user_info["data"]["ext"]["sc"];

*/


                            $_SESSION["cusId"] = $user_info["data"]["id"];

                            //登陆标志位-全局使用
                            $_SESSION["is_login"] = "bl4ctruxojGx3C5Y";


                            $arr = ["error" => false, "data" => [], "result" => ["success" => 1, "msg" => "登陆成功"]];
                            echo json_encode($arr);
                            return false;

                        }


                    }
                }

            }
            //---------- 检测手机号是否注册 end---------------------------||


        } else {
            //验证码不正确执行
            $arr = ["error" => "请输入有效验证码", "data" => [], "result" => []];
            echo json_encode($arr);
            return false;

        }


    } else {
        //不是手机号
        $arr = ["error" => "请输入合法的手机号", "data" => [], "result" => []];
        echo json_encode($arr);
        return false;

    }


}
 
 
 
 
 
 
 
 
