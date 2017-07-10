<?php

namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\AppBundle\Model\UserActionsEntityMeta;

/**
 * UploadMovieEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class UploadMovieEntityMeta extends UserActionsEntityMeta implements UploadInterface {

    const CONVERT_MP4 = 'mp4';
    const CONVERT_WEBM = 'webm';

    /**
     * Generated frames per movie.
     * @static
     */
    public static $prev_frames = 12;

    /**
     *
     * @var type convert format types
     */
    protected static $convertFormatsTypes = [
        self::CONVERT_MP4 => ' -vcodec libx264 -pix_fmt yuv420p -profile:v baseline -preset slower -crf 18 -vf "scale=trunc(in_w/2)*2:trunc(in_h/2)*2" ',
        self::CONVERT_WEBM => ' -c:v libvpx -c:a libvorbis -pix_fmt yuv420p -quality good -b:v 2M -crf 5 -vf "scale=trunc(in_w/2)*2:trunc(in_h/2)*2" '
    ];

    /**
     * @Assert\File(maxSize="536870912")
     */
    protected $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    protected $path;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    protected $ext;

    /**
     * @ORM\Column(name="defaultKeyFrame", type="smallint", nullable=false, options={"default" = 1})
     */
    protected $defaultKeyFrame = 1;

    /**
     * @ORM\Column(name="duration", type="integer", nullable=false, options={"default" = 0})
     */
    protected $duration = 0;

    /**
     * @ORM\Column(name="bitrate", type="integer", nullable=false, options={"default" = 0})
     */
    protected $bitrate = 0;

    /**
     * @ORM\Column(name="width", type="integer", nullable=false, options={"default" = 0})
     */
    protected $width = 0;

    /**
     * @ORM\Column(name="height", type="integer", nullable=false, options={"default" = 0})
     */
    protected $height = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $size;

    /**
     * defaultUploadPath
     * @var type
     */
    protected $defaultUploadPath = 'uploads/movies';

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    protected $mp4 = 0;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    protected $webm = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $convertStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $convertEnd;

    /**
     * Temporary variable
     * @var type
     */
    protected $temp;

    /**
     * @example C:\www\project\src\App\MainBundle\Lib\ffmpeg_win\bin\ffmpeg.exe
     */
    protected static $ffmpegPath = null;

    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    public function getPath() {
        return $this->path;
    }

    public function setDefaultKeyFrame($defaultKeyFrame) {
        if ($defaultKeyFrame < 1 || $defaultKeyFrame > self::$prev_frames) {
            $defaultKeyFrame = 1;
        }

        $this->defaultKeyFrame = (int) $defaultKeyFrame;

        return $this;
    }

    public function getDefaultKeyFrame() {
        return $this->defaultKeyFrame;
    }

    public function setDuration($duration) {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration() {
        return $this->duration;
    }

    public function setBitrate($bitrate) {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function getBitrate() {
        return $this->bitrate;
    }

    public function setWidth($width) {
        $this->width = $width;

        return $this;
    }

    public function getWidth() {
        return $this->width;
    }

    public function setHeight($height) {
        $this->height = $height;

        return $this;
    }

    public function getHeight() {
        return $this->height;
    }

    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    public function getSize() {
        return $this->size;
    }

    public function setMp4($mp4) {
        $this->mp4 = $this->parseFormatProgress($mp4);

        return $this;
    }

    public function getMp4() {
        return $this->mp4;
    }

    public function setWebm($webm) {
        $this->webm = $this->parseFormatProgress($webm);

        return $this;
    }

    public function getWebm() {
        return $this->webm;
    }

    public function setConvertStart() {
        $this->convertStart = new \DateTime("now");

        return $this;
    }

    public function getConvertStart() {
        return $this->convertStart;
    }

    public function setConvertEnd() {
        $this->convertEnd = new \DateTime("now");

        return $this;
    }

    public function getConvertEnd() {
        return $this->convertEnd;
    }

    public function setExt($ext) {
        $this->ext = $ext;

        return $this;
    }

    public function getExt() {
        return $this->ext;
    }

    /**
     * @return boolean or convert time
     */
    public function getConvertTimeDiff() {
        if ($this->getConvertStart() !== null && $this->getConvertEnd() !== null) {
            $timeStart = date("H:i:s", $this->getConvertStart()->getTimestamp());
            $timeEnd = date("H:i:s", $this->getConvertEnd()->getTimestamp());
            return gmdate("H:i:s", round(abs(strtotime($timeEnd) - strtotime($timeStart))));
        }
        return false;
    }

    /**
     * @param type $progress 1<>100
     * @param type $precision
     * @return int
     */
    protected function parseFormatProgress($progress, $precision = 0) {
        $progress = round((int) $progress, $precision);

        if ($progress < 0) {
            return 0;
        }

        if ($progress > 100) {
            return 100;
        }

        return $progress;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null) {
        $this->file = $file;
        if (isset($this->path)) {
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
    public function getFile() {
        return $this->file;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (null !== $this->getFile()) {

            if (null !== $this->getFile()->getClientSize()) {
                $this->setSize($this->getFile()->getSize());
            }

            $ext = $this->getFile()->getClientOriginalExtension();
            if (is_null($ext) || !strlen($ext)) {
                $ext = $this->getFile()->guessExtension();
            }
            $this->setExt($ext);
            $this->path = sha1(uniqid(mt_rand(), true)) . '.' . $ext;
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->getFile()) {
            return;
        }

        if (isset($this->temp)) {
            if (file_exists($this->getUploadRootDir() . '/' . $this->temp)) {
                unlink($this->getUploadRootDir() . '/' . $this->temp);
            }
            $this->temp = null;
        }

        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        $this->removeKeyFrames();
        $this->removeByFormatType(self::CONVERT_MP4);
        $this->removeByFormatType(self::CONVERT_WEBM);
        $file = $this->getAbsoluteRealPath();
        if (\file_exists($file)) {
            unlink($file);
        }
    }

    public function getExtension() {
        return pathinfo($this->getAbsoluteRealPath(), PATHINFO_EXTENSION);
    }

    public function getAbsoluteRealPath() {
        return realpath($this->getAbsolutePath());
    }

    public function getAbsolutePath() {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath() {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    public function getUploadRootDir() {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    public function getWebPathByFormatType($type) {

        self::checkConvertFormatType($type);

        $filePath = $this->getAbsoluteRealPath();

        $fileExtension = $this->getExtension();

        $type_output = str_replace('.' . $fileExtension, 'convert.' . $type, $filePath);

        if (file_exists($type_output)) {
            $uploadDir = $this->getUploadDir();
            $type_output = str_replace('.' . $fileExtension, 'convert.' . $type, $this->getPath());
            return $uploadDir . '/' . $type_output;
        }

        return false;
    }

    public function getPathByFormatType($type) {

        self::checkConvertFormatType($type);

        return str_replace('.' . $this->getExt(), 'convert.' . $type, $this->getPath());
    }

    public static function setFFMpegPath($ffmpegPath) {
        return self::$ffmpegPath = $ffmpegPath;
    }

    public static function getFFMpegRealPath() {
        return realpath(self::getFFMpegPath());
    }

    public static function getFFMpegPath() {
        return self::$ffmpegPath;
    }

    public function getConvertProgress() {

        $filePath = $this->getAbsoluteRealPath();
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        $ft = 0;
        $allFormatsProgress = 0;

        foreach (array_keys(self::$convertFormatsTypes) as $formatType) {
            $progress = 0;
            $info_txt = str_replace('.' . $fileExtension, 'convert' . $formatType . '.txt', $filePath);
            if (file_exists($info_txt)) {
                $content = @file_get_contents($info_txt);
                if ($content) {
                    preg_match("/Duration: (.*?), start:/", $content, $matches);

                    $rawDuration = $matches[1];

                    $ar = array_reverse(explode(":", $rawDuration));
                    $duration = floatval($ar[0]);
                    if (!empty($ar[1])) {
                        $duration += intval($ar[1]) * 60;
                    }
                    if (!empty($ar[2])) {
                        $duration += intval($ar[2]) * 60 * 60;
                    }
                    preg_match_all("/time=(.*?) bitrate/", $content, $matches);

                    $rawTime = array_pop($matches);

                    if (is_array($rawTime)) {
                        $rawTime = array_pop($rawTime);
                    }

                    $ar = array_reverse(explode(":", $rawTime));
                    $time = floatval($ar[0]);
                    if (!empty($ar[1]))
                        $time += intval($ar[1]) * 60;
                    if (!empty($ar[2]))
                        $time += intval($ar[2]) * 60 * 60;

                    $progress = round(($time / $duration) * 100, 1);

                    $ft++;

                    $allFormatsProgress = $allFormatsProgress + $progress;
                }
            }
        }// end foreach

        if ($ft < 1) {
            return false;
        }

        return $this->parseFormatProgress($allFormatsProgress / count(self::$convertFormatsTypes), 1);
    }

    public function prepareMovieInfo() {
        $filePath = $this->getAbsoluteRealPath();
        $fileExtension = $this->getExt();
        $info_txt = str_replace('.' . $fileExtension, 'info.txt', $filePath);
        $info_img = str_replace('.' . $fileExtension, 'info.jpg', $filePath);

        $ffmpegBin = self::getFFMpegRealPath();
        exec($ffmpegBin . " -i " . $filePath . " -an -ss 00:00:03 -t 00:00:01 -r 1 -y -s 100x100 " . $info_img . " 1> " . $info_txt . " 2>&1");
        if (file_exists($info_txt) && file_exists($info_img)) {
            $content = file_get_contents($info_txt);
            if ($content) {
                preg_match('/(\d{2,4})x(\d{2,4})/', $content, $sizeMatches);
                $this->setWidth(isset($sizeMatches[1]) ? $sizeMatches[1] : 0);
                $this->setHeight(isset($sizeMatches[2]) ? $sizeMatches[2] : 0);

                preg_match('/bitrate: (\d+) kb\/s/', $content, $bitrateMatches);
                $this->setBitrate(isset($bitrateMatches[1]) ? ($bitrateMatches[1] * 1024) : 0);

                preg_match('/Duration: (\d{2}:\d{2}:\d{2}.\d+),/', $content, $durationMatches);
                if (isset($durationMatches[1])) {
                    $duration = explode(":", $durationMatches[1]);
                    $duration_in_seconds = ($duration[0] * 3600) + ($duration[1] * 60) + round($duration[2]);
                    $this->setDuration($duration_in_seconds);
                }
            }
            unlink($info_txt);
            unlink($info_img);
        }
    }

    public function convertMovieDuration($duration, $dateFormat = 'H:i:s') {
        return gmdate($dateFormat, $duration);
    }

    public function prepareKeyFrames() {
        $filePath = $this->getAbsoluteRealPath();
        if ($filePath) {
            $ffmpegBin = self::getFFMpegRealPath();
            $prevNameSuffix = str_replace('.' . $this->getExt(), '', $filePath);
            $prevNameTxt = $prevNameSuffix . '_prev.txt';
            for ($pf = 1; $pf <= self::$prev_frames; $pf++) {
                $frameTime = $this->convertMovieDuration(($this->getDuration() / self::$prev_frames) * $pf);
                $prevName = $prevNameSuffix . '_prev_' . $pf . '.jpg';
                if (file_exists($prevName)) {
                    unlink($prevName);
                }
                exec($ffmpegBin . ' -ss ' . $frameTime . ' -i ' . $filePath . ' ' . $prevName . " 1> " . $prevNameTxt . " 2>&1");
            }
            if (file_exists($prevNameTxt)) {
                unlink($prevNameTxt);
            }
        }
    }

    public function removeKeyFrames() {
        $filePath = $this->getAbsoluteRealPath();
        if ($filePath) {
            $prevNameSuffix = str_replace('.' . $this->getExt(), '', $filePath);
            $prevNameTxt = $prevNameSuffix . '_prev.txt';
            for ($pf = 1; $pf <= self::$prev_frames; $pf++) {
                $prevName = $prevNameSuffix . '_prev_' . $pf . '.jpg';
                if (file_exists($prevName)) {
                    unlink($prevName);
                }
            }
            if (file_exists($prevNameTxt)) {
                unlink($prevNameTxt);
            }
        }
    }

    /*
     * @param CONST $type
     */

    public static function checkConvertFormatType($type) {
        if (!in_array($type, array_keys(self::$convertFormatsTypes))) {
            throw new \InvalidArgumentException("Invalid format type in checkConvertFormatType.");
        }
        return $type;
    }

    /*
     * @param CONST $type
     */

    public function convertByFormatType($type) {

        self::checkConvertFormatType($type);

        $filePath = $this->getAbsoluteRealPath();

        $fileExtension = $this->getExtension();

        $type_txt = str_replace('.' . $fileExtension, 'convert' . $type . '.txt', $filePath);
        $type_output = str_replace('.' . $fileExtension, 'convert.' . $type, $filePath);

        if (file_exists($type_txt)) {
            unlink($type_txt);
        }

        if (file_exists($type_output)) {
            unlink($type_output);
        }

        $ffmpegBin = self::getFFMpegRealPath();

        exec($ffmpegBin . ' -i ' . $filePath . self::$convertFormatsTypes[$type] . $type_output . " 1> " . $type_txt . " 2>&1");
    }

    public function removeByFormatType($type) {

        self::checkConvertFormatType($type);

        $filePath = $this->getAbsoluteRealPath();

        $fileExtension = $this->getExtension();

        $type_txt = str_replace('.' . $fileExtension, 'convert' . $type . '.txt', $filePath);
        $type_output = str_replace('.' . $fileExtension, 'convert.' . $type, $filePath);

        if (file_exists($type_txt)) {
            unlink($type_txt);
        }

        if (file_exists($type_output)) {
            unlink($type_output);
        }
    }

    public function getKeyFrameImage() {
        $uploadDir = $this->defaultUploadPath;
        $filePath = $this->getAbsoluteRealPath();
        if ($filePath) {
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $prevNameSuffix = str_replace('.' . $fileExtension, '', $this->getPath());
            $prevName = $prevNameSuffix . '_prev_' . $this->getDefaultKeyFrame() . '.jpg';
            return $uploadDir . '/' . $prevName;
        }
        return false;
    }

    public function getKeyFramesImage() {
        $items = array();
        $uploadDir = $this->defaultUploadPath;
        $filePath = $this->getAbsoluteRealPath();
        if ($filePath) {
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $prevNameSuffix = str_replace('.' . $fileExtension, '', $this->getPath());
            for ($pf = 1; $pf <= self::$prev_frames; $pf++) {
                $prevName = $prevNameSuffix . '_prev_' . $pf . '.jpg';
                if (realpath($uploadDir . '/' . $prevName)) {
                    $items[$pf] = $uploadDir . '/' . $prevName;
                }
            }
        }
        return $items;
    }

    public function getDurationConverted() {
        return $this->convertMovieDuration($this->duration);
    }

}

?>
