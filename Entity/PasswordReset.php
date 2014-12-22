<?php

namespace ScoutEvent\PasswordResetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ScoutEvent\BaseBundle\Entity\User;

/**
 * PasswordReset
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PasswordReset
{
    /**
     * @var User
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ScoutEvent\BaseBundle\Entity\User")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, unique=true)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var User
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->created = new \DateTime();
        $this->token = hash('sha512', uniqid(null, true));
    }

    /**
     * Set user
     *
     * @param User $user
     * @return PasswordReset
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return PasswordReset
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
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

}
