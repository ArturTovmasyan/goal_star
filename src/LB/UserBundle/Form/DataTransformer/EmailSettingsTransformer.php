<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/28/15
 * Time: 1:30 PM
 */

namespace LB\UserBundle\Form\DataTransformer;

use AppBundle\Model\EmailSettingsData;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EmailSettingsTransformer implements DataTransformerInterface
{
    private $container;
    private $currentUser;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->currentUser = $container->get('security.token_storage')->getToken()->getUser();
    }


    public function transform($emailSettings)
    {
        $emailSettingsArray = $this->currentUser->getEmailSettings();


        if(count($emailSettingsArray) == 0){
            return $emailSettings;
        }

        $emailSettings->acceptFriendshipRequest = $emailSettingsArray['acceptFriendshipRequest'];
        $emailSettings->groupInfoUpdate = $emailSettingsArray['groupInfoUpdate'];
        $emailSettings->joinGroup = $emailSettingsArray['joinGroup'];
        $emailSettings->newMessage = $emailSettingsArray['newMessage'];
        $emailSettings->requestJoinAdminGroup = $emailSettingsArray['requestJoinAdminGroup'];
        $emailSettings->sendFriendshipRequest = $emailSettingsArray['sendFriendshipRequest'];
        $emailSettings->promotedAdminOrModerGroup = $emailSettingsArray['promotedAdminOrModerGroup'];

        return $emailSettings;
    }

    /**
     * @param mixed $emailSettings
     * @return array|mixed
     */

    public function reverseTransform($emailSettings)
    {

        $emailSettingsArray = array(
            'acceptFriendshipRequest' => $emailSettings->acceptFriendshipRequest,
            'groupInfoUpdate' => $emailSettings->groupInfoUpdate,
            'joinGroup' => $emailSettings->joinGroup,
            'newMessage' => $emailSettings->newMessage,
            'requestJoinAdminGroup' => $emailSettings->requestJoinAdminGroup,
            'sendFriendshipRequest' => $emailSettings->sendFriendshipRequest,
            'promotedAdminOrModerGroup' => $emailSettings->promotedAdminOrModerGroup
        );

        $this->currentUser->setEmailSettings($emailSettingsArray);

        return $emailSettingsArray;
    }
}