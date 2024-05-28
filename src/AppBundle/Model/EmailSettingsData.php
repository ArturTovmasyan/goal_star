<?php

namespace AppBundle\Model;

class EmailSettingsData
{
    const NEW_MESSAGE           = 0;
    const SEND_FRIEND_REQUEST   = 1;
    const ACCEPT_FRIEND_REQUEST = 2;
    const JOIN_GROUP            = 3;
    const GROUP_UPDATE          = 4;
    const PROMOTED_GROUP        = 5;
    const JOIN_PRIVATE_GROUP    = 6;
    const FAVORITE              = 7;

    /**
     * @var
     */
    public $newMessage;

    /**
     * @var
     */
    public $sendFriendshipRequest;

    /**
     * @var
     */
    public $acceptFriendshipRequest;

    /**
     * @var
     */
    public $joinGroup;

    /**
     * @var
     */
    public $groupInfoUpdate;

    /**
     * @var
     */
    public $promotedAdminOrModerGroup;

    /**
     * @var
     */
    public $requestJoinAdminGroup;

    /**
     * @var
     */
    public $favorite;

}