<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/15
 * Time: 8:13 AM
 */
namespace LB\UserBundle\Handler;

use JMS\Serializer\SerializerBuilder;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Provider\FOSUBUserProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class AuthenticationHandler
 * @package AppBundle\Handler
 */
class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return RedirectResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if($user)
//        if($user && $user->getZipCode())
        {
            // create mailchimp user Data
            $mailchimpData = [
                'email'     => $user->getEmail(),
                'status'    => 'subscribed',
                'firstname' => $user->getFirstName(),
                'lastname'  => $user->getLastName(),
                'birthday'  => $user->getBirthday() ?$user->getBirthday()->format('m/d/Y') : null,
                'zip_code'  => $user->getZipCode() ? $user->getZipCode() : ''
            ];

            // connect to mailchimp api service for create subscriber
            $this->container->get('app.mailchimp')->syncMailchimp($mailchimpData);
        }

        // get router
        $router = $this->container->get('router');
        $phpSessionId = $request->cookies->get('PHPSESSID');

        // Otherwise, redirect him profile show
        $url = $router->generate('members');

        if($user->getSearchVisibility()){
            // Otherwise, redirect him to disable account
            $url = $router->generate('disable_account');
        }

        // check request method
        if ($request->isXmlHttpRequest()) {


           $content = array(
                'sessionId' => $phpSessionId,
                'userInfo' => $token->getUser()
            );


            $serializer = SerializerBuilder::create()->build();
            $jsonContent = $serializer->serialize($content, 'json');

            // create response
            $response =  new Response(Response::HTTP_OK);
            $response->setContent($jsonContent);

            $response->headers->set('Content-Type', 'application/json');

            // return response
            return $response;
        }
        else {

            return new RedirectResponse($url);
        }
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // check request method
        if ($request->isXmlHttpRequest()) {

            // create response
            $response =  new Response('', Response::HTTP_BAD_REQUEST);

            $response->setContent(json_encode(array('message'=>'Incorrect Email or Password')));
            // set header
            $response->headers->set('Content-Type', 'application/json');

            // return response
            return $response;
        }
        else {

            // check exception error
            if($exception instanceof DisabledException && $exception->getMessage() == FOSUBUserProvider::SOCIAL_ERROR){
                // get session
                $session = $request->getSession();
                $user = $session->get(User::SESSION_NAME);

                // check is user in session
                if($user){

                    $session->set(FOSUBUserProvider::IS_FROM_SOCIAL, true);
                    return new RedirectResponse($this->container->get('router')->generate('fos_user_registration_register'));
                }
            }

            // set flush message
            $request->getSession()->getFlashBag()->add('error', $exception->getMessage());

            // set url
            $url = $this->container->get('router')->generate('fos_user_security_login');

            // redirect
            return new RedirectResponse($url);
        }
    }
}