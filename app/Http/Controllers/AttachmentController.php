<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/19/17
 * Time: 9:32 PM
 */

namespace App\Http\Controllers;


use App\Jobs\UploadJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Mockery\Exception;
use MyHelper;
use OSS\OssClient;

class AttachmentController extends Controller
{

    protected $attachmentRepository;

    protected $ossClient;

    function __construct()
    {

    }

    /**
     * desription 判断是否gif动画
     * @param $image_file
     * @return bool t 是 f 否
     * @internal param sting $image_file图片路径
     */
    function check_gifcartoon($image_file)
    {
        $fp = fopen($image_file, 'rb');
        $image_head = fread($fp, 1024);
        fclose($fp);
        return preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $image_head) ? false : true;
    }

    /**
     * desription 压缩图片
     * @param sting $imgsrc 图片路径
     * @param string $imgdst 压缩后保存路径
     */
    function compressed_image($imgsrc, $imgdst)
    {
        list($width, $height, $type) = getimagesize($imgsrc);
        $new_width = $width;
        $new_height = $height;

        switch ($type) {
            case 1:
                $giftype = check_gifcartoon($imgsrc);
                if ($giftype) {
                    header('Content-Type:image/gif');
                    $image_wp = imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefromgif($imgsrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    //75代表的是质量、压缩图片容量大小
                    imagejpeg($image_wp, $imgdst, 75);
                    imagedestroy($image_wp);
                }
                break;
            case 2:
                header('Content-Type:image/jpeg');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromjpeg($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //75代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 75);
                imagedestroy($image_wp);
                break;
            case 3:
                header('Content-Type:image/png');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefrompng($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //75代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 75);
                imagedestroy($image_wp);
                break;
        }
    }

// Quality is a number between 0 (best compression) and 100 (best quality)
    private function png2jpg($originalFile, $outputFile, $quality)
    {
        $image = imagecreatefrompng($originalFile);
        imagejpeg($image, $outputFile, $quality);
        imagedestroy($image);
    }

    public function uploadReceipt(Request $request)
    {
        try {
            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                throw new Exception('file not specified or not valid!');
            }
            $fileId = MyHelper::GUID();
            $fileName = $fileId . '.' . $request->file('file')->getClientOriginalExtension();
            $jpgFileName = $fileId . '.' . 'jpg';
            $filePath = storage_path() . '/uploads/' . Date('Ymd') . '/';
            $request->file('file')->move($filePath, $fileName);
//            $this->png2jpg($filePath . $fileName, $filePath . $jpgFileName, 1);
            $this->compressed_image($filePath . $fileName, $filePath . $jpgFileName);
            $imageData = file_get_contents($filePath . $jpgFileName);

            unlink($filePath . $jpgFileName);
            $base64 = base64_encode($imageData);

            return $base64;

        } catch (\Exception $e) {
            throw $e;
        }
    }

}