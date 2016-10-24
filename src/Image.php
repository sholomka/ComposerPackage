<?php
declare(strict_types=1);
namespace Sholomka\Image;

use Sholomka\Image\Exceptions\ImageException;

/**
 * Package for the Composer, which will deal with the fact that from
 * the remote host to upload images and save them on the file system.
 *
 * Class Image
 * @package Sholomka\Image
 */
class Image
{
    /**
     * Regex for parsing pictures
     */
    const REGEX = '/<\s*img[^>]*src=[\"|\'](.*?)[\"|\'][^>]*\/*>/i';

    /**
     * The default directory for storing pictures
     *
     * @var string
     */
    private $imageDirectory;

    /**
     * Code received from the site
     *
     * @var
     */
    private $code;

    /**
     * URL Host
     *
     * @var
     */
    private $host;

    /**
     * absolute url to the image
     *
     * @var
     */
    private $absoluteUrl;

    /**
     * Http protocol
     *
     * @var
     */
    private $scheme;

    /**
     * Not allowed image names
     *
     * @var array
     */
    private $notAllowedNames = [
        'captcha_mod'
    ];

    /**
     * Image constructor.
     */
    public function __construct()
    {
        $this->imageDirectory = 'src' . DIRECTORY_SEPARATOR . 'img';
    }

    /**
     * Take the code from the site
     *
     * @param string $url - site url
     * @return string
     * @throws ImageException
     */
    public function grabbingSite(string $url): string
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        $code = curl_exec($curl);

        if (!$code) {
            throw new ImageException('Не удалось соединиться с сайтом');
        }

        curl_close($curl);

        return $code;
    }

    /**
     * Сhecking for the existence of files on a remote server
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return (@fopen($path, "r") == true);
    }

    /**
     * Save images
     */
    public function saveImages()
    {
        $imageUrl = $this->parseCode($this->code);

        for ($i=0, $count=count($imageUrl); $i<$count; $i++) {
            $this->createAbsoluteUrl($imageUrl[$i]);
            $this->createImage();
        }
    }

    /**
     * @param $imageUrl
     */
    public function createAbsoluteUrl($imageUrl)
    {
        $path = parse_url($imageUrl, PHP_URL_PATH);
        $this->absoluteUrl = $this->scheme . $this->host . $path;
    }

    /**
     * Copying images in the directory
     */
    public function createImage()
    {
        $name = basename($this->absoluteUrl);
        if ($this->fileExists($this->absoluteUrl) && !in_array($name, $this->notAllowedNames)) {
            $mime = @exif_imagetype($this->absoluteUrl);
            if ($mime == IMAGETYPE_JPEG || $mime == IMAGETYPE_PNG || $mime == IMAGETYPE_GIF) {
                if (!file_exists($this->imageDirectory)) {
                    mkdir($this->imageDirectory, 0700);
                }

                copy($this->absoluteUrl, $this->imageDirectory . DIRECTORY_SEPARATOR . $name);
            }
        }
    }

    /**
     * Parsing code from the site
     *
     * @param string $code
     * @return array
     */
    public function parseCode(string $code): array
    {
        $arrayImg = [];
        $regex = self::REGEX;
        preg_match_all($regex, $code, $arrayImg);
        $imageUrl = $arrayImg[1];

        return $imageUrl;
    }

    /**
     * To receive and save images
     *
     * @param \string[] ...$options
     */
    public function getImages(string ...$options)
    {
        list($url, $dir) = $options;
        $this->code = $this->grabbingSite($url);
        $this->imageDirectory = !$dir ? $this->imageDirectory : $dir;
        $this->host = parse_url($url, PHP_URL_HOST);
        $this->scheme = parse_url($url, PHP_URL_SCHEME) . '://';

        $this->saveImages();
    }
}
