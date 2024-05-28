<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 11/3/15
 * Time: 2:05 PM
 */
namespace LB\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class Message
 * @package LB\MessageBundle\Entity
 *
 * @ORM\Entity(repositoryClass="LB\MessageBundle\Entity\Repository\MessageRepository")
 * @ORM\Table(name="message", indexes={
 *          @ORM\Index(name="created", columns={"created"}),
 * })
 */
class Message
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"message"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="LB\MessageBundle\Model\MessageUserInterface")
     * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Groups({"message_fromUser"})
     */
    protected $fromUser;

    /**
     * @ORM\ManyToOne(targetEntity="LB\MessageBundle\Model\MessageUserInterface")
     * @ORM\JoinColumn(name="to_user_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Groups({"message_toUser"})
     */
    protected $toUser;

    /**
     * @Assert\NotBlank(message = "message.subject.not_blank")
     * @Assert\Length(max=50, maxMessage = "message.subject.maxLength")
     * @ORM\Column(name="subject", type="string", length=50, nullable=false)
     * @Groups({"message"})
     */
    protected $subject;

    /**
     * @Assert\NotBlank(message = "message.content.not_blank")
     * @Assert\Length(max=1000, maxMessage = "message.content.maxLength")
     * @ORM\Column(name="content", type="string", length=1000, nullable=false)
     * @Groups({"message"})
     */
    protected $content;

    /**
     * @ORM\Column(name="is_read", type="boolean", nullable=false)
     * @Groups({"message"})
     */
    protected $isRead = false;


    /**
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    protected $isDeleted = false;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime", nullable=false)
     * @ORM\Version
     * @Groups({"message"})
     */
    protected $created;

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
     * Set subject
     *
     * @param string $subject
     *
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Message
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
     * @return Message
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
     * @return Message
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
     * @param \LB\MessageBundle\Model\MessageUserInterface $fromUser
     *
     * @return Message
     */
    public function setFromUser(\LB\MessageBundle\Model\MessageUserInterface $fromUser = null)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get fromUser
     *
     * @return \LB\MessageBundle\Model\MessageUserInterface
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set toUser
     *
     * @param \LB\MessageBundle\Model\MessageUserInterface $toUser
     *
     * @return Message
     */
    public function setToUser(\LB\MessageBundle\Model\MessageUserInterface $toUser = null)
    {
        $this->toUser = $toUser;

        return $this;
    }

    /**
     * Get toUser
     *
     * @return \LB\MessageBundle\Model\MessageUserInterface
     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return Message
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }
}
