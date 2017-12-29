<?php
namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use App\AppBundle\Model\SimpleActiveEntityMeta;
use App\AppBundle\Model\UploadInterface;
use App\AppBundle\Helper\Formatter;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * UploadEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class UploadEntityMeta extends SimpleActiveEntityMeta implements UploadInterface
{

    /**
     * Temporary variable
     * @var type
     */
    protected $temp;

    /**
     * @Assert\File(
     *      mimeTypes = {
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif"
     *      }
     * )
     */
    protected $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    protected $path;

    /**
     * Set path
     *
     * @param string $path
     * @return Photo
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            $formatter = new Formatter();
            $filename = str_replace($this->getFile()->getClientOriginalExtension(), '', $this->getFile()->getClientOriginalName());
            $filename = $formatter->getNameCanonical($filename);
            $filename = ($this->getId() ? $this->getId() : \uniqid('', true)) . '-' . $filename;
            $this->path = $filename . '.' . $this->getFile()->getClientOriginalExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        if (isset($this->temp)) {
            if (\file_exists($this->getUploadRootDir() . '/' . $this->temp)) {
                \unlink($this->getUploadRootDir() . '/' . $this->temp);
            }
            $this->temp = null;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();
        if ($file && file_exists($file)) {
            \unlink($file);
        }
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }
}

?>
