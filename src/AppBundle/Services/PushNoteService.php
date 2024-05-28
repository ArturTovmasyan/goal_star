<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/26/15
 * Time: 4:30 PM
 */

namespace AppBundle\Services;

use LB\UserBundle\Entity\User;
use RMS\PushNotificationsBundle\Message\iOSMessage;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class PushNoteService
 * @package AppBundle\Services
 */
class PushNoteService
{
    const ANDROID = 'android';
    const IOS = 'ios';


    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected  $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param User $fromUser
     * @param User $toUser
     */
    public function sendEmailNote(User $fromUser, User $toUser)
    {
        // get translator
        $trans = $this->container->get('translator');

        // get message
        $text = $trans->trans('note.email', array('%firstName%' => $fromUser->getFirstName()), 'messages');

        $message = array('adId' =>null, 'message' => $text);

        if(!$toUser->getNotificationMessagesSwitch())return;
        // send push
        $this->sendPushNote($toUser, $message);
    }

    /**
     * @param User $fromUser
     * @param User $toUser
     */
    public function sendConnectionNote(User $fromUser, User $toUser)
    {
        // get translator
        $trans = $this->container->get('translator');

        // get message
        $text = $trans->trans('note.connection', array('%firstName%' => $fromUser->getFirstName()), 'messages');

        $message = array('adId' =>null, 'message' => $text);

        // send push
        $this->sendPushNote($toUser, $message);
    }

    /**
     * @param User $fromUser
     * @param User $toUser
     */
    public function sendLikeNote(User $fromUser, User $toUser)
    {
        // get translator
        $trans = $this->container->get('translator');

        // get message
        $text = $trans->trans('note.like', array('%firstName%' => $fromUser->getFirstName()), 'messages');

        $message = array('adId' =>null, 'message' => $text);

        if(!$toUser->getNotificationLikeSwitch())return;
        // send push
        $this->sendPushNote($toUser, $message);
    }

    /**
     * @param User $fromUser
     * @param User $toUser
     */
    public function sendVisitNote(User $fromUser, User $toUser)
    {
        // get translator
        $trans = $this->container->get('translator');

        // get message
        $text = $trans->trans('note.visit', array('%firstName%' => $fromUser->getFirstName()), 'messages');

        $message = array('adId' =>null, 'message' => $text);

        if(!$toUser->getNotificationViewsSwitch())return;
        // send push
        $this->sendPushNote($toUser, $message);
    }

    /**
     * @param User $fromUser
     * @param User $toUser
     */
    public function sendFavoriteNote(User $fromUser, User $toUser)
    {
        // get translator
        $trans = $this->container->get('translator');

        // get message
        $text = $trans->trans('note.favorite', array('%firstName%' => $fromUser->getFirstName()), 'messages');

        $message = array('adId' =>null, 'message' => $text);

        if(!$toUser->getNotificationFavoriteSwitch())return;
        // send push
        $this->sendPushNote($toUser, $message);
    }

    /**
     * Send push to androids
     *
     * @param $ids
     * @param $message
     */
    public function sendNoteToAndroid($ids, $message)
    {

        $msg = array("MessageKeyFromServer" => $message);
        $fields = array
        (
            'registration_ids' 	=> $ids,
            'data'			=> $msg
        );

        $headers = array
        (
            'Authorization: key=' . 'zzzzz',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

        curl_exec($ch);

        curl_close( $ch );
    }

    /**
     * Check and send mobiles notes
     *
     * @param $currentUser
     * @param $message
     */
    public function sendPushNote($currentUser, $message)
    {
        $androidIds = array();
        $iosIds = array();

        // if user is deactivate, return
        if($currentUser->isDisable() || !$currentUser->getNotificationSwitch()){
            return;
        }

        // check registration ids
        $registrations = $currentUser->getRegistrationIds();

        // check registrations
        if($registrations){
            // check, and get android ids, if exist
            if(array_key_exists( self::ANDROID, $registrations)){
                $androidIds = $registrations[self::ANDROID];
            }

            // check, and get ios ids, if exist
            if(array_key_exists(self::IOS, $registrations)){
                $iosIds = $registrations[self::IOS];
            }
        }

        // check androids ids
        if(count($androidIds) > 0){
            $this->sendNoteToAndroid($androidIds, $message);
        }

        // check ios ids
        if(count($iosIds) > 0){
            $this->sendNoteToIos($iosIds, $message);
        }
    }

    /**
     * Send push to ios
     *
     * @param $iosIds
     * @param $message
     */
    public function sendNoteToIos($iosIds, $message)
    {
        // get notifications
        $notifications = $this->container->get('rms_push_notifications');

        // loop for ids
        foreach($iosIds as $id){

            // check id
            if(is_numeric($id) && $id == 0){
                continue;
            }

            // create ios message
            $push = new iOSMessage();

            $push->setAPSBadge(1);
            $push->setAPSSound('default');

            // check is array
            if(is_array($message)){
                // set message
                $push->setMessage($message['message']);

                $push->setData(array('adId' => $message['adId']));
            }
            else{
                $push->setMessage($message);
            }

            // device
            $push->setDeviceIdentifier($id);

            // get pem file
            $pemFile    = $this->container->getParameter("rms_push_notifications.ios.pem");

            // set passphrase
            $passphrase =null;

            // get pem phrase
            $pemContent = file_get_contents($pemFile);

            // set content
            $notifications->setAPNSPemAsString($pemContent, $passphrase);

            // send push
            $notifications->send($push);
        }
    }

}
