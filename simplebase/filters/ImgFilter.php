<?php
namespace Kobalt\SimpleBase;
/* A descendant of the FileFilter class
 * By default, it filters for JPG, PNG, and GIF files.  
 * As with its parent class, mime types are detected with finfo, not file extension.
 * The filter method for this class is imageFilter, which uses its inherited filter class to do 
 * It optionally generates a thumbnail
 * The setExt method sets the "ext" property of the filter, so it can be used with the filename filter
 */
class ImgFilter extends FileFilter {
    /* An array of mime types, with a default allowing JPG, PNG, and GIF files
     * @var: array
     */
    protected $mimeList = ["image/jpeg", "image/png", "image/gif"];
    
    /* The maximum width of an image in pixels
     * @var: int
     */
    protected $maxWidth;
    
    /* The maximum height of an image in pixels
     * @var: int
     */
    protected $maxHeight;
    /* The maximum width of a thumbnail in pixels
     * If the value is 0, then there's no limit on thumbnail width
     * @var: int
     */
    protected $thumbWidth = 0;
    
    /* The maximum height of a thumbnail in pixels
     * If the value is 0, then there's no limit on thumbnail height
     * @var int
     */
    protected $thumbHeight = 0;
    
    /* The php generated thumbnail, which you can write to a file
     * @var resource (true color image)
     */
    protected $thumb;
    
    /* The mimetype of the image
     * @var string
     */
    public $mimetype;
    
    /* The width of the generated thumbnail in pixels
     * @var int
     */
    public $tw;
    
    /* The height of the generated thumbnail in pixels
     * @var int
     */
    public $th;
    
    /* ImageFilter constructor, which establishes the maximum filesize, width, and height of the image
     * @param $maxSize: number (int or float, the maximum filesize of the image
     * @param $maxWidth: int, the maximum width images can be
     * @param $maxHeight: int, the maximum height images can be
     * @param $units: string, the units that the $maxSize is in, with a default of "MB"
     * @param $mimeList: array, an array of acceptable mime types, which will override the default if it's not null
     */
    function __construct($maxSize,  $maxWidth, $maxHeight, $units = "MB", $mimeList = null) {
        $this->maxSize = $maxSize;
        $this->units = $units;
        if ($mimeList !== null) {
            $this->mimeList = $mimeList;
        }
        $this->setMaxBytes();
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /* Setting the maximum width and height of the thumb, with 0 meaning no constraint
     * @param $width: int, width in pixels
     * @param $height: int, height in pixels
     * @return: void
     */
    public function setThumbProps($width, $height = 0) {
        $this->thumbWidth = $width;
        $this->thumbHeight = $height;
    }
    
    /* Filters an image file for uploading.  
     * The method isn't "filter" because it needs to call on the filter method it inherited from the FileFilter class
     * @param $filename: string, full path filename
     * @param $thumb: boolean, whether or not to generate a thumbnail.
     * @return: a named array with the width, height, and type from getimagesize
     * @except: FilterExcept
     */
    public function imageFilter($filename, $thumb = false) {
        // Assuming units of MB on files
        $this->mimetype = $this->filter($filename);
        $imgData = getimagesize($filename);
        if ($imgData) {
            if ($imgData[0] > $this->maxWidth || $imgData[1] > $this->maxHeight) {
                throw new FilterExcept("Image too large.  Please make sure it is no larger than $this->maxWidth x $this->maxHeight");
            } else {
                if ($thumb) {
                    $this->genThumb($filename, $imgData[0], $imgData[1], $this->mimetype);
                } else {
                    $this->thumb = null;
                }
                return ["width" => $imgData[0], "height" => $imgData[1], "type" => $imgData[2]];
            }
        } else {
            throw new FilterExcept("Could not retrieve image properties.");
        }
    }
    
    /* Generates a thumbnail resource that can be written to the system
     * @param $filename: string, full path filename
     * @param $imgWidth: int, the width of the original image in pixels
     * @param $imgHeight: int, the height of the original image in pixels
     * @param $mimetype: 
     * @return: void
     * @except: FilterExcept
     */
    protected function genThumb($filename, $imgWidth, $imgHeight, $mimetype) {
        switch($mimetype) {
            case "image/jpeg":
                $source = imagecreatefromjpeg($filename);
                break;
            case "image/png":
                $source = imagecreatefrompng($filename);
                break;
            case "image/gif":
                $source = imagecreatefromgif($filename);
                break;
        }
        if (!$source) {
            throw new FilterExcept("Could not create source from image.  Cannot create thumbnail.  Image was of mimetype $mimetype");
        }

        // Make thumbnail fit the width and height of the thumbnail settings
        if ($this->thumbWidth > 0 && $this->thumbWidth < $imgWidth) {
            $percentX = $this->thumbWidth / $imgWidth;
        } else {
            $percentX = 1;
        }
        if ($this->thumbHeight > 0 && $this->thumbHeight < $imgHeight) {
            $percentY = $this->thumbHeight / $imgHeight;
        } else {
            $percentY = 1;
        }
        if ($percentX <= $percentY) {
            $this->tw = round($percentX * $imgWidth);
            $this->th = round($percentX * $imgHeight);
        } else {
            $this->tw = round($percentY * $imgWidth);
            $this->th = round($percentY * $imgHeight);
        }
        $this->thumb = imagecreatetruecolor($this->tw, $this->th);
        imagecopyresampled($this->thumb, $source, 0, 0, 0, 0, $this->tw, $this->th, $imgWidth, $imgHeight);
    }
    
    /* Writes a thumbnail image file to the system
     * The thumbnail can be either a JPG or a PNG.  
     * If you want to make other types of thumbnails, add more type conditions.
     * See the PHP documentation for more image generation functions: http://php.net/manual/en/book.image.php
     * @param $path: string, the full path of the image file
     * @param $type: string, should be either "JPG" or "PNG" unless you add more conditions
     * @return: void
     */
    public function writeThumb($path, $type = "JPG") {
        $type = strtoupper($type);
        if ($type == "JPG") {
            imagejpeg($this->thumb, $path, 85);
        } else {
            imagepng($this->thumb, $path);
        }
        imagedestroy($this->thumb);
    }
}
?>
