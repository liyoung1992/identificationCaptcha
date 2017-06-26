<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2017-06-26
 * Time: 17:44
 * 针对66的自动识别验证码
 */
class Captcha{

    /**
     * 66验证码字典表
     * */
    private $dic_66 = array(
        '011110100001100001101101101101101101101101100001100001011110' => 0,
        '001000111000001000001000001000001000001000001000001000111110' => 1,
        '011110100001100001000001000010000100001000010000100001111111' => 2,
        '011110100001100001000010001100000010000001100001100001011110' => 3,
        '000100000100001100010100100100100100111111000100000100001111' => 4,
        '111111100000100000101110110001000001000001100001100001011110' => 5,
        '001110010001100000100000101110110001100001100001100001011110' => 6,
        '111111100010100010000100000100001000001000001000001000001000' => 7,
        '011110100001100001100001011110010010100001100001100001011110' => 8,
        '011100100010100001100001100011011101000001000001100010011100' => 9,
    );
    /**
     * 验证码url
     * */
    private $url;

    /**
     * 获取验证码数字
     * */
    public function getCaptcha($platform,$url){
        if($platform == '66' || $platform == 'guoyu'){
            return $this->get66Captcha($url);
        }else{
            return "不支持该平台自动识别验证码！";
        }

    }
    /**
     * php不支持直接处理bmp格式图片，这里实现对bmp格式图片的支持
     * */
    public function imageCreateFromBMP($filename) {
        //Ouverture du fichier en mode binaire
        if (!$f1 = fopen($filename, "rb"))
            return FALSE;

        //1 : Chargement des ent�tes FICHIER
        $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
        if ($FILE['file_type'] != 19778)
            return FALSE;

        //2 : Chargement des ent�tes BMP
        $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
            '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
            '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
        $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
        if ($BMP['size_bitmap'] == 0)
            $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
        $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
        $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
        $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] = 4 - (4 * $BMP['decal']);
        if ($BMP['decal'] == 4)
            $BMP['decal'] = 0;

        //3 : Chargement des couleurs de la palette
        $PALETTE = array();
        if ($BMP['colors'] < 16777216) {
            $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
        }

        //4 : Cr�ation de l'image
        $IMG = fread($f1, $BMP['size_bitmap']);
        $VIDE = chr(0);

        $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
        $P = 0;
        $Y = $BMP['height'] - 1;
        while ($Y >= 0) {
            $X = 0;
            while ($X < $BMP['width']) {
                if ($BMP['bits_per_pixel'] == 24)
                    $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
                elseif ($BMP['bits_per_pixel'] == 16) {
                    $COLOR = unpack("n", substr($IMG, $P, 2));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 8) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, $P, 1));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 4) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 2) % 2 == 0)
                        $COLOR[1] = ($COLOR[1] >> 4);
                    else
                        $COLOR[1] = ($COLOR[1] & 0x0F);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }
                elseif ($BMP['bits_per_pixel'] == 1) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 8) % 8 == 0)
                        $COLOR[1] = $COLOR[1] >> 7;
                    elseif (($P * 8) % 8 == 1)
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                    elseif (($P * 8) % 8 == 2)
                        $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                    elseif (($P * 8) % 8 == 3)
                        $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                    elseif (($P * 8) % 8 == 4)
                        $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                    elseif (($P * 8) % 8 == 5)
                        $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                    elseif (($P * 8) % 8 == 6)
                        $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                    elseif (($P * 8) % 8 == 7)
                        $COLOR[1] = ($COLOR[1] & 0x1);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } else
                    return FALSE;
                imagesetpixel($res, $X, $Y, $COLOR[1]);
                $X++;
                $P += $BMP['bytes_per_pixel'];
            }
            $Y--;
            $P+=$BMP['decal'];
        }

        //Fermeture du fichier
        fclose($f1);
        return $res;
    }

    /**
     * 获取验证码数字
     * */
    public function get66Captcha($url){

        $res = $this->ImageCreateFromBMP($url);
        imagejpeg($res, '1.jpeg');
        $size = getimagesize($url);
        echo "<img src='1.jpeg' /><br>";

        $wid = $size['0'];
        $hid = $size['1'];
        for ($i = 0; $i < $hid; ++ $i) {
            for ($j = 0; $j < $wid; ++ $j) {
                $rgb = imagecolorat($res, $j, $i);
                $rgbArray[$i][$j] = imagecolorsforindex($res, $rgb);
            }
        }

        $str = [];

        for ($i = 0; $i < $hid; $i ++) {
            for ($j = 0; $j < $wid; $j ++) {
                if ($i >= 0 && $i < 10) {
                    if ($j > 1 && $j < 38) {
                        if ($rgbArray[$i][$j]['red'] == 211) {
                            $str[] = '0';
//                            echo '0';
                        } else {
                            $str[] = '1';
//                            echo  '1';
                        }
                    }
                }
            }
//            echo "<br>";
        }
        $temp = array_chunk($str, 36);
        $one = '';
        $two = '';
        $three = '';
        $four = '';
        for ($i = 0; $i < 10; $i ++) {
            for ($j = 0; $j < 36; $j ++) {
                if ($j < 6) {
                    $one .= $temp[$i][$j];
                }
                if ($j >= 10 && $j <= 15) {
                    $two .= $temp[$i][$j];
                }
                if ($j >= 20 && $j <= 25) {
                    $three .= $temp[$i][$j];
                }
                if ($j >= 30 && $j <= 36) {
                    $four .= $temp[$i][$j];
                }
            }
        }
        $captcha = $this->dic_66[$one] . $this->dic_66[$two] .
            $this->dic_66[$three] . $this->dic_66[$four];
        return  $captcha;
    }

}