<?php

namespace AppBundle\EventListener;

use AppBundle\Util\ImagickPhotoResizer;
use AppBundle\Entity\Photo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PhotoListener
{
	private static $MAX_IMAGE_WIDTH = 1920;
	private static $MAX_IMAGE_HEIGHT = 1080;
	private static $THUMBNAIL_SIDE = 300;
	
    private $uploadRootDir;

    /**
     * PhotoListener constructor.
     *
     * @param string $photoUploadDir The directory inside which the photo files will be saved.
     */
    public function __construct($photoUploadDir)
    {
        if (substr($photoUploadDir, -1) != '/') {
            $photoUploadDir = $photoUploadDir . '/';
        }

        $this->uploadRootDir = $photoUploadDir;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function saveExtension(Photo $photo, LifecycleEventArgs $event)
    {
        if (null !== $photo->getFile()) {
            $photo->setExtension($photo->getFile()->guessExtension());
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function saveFile(Photo $photo, LifecycleEventArgs $event)
    {
        if (null === $photo->getFile()) {
            return;
        }

        // Save and resize photo
        $photo->getFile()->move($this->uploadRootDir, $photo->getFilename());

        $resizer = new ImagickPhotoResizer($this->uploadRootDir . $photo->getFilename());

        $resizer->resize($this->uploadRootDir . $photo->getResizedFilename(),
        		static::$MAX_IMAGE_WIDTH, static::$MAX_IMAGE_HEIGHT);
        $resizer->resizeToSquare($this->uploadRootDir . $photo->getThumbFilename(),
        		static::$THUMBNAIL_SIDE);

        $photo->setFile(null);
        
        // Add photo to ablum archive        
        $photoFilename = $this->uploadRootDir . $photo->getFilename();
        $zip = $this->openAlbumArchive($photo);
        
        if ($zip->addFile($photoFilename, $photo->getFilename()) !== true) {
        	throw new \Exception('Cannot add file ' . $photoFilename .
        			' to album archive');
        }
        
        $zip->close();
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove(Photo $photo, LifecycleEventArgs $event)
    {
        $photo->storeTempFileNames();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeFiles(Photo $photo, LifecycleEventArgs $event)
    {
    	// Remove photo files
        $fileNames = $photo->getTempFileNames();
        if (is_array($fileNames)) {
            foreach ($fileNames as $name) {
                $this->safeUnlink($this->uploadRootDir . $name);
            }
        }
        
        // Remove file from album archive
        $photoFilename = $photo->getTempFileNames()[0];
        
		$zip = $this->openAlbumArchive($photo);
		$zip->deleteName($photoFilename);
		$zip->close();
    }
    
    private function openAlbumArchive(Photo $photo)
    {
    	$zip = new \ZipArchive();
    	$filename = $this->uploadRootDir . $photo->getAlbum()->getArchiveName();
    	
    	if ($zip->open($filename, \ZipArchive::CREATE) !== true) {
    		throw new \Exception('Cannot open or create ZIP archive for file ' . $filename);
    	}
    	
    	return $zip;
    }

    private function safeUnlink($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
