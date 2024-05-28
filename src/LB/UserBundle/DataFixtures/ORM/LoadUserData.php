<?php
namespace LB\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LB\PaymentBundle\Entity\Subscriber;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // get interest1 and interest2
        $interest1 = $manager->getRepository('AppBundle:Interest')->findOneByName('interest1');
        $interest2 = $manager->getRepository('AppBundle:Interest')->findOneByName('interest2');

        $defaultCity = array(
            'address' => "Denver, CO, USA",
            'location' => array(
                'latitude' => 39.7392358,
                'longitude' => -104.990251
            ));

        $defaultCity = json_encode($defaultCity);

        // create user
        $user = new User();
        $user->setEmail('user@gmail.com');
        $user->setUsername('User');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('User');
        $user->setLastName('Useryan');
//        $user->setRoles(array('ROLE_SUPER_ADMIN'));
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('Yerevan');
        $user->setLocation($defaultCity);
        $user->setStep(3);

        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarz');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setSummary('summery');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfo');
        $manager->persist($user);

        // create user
        $user = new User();
        $user->setEmail('userEdit@gmail.com');
        $user->setUsername('User111');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('User111');
        $user->setLastName('Useryan111');
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('Yerevan111');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarz111');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setSummary('summery111');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfo111');
        $user->setStep(3);
        $manager->persist($user);

        // create user
        $user = new User();
        $user->setEmail('user222@gmail.com');
        $user->setUsername('User222');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('User222');
        $user->setLastName('Useryan222');
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('Yerevan222');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarz222');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setSummary('summery222');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setStep(3);
        $user->setPersonalInfo('personalInfo2');
        $manager->persist($user);

        // create user
        $user = new User();
        $user->setEmail('userRestFrom@gmail.com');
        $user->setUsername('UserRestFrom');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('UserRestFrom');
        $user->setLastName('UseryanRestFrom');
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('YerevanRestFrom');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
        $user->setStep(3);
//        $user->setState('YerevanMarzRestFrom');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setSummary('summeryRestFrom');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfoRestFrom');
        $manager->persist($user);

        // create user
        $user = new User();
        $user->setEmail('disableUser@gmail.com');
        $user->setUsername('disableUser');
        $user->setEnabled(true);
        $user->setStep(3);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('disableUser');
        $user->setLastName('disableUser');
        $user->setBirthday(new \DateTime('1988-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('YerevanDisableUser');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarzRestFrom');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setSummary('summeryRestFrom');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('disableUser');
        $manager->persist($user);

        // create user
        $user = new User();
        $user->setEmail('userRestTo@gmail.com');
        $user->setUsername('UserRestTo');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('UserRestTo');
        $user->setLastName('UseryanRestTo');
        $user->setBirthday(new \DateTime('1990-11-15'));
        $user->setLookingFor(4);
        $user->setIAm(5);
        $user->setCity('YerevanRestTo');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarzRestTo');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setStep(3);
        $user->setSummary('summeryRestTo');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfoRestTo');
//        $user->setCityLat(40.1791857);
//        $user->setCityLng(44.4991029);

        $dir =__DIR__ . '/../../../../../web/uploads/UserRestFrom';

        if(!file_exists($dir)) {
            mkdir($dir,0775);
            mkdir($dir.'/Images',0775);
        }

        $imageDir = $dir.'/Images';

        $oldPhotoPath = __DIR__ . '/../../../../AppBundle/Tests/Controller/images/test.jpg';
        $photoPath = $imageDir.'/test_image.jpg';

        // copy photo path
        copy($oldPhotoPath, $photoPath);

        $profileImage = new File();
        $profileImage->setClientName('test_image.jpg');
        $profileImage->setName('test_image.jpg');
        $profileImage->setSize(111);
        $profileImage->setType(File::IMAGE);
        $profileImage->setPath('UserRestFrom/Images/test_image.jpg');
        $user->setProfileImage($profileImage);

        $manager->persist($user);


        $photoPath = $imageDir.'/test_image2.jpg';

        // copy photo path
        copy($oldPhotoPath, $photoPath);

        $image = new File();
        $image->setClientName('test_image2.jpg');
        $image->setName('test_image.jpg');
        $image->setSize(111);
        $image->setType(File::IMAGE);
        $image->setPath('UserRestFrom/Images/test_image2.jpg');
        $manager->persist($image);


        // create user
        $user = new User();
        $user->setEmail('changePassword@gmail.com');
        $user->setUsername('changePassword');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('changePassword');
        $user->setLastName('LAST NAME');
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('YerevanRestTo');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarzRestTo');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setStep(3);
        $user->setSummary('summeryRestTo');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfoRestTo');
        $user->setStateVisibility(0);
        $user->setZipCodeVisibility(1);
        $user->setCraziestOutdoorAdventureVisibility(2);
        $user->setFavoriteOutdoorActivityVisibility(3);
        $user->setLikeTryTomorrowVisibility(2);
        $manager->persist($user);

        // create user
        $user = new User();
        $user->setEmail('userMassage2@gmail.com');
        $user->setUsername('userMassage2');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('userMassage2');
        $user->setLastName('awdadfdcds');
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('YerevanRestTo');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarzRestTo');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setStep(3);
        $user->setSummary('summeryRestTo');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfoRestTo');
        $manager->persist($user);

        // create user
        $user = new User();
        $user->setEmail('userMassage3@gmail.com');
        $user->setUsername('userMassage3');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('userMassage3');
        $user->setLastName('awdadfdcds');
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('YerevanRestTo');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarzRestTo');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setStep(3);
        $user->setSummary('summeryRestTo');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfoRestTo');
        $manager->persist($user);


        // create user
        $user = new User();
        $user->setEmail('adminUser@gmail.com');
        $user->setUsername('adminUser');
        $user->setEnabled(true);
        $user->setPlainPassword('superAdmin');
        $user->setFirstName('adminUser');
        $user->setLastName('adminUser');
        $user->setRoles(array('ROLE_SUPER_ADMIN'));
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('adminUserCity');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
//        $user->setState('YerevanMarzRestTo');
//        $user->setStateVisibility(3);
//        $user->setCountry('Armenia');
        $user->setStep(3);
        $user->setSummary('adminUser');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('adminUser');
        $manager->persist($user);

        // create Admin User
        $user = new User();
        $user->setEmail('admin@admin.com');
        $user->setUsername('admin');
        $user->setEnabled(true);
        $user->setPlainPassword('admin');
        $user->setFirstName('Admin');
        $user->setLastName('Admin');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setBirthday(new \DateTime('1982-11-15'));
        $user->setLookingFor(5);
        $user->setIAm(4);
        $user->setCity('Yerevan111');
        $user->setLocation($defaultCity);
        $date = new \DateTime();
        $date->modify("+1 month");
        $user->setTrialPeriod(Subscriber::UNLIMITED, $date->getTimestamp());
        $user->setSummary('summery111');
        $user->setIAgree(1);
        $user->setRegister(1);
        $user->setLastActivity(new \DateTime('yesterday'));
        $user->addInterest($interest1);
        $user->addInterest($interest2);
        $user->setPersonalInfo('personalInfo111');
        $user->setStep(3);
        $manager->persist($user);

        $manager->flush();

        $this->addReference('user', $user);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}