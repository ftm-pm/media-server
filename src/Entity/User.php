<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User implements AdvancedUserInterface, EquatableInterface, \Serializable
{
    use TimestampableTrait;

    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var int The entity Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @ORM\Column(name="username", type="string", length=255, options={"comment": "Username"}, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ORM\Column(name="email", type="string", length=255, options={"comment": "Email"}, unique=true)
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", length=255, options={"comment": "User enabled"})
     */
    private $enabled;

    /**
     * The salt to use for hashing.
     *
     * @ORM\Column(name="salt", type="string", nullable=true, options={"comment": "Password salt"})
     */
    private $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     * @ORM\Column(name="password", type="string", options={"comment": "Password"})
     */
    private $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     * @var string
     */
    private $plainPassword;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true, options={"comment": "Last login"})
     */
    private $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", nullable=true, options={"comment": "Confirmation token"})
     */
    private $confirmationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true, options={"comment": "Password requested at"})
     */
    private $passwordRequestedAt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array", nullable=true, options={"comment": "Roles"})
     */
    private $roles;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="user")
     */
    private $images;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="user")
     */
    private $documents;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->roles = [];
        $this->images = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getUsername();
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role): User
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize([
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        $data = unserialize($serialized);

        list(
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            ) = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime|null
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): ?array
    {
        $roles = $this->roles;
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(static::ROLE_ADMIN);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role): User
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username): User
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt): User
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($boolean): User
    {
        $this->enabled = (bool)$boolean;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdmin($boolean): User
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_ADMIN);
        } else {
            $this->removeRole(static::ROLE_ADMIN);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword($password): User
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLogin(\DateTime $time = null): User
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
            $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles)
    {
        $this->roles = [];
        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @param Collection $images
     */
    public function setImages(Collection $images): void
    {
        $this->images = $images;
    }

    /**
     * @return Collection
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param Collection $documents
     */
    public function setDocuments(Collection $documents): void
    {
        $this->documents = $documents;
    }
}