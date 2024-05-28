<?php

namespace LB\UserBundle\Controller\Rest;


use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use LB\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Rest\RouteResource("Profile")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class ProfileController extends FOSRestController
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Profile",
     *  description="This function is used to get about me page data",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         401="Return when user not authorized",
     *     }
     * )
     * @return Response
     * @Rest\View(serializerGroups={"profile_edit", "interestGroup", "interestGroup_interest", "interest", "show_last_name"})
     */
    public function getPageEditAction()
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user edit data
        $profileEditData = $this->get('security.token_storage')->getToken()->getUser();

        //check userId
        if(!is_object($profileEditData)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get all interest group
        $interestGroups = $em->getRepository('AppBundle:InterestGroup')->findAllOrderByPosition();

        //get current user interests
        $userInterest = $profileEditData->getInterests();

        // check interests
        if($interestGroups){

            // loop for interests
            foreach($interestGroups as $interestGroup){

                foreach($interestGroup->getInterest() as $interest)
                {
                    if($userInterest->contains($interest)) {
                        $interest->checked = true;
                    }
                }
            }
        }

        //todo remove after mobile updates
//        $profileEditData->lookingForMobile = $this->returnGenerateLookingFor($profileEditData->getLookingFor());
        $profileEditData->lookingForMobile = $profileEditData->getLookingFor();//$this->returnGenerateLookingFor($profileEditData->getLookingFor());

        return array($profileEditData, $interestGroups);
    }

    /**
     * todo remove after mobile updates
     * @deprecated
     * @param $lookingFor
     * @return array|int|mixed
     */
    private function generateLookingFor($lookingFor)
    {
        // check if is not array try to decode
        if(!is_array($lookingFor)){
            $lookingFor = json_decode($lookingFor);
        }

        // if is array after decode
        if(is_array($lookingFor)){

            if(count($lookingFor) == 1){
                $lookingFor = reset($lookingFor);
            }else{
                $lookingFor = User::BISEXUAL;
            }
        }

        return $lookingFor;
    }

//    /**
//     * todo remove after mobile updates
//     * @deprecated
//     * @param $lookingFor
//     * @return array
//     */
//    private function returnGenerateLookingFor($lookingFor)
//    {
//        // check if is array return
//        if(is_array($lookingFor)){
//
//            return $lookingFor; // looking for
//        }
//
//        switch ($lookingFor){
//            case User::BISEXUAL;
//                $lookingFor = array(User::MAN, User::WOMAN);
//                break;
//            default:
//                $lookingFor = array($lookingFor);
//                break;
//        }
//
//        return $lookingFor;
//    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Profile",
     *  description="This function is used to update base data",
     *  statusCodes={
     *         400="Bad request",
     *         200="Return when successful",
     *         401="Return when user not authorized",
     *     },
     * parameters={
     *      {"name"="firstName", "dataType"="string", "required"=true, "description"="User`s first name | min=3 / max=20 symbols"},
     *      {"name"="lastName", "dataType"="string", "required"=true, "description"="User`s last name | min=3 / max=20 symbols"},
     *      {"name"="birthday", "dataType"="string", "required"=true, "description"="User`s birthday | in this 01/12/2015 format"},
     *      {"name"="iam", "dataType"="smallint", "required"=true, "description"="User`s  gender | MAN = 4 / WOMAN= 5 / BISEXUAL=6" },
     *      {"name"="email", "dataType"="string", "required"=true, "description"="User`s  email"},
     *      {"name"="interests", "dataType"="array", "required"=false, "description"="Activities interest ids" },
     *      {"name"="summary", "dataType"="string", "required"=true, "description"="User`s summary | min=3 / max=500 symbols"},
     *      {"name"="craziestOutdoorAdventure", "dataType"="string", "required"=false, "description"="User`s craziest Outdoor Adventure " },
     *      {"name"="favoriteOutdoorActivity", "dataType"="string", "required"=false, "description"="User`s favorite Outdoor Activity" },
     *      {"name"="likeTryTomorrow", "dataType"="string", "required"=false, "description"="User`s like try tomorrow" },
     *      {"name"="personalInfo", "dataType"="string", "required"=false, "description"="User`s personal info"},
     *      {"name"="lookingFor", "dataType"="smallint", "required"=false, "description"="Hwo user looking for | MAN = 4 / WOMAN= 5" },
     *      {"name"="city", "dataType"="string", "required"=false, "description"="User`s city | min=3 / max=100 symbols"},
     *      {"name"="feet", "dataType"="smallint", "required"=false, "description"="User height feet, from 3 to 9" },
     *      {"name"="inches", "dataType"="smallint", "required"=false, "description"="User height inches from 0 to 12" },
     * }
     * )
     * @Rest\View()
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function postPageEditAction(Request $request)
    {
        // get all data
        $data = $request->request->all();

        //get user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //check if user not found
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get interest ids
        $interests = array_key_exists('interests', $data) ? str_replace('\n', '', $data['interests']) : '';
        $interests = json_decode($interests);

        $interestsIds = array_map('trim', $interests);

        //get user interest ids
        $userInterestId = $em->getRepository("AppBundle:Interest")->findIdsByUser($user->getId());

        //get user interest ids
        $userInterests = array_map(function($item){ return $item['id']; }, $userInterestId);

        //get old interest ids
        $oldIds =  array_diff($userInterests,$interestsIds);

        //get new interest ids
        $newIds = array_diff($interestsIds, $userInterests);

        //get old interests by ids
        $oldInterests = $em->getRepository("AppBundle:Interest")->findByIds($oldIds);

        foreach($oldInterests as $oldInterest)
        {
            $user->removeInterest($oldInterest);
        }

        // get interests
        $objInterests = $em->getRepository("AppBundle:Interest")->findByIds($newIds);

        // check objects
        if($objInterests){

            // loop for objects
            foreach($objInterests as $objInterest){

                $user->addInterest($objInterest);
            }
        }

        // set users data, if exist in request
        $user->setFirstName(array_key_exists('firstName', $data) ? $data['firstName'] : null);
        $user->setLastName(array_key_exists('lastName', $data) ? $data['lastName'] : null);
        $user->setBirthday(array_key_exists('birthday', $data) ? \DateTime::createFromFormat('d/m/Y', $data['birthday'])  : null);
        $user->setIAm(array_key_exists('iam', $data) ? $data['iam'] : null);
        $user->setEmail(array_key_exists('email', $data) ? $data['email'] : null);
        $user->setSummary(array_key_exists('summary', $data) ? $data['summary'] : null);
        $user->setCraziestOutdoorAdventure(array_key_exists('craziestOutdoorAdventure', $data) ? $data['craziestOutdoorAdventure'] : null);
        $user->setFavoriteOutdoorActivity(array_key_exists('favoriteOutdoorActivity', $data) ? $data['favoriteOutdoorActivity'] : null);
        $user->setLikeTryTomorrow(array_key_exists('likeTryTomorrow', $data) ? $data['likeTryTomorrow'] : null);
        $user->setPersonalInfo(array_key_exists('personalInfo', $data) ? $data['personalInfo'] : null);

        $user->setFeet(array_key_exists('feet', $data) ? $data['feet'] : null);
        $user->setInches(array_key_exists('inches', $data) ? $data['inches'] : null);

//        $user->setHeight(array_key_exists('height', $data) ? $data['height'] : null);
//        $user->setHeightUnit(array_key_exists('heightUnit', $data) ? $data['heightUnit'] : null);


        $lookingFor = array_key_exists('lookingFor', $data) ? $data['lookingFor'] : null;

//        // todo remove after mobile update
        $lookingFor = $this->generateLookingFor($lookingFor);

//        if(!is_array($lookingFor)){
//            $lookingFor = json_decode($lookingFor);
//        }
        $user->setLookingFor($lookingFor);

        $city = array_key_exists('city', $data) ? $data['city'] : null;

        // for mobile
        if(!is_array($city) && $city){

            // set location
            $user->setLocation($city);
            $city = json_decode($city, true);
            $user->setCity(array_key_exists('address', $city) ? $city['address'] : null);
        }

        // get validator
        $validator = $this->get('validator');

        //get errors
        $errors = $validator->validate($user, null, array('Base'));

        // check count of errors
        if(count($errors) > 0){

            // returned value
            $returnResult = array();

            // loop for error
            foreach($errors as $error){
                $returnResult[$error->getPropertyPath()] = $error->getMessage();
            }

            // return json response
            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();

        return new Response('', Response::HTTP_OK);
    }

}