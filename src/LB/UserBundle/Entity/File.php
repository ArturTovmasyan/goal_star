<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/29/15
 * Time: 5:23 PM
 */
namespace LB\UserBundle\Entity;

use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class File
 * @package LB\UserBundle\Entity
 *
 * @ORM\Entity(repositoryClass="LB\UserBundle\Entity\Repository\FileRepository")
 * @ORM\Table(name="file")
 * @ORM\HasLifecycleCallbacks
 */
class File
{
    const IMAGE = 0;
    const VIDEO = 1;
    const MUSIC = 2;
    const REGISTER = 'register';

    // constant for app
    const MOBILE = 1;
    const FRONTEND = 2;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"file", "profile_edit"})
     */
    protected $id;

    /**
     * @ORM\Column(name="client_name", type="string", length=255, nullable=true)
     */
    protected $clientName;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     */
    protected $caption;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer", nullable=true)
     */
    protected $size;

    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @var
     *
     * @ORM\Column(name="cache_version", type="string", length=10, nullable=true)
     */
    private $cacheVersion;

    /**
     * @Assert\NotBlank(message = "file.type.not_blank", groups={"Registration", "Profile"})
     * @ORM\Column(name="type", type="smallint", nullable=false)
     * @Groups({"search"})
     */
    protected $type;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="files")
     * @ORM\JoinTable(name="user_files",
     *      joinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id_id", referencedColumnName="id")}
     *      )
     */
    protected $user;

    /**
     * @Assert\File(
     *         maxSize = "6M",
     *          mimeTypes = {
     *              "image/png",
     *              "image/jpeg",
     *              "image/jpg",
     *              "image/gif",
     *          },
     *          mimeTypesMessage = "Allow types are jpeg|png|gif",
     *          maxSizeMessage = "Allow max size is 6M",
     * )
     */
    protected  $file;

    /**
     * @var null
     */
    public $imageFromCache = null;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
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
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->getPath()
            ? null
            : $this->getUploadRootDir().'/'.$this->getPath();
    }

    /**
     * @return null|string
     * @VirtualProperty()
     * @Groups({"search"})
     * @param null $app
     * @return null|string
     */
    public function getWebPath($app = null)
    {
        if(null === $this->getPath()){
           return null;
        }

        // switch app
        switch($app){
            case self::MOBILE:
                $path = array('web_path_for_mobile' => $this->getWebPathForMobile());
                break;
            case self::FRONTEND:
                $path = array('web_path' => "/" . $this->getUploadDir() . '/' . $this->getPath(), 'type' => $this->getType());
                break;
            default:
                $path = "/" . $this->getUploadDir() . '/' . $this->getPath();
        }

        return $path;
    }

    /**
     * @return null|string
     * @VirtualProperty()
     * @Groups({"file"})
     */
    public function getWebPathForMobile()
    {
        $path = null === $this->getPath()
            ? null
            : User::BASE_PATH . $this->getUploadDir() . '/' . $this->getPath();

            return $this->imageFromCache ? $this->imageFromCache : $path;
    }

    /**
     * @return string
     */
    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    /**
     * @return string
     */
    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads';
    }


    /**
     * @return string
     */
    private function folderName()
    {
        if($this->getUser()){
            return $this->getUser()->getUId();
        }

        return self::REGISTER;

    }

    public function getWebPathFromCLI()
    {
        return '/'. $this->getUploadDir() . '/' . $this->folderName();
    }

    /**
     * @ORM\PrePersist()
     */
    public function upload()
    {

        if (null === $this->getFile()) {
            return;
        }

        $dir = $this->getUploadRootDir() . '/' . $this->folderName();
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->name = sha1(uniqid(mt_rand(), true)) . '.' . $this->file->getClientOriginalExtension();
        $this->path = $this->folderName() . '/' . $this->name;
        $this->clientName = $this->file->getClientOriginalName();
        $this->size = $this->file->getClientSize();

        $this->file->move($dir, $this->name);



        $this->file = null;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function update()
    {

        // get user
        $user = $this->getUser();

        // if user is set
        if($user && $user instanceof User){

            // get file path
            $path = $this->getPath();

            // change folder
            if(strpos($path, self::REGISTER) !== false){
                $newPath = str_replace(self::REGISTER, $this->folderName(), $path);
                $this->path = $newPath;

                $oldImage = $this->getUploadRootDir() . '/' . $path;
                $newDir = $this->getUploadRootDir() . '/' . $this->folderName();

                // check if old file is exist
                if(file_exists($oldImage)){

                    if (!file_exists($newDir)) {
                        mkdir($newDir, 0777, true);
                    }

                    $newImage = $newDir . '/' . $this->name;

                    rename($oldImage, $newImage);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getPathForUploadPath()
    {
       return  str_replace(' ', '', $this->folderName() ) . '/' . $this->name;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        $dir = $this->getUploadRootDir() . '/'. str_replace(' ', '', $this->folderName() );
        return $dir;
    }

    /**
     * @return string
     */
    public function getFolderDir()
    {
        $dir = $this->getUploadRootDir() . '/'. str_replace(' ', '', $this->folderName() );
        return $dir;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();
        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

     /**
     * Set name
     *
     * @param string $name
     *
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return File
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
     * Set type
     *
     * @param integer $type
     *
     * @return File
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set clientName
     *
     * @param string $clientName
     *
     * @return File
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * Get clientName
     *
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @return mixed
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param mixed $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user
     *
     * @param \LB\UserBundle\Entity\User $user
     *
     * @return File
     */
    private function addUser(\LB\UserBundle\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \LB\UserBundle\Entity\User $user
     */
    private function removeUser(\LB\UserBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user ? $this->user->first() : null;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(\LB\UserBundle\Entity\User $user)
    {
        if ($this->user){
            foreach($this->user as $tmpUser){
                $this->removeUser($tmpUser);
            }
        }

        $this->addUser($user);

        return $this;
    }


    /**
     * @ORM\PreRemove()
     */
    public function removeFile()
    {
        // get dir
        $dir = $this->getUploadRootDir() . '/' . $this->folderName();
        if (file_exists($dir) && is_file($dir)) {
            unlink($dir);
        }
    }

    /**
     * @return null|string
     */
    public function generateCacheVersion()
    {
        $version = null;

        // if image. return download path
        if ($this->getCacheVersion()) {
            // return path
            $version = '?v=' . $this->getCacheVersion();
        }

        return $version;
    }

    /**
     * @return mixed
     */
    public function getCacheVersion()
    {
        return $this->cacheVersion;
    }

    /**
     * @param mixed $cacheVersion
     */
    public function setCacheVersion($cacheVersion)
    {
        $this->cacheVersion = $cacheVersion;
    }
}
