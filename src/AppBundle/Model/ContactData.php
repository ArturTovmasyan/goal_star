<?php

namespace AppBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ContactData
{
    /**
     * @Assert\NotBlank(message="Name should not be blank")
     */
    protected $name;

    /**
     * @Assert\NotBlank(message="Email should not be blank")
     * @Assert\Email()
     */
    protected $email;

    /**
     * @Assert\NotBlank(message="Subject should not be blank")
     */
    protected $subject;

    /**
     * @Assert\NotBlank(message="UserName should not be blank")
     */
    protected $userName;

    /**
     * @Assert\NotBlank(message="Message should not be blank")
     */
    protected $message;

    /**
     * @Assert\NotBlank(message="Device should not be blank")
     */
    protected $device;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return ContactData
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return ContactData
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return ContactData
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
     * Set userName
     *
     * @param string $userName
     *
     * @return ContactData
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return ContactData
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param mixed $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }
}