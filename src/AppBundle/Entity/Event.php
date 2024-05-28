<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/19/16
 * Time: 11:59 AM
 */

namespace AppBundle\Entity;

use AppBundle\Traits\File;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

use LB\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\EventRepository")
 * @ORM\Table(name="event")
 * @UniqueEntity("title", message="title is already exist")
 */
class Event
{
    // constants for privacy status
    const PUBLIC_PRIVACY = true;
    const PRIVATE_PRIVACY = false;
    const FREE_TYPE = 0;
    const BUY_TYPE = 1;
    const DONATE_TYPE = 2;
    // this trait is add file(UploadFile object) to this entity
    use File;
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"events_for_mobile", "event_for_mobile"})
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string")
     * @Groups({"events_for_mobile", "event_for_mobile"})
     */
    protected $title;

    /**
     * @var
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    protected $status = self::PUBLIC_PRIVACY;

    /**
     * @Assert\Choice(choices = {"0","1","2"})
     * @ORM\Column(name="type", type="smallint", nullable=true)
     * @Groups({"events_for_mobile", "event_for_mobile"})
     */
    protected $type;
    
    /**
     * @ORM\Column(name="meta_description", type="text", length=200)
     * @Groups({"event_for_mobile"})
     */
    protected $metaDescription;

    /**
     * @Groups({"events_for_mobile", "event_for_mobile"})
     */
    private $cachedImage;

    /**
     * @ORM\Column(name="content", type="text", nullable=true)
     * @Groups({"event_for_mobile"})
     */
    protected $content;

    /**
     * @ORM\Column(name="start", type="datetime", nullable=true)
     * @Groups({"events_for_mobile", "event_for_mobile"})
     */
    protected $start;

    /**
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({"events_for_mobile", "event_for_mobile"})
     */
    protected $end;

    /**
     * @ORM\Column(name="city", type="string", length=100, nullable=true)
     * @Assert\Expression(
     *     "this.checkCityValidate()",
     *     message="Value of city is incorrect",
     *     groups={"step2", "Profile", "personalInfo", "Base"}
     * )
     * @Assert\NotBlank(message="user.city.not_blank", groups={"step2", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max=100,
     *     minMessage="user.city.minLength",
     *     maxMessage="user.city.maxLength",
     *     groups={"Registration", "Profile"}
     * )
     * @Groups({"events_for_mobile", "event_for_mobile"})
     */
    protected $city;

    /**
     * @ORM\Column(type="float", name="lat", nullable=true)
     * @Groups({"event_for_mobile"})
     * @var
     */
    protected  $cityLat;

    /**
     * @ORM\Column(type="float", name="lng", nullable=true)
     * @Groups({"event_for_mobile"})
     * @var
     */
    protected  $cityLng;

    /**
     * @ORM\Column(type="integer", name="price", nullable=true)
     * @Groups({"event_for_mobile"})
     * @var
     */
    protected  $price;

    /**
     * @ORM\ManyToMany(targetEntity="LB\UserBundle\Entity\User", inversedBy="events")
     * @ORM\JoinTable(name="event_users",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    protected $users;

    /**
     * @return mixed
     */
    function __toString()
    {
        return ((string)$this->title) ? (string)$this->title : '';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users     = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * @return bool
     */
    public function checkCityValidate()
    {
        // check city
        $city = $this->getCity();

        // check city and return error
        if(!$city){
            return false;
        }

        // check location
        $location = $this->getLocation();
        $location = json_decode($location, true);

        // check is location array
        if(!$location || !is_array($location)){
            return false;
        }

        // check hav location address and location key
        if(!array_key_exists('address', $location) || !array_key_exists('location', $location)){
            return false;
        }

        // get address
        $address = $location['address'];

        // explode by array
        $addressArray = explode(',', $address);

        // check address
        if(count($addressArray) < 1){
            return false;
        }

        // get locations coordinates
        $cords = $location['location'];

        // check data in longitude latitude
        if(!array_key_exists('latitude', $cords) || !array_key_exists( 'longitude', $cords)){
            return false;
        }

        return true;
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
     * Set title
     *
     * @param string $title
     *
     * @return Blog
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Blog
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setStatus($status = self::PRIVATE_PRIVACY)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return LSoftAdManager
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
     * @return mixed
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     *
     * @return Blog
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param null $start
     */
    public function setStart($start = null)
    {
        if ($start) {
            $this->start = $start;
        } else {
            $this->start = new \DateTime('now');
        }
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param null $end
     */
    public function setEnd($end = null)
    {
        if ($end) {
            $this->end = $end;
        } else {
            $this->end = new \DateTime('now');
        }
    }

   public function getStringType() {
       
       switch ($this->type) {
           case 0:
             return 'Free Ticket';
           case 1:
             return "Paid Ticket";
           case 2:
               return "Donation";
           default:
               return "";
       }
   }
    
    
    /**
     * @param $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return mixed
     */
    public function getCityLng()
    {
        return $this->cityLng;
    }

    /**
     * @param mixed $cityLng
     */
    public function setCityLng($cityLng)
    {
        $this->cityLng = $cityLng;
    }

    /**
     * @return mixed
     */
    public function getCityLat()
    {
        return $this->cityLat;
    }

    /**
     * @param mixed $cityLat
     */
    public function setCityLat($cityLat)
    {
        $this->cityLat = $cityLat;
    }

    /**
     * @param $location
     */
    public function setLocation($location)
    {
        if($location){
            $location = json_decode($location);

            if(isset($location->address)){
                $this->setCity($location->address);
            }

            if(isset($location->location)){
                $coordinates = $location->location;

                if(is_string($coordinates)){
                    $coordinates = json_decode($coordinates);
                }
                $this->setCityLat($coordinates->latitude);
                $this->setCityLng($coordinates->longitude);
            }
        }
    }


    /**
     * @return null|string
     * profile_edit
     * @VirtualProperty()
     * @Groups({"profile_edit"})
     */
    public function getLocation()
    {
        // get city
        $city = $this->getCity();
        $lng = $this->getCityLng();
        $lat = $this->getCityLat();

        // check data
        if($city && $lng && $lat){
            $location = array(
                'address' => $city,
                'location' => array(
                    'latitude' => $lat,
                    'longitude' => $lng
                )
            );

            return json_encode($location);
        }
        return "''";
    }

    /**
     * This function is used to get locations data by json
     *
     * @return string
     */
    public function getLocationsJson()
    {
        // get default value for location
        $result = array();

        $lng = $this->getCityLng();
        $lat = $this->getCityLat();
        $city = $this->getCity();

        // check location
        if($lng && $lat && $city){
            // generate array
            $result[] = array(
                'id' => 1,
                'latitude' => $lat,
                'longitude' => $lng,
                'address' => $city,
            );
        }

        return json_encode($result);
    }

    /**
     * @param \LB\Userbundle\Entity\User $users
     * @return $this
     */
    public function addUser(\LB\Userbundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * @param User $users
     */
    public function removeUser(\LB\Userbundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return mixed
     */
    public function getUsersCount()
    {
        return $this->users->count();
    }


    /**
     * @return mixed
     */
    public function getCachedImage()
    {
        return $this->cachedImage;
    }

    /**
     * @param mixed $cachedImage
     */
    public function setCachedImage($cachedImage)
    {
        $this->cachedImage = $cachedImage;
    }
    
    /**
     * This function is used to return file web path
     *
     * @VirtualProperty()
     * @Groups({"events_for_mobile", "event_for_mobile"})
     * @return string
     */
    public function getDownloadLink()
    {
        $path = "bundles/app/images/no-icon.png";

        if($this->fileName){
            $path = $this->getUploadDir() . '/' . $this->getPath() . '/' . $this->fileName;
        }

        return $this->imageFromCache ? $this->imageFromCache : User::BASE_PATH . $path;
    }

    /**
     * This function is used to return file path
     *
     * @VirtualProperty()
     * @Groups({"events_for_mobile", "event_for_mobile"})
     * @return string
     */
    public function getImagePath()
    {
        $path = "bundles/app/images/no-icon.png";

        if($this->fileName){
            $path = $this->getUploadDir() . '/' . $this->getPath() . '/' . $this->fileName;
        }

        return $this->imageFromCache ? $this->imageFromCache : $path;
    }
}
