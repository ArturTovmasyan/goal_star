<?php

namespace LB\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="LB\NotificationBundle\Entity\Repository\NotificationRepository")
 */
class Notification
{
    const INVITE = 0;
    const CONFIRM = 1;
    const REQUEST_TO_ADMIN = 2;
    const CONFIRM_FOR_ADMIN = 3;
    const REMOVE = 4;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"note"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="LB\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $fromUser;

    /**
     * @ORM\ManyToOne(targetEntity="LB\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="to_user_id", referencedColumnName="id", onDelete="SET NULL")
     **/
    private $toUser;

    /**
     * @ORM\Column(name="status", type="smallint", nullable=false)
     * @Groups({"note"})
     */
    private $status;

    /**
     * @ORM\Column(name="content", type="string", nullable=true)
     * @Groups({"note"})
     */
    private $content;

    /**
     * @ORM\Column(name="link", type="string", nullable=true)
     * @Groups({"note"})
     */
    private $link;

    /**
     * @ORM\Column(name="is_read", type="boolean", nullable=false)
     * @Groups({"note"})
     */
    protected $isRead = false;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     * @Groups({"note"})
     */
    private $created;


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
     * Set status
     *
     * @param integer $status
     *
     * @return Notification
     */
    public function setStatus($status)
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
     * Set content
     *
     * @param string $content
     *
     * @return Notification
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
     * Set isRead
     *
     * @param boolean $isRead
     *
     * @return Notification
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return boolean
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Notification
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set fromUser
     *
     * @param \LB\UserBundle\Entity\User $fromUser
     *
     * @return Notification
     */
    public function setFromUser(\LB\UserBundle\Entity\User $fromUser = null)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get fromUser
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set toUser
     *
     * @param \LB\UserBundle\Entity\User $toUser
     *
     * @return Notification
     */
    public function setToUser(\LB\UserBundle\Entity\User $toUser = null)
    {
        $this->toUser = $toUser;

        return $this;
    }

    /**
     * Get toUser
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Notification
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
}
