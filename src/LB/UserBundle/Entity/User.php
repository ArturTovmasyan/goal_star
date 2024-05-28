<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 10/22/15
 * Time: 11:56 AM
 */

namespace LB\UserBundle\Entity;

use AppBundle\Traits\Location;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;
use LB\MessageBundle\Model\MessageUserInterface;
use LB\NotificationBundle\Entity\Notification;
use LB\PaymentBundle\Entity\Subscriber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="LB\UserBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="fos_user", indexes={
 *          @ORM\Index(name="search", columns={"birthday", "I_am", "city"}),
 *          @ORM\Index(name="search_location", columns={"birthday", "I_am", "lng", "lat"}),
 *          @ORM\Index(name="message", columns={"id", "first_name", "last_name"}),
 *          @ORM\Index(name="deactivate", columns={"deactivate"}),
 * })
 * @Assert\Callback(methods={"validate"}, groups={"Registration", "step2", "step3", "myInterest", "basicInfo", "Admin", "Default", "ResetPassword", "ChangePassword"})
 * @UniqueEntity("email", message="Email is already exist", groups={"accountType", "Admin"})
 * @UniqueEntity("username", message="Username is already exist", groups={"accountType", "Admin"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\EntityListeners({"LB\UserBundle\Listener\UserListener"})
 */
class User extends BaseUser implements MessageUserInterface
{
    // constant for visibility
    const ONLY_ME = 0;
    const MY_FRIENDS = 1;
    const ALL_MEMBERS = 2;
    const EVERYONE = 3;

    // constant for gender
    const MAN = 4;
    const WOMAN = 5;
    const BISEXUAL = 6;

    // constant for height
    const FEET = 1;
    const INCHES = 2;
    static $HEIGHT_CHOICE = array(User::FEET => 'Feet', User::INCHES => 'Inches');

    const SESSION_NAME = 'lb_user';
//    const BASE_PATH = "http://luvbyrd.laravelsoft.com/";
//    const BASE_PATH = "http://loc.luvbyrd.com/";
    const BASE_PATH = "https://luvbyrd.com/";

    // constant for steps
    const FIRST = 1;
    const SECOND = 2;
    const THIRD = 3;
    const COMPLETE = 4;

    static $GENDER_CHOICE_FOR_I_AM = array(User::MAN => 'Man', User::WOMAN => 'Woman');
    static $GENDER_CHOICE = array(User::MAN => 'Man', User::WOMAN => 'Woman', User::BISEXUAL => 'Bisexual');

    /**
     * Format State
     *
     * Note: Does not format addresses, only states. $input should be as exact as possible, problems
     * will probably arise in long strings, example 'I live in Kentukcy' will produce Indiana.
     *
     * @example echo myClass::format_state( 'Florida', 'abbr'); // FL
     * @example echo myClass::format_state( 'we\'re from georgia' ) // Georgia
     *
     * @param  string $input  Input to be formatted
     * @param  string $format Accepts 'abbr' to output abbreviated state, default full state name.
     * @return string          Formatted state on success,
     */
    static function format_state( $input, $format = '' ) {
        if( ! $input || empty( $input ) )
            return;

        $states = array (
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District Of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        foreach( $states as $abbr => $name ) {
            if ( preg_match( "/\b($name)\b/", ucwords( strtolower( $input ) ), $match ) )  {
                if( 'abbr' == $format ){
                    return $abbr;
                }
                else return $name;
            }
            elseif( preg_match("/\b($abbr)\b/", strtoupper( $input ), $match) ) {
                if( 'abbr' == $format ){
                    return $abbr;
                }
                else return $name;
            }
        }
        return;
    }
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"user_for_mobile", "user_for_mobile_status", "for_mobile", "user", "relatedUser", "user_by_status", "note", "search", "lb_group", "lb_group_mobile", "lb_group_single_mobile", "slider", "message"})
     */
    protected $id;

    /**
     * @ORM\Column(name="u_id", type="string", length=9, unique=true)
     * @Groups({"user_for_mobile", "user_for_mobile_status", "for_mobile", "user", "relatedUser", "user_by_status", "note", "search", "lb_group", "lb_group_mobile", "lb_group_single_mobile", "slider", "message"})
     */
    protected $uId;

    /**
     * @ORM\Column(name="first_name", type="string", length=20, nullable=true)
     * @Assert\NotBlank(message="user.firstName.not_blank", groups={"Registration", "Profile", "accountType", "Base", "Admin"})
     * @Assert\Length(
     *     min=2,
     *     max=20,
     *     minMessage="user.firstName.minLength",
     *     maxMessage="user.firstName.maxLength",
     *     groups={"Registration", "Profile", "accountType", "Admin"}
     * )
     * @Groups({"user_for_mobile", "user_for_mobile_status", "for_mobile", "user", "relatedUser", "user_by_status", "note", "profile_edit", "search", "lb_group", "slider", "message" })
     */
    protected $firstName;


    /**
     * @ORM\Column(name="last_name", type="string", length=20, nullable=true, )
     * @Assert\NotBlank(message="user.lastName.not_blank", groups={"Registration", "Profile", "Base", "accountType", "Admin"})
     * @Assert\Length(
     *     min=2,
     *     max=20,
     *     minMessage="user.lastName.minLength",
     *     maxMessage="user.lastName.maxLength.",
     *     groups={"Registration", "Profile", "accountType", "Admin"}
     * )
     *
     */
    protected $lastName;

    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Assert\NotBlank(message="user.birthday.not_blank", groups={"step2", "Profile", "Base", "basicInfo"})
     * @Groups("profile_edit")
     */
    protected $birthday;

    /**
     * @var
     */
    protected $plainPassword;

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
     * @Groups({"search", "user_for_mobile", "for_mobile", "user_for_mobile_status", "profile_edit"})
     */
    protected $city;



    /**
     * @var
     * @ORM\Column(name="deactivate", type="boolean", nullable=true)
     */
    protected $deactivate;


    /**
     * @var
     * @ORM\Column(name="search_visibility", type="boolean", nullable=true)
     * @Groups({"for_mobile", "user_for_mobile_status", "user_by_status", "user_for_mobile", "user", "search", "lb_group_mobile", "lb_group", "lb_group_single_mobile"})
     *
     */
    protected $searchVisibility;

    /**
     * @ORM\Column(type="float", name="lat", nullable=true)
     * @var
     */
    protected  $cityLat;

    /**
     * @ORM\Column(type="float", name="lng", nullable=true)
     * @var
     */
    protected  $cityLng;

    /**
     * @ORM\Column(name="state", type="string", length=30, nullable=true)
     */
    protected $state;

    /**
     * @deprecated
     * @Assert\Choice(choices = {"0", "1", "2", "3"}, message = "user.visibility", groups={"step3", "Profile", "Edit"})
     * @ORM\Column(name="state_visibility", type="smallint", nullable=true)
     * @Groups("profile_setting")
     */
    protected $stateVisibility;

    /**
     * @deprecated
     * @Assert\Length(
     *     max=10,
     *     maxMessage="user.zipCode.maxLength",
     *     groups={"step3", "Profile"}
     * )
     * @ORM\Column(name="zip_code", type="string", length=10, nullable=true)
     */
    protected $zipCode;

    /**
     * @ORM\ManyToOne(targetEntity="LB\UserBundle\Entity\ZipCode", inversedBy="user")
     * @ORM\JoinColumn(name="zip_id", referencedColumnName="id")
     */
    protected $zip;

    /**
     * @deprecated
     * @Assert\Choice(choices = {"0", "1", "2", "3"}, message = "user.visibility", groups={"step3", "Profile", "Edit"})
     * @ORM\Column(name="zip_code_visibility", type="smallint", nullable=true)
     * @Groups("profile_setting")
     */
    protected $zipCodeVisibility;

    /**
     * @deprecated
     * @Assert\Choice(choices = {"0", "1", "2", "3"}, message = "user.visibility", groups={"step3", "Profile", "Edit"})
     * @ORM\Column(name="country_visibility", type="smallint", nullable=true)
     * @Groups("profile_setting")
     */
    protected $countryVisibility;

    /**
     * @ORM\Column(name="summary", type="text", nullable=true)
     * @Assert\NotBlank(message="user.summary.not_blank", groups={"step3", "AboutMe", "Base"})
     * @Assert\Length(
     *     min=3,
     *     minMessage="user.summary.minLength",
     *     groups={"step3", "AboutMe"}
     * )
     * @Groups({"profile_edit", "user_for_mobile"})
     */
    protected $summary;

    /**
     * @ORM\Column(name="craziest_outdoor_adventure", type="text", nullable=true)
     * @Groups({"profile_edit", "user_for_mobile"})
     */
    protected $craziestOutdoorAdventure;

    /**
     * @deprecated
     * @Assert\Choice(choices = {"0", "1", "2", "3"}, message = "user.visibility", groups={"step3", "Profile", "Edit"})
     * @ORM\Column(name="coa_visibility", type="smallint", nullable=true)
     * @Groups("profile_setting")
     */
    protected $craziestOutdoorAdventureVisibility;

    /**
     * @ORM\Column(name="favorite_outdoor_activity", type="text", nullable=true)
     * @Groups({"profile_edit", "user_for_mobile"})
     */
    protected $favoriteOutdoorActivity;

    /**
     * @deprecated
     * @Assert\Choice(choices = {"0", "1", "2", "3"}, message = "user.visibility", groups={"step3", "Profile", "Edit"})
     * @ORM\Column(name="foa_visibility", type="smallint", nullable=true)
     * @Groups("profile_setting")
     */
    protected $favoriteOutdoorActivityVisibility;

    /**
     * @ORM\Column(name="like_try_tomorrow", type="text", nullable=true)
     * @Groups({"profile_edit", "user_for_mobile"})
     */
    protected $likeTryTomorrow;

    /**
     * @deprecated
     * @Assert\Choice(choices = {"0", "1", "2", "3"}, message = "user.visibility", groups={"step3", "Profile", "Edit"})
     * @ORM\Column(name="ltt_visibility", type="smallint", nullable=true)
     * @Groups("profile_setting")
     */
    protected $likeTryTomorrowVisibility;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Interest", indexBy="id")
     */
    protected $interests;

    /**
     * @ORM\Column(name="email_settings", type="array", nullable=true)
     */
    protected $emailSettings;

    /**
     * @ORM\ManyToMany(targetEntity="File", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $files;

    /**
     * @ORM\OneToOne(targetEntity="File", cascade={"persist"})
     * @ORM\JoinColumn(name="profile_image", referencedColumnName="id", onDelete="SET NULL")
     * @Groups("profile_edit")
     */
    protected $profileImage;

    /**
     * @Assert\NotBlank(message = "You must select I am", groups={"Registration"})
     * @Assert\Choice(choices = {"4", "5"}, message = "user.gender", groups={"Registration", "Profile", "Base"})
     * @ORM\Column(name="i_am", type="smallint", nullable=true)
     * @Groups({"user_for_mobile", "profile_edit", "search", "for_mobile", "user_for_mobile_status",})
     */
    protected $I_am;

    /**
     * @Assert\NotBlank(message="You must select looking for", groups={"Registration", "Admin", "basicInfo"})
     * @ORM\Column(name="looking_for", type="smallint", nullable=true)
     */
    protected $looking_for;

    /**
     * todo remove ufter update, and add groups to looking for
     * @deprecated
     * @Groups({"profile_edit"})
     * @SerializedName("looking_for")
     */
    public $lookingForMobile;

    /**
     * @ORM\Column(name="looking_for_temp", type="smallint", nullable=true)
     */
    protected $lookingForTemp;


    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    protected $facebook_id;

    /**
     * @ORM\Column(name="facebook_token", type="string", length=255, nullable=true)
     */
    protected $facebookToken;

    /**
     * @var
     * @ORM\Column(type="string", length=50,  nullable=true)
     */
    protected $twitterId;


    /**
     * @var
     * @ORM\Column(type="string", length=50,  nullable=true)
     */
    protected $instagramId;

    /**
     * @var
     * @ORM\Column(name="step", type="smallint", nullable=true)
     */
    protected $step = 1;

    /**
     * @Groups({"slider", "search", "lb_group", "user_for_mobile_status"})
     * @param bool|false $list
     * @return mixed
     */
    public $imageCachePath = null;


    /**
     * @param bool|false $list
     * @return mixed
     */
    public $messageImage = null;

    /**
     * @Groups({"user_for_mobile_status"})
     * @param bool|false $list
     * @return mixed
     */
    public $statusForMobile = false;


    /**
     * @Groups({"search"})
     * @param bool|false $list
     * @return mixed
     */
    public $allFiles = null;

    /**
     * @Groups({"user_for_mobile", "for_mobile"})
     * @param bool|false $list
     * @return mixed
     */
    public $isAdmin = null;


    /**
     * @Groups({"search"})
     * @param bool|false $list
     * @return mixed
     */
    public $usersCount = null;


    /**
     * @Groups({"search", "lb_group"})
     * @param bool|false $list
     * @return mixed
     */
    public $fullName = null;


    /**
     * @Groups({"search"})
     * @param bool|false $list
     * @return mixed
     */
    public $status = null;


    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    protected $socialPhotoLink;

    /**
     * @deprecated
     * @var
     * @ORM\Column(name="register", type="boolean")
     */
    protected $register;

    /**
     * @var
     * @Assert\NotBlank(message="user.iAgree", groups={"Registration", "AboutMe"})
     */
    protected $iAgree;

    /**
     * @ORM\Column(name="last_activity", type="datetime", nullable=true)
     * @var
     */
    protected $lastActivity;

    /**
     * @var
     * @ORM\Column(name="ip_address", type="string", length=15, nullable=true)
     */
    protected $IPAddress;

    /**
     * @var
     * @ORM\Column(name="personal_info", type="text", nullable=true)
     * @Groups({"user_for_mobile", "profile_edit"})
     */
    protected $personalInfo;

    /**
     * @var
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    protected $oldId;


    /**
     * @var
     * @ORM\Column(name="has_geo", type="boolean", nullable=true)
     */
    protected $hasGeo;

    /**
     * @var
     * @Groups({"user_for_mobile", "for_mobile"})
     * @ORM\Column(name="notification_switch", type="boolean", nullable=true)
     */
    protected $notificationSwitch = true;

    /**
     * @var
     * @Groups({"user_for_mobile", "for_mobile"})
     * @ORM\Column(name="notification_like_switch", type="boolean", nullable=true)
     */
    protected $notificationLikeSwitch = true;

    /**
     * @var
     * @Groups({"user_for_mobile", "for_mobile"})
     * @ORM\Column(name="notification_favorite_switch", type="boolean", nullable=true)
     */
    protected $notificationFavoriteSwitch = true;

    /**
     * @var
     * @Groups({"user_for_mobile", "for_mobile"})
     * @ORM\Column(name="notification_messages_switch", type="boolean", nullable=true)
     */
    protected $notificationMessagesSwitch = true;

    /**
     * @var
     * @Groups({"user_for_mobile", "for_mobile"})
     * @ORM\Column(name="notification_views_switch", type="boolean", nullable=true)
     */
    protected $notificationViewsSwitch = true;

    /**
     * @var
     * @ORM\Column(name="registration_ids", type="text", nullable=true)
     */
    protected $registrationIds;

    /**
     * @var
     * @ORM\Column(name="searching_params", type="string", length=400, nullable=true)
     */
    protected $searchingParams;

    /**
     * @deprecated
     * @Groups({"profile_edit", "search", "user_for_mobile", "for_mobile", "user_for_mobile_status"})
     * @ORM\Column(name="height", type="float", nullable=true)
     */
    protected $height;

    /**
     * @deprecated
     * @Groups({"profile_edit", "search", "user_for_mobile", "for_mobile", "user_for_mobile_status"})
     * @Assert\Choice(choices = {"1", "2"}, message = "user.height_unit", groups={"step3", "Profile", "Base", "basicInfo"})
     * @ORM\Column(name="height_unit", type="smallint", nullable=true)
     */
    protected $heightUnit;

    /**
     * @ORM\Column(name="feet", type="smallint", nullable=true)
     * @Assert\NotBlank(message="Feet can not be blank", groups={"step3"})
     * @Groups({"profile_edit", "search", "user_for_mobile", "for_mobile", "user_for_mobile_status"})
     */
    protected $feet;

    /**
     * @ORM\Column(name="inches", type="smallint", nullable=true)
     * @Assert\NotBlank(message="Inches can not be blank", groups={"step3"})
     * @Groups({"profile_edit", "search", "user_for_mobile", "for_mobile", "user_for_mobile_status"})
     */
    protected $inches;

    /**
     * @var
     * @ORM\Column(name="created_at", type="date", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @ORM\OneToOne(targetEntity="LB\PaymentBundle\Entity\Customer",  mappedBy="user", cascade={"persist", "remove"})
     */
    protected $customer;

    /**
     * @var
     * @ORM\Column(name="trial_period", type="string", nullable=true)
     */
    protected $trialPeriod;

    /**
     * @var
     * @ORM\Column(name="has_simulate_period", type="boolean", nullable=true)
     */
    protected $hasSimulatePeriod = false;

    /**
     * @ORM\Column(name="api_key", type="string", length=50, nullable=true, unique=true)
     */
    protected $apiKey;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Event", mappedBy="users")
     */
    protected $events;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->interests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->register = false;
        $this->enabled = true;
    }

    /**
     * @return string
     * @VirtualProperty()
     * @Groups({"search", "user_for_mobile", "for_mobile", "user_for_mobile_status"})
     */
    public function getAge()
    {
        // get birth day
        $birthDay = $this->birthday;

        if($this->birthday){
            $date = new \DateTime();

            $diff = date_diff($date, $birthDay);

            return $diff->y;
        }

        return null;

    }

    /**
     * @return array
     */
    public function getInterestsInString()
    {
        $interestGroups = [];
        foreach ($this->interests as $interest) {
            if (!isset($interestGroups[$interest->getGroup()->getName()])) {
                $interestGroups[$interest->getGroup()->getName()] = $interest->getName();
            } else {
                $interestGroups[$interest->getGroup()->getName()] .= ', ' . $interest->getName();
            }
        }

        return $interestGroups;
    }


    /**
     * @return array
     * @VirtualProperty()
     * @Groups({"user_for_mobile"})
     */
    public function getInterestsWithGroup()
    {
        // get interests
        $interests =  $this->interests;

        // default value for return result
        $interestsArray = array();

        // check interests
        if($interests){

            // loop for interests
            foreach($interests as $interest){

                // if groups is defines, add to group
                if (!isset($interestsArray[$interest->getGroup()->getName()])){

                    $interestsArray[$interest->getGroup()->getName()] = array();
                }

                $interestsArray[$interest->getGroup()->getName()][] = $interest;
            }
        }
        return $interestsArray;
    }



    /**
     * @return array
     */
    public function getInterestsIds()
    {
        // get interests
        $interests =  $this->interests;

        // default value for return result
        $interestsArray = array();

        // check interests
        if($interests){

            // loop for interests
            foreach($interests as $interest){
                $interestsArray[] = $interest->getId();
            }
        }
        return $interestsArray;
    }

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * @param mixed $facebook_id
     */
    public function setFacebookId($facebook_id)
    {
        $this->facebook_id = $facebook_id;
    }

    /**
     * @return mixed
     */
    public function getFacebookToken()
    {
        return $this->facebookToken;
    }

    /**
     * @param mixed $facebookToken
     */
    public function setFacebookToken($facebookToken)
    {
        $this->facebookToken = $facebookToken;
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return User
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
     * Set state
     *
     * @param string $state
     *
     * @return User
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return User
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set summary
     *
     * @param string $summary
     *
     * @return User
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set craziestOutdoorAdventure
     *
     * @param string $craziestOutdoorAdventure
     *
     * @return User
     */
    public function setCraziestOutdoorAdventure($craziestOutdoorAdventure)
    {
        $this->craziestOutdoorAdventure = $craziestOutdoorAdventure;

        return $this;
    }

    /**
     * Get craziestOutdoorAdventure
     *
     * @return string
     */
    public function getCraziestOutdoorAdventure()
    {
        return $this->craziestOutdoorAdventure;
    }

    /**
     * Set favoriteOutdoorActivity
     *
     * @param string $favoriteOutdoorActivity
     *
     * @return User
     */
    public function setFavoriteOutdoorActivity($favoriteOutdoorActivity)
    {
        $this->favoriteOutdoorActivity = $favoriteOutdoorActivity;

        return $this;
    }

    /**
     * Get favoriteOutdoorActivity
     *
     * @return string
     */
    public function getFavoriteOutdoorActivity()
    {
        return $this->favoriteOutdoorActivity;
    }

    /**
     * Set likeTryTomorrow
     *
     * @param string $likeTryTomorrow
     *
     * @return User
     */
    public function setLikeTryTomorrow($likeTryTomorrow)
    {
        $this->likeTryTomorrow = $likeTryTomorrow;

        return $this;
    }

    /**
     * Get likeTryTomorrow
     *
     * @return string
     */
    public function getLikeTryTomorrow()
    {
        return $this->likeTryTomorrow;
    }

    /**
     * Add interest
     *
     * @param \AppBundle\Entity\Interest $interest
     *
     * @return User
     */
    public function addInterest(\AppBundle\Entity\Interest $interest)
    {
        if(!isset($this->interests[$interest->getId()])) {
            $this->interests[$interest->getId()] = $interest;
        }

        return $this;
    }

    /**
     * Remove interest
     *
     * @param \AppBundle\Entity\Interest $interest
     */
    public function removeInterest(\AppBundle\Entity\Interest $interest)
    {
        $this->interests->removeElement($interest);
    }

    /**
     * Get interests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * Add file
     *
     * @param \LB\UserBundle\Entity\File $file
     *
     * @return User
     */
    public function addFile(\LB\UserBundle\Entity\File $file)
    {
        $this->files[] = $file;
        $file->setUser($this);

        return $this;
    }

    /**
     * Remove file
     *
     * @param \LB\UserBundle\Entity\File $file
     */
    public function removeFile(\LB\UserBundle\Entity\File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set profileImage
     *
     * @param \LB\UserBundle\Entity\File $profileImage
     *
     * @return User
     */
    public function setProfileImage(\LB\UserBundle\Entity\File $profileImage = null)
    {
        $profileImage->setType(File::IMAGE);
        $this->addFile($profileImage);
        $this->profileImage = $profileImage;

        return $this;
    }


    /**
     * Get profileImage
     *
     * @return \LB\UserBundle\Entity\File
     */
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * @VirtualProperty()
     * @Groups({"note", "user", "search", "lb_group", "event_for_mobile"})
     * @param bool|false $list
     * @return mixed
     */
    public function getProfileImagePath($list = false)
    {
        // get profile image
        $image = $this->profileImage;

        // if image. return download path
        if ($image && $image->getPath()) {
            // return path
            return '/' . $image->getUploadDir() . '/' . $image->getPath();
        } elseif ($this->getSocialPhotoLink()) {
            return $this->getSocialPhotoLink();
        }

        return  '/bundles/app/images/no-profile-image.png';
    }

    /**
     * @return null|string
     */
    public function getProfileImageCacheVersion()
    {
        // get profile image
        $image = $this->profileImage;
        $version = null;

        // if image. return download path
        if ($image && $image->getCacheVersion()) {
            // return path
            $version = '?v=' . $image->getCacheVersion();
        }

        return $version;
    }

    /**
     * @VirtualProperty()
     * @Groups({"for_mobile", "user_for_mobile_status", "user_by_status", "user_for_mobile", "user", "search", "lb_group_mobile", "lb_group", "lb_group_single_mobile"})
     * @return mixed
     */
    public function getProfileImagePathForMobile()
    {
        // get profile image
        $image = $this->profileImage;

        // if image. return download path
        if ($image && $image->getPath()) {
            // return path
            return $image->imageFromCache ? $image->imageFromCache : self::BASE_PATH . $image->getUploadDir() . '/' . $image->getPath();
        } elseif ($this->getSocialPhotoLink()) {
            return $this->getSocialPhotoLink();
        }

        return self::BASE_PATH . 'bundles/app/images/profile.png';
    }

    /**
     * @param $file
     * @return null
     */
    public function getNextImage($file)
    {
        // get all files
        $images = $this->getFiles();

        // check images
        if($images){

            // loop for images
            foreach($images as $image){

                // check is file current
                if($image->getId() != $file->getId()){
                    return $image;
                }
            }
        }
        return null;
    }

    /**
     * Set stateVisibility
     *
     * @param integer $stateVisibility
     *
     * @return User
     */
    public function setStateVisibility($stateVisibility)
    {
        $this->stateVisibility = $stateVisibility;

        return $this;
    }

    /**
     * Get stateVisibility
     *
     * @return integer
     */
    public function getStateVisibility()
    {
        return $this->stateVisibility;
    }

    /**
     * Set zipCodeVisibility
     *
     * @param integer $zipCodeVisibility
     *
     * @return User
     */
    public function setZipCodeVisibility($zipCodeVisibility)
    {
        $this->zipCodeVisibility = $zipCodeVisibility;

        return $this;
    }

    /**
     * Get zipCodeVisibility
     *
     * @return integer
     */
    public function getZipCodeVisibility()
    {
        return $this->zipCodeVisibility;
    }

    /**
     * Set countryVisibility
     *
     * @param integer $countryVisibility
     *
     * @return User
     */
    public function setCountryVisibility($countryVisibility)
    {
        $this->countryVisibility = $countryVisibility;

        return $this;
    }

    /**
     * Get countryVisibility
     *
     * @return integer
     */
    public function getCountryVisibility()
    {
        return $this->countryVisibility;
    }

    /**
     * Set craziestOutdoorAdventureVisibility
     *
     * @param integer $craziestOutdoorAdventureVisibility
     *
     * @return User
     */
    public function setCraziestOutdoorAdventureVisibility($craziestOutdoorAdventureVisibility)
    {
        $this->craziestOutdoorAdventureVisibility = $craziestOutdoorAdventureVisibility;

        return $this;
    }

    /**
     * Get craziestOutdoorAdventureVisibility
     *
     * @return integer
     */
    public function getCraziestOutdoorAdventureVisibility()
    {
        return $this->craziestOutdoorAdventureVisibility;
    }

    /**
     * Set favoriteOutdoorActivityVisibility
     *
     * @param integer $favoriteOutdoorActivityVisibility
     *
     * @return User
     */
    public function setFavoriteOutdoorActivityVisibility($favoriteOutdoorActivityVisibility)
    {
        $this->favoriteOutdoorActivityVisibility = $favoriteOutdoorActivityVisibility;

        return $this;
    }

    /**
     * Get favoriteOutdoorActivityVisibility
     *
     * @return integer
     */
    public function getFavoriteOutdoorActivityVisibility()
    {
        return $this->favoriteOutdoorActivityVisibility;
    }

    /**
     * Set likeTryTomorrowVisibility
     *
     * @param integer $likeTryTomorrowVisibility
     *
     * @return User
     */
    public function setLikeTryTomorrowVisibility($likeTryTomorrowVisibility)
    {
        $this->likeTryTomorrowVisibility = $likeTryTomorrowVisibility;

        return $this;
    }

    /**
     * Get likeTryTomorrowVisibility
     *
     * @return integer
     */
    public function getLikeTryTomorrowVisibility()
    {
        return $this->likeTryTomorrowVisibility;
    }

    /**
     * Set iAm
     *
     * @param integer $iAm
     *
     * @return User
     */
    public function setIAm($iAm)
    {
        $this->I_am = $iAm;

        return $this;
    }

    /**
     * Get iAm
     *
     * @return integer
     */
    public function getIAm()
    {
        return $this->I_am;
    }

    /**
     * Set lookingFor
     *
     * @param integer $lookingFor
     *
     * @return User
     */
    public function setLookingFor($lookingFor)
    {
        $this->looking_for = $lookingFor;

        return $this;
    }
    /**
     * Get lookingFor
     *
     * @return integer
     */
    public function getLookingFor()
    {
        $lookingFor = $this->looking_for;

        return $lookingFor;
    }

    /**
     * @VirtualProperty()
     * @Groups({"profile_edit"})
     * @return integer
     *
     */
    public function isEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getRegister()
    {
        return $this->register;
    }

    /**
     * @param mixed $register
     */
    public function setRegister($register)
    {
        $this->register = $register;
    }


    /**
     * @param \DateTime $time
     * @return $this|\FOS\UserBundle\Model\UserInterface
     */
    public function setLastLogin(\DateTime $time)
    {
        $this->setLastActivity($time);
        return parent::setLastLogin($time); // TODO: Change the autogenerated stub
    }


    /**
     * @return mixed
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @param mixed $lastActivity
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }


    /**
     * @return string|void
     * @VirtualProperty()
     * @Groups({"for_mobile", "user_for_mobile", "search", "user", "user_for_mobile_status", "message"})
     */
    public function getActivity()
    {
        $result = array('minute' => -1, 'title' => null);

        // get last activity
        $lastActivity = $this->getLastActivity();

        // now
        $now = new \DateTime('now');

        if (!$lastActivity) {
            return $result;
        }

        // get date diff
        $dateDiff = date_diff($now, $lastActivity);


        // activity result
        switch ($dateDiff) {
            case $dateDiff->y > 0:
                $result = array('minute' => $dateDiff->days * 365 * 1440 + $dateDiff->i, 'title' => 'active within 1 year');
                break;
            case $dateDiff->m >= 6:
                $result = array('minute' => $dateDiff->days * 30 * 1440 + $dateDiff->i, 'title' => 'active within 6 months');
                break;
            case $dateDiff->m > 0:
                $result = array('minute' => $dateDiff->days * 30 * 1440 + $dateDiff->i, 'title' => 'active within one month');
                break;
            case $dateDiff->d >= 7:
                $result = array('minute' => $dateDiff->days * 1440 + $dateDiff->i, 'title' => 'active within one week');
                break;
            case $dateDiff->d >= 3:
                $result = array('minute' => $dateDiff->days * 1440 + $dateDiff->i, 'title' => 'active within 72 hrs');
                break;
            case $dateDiff->d > 0:
                $result = array('minute' => $dateDiff->days * 1440 + $dateDiff->i, 'title' => 'active within 24 hrs');
                break;
            case $dateDiff->h > 0:
                $result = array('minute' => $dateDiff->h * 60 + $dateDiff->i, 'title' => 'active within 1 hr');
                break;
            default:
                $result = array('minute' => $dateDiff->i, 'title' => 'active less than 1 hr');
                break;
        }

        return $result;
    }

    /**
     * Set twitterId
     *
     * @param string $twitterId
     *
     * @return User
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    /**
     * Get twitterId
     *
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * Set instagramId
     *
     * @param string $instagramId
     *
     * @return User
     */
    public function setInstagramId($instagramId)
    {
        $this->instagramId = $instagramId;

        return $this;
    }

    /**
     * Get instagramId
     *
     * @return string
     */
    public function getInstagramId()
    {
        return $this->instagramId;
    }

    /**
     * Set socialPhotoLink
     *
     * @param string $socialPhotoLink
     *
     * @return User
     */
    public function setSocialPhotoLink($socialPhotoLink)
    {
        $this->socialPhotoLink = $socialPhotoLink;

        return $this;
    }

    /**
     * Get socialPhotoLink
     *
     * @return string
     */
    public function getSocialPhotoLink()
    {
        return $this->socialPhotoLink;
    }

    /**
     * @return string
     * @VirtualProperty()
     * @Groups({"search", "lb_group", "lb_group_mobile", "lb_group_single_mobile", "event_for_mobile"})
     *
     */
    public function getShowName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return null|string
     */
    public function getRealAge()
    {
        // get age
        $age = $this->getBirthday();

        // check age
        if ($age) {
            $y = $age->format('Y');
            $now = new \DateTime('now');

            return $now->format('Y') - $y;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getIAgree()
    {
        return $this->iAgree;
    }

    /**
     * @param mixed $iAgree
     */
    public function setIAgree($iAgree)
    {
        $this->iAgree = $iAgree;
    }

    /**
     * Set emailSettings
     *
     * @param array $emailSettings
     *
     * @return User
     */
    public function setEmailSettings($emailSettings)
    {
        $this->emailSettings = $emailSettings;

        return $this;
    }

    /**
     * Get emailSettings
     *
     * @return array
     */
    public function getEmailSettings()
    {
        return $this->emailSettings;
    }

    /**
     * @return bool
     */
    public function isSocialUser()
    {
        // is social user
        if($this->getFacebookId() || $this->getTwitterId() || $this->getInstagramId()){
            return true;
        }
        return false;
    }

    /**
     * @param null $type
     * @return array
     * @VirtualProperty()
     */
    public function getGallery($type = null, $arrayValue = false)
    {
        // get user files
        $files = $this->files;

        $images = array();

        // get profile image
        $profileImage = $this->getProfileImage();

        // check is profile image exist
        if($profileImage){

            $images[$profileImage->getId()] = $profileImage->getWebPath($type);

        }elseif($this->getSocialPhotoLink()){

            if($type == File::MOBILE){
                $path = array('web_path_for_mobile' => $this->getSocialPhotoLink());
            }elseif($type == File::FRONTEND){
                $path = array('web_path' => $this->getSocialPhotoLink(),'type' => File::IMAGE);
            }else{
                $path = $this->getSocialPhotoLink();
            }

            $path = str_replace('type=large', 'height=526&width=526', $path);
            $images[0] = $path;
        }

        if($files && $files->count() > 0){
            // loop for files
            foreach($files as $file){

                // check type and check if not profile image
                if($file->getType() == File::IMAGE ){
                    $images[$file->getId()] = $file->getWebPath($type);
                }
            }
        }

        // if no images return blank photo
        if(count($images) == 0){

            if($type == File::MOBILE){
                $path = array('web_path_for_mobile' => User::BASE_PATH .  'bundles/app/images/profile.png');
            }elseif($type == File::FRONTEND){
                $path = array('web_path' => '/bundles/app/images/profile.png','type' => File::IMAGE);
            }else{
                $path = '/bundles/app/images/profile.png';
            }

            // empty value for image
            $images[] = $path;
        }

        return $arrayValue ? array_values($images) : $images;
    }


    /**
     * @return array
     */
    public function getImagesCacheVersion()
    {
        // get user files
        $files = $this->files;
        $result = [];

        if($files && $files->count() > 0){
            // loop for files
            foreach($files as $file){
                $result[$file->getId()] = $file->generateCacheVersion();
            }
        }


        return $result;
    }

    /**
     * @return array
     * @VirtualProperty()
     * @Groups({"user_for_mobile", "file"})
     * @SerializedName("files")
     */
    public function getGalleryForMobile()
    {
        $images = $this->getGallery(File::MOBILE, true);

        return $images;
    }

    /**
     * @return array
     * @VirtualProperty()
     */
    public function getAllFiles()
    {
        $images = $this->getGallery(File::FRONTEND);

        return $images;
    }

    /**
     * Set iPAddress
     *
     * @param integer $iPAddress
     *
     * @return User
     */
    public function setIPAddress($iPAddress)
    {
        $this->IPAddress = $iPAddress;

        return $this;
    }

    /**
     * Get iPAddress
     *
     * @return integer
     */
    public function getIPAddress()
    {
        return $this->IPAddress;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        // generate password groups
        $passwordGroups = array("Registration","Profile", "Default", "ResetPassword", "ChangePassword", "Admin");
        $interestsGroups = array("step3", "myInterest");

        // get groups
        $groups = $context->getGroup();

        // check registration
        if(in_array($groups, $passwordGroups)){

            // check social user
            if(!$this->isSocialUser()){

                // get plain password
                $plainPassword = $this->getPlainPassword();

                if($groups == "Admin" ? strlen($plainPassword) > 0 :strlen($plainPassword) > 1){

                    // get pattern
                    $pattern  = '/^(?=.*[A-Z])(?=.*\d).*$/';
//                    $pattern  = '/^(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).*$/';
//                    $pattern  = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*_+()]).*$/';

                    // preg match password
                    preg_match($pattern, $plainPassword, $match);

                    // check match
                    if(count($match) == 0 || strlen($plainPassword) < 4){
                        $context->buildViolation('fos_user.password.conditions')
                            ->atPath('plainPassword')
                            ->addViolation();
                    }
                }
            }
            else{
                // set plain password for socials
                $this->setPlainPassword(sha1(uniqid(mt_rand(), true)));
            }

        }

        if(in_array($groups, $interestsGroups)){
            // get interests
            $interests = $this->interests;

            // check interests
            if (count($interests) == 0) {
                // If you're using the new 2.5 validation API (you probably are!)
                $context->buildViolation('You must select at least one interest per interest group !')
                    ->atPath('interests')
                    ->addViolation();
            }
        }

        // get birthday
        $birthDay = $this->birthday;
        // check birthday
        if($birthDay){
            $year = $birthDay->format('Y');
            $now = new \DateTime('now');
            $nYear = $now->format('Y');

            if($nYear - $year < 18){
                $context->buildViolation('Year must bo older 18 year')
                    ->atPath('birthday')
                    ->addViolation();
            }
        }

        // check inches
        $inches = $this->inches;
        // check $inches
        if($inches){

            if($inches < 0 && $inches > 12){
                $context->buildViolation('Wrong value for inches')
                    ->atPath('inches')
                    ->addViolation();
            }
        }

        // check feet
        $feet = $this->feet;
        // check $feet
        if($feet){

            if($feet < 3 && $feet > 9){
                $context->buildViolation('Wrong value for feet')
                    ->atPath('feet')
                    ->addViolation();
            }
        }

//        if($groups == 'Registration' || $groups == 'Admin' || $groups == 'basicInfo'){
//
//            $lookingFor = $this->looking_for;
//
//            // check birthday
//            if(count($lookingFor) == 0){
//                $context->buildViolation('You must select looking for')
//                    ->atPath('looking_for')
//                    ->addViolation();
//            }
//        }
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
     * Set personalInfo
     *
     * @param string $personalInfo
     *
     * @return User
     */
    public function setPersonalInfo($personalInfo)
    {
        $this->personalInfo = $personalInfo;

        return $this;
    }

    /**
     * Get personalInfo
     *
     * @return string
     */
    public function getPersonalInfo()
    {
        return $this->personalInfo;
    }

    /**
     * Set oldId
     *
     * @param integer $oldId
     *
     * @return User
     */
    public function setOldId($oldId)
    {
        $this->oldId = $oldId;

        return $this;
    }

    /**
     * Get oldId
     *
     * @return integer
     */
    public function getOldId()
    {
        return $this->oldId;
    }

    /**
     * Set hasGeo
     *
     * @param boolean $hasGeo
     *
     * @return User
     */
    public function setHasGeo($hasGeo)
    {
        $this->hasGeo = $hasGeo;

        return $this;
    }

    /**
     * Get hasGeo
     *
     * @return boolean
     */
    public function getHasGeo()
    {
        return $this->hasGeo;
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {

        return serialize(array(
            $this->emailCanonical,
            $this->groups,
            $this->roles,
            $this->firstName,
            $this->lastName,
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->email,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->birthday,
            $this->I_am,
            $this->socialPhotoLink,
            $this->facebook_id,
            $this->twitterId,
            $this->instagramId,
            $this->register,
            $this->plainPassword,
            $this->facebookToken
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->emailCanonical,
            $this->groups,
            $this->roles,
            $this->firstName,
            $this->lastName,
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->email,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->birthday,
            $this->I_am,
            $this->socialPhotoLink,
            $this->facebook_id,
            $this->twitterId,
            $this->instagramId,
            $this->register,
            $this->plainPassword,
            $this->facebookToken
            ) = $data;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getRegistrationIds()
    {
        return json_decode($this->registrationIds, true);
    }

    /**
     * @param mixed $data
     */
    public function setRegistrationIds($data)
    {
        $this->registrationIds = json_encode($data) ;
    }

    /**
     * @return mixed
     */
    public function getCityLng()
    {
        return $this->cityLng;
    }

    /**
     * @return string
     * @VirtualProperty()
     * @Groups({"search", "user_for_mobile", "for_mobile", "profile_edit"})
     */
    public function getOnlyCity()
    {
        $cityCountry = $this->city;
        if($cityCountry){
            $city = explode(',', $cityCountry);
            if($this->state){
                $state = self::format_state($this->state,'abbr');
            } else{
                $state = isset($city[1])?self::format_state($city[1],'abbr'):null;
            }
            $city = $state?($city[0].', '.$state):$city[0];
            return $city;
        }

        return "''";
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
     * @return mixed
     */
    public function getSearchingParams()
    {
        return json_decode($this->searchingParams);
    }

    /**
     * @param mixed $searchingParams
     */
    public function setSearchingParams($searchingParams)
    {
        $this->searchingParams = json_encode($searchingParams);
    }

    public function getChoiceGender()
    {
        return self::$GENDER_CHOICE;
    }

    /**
     * @return mixed
     */
    public function getDeactivate()
    {
        return $this->deactivate;
    }

    /**
     * @param mixed $deactivate
     */
    public function setDeactivate($deactivate)
    {
        $this->deactivate = $deactivate;
    }

    /**
     * @return mixed
     */
    public function getSearchVisibility()
    {
        return $this->searchVisibility;
    }

    /**
     * @param mixed $searchVisibility
     */
    public function setSearchVisibility($searchVisibility)
    {
        $this->searchVisibility = $searchVisibility;
    }

    /**
     * @return mixed
     */
    public function getNotificationSwitch()
    {
        return $this->notificationSwitch;
    }

    /**
     * @param mixed $notificationSwitch
     */
    public function setNotificationSwitch($notificationSwitch)
    {
        $this->notificationSwitch = $notificationSwitch;
    }

    /**
     * @deprecated
     * @param $plan
     * @return bool
     */
    public function hasAccessToPlan($plan)
    {
//        todo remove when payment is opened
//        return true;
        $today = new \DateTime();
        $today->setTime(23, 59, 59);

        if($plan == Subscriber::MESSAGE){
            return true;
        }

        // get trial period
        $trialPeriod = $this->getTrialPeriod();

        // check is exist trial period
        if(is_array($trialPeriod)){

            // check is exist plan
            if(array_key_exists(Subscriber::UNLIMITED, $trialPeriod)){
                $endDate = $trialPeriod[Subscriber::UNLIMITED];
                $endDate = new \DateTime("@$endDate") ;

                // check dates
                if($endDate > $today){
                    return true;
                }
            }

            // check for old plan
            $newPlan = strpos($plan, '_new') === false ? $plan . '_new' : $plan;

            // check is exist plan
            if(array_key_exists($plan, $trialPeriod) || array_key_exists($newPlan, $trialPeriod)){

                // get date
                $date = array_key_exists($plan, $trialPeriod) ?  $trialPeriod[$plan] : $trialPeriod[$newPlan];

                $endDate = new \DateTime("@$date") ;

                // check dates
                if($endDate >= $today){
                    return true;
                }
            }

        }
        return false;
    }

    /**
     * @param $plan
     * @return bool
     */
    public function hasSubscribeToPlan($plan)
    {
//        if($plan == Subscriber::UNLIMITED){
            //todo remove when payment is opened
//            return true;
//        }

        // get trial period
        $trialPeriod = $this->getTrialPeriod();

        // check plan
        if(is_array($trialPeriod) && array_key_exists($plan, $trialPeriod)){

            $endSubscribe = $trialPeriod[$plan];
            $endSubscribe = new \DateTime("@$endSubscribe") ;
            $today = new \DateTime();
            $today->setTime(23, 59, 59);
            if($today <= $endSubscribe){
                return true;
            }

        }

        // check for old plan
        $oldPlan = str_replace('_new', '', $plan);

        // check plan
        if(is_array($trialPeriod) && array_key_exists($oldPlan, $trialPeriod)){

            $endSubscribe = $trialPeriod[$oldPlan];
            $endSubscribe = new \DateTime("@$endSubscribe") ;
            $today = new \DateTime();
            $today->setTime(23, 59, 59);
            if($today <= $endSubscribe){
                return true;
            }

        }


        return false;
    }


    /**
     * This function is used to return access for mobile
     *
     * @VirtualProperty()
     * @Groups({"user_for_mobile", "user_for_mobile_status", "for_mobile"})
     */
    public function permissions()
    {
        // get trial period
        $dbPeriod = $this->getTrialPeriod();
        $trialPeriod = array(Subscriber::MESSAGE => true);

        //todo remove when payment is opened
//        $trialPeriod = array(Subscriber::UNLIMITED => true);
//        return $trialPeriod;

        $today = new \DateTime();

        if(is_array($dbPeriod)){

            // loop for period
            foreach($dbPeriod as $key => $period){

                $index = (strpos($key, '_new') === false && $key != Subscriber::UNLIMITED)  ? $key . '_new' : $key;
                $date = new \DateTime("@$period");
                $isAllow  = $date >= $today;
                $trialPeriod[$index] = $isAllow;

            }

            $trialPeriod[Subscriber::MESSAGE] = true;
        }
        return $trialPeriod ;

    }

    /**
     * @return mixed
     */
    public function getTrialPeriod()
    {
        return json_decode($this->trialPeriod, true);
    }

    /**
     * @param $planId
     * @param $period
     */
    public function setTrialPeriod($planId, $period)
    {
        // get trial  period
        $trialPeriod = $this->getTrialPeriod();


        // check is array
        if(is_array($trialPeriod)){
            $trialPeriod[$planId] = $period;
        }
        else{
            $trialPeriod = array($planId => $period);
        }

        $this->trialPeriod = json_encode($trialPeriod);
    }

    /**
     * @param $planId
     * @param $all
     */
    public function deleteTrialPeriod($planId, $all = false)
    {
        if($all){
            $this->trialPeriod = null;
            return;
        }
        // get trial  period
        $trialPeriod = $this->getTrialPeriod();

        // check is array
        if(is_array($trialPeriod) && array_key_exists($planId, $trialPeriod)){
            unset ($trialPeriod[$planId]);
        }

        $this->trialPeriod = json_encode($trialPeriod);
    }


    /**
     * Set customer
     *
     * @param \LB\PaymentBundle\Entity\Customer $customer
     *
     * @return User
     */
    public function setCustomer(\LB\PaymentBundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;
        if($customer){
            $customer->setUser($this);
        }

        return $this;
    }

    /**
     * Get customer
     *
     * @return \LB\PaymentBundle\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return mixed
     */
    public function getHasSimulatePeriod()
    {
        return $this->hasSimulatePeriod;
    }

    /**
     * @param mixed $hasSimulatePeriod
     */
    public function setHasSimulatePeriod($hasSimulatePeriod)
    {
        $this->hasSimulatePeriod = $hasSimulatePeriod;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return User
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return bool
     */
    public function isDisable()
    {
        // check is deactivate or unsearchable
        if($this->getDeactivate() || $this->getSearchVisibility()){
            return true;

        }

        return false;
    }

    /**
     * @return string
     * @VirtualProperty()
     * @Groups({"profile_edit", "search", "user_for_mobile", "for_mobile", "user_for_mobile_status"})
     */
    public function showHeight()
    {
        $result = null;

        $feet = $this->getFeet() ? $this->getFeet() : 0;
        $inches = $this->getInches() ? $this->getInches() : 0;

        // feet and inches
        if($feet || $inches){
            // generate result
            $result =  $feet . '\' ' . $inches  . '"';
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getHeightUnit()
    {
        return $this->heightUnit;
    }

    /**
     * @param mixed $heightUnit
     */
    public function setHeightUnit($heightUnit)
    {
        $this->heightUnit = $heightUnit;
    }

    /**
     * @param \AppBundle\Entity\Event $events
     * @return $this
     */
    public function addEvent(\AppBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * @param \AppBundle\Entity\Event $events
     */
    public function removeEvent(\AppBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get goal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }


    /**
     * Set zip
     *
     * @param \LB\UserBundle\Entity\ZipCode $zip
     *
     * @return User
     */
    public function setZip(\LB\UserBundle\Entity\ZipCode $zip = null)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return \LB\UserBundle\Entity\ZipCode
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @return mixed
     */
    public function getLookingForTemp()
    {
        return $this->lookingForTemp;
    }

    /**
     * @param mixed $lookingForTemp
     */
    public function setLookingForTemp($lookingForTemp)
    {
        $this->lookingForTemp = $lookingForTemp;
    }

    /**
     * @return mixed
     */
    public function getFeet()
    {
        return $this->feet;
    }

    /**
     * @param mixed $feet
     */
    public function setFeet($feet)
    {
        $this->feet = $feet;
    }

    /**
     * @return mixed
     */
    public function getInches()
    {
        return $this->inches;
    }

    /**
     * @param mixed $inches
     */
    public function setInches($inches)
    {
        $this->inches = $inches;
    }

    /**
     * Set uId
     *
     * @param string $uId
     *
     * @return User
     */
    public function setUId($uId)
    {
        $this->uId = $uId;

        return $this;
    }

    /**
     * Get uId
     *
     * @return string
     */
    public function getUId()
    {
        return $this->uId;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param mixed $step
     */
    public function setStep($step)
    {

        $this->step = $step;
    }

    /**
     * Set notificationLikeSwitch
     *
     * @param boolean $notificationLikeSwitch
     *
     * @return User
     */
    public function setNotificationLikeSwitch($notificationLikeSwitch)
    {
        $this->notificationLikeSwitch = $notificationLikeSwitch;

        return $this;
    }

    /**
     * Get notificationLikeSwitch
     *
     * @return boolean
     */
    public function getNotificationLikeSwitch()
    {
        return $this->notificationLikeSwitch;
    }

    /**
     * Set notificationFavoriteSwitch
     *
     * @param boolean $notificationFavoriteSwitch
     *
     * @return User
     */
    public function setNotificationFavoriteSwitch($notificationFavoriteSwitch)
    {
        $this->notificationFavoriteSwitch = $notificationFavoriteSwitch;

        return $this;
    }

    /**
     * Get notificationFavoriteSwitch
     *
     * @return boolean
     */
    public function getNotificationFavoriteSwitch()
    {
        return $this->notificationFavoriteSwitch;
    }

    /**
     * Set notificationMessagesSwitch
     *
     * @param boolean $notificationMessagesSwitch
     *
     * @return User
     */
    public function setNotificationMessagesSwitch($notificationMessagesSwitch)
    {
        $this->notificationMessagesSwitch = $notificationMessagesSwitch;

        return $this;
    }

    /**
     * Get notificationMessagesSwitch
     *
     * @return boolean
     */
    public function getNotificationMessagesSwitch()
    {
        return $this->notificationMessagesSwitch;
    }

    /**
     * Set notificationViewsSwitch
     *
     * @param boolean $notificationViewsSwitch
     *
     * @return User
     */
    public function setNotificationViewsSwitch($notificationViewsSwitch)
    {
        $this->notificationViewsSwitch = $notificationViewsSwitch;

        return $this;
    }

    /**
     * Get notificationViewsSwitch
     *
     * @return boolean
     */
    public function getNotificationViewsSwitch()
    {
        return $this->notificationViewsSwitch;
    }
}
