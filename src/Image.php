<?php
declare(strict_types=1);
namespace Sholomka\Image;

use Sholomka\Image\Exceptions\ImageException;


class Image
{
    private $imageName;
    private $imageDirectory;

    /**
     * Image constructor.
     */
    public function __construct()
    {
        $this->imageDirectory = 'src' . DIRECTORY_SEPARATOR . 'img';
    }

    public function getImages(string $url, string $dir = '')
    {
        $dir = (empty($dir)) ? $this->imageDirectory : $dir;

        $host = parse_url($url, PHP_URL_HOST);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        $code = curl_exec($curl);

        curl_close($curl);

        $this->putImages($code, $host, $dir);
    }

    public function fileExists(string $path): bool
    {
        return (@fopen($path, "r") == true);
    }

    public function putImages(string $code, string $host, string $dir)
    {
        $arrayImg = [];
        $regex = '/<\s*img[^>]*src=[\"|\'](.*?)[\"|\'][^>]*\/*>/i';
        preg_match_all($regex, $code, $arrayImg);
        $imageUrl = $arrayImg[1];

        for ($i=0, $count = count($imageUrl); $i<$count; $i++) {
            $path = parse_url($imageUrl[$i], PHP_URL_PATH);
            $absolute_url = 'http://'.$host.$path;
            $name = basename($absolute_url);

            if ($this->fileExists($absolute_url) && $name != 'captcha_mod') {
                $mime = @exif_imagetype($absolute_url);
                if ($mime == IMAGETYPE_JPEG || $mime == IMAGETYPE_PNG || $mime == IMAGETYPE_GIF) {
                    copy($absolute_url, $dir.'/'.$name);
                }
            }
        }
    }
}
