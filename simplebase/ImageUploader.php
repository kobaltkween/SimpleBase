<?php
namespace Kobalt\SimpleBase;
/* A class for models to use to upload files
 * 
 */
class ImageUploder {
    
    /* The size of the files accepted, in MB, with a default of 0.75
     * @var float
     */
     
    public $size = 1.5;
    
    /* The maximum image width in pixels
     * @var int
     */
     
    public $width = 3000;
    
    /* The maximum image height in pixels
     * @var int
     */
     
    public $height = 1500;    

    /* The upload directory
     * @var string
     */
    protected $uploadDir;
    
    /* The ImgFilter for this class
     * @var ImgFilter
     */
    protected $imgFilter;
    
    
    /* Constructor sets upload directory, file size, file types, and filter for uploader.
     * @param $dir: string, the path to the directory for image uploads
     * @param $size: float or int, OPTIONAL, max file size of the images
     * @param $width: int, OPTIONAL, max width of the images
     * @param $height: int, OPTIONAL, max height of the images
     * @param $imgTypes: array, OPTIONAL, a list of allowed image mime types, 
     *                   by default PNG, JPG, and GIF are allowed
     * @throws \Exception
     */
    public function __construct($dir, $size = 0, $width = 0, $height = 0, $imgTypes = null) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw Exception("Could not create directory");
            }
        }
        $this->uploadDir = $dir;
        if ($size > 0) {
            $this->size = $size;
        }
        if ($width > 0) {
            $this->width = $width;
        }
        if ($height > 0) {
            $this->height = $height;
        }
        if ($imgTypes === null) {
            $this->imgFilter = new ImgFilter($this->size, $this->width, $this->height);
        } else {
            $this->imgFilter = new ImgFilter($this->size, $this->width, $this->height, "MB", $imgTypes);
        }
        $this->fnFilter = new FNFilter();
    }
    
    /* Upload an image
     * @param $file: string, full path file name
     * @param $thumb: boolean, OPTIONAL, whether or not to generate a thumbnail, defaults to true
     * @param, $oldFile: string, full path file name of the file this one is replacing
     * @returns: string, the full path file name of the uploaded file 
     * @throws: FilterExcept or \Exception
     */
    public function upload($file, $thumb = true, $oldFile = "") {
        // Get rid of the old file, if it exists
        if (is_file($oldFile)) {
            unlink($oldFile);
            if ($thumb) {
                $oldPath = pathinfo($oldFile);
                $oldThumb = $oldPath["dirname"] . "/". $oldPath["filename"] 
                                . "Thumb" . "." . $oldPath["extension"];
                unlink($oldThumb);
            }
        }
        $imgData = $this->imgFilter->imageFilter($file, $thumb);
        $path = pathinfo($file);
        $date = new DateTime();
        $base = $path["filename"] . $date->getTimestamp() . "." . $path["extension"];
        $newFile = $this->fnFilter->filter($this->uploadDir . "/" . $base, $this->imgFilter->ext);
        if (!copy($file, $newFile)) {
            throw Exception("Could not copy file to new location.");
        } else {
            $newImg = $this->imgFilter->display($newFile);
            $img = new stdClass;
            $img->main = $newImg;
            if ($thumb) {
                $thumbBase = pathinfo($base)["filename"] . "Thumb.jpg";  // Thumbnails always jpgs
                $newThumb = $this->fnFilter->filter($this->uploadDir . "/" . $thumbBase, "jpg");
                $this->imgFilter->writeThumb($newThumb);
                $img->thumb = $newThumb;
                $img->thumbWidth = $this->imgFilter->tw;
                $img->thumbHeight = $this->imgFilter->th;
            }
            return $img;
        }
    }
}
