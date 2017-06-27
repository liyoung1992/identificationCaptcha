<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2017-06-27
 * Time: 15:20
 */

include_once ("Captcha.php");


$captcha = new Captcha();
$result = $captcha->getCaptcha('66',
    'http://www.66.cn/validcode.asp?sname=validcode_login');
echo  $result;