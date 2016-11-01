<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class and the parent of the ImgFilter class
 * This filter takes arguments with its constructor:
 * It tests mime types using finfo rather than file extension
 * It throws an exception if the file doesn't exist, isn't the right mime type, or is too large.
 * The setExt method sets the "ext" property of the filter, so it can be used with the filename filter
 * The to support additional mime types, just add a case
 */
class FileFilter extends DataFilter {
    
    /* A maximum file size
     * @var int or float
     */
    protected $maxSize;
    
    /* A maximum filesize in bytes, example: 1 or 0.5
     * @var int
     */
    protected $maxBytes;
    
    /* An array of mime types, example: ["application/zip", "text/plain", "audio/mpeg"]
     * @var array
     */
    protected $mimeList;
    
    /* One of three abbreviations of units for the maximum filesize ("KB", "MB", "GB"), defaulting to "MB"
     * @var string
     */
    protected $units;
    
    /* A multiplier to convert $maxSize to bytes
     * @var int
     */
    protected $mp; 
    
    /* A file extension
     * @var string
     */
    public $ext;
    
    /* A full path filename
     * @var string
     */
    public $filename;
    
    /* Constructor
     * @param $maxSize: float or int, maximum size of the file 
     * @param $mimeList: an array of mimetypes
     * @param $units: string, one of the following unit abbrevations: KB, MB, or GB
     */
    function __construct($maxSize, $mimeList, $units = "MB") {
        $this->maxSize = $maxSize;
        $this->mimeList = $mimeList;
        $this->units = strtoupper($units);
        $this->setMaxBytes();
    }

    /* Sets the filter's extension property based on the mimetype from finfo
     * @param $mimeType: string, mime type from finfo
     * @return void
     */
    protected function setExt($mimeType) {
        switch ($mimeType){
            case "application/zip":
                $this->ext = "zip";
                break;
            case "application/x-rar-compressed":
                $this->ext = "rar";
                break;
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                $this->ext = "docx";
                break;
            case "application/ms-word":
                $this->ext = "doc";
                break;
            case  "image/gif":
                $this->ext = "gif";
                break;
            case "image/jpeg":
                $this->ext = "jpg";
                break;
            case "image/png":
                $this->ext = "png";
                break;
            case "audio/mpeg":
                $this->ext = "mp3";
                break;
            case "video/mp4":
                $this->ext = "mp4";
                break;
            case "video/ogg";
                $this->ext = "ogg";
                break;
            case "application/pdf":
                $this->ext = "pdf";
                break;
            case "text/plain":
                $this->ext = "txt";
                break;
            case "audio/ogg":
                $this->ext = "ogg";
                break;
        }
    }
    
    /* Sets the maximum number of bytes givern the maxSize and units properties
     * @return void
     */
    protected function setMaxBytes() {
        // Max size
        switch($this->units) {
            case "MB":
                $this->mp = pow(1024, 2);
                break;
            case "KB":
                $this->mp = 1024;
                break;
            case "GB":
                $this->mp = pow(1024, 3);
                break;
            default:
                $this->mp = pow(1024, 2);
        }
        $this->maxBytes = $this->mp * $this->maxSize;
    }
    
    /* Filters any type of input that has a limited number of acceptable values
     * @param $filename: string, a full path filename
     * @return: string, mime type of the uploaded file
     * @throws: FilterExcept
     */    
     public function filter($filename) {
        $this->filename = $this->clean($filename);
        // Make sure the file exists
        if (file_exists($this->filename)) {
            $finfo = new \finfo();
            $fileinfo = $finfo->file($this->filename, FILEINFO_MIME_TYPE);
            if (in_array($fileinfo, $this->mimeList)){
                $this->setExt($fileinfo);
                // Check filesize
                $size = filesize($this->filename);
                if ($size < $this->maxBytes) {
                    // Return the mime type if it gets through
                    return $fileinfo;
                } else {
                    $fileSize = $size / $this->mp;
                    throw new FilterExcept("File too large ($fileSize $this->units).  Please make sure it is $this->maxSize $this->units or smaller.");
                }
            } else {
                throw new FilterExcept("File of file type $fileinfo is not allowed.");
            }
        } else {
            throw new FilterExcept("Could not find file $this->filename.");
        }
    }
    
    /* Prepares filename output for display by making sure it exists
     * @param $fn: string, a full path filename
     * @return: void
     * @throws: FilterExcept
     */ 
    public function display($fn) {
        if (file_exists($fn)) {
            return $fn;
        } else {
            throw new FilterExcept("File not found.");
        }
    }
}
?>
