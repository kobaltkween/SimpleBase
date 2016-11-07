<?php
namespace Kobalt\SimpleBase;
/* A class for models to use to upload files
 * 
 */
class FileUploder {
    /* The types of files accepted, with a default of some common types
     * @var array
     */
    protected $fileTypes = ["application/pdf", "text/plain", "application/msword",  "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "audio/mpeg", "video/mpeg", "video/mp4"];
    
    /* The size of the files accepted, in MB, with a default of 2.5
     * @var float
     */
     
    public $fileSize = 2.5;

    /* The upload directory
     * @var string
     */
    protected $uploadDir;
    
    /* The file Filter for this class
     * @var FileFilter
     */
    protected $fileFilter;
    
    /* The file name filter for this class
     * @var FNFilter
     */
    protected $fnFilter;
    
    /* Constructor sets upload directory, file size, file types, and filter for uploader.
     * @param $dir: string, the path to the directory for uploads
     * @param $fileSize: float or int, OPTIONAL, will use default of 2.5 if not set
     * @param $fileTypes: array, OPTIONAL, an array of mime types, will use default if not set
     * @throws \Exception
     */
    public function __construct($dir, $fileSize = 0, $fileTypes = null) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw Exception("Could not create directory");
            }
        }
        $this->uploadDir = $dir;
        if ($fileSize > 0) {
            $this->fileSize = $fileSize;
        }
        if ($fileTypes !== null) {
            $this->fileTypes = $fileTypes;
        }
        $this->fileFilter = new FileFilter($this->fileSize, $this->fileTypes);
        $this->fnFilter = new FNFilter();
    }
    
    /* Upload a file
     * @param $file: string, full path file name
     * @param, $oldFile: string, full path file name of the file this one is replacing
     * @returns: string, the full path file name of the uploaded file 
     * @throws: FilterExcept or \Exception
     */
    public function upload($file, $oldFile = "") {
        // Get rid of the old file, if it exists
        if (is_file($oldFile)) {
            unlink($oldFile);
        }
        $this->fileFilter->filter($file);
    
        // Use datetime to generate unique filename
        $path = pathinfo($file);
        $date = new DateTime();
        // Make The filename unique
        $base = $path["filename"] . $date->getTimestamp() . "." . $path["extension"];
       
        $newFile = $this->fnFilter->filter($this->uploadDir . $base, $this->fileFilter->ext);
        if (!copy($file, $newFile)) {
            throw Exception("Could not copy file to new location.");
        } else {
            return $this->fileFilter->display($newFile);
        } 
    }
}
