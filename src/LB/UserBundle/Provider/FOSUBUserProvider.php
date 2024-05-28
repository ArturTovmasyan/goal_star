<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/22/15
 * Time: 6:50 PM
 */
namespace LB\UserBundle\Provider;

use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\InstagramResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProvider extends BaseClass
{
    const SOCIAL_ERROR = 0;
    const IS_FROM_SOCIAL = 1;
    /**
     * @var
     */
    private $container;

    /**
     * @param UserManagerInterface $userManager
     * @param array $properties
     * @param $container
     */
    public function __construct(UserManagerInterface $userManager, array $properties, Container $container)
    {
        parent::__construct($userManager, $properties);
        $this->container = $container;

    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username, $response = null)
    {
        $container = $this->container;
        $request = $container->get('request');
        $session = $request->getSession();

        $user = null;

        // get owner
        $resourceOwner = $response->getResourceOwner();

        // check owner resource
        if($resourceOwner instanceof InstagramResourceOwner){

            // get google user
            $user = $this->createInstagramUser($response->getResponse());
        }
        elseif($resourceOwner instanceof TwitterResourceOwner){

            // get twitter user
            $user = $this->createTwitterUser($response->getResponse());
        }
        elseif($resourceOwner instanceof FacebookResourceOwner){

            $token = $response->getAccessToken();

            // get facebook user
            $user = $this->createFacebookUser($response->getResponse(), $token);
        }

        if(!$user){
            // return exception if user not found,
            throw new UnsupportedUserException(sprintf('User not found, please try again'));
        }
        elseif($user->getId()){

            // check is user enabled
            if(!$user->isEnabled()){
                $ex = new DisabledException('User account is disabled.');
                $ex->setUser($user);
                throw $ex;
            }

            return $user;
        }
        else{

            $ex = new DisabledException(self::SOCIAL_ERROR);
            $ex->setUser($user);

            $session->set(User::SESSION_NAME, $user);

            throw $ex;
        }


    }


    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {

        return $this->loadUserByUsername($response->getNickname(), $response);

    }

    /**
     * This function is used to create google user
     *
     * @param $response
     * @return User|\FOS\UserBundle\Model\UserInterface
     */
    private function createInstagramUser($response)
    {
        $data = $response['data'];

        // check is user in our bd
        $user = $this->userManager->findUserBy(array('instagramId'=>$data['id']));

        // if user not found in bd, create
        if(!$user) {

            // create new user
            $user = new User();

            // set google id
            $user->setInstagramId($data['id']);

            if(array_key_exists('email', $response)){
                // set email
                $user->setEmail($response['email']) ;
            }

            // set email
            $user->setUsername($data['id'].'-'.'instagram');

            // get fullName
            $fullName = explode(' ', $data['full_name']);

            // set first name
            $user->setFirstName($fullName[0]);

            // set last name
            $user->setLastName($fullName[1]);

            // set photo link
            $user->setSocialPhotoLink($data['profile_picture']);

            // check is birthday exist
            if(array_key_exists('birthday', $response)){

                // get birthday
                $birthday = $response['birthday'];
                $birthday = \DateTime::createFromFormat('m/d/Y',$birthday);

                $user->setBirthday($birthday);

            }
        }

        return $user;
    }


    /**
     * This function is used to create facebook user
     *
     * @param $response
     * @return User|\FOS\UserBundle\Model\UserInterface
     */
    private function createFacebookUser($response, $token = null)
    {

        // check is user in our bd
        $user = $this->userManager->findUserBy(array('facebook_id'=>$response['id']));

        // if user not found in bd, create
        if(!$user) {

            // create new user
            $user = new User();

            // set google id
            $user->setFacebookId($response['id']);

            if(array_key_exists('email', $response)){
                // set email
                $user->setEmail($response['email']) ;
            }

            // set email
            $user->setUsername($response['id'] . 'facebook');

            // get fullName
            $fullName = explode(' ', $response['name']);

            // set first name
            $user->setFirstName($fullName[0]);

            if(array_key_exists('gender', $response)){
                // get gender
                $gender = $response['gender'];

                // set gender
                $user->setIAm($gender == 'male'  ? User::MAN :User::WOMAN );
            }

            // check is birthday exist
            if(array_key_exists('birthday', $response)){

                // get birthday
                $birthday = $response['birthday'];
                $birthday = \DateTime::createFromFormat('m/d/Y',$birthday);

                $user->setBirthday($birthday);

            }

            // set last name
            $user->setLastName($fullName[1]);

            $user->setSocialPhotoLink("https://graph.facebook.com/" . $response['id'] . "/picture?type=large");

        }

        $user->setFacebookToken($token);

        return $user;
    }

    /**
     * This function is used to create Twitter user
     *
     * @param $response
     * @return User|\FOS\UserBundle\Model\UserInterface
     */
    private function createTwitterUser($response)
    {
        // check is user in our bd
        $user = $this->userManager->findUserBy(array('twitterId'=>$response['id']));

        // if user not found in bd, create
        if(!$user) {

            // create new user
            $user = new User();

            // set google id
            $user->setTwitterId($response['id']);

            if(array_key_exists('email', $response)){
                // set email
                $user->setEmail($response['email']) ;
            }

            // set email
            $user->setUsername($response['id'] . 'twitter');

            // get fullName
            $fullName = explode(' ', $response['name']);

            // set first name
            $user->setFirstName($fullName[0]);

            // set last name
            $user->setLastName($fullName[1]);

            $imageLink = $response['profile_image_url'];
            $imageLink = str_replace('_normal', '', $imageLink);

            // set photo link
            $user->setSocialPhotoLink($imageLink);

            // check is birthday exist
            if(array_key_exists('birthday', $response)){

                // get birthday
                $birthday = $response['birthday'];
                $birthday = \DateTime::createFromFormat('m/d/Y',$birthday);

                $user->setBirthday($birthday);

            }
        }

        return $user;
    }


}