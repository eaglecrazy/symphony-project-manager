<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_users", uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"email"}),
 *      @ORM\UniqueConstraint(columns={"reset_token_token"}),
 *     })
 */
class User
{
    public const STATUS_WAIT   = 'wait';
    public const STATUS_ACTIVE = 'active';

    /**
     * @var Id
     * @ORM\Id
     * @ORM\Column (type="user_user_id")
     */
    private $id;

    /**
     * @var DateTimeImmutable
     * @ORM\Column (type="datetime_immutable")
     */
    private $date;

    /**
     * @var Email|null
     * @ORM\Column (type="user_user_email", nullable=true)
     */
    private $email;

    /**
     * @var string|null
     * @ORM\Column (type="string", nullable=true, name="password_hash")
     */
    private $passwordHash;

    /**
     * @var string|null
     * @ORM\Column (type="string", nullable=true, name="confirm_token")
     */
    private $confirmToken;

    /**
     * @var Name
     * @ORM\Embedded(class="Name")
     */
    private $name;

    /**
     * @var Email|null
     * @ORM\Column(type="user_user_email", name="new_email", nullable=true)
     */
    private $newEmail;

    /**
     * @var string|null
     * @ORM\Column(type="string", name="new_email_token", nullable=true)
     */
    private $newEmailToken;

    /**
     * @var string
     * @ORM\Column (type="string", length=16)
     */
    private $status;

    /**
     * @var Network[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Network", mappedBy="user", orphanRemoval=true, cascade="persist")
     */
    private $networks;

    /**
     * @var ResetToken
     * @ORM\Embedded(class="ResetToken", columnPrefix="reset_token_")
     */
    private $resetToken;

    /**
     * @var Role
     * @ORM\Column(type="user_user_role", length=16)
     */
    private $role;

    private function __construct(Id $id, DateTimeImmutable $date, Name $name)
    {
        $this->id       = $id;
        $this->date     = $date;
        $this->name     = $name;
        $this->networks = new ArrayCollection();

        $this->role = Role::user();
    }

    /**
     * @param Id $id
     * @param DateTimeImmutable $date
     * @param Name $name
     * @param Email $email
     * @param string $hash
     * @param string $token
     * @return User
     */
    public static function signUpByEmail(
        Id $id,
        DateTimeImmutable $date,
        Name $name,
        Email $email,
        string $hash,
        string $token
    ): self {
        $user = new self ($id, $date, $name);

        $user->email        = $email;
        $user->passwordHash = $hash;
        $user->confirmToken = $token;
        $user->status       = self::STATUS_WAIT;

        return $user;
    }

    /**
     *
     */
    public function confirmSignUp(): void
    {
        if (!$this->isWait()) {
            throw new DomainException('User is already confirmed.');
        }

        $this->status       = self::STATUS_ACTIVE;
        $this->confirmToken = null;
    }

    /**
     * @param Id $id
     * @param DateTimeImmutable $date
     * @param Name $name
     * @param string $network
     * @param string $identity
     * @return static
     */
    public static function signUpByNetwork(
        Id $id,
        DateTimeImmutable $date,
        Name $name,
        string $network,
        string $identity
    ): self {
        $user = new self($id, $date, $name);

        $user->attachNetwork($network, $identity);
        $user->status = self::STATUS_ACTIVE;

        return $user;
    }

    /**
     * @return Id
     */
    public function getId(): Id
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return string
     */
    public function getConfirmToken(): ?string
    {
        return $this->confirmToken;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getNewEmail(): ?Email
    {
        return $this->newEmail;
    }

    public function getNewEmailToken(): ?string
    {
        return $this->newEmailToken;
    }

    /**
     * @return bool
     */
    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @param string $network
     * @param string $identity
     */
    public function attachNetwork(string $network, string $identity): void
    {
        foreach ($this->networks as $existing) {
            /** @var Network $existing */
            if ($existing->isForNetwork($network)) {
                throw new DomainException('Network is already attached.');
            }
        }

        $this->networks->add(new Network($this, $network, $identity));
    }

    /**
     * @param string $network
     * @param string $identity
     */
    public function detachNetwork(string $network, string $identity): void
    {
        foreach ($this->networks as $existing) {
            /** @var Network $existing */
            if ($existing->isFor($network, $identity)) {
                if (!$this->email && $this->networks->count() === 1) {
                    throw new DomainException('Unable to detach the last identity');
                }

                $this->networks->removeElement($existing);

                return;
            }
        }

        throw new DomainException('Network is not attached.');
    }

    /**
     * @return Network[]
     */
    public function getNetworks(): array
    {
        return $this->networks->toArray();
    }

    /**
     * @param ResetToken $token
     * @param DateTimeImmutable $date
     */
    public function requestPasswordReset(ResetToken $token, DateTimeImmutable $date): void
    {
        if (!$this->email) {
            throw new DomainException('Email is not specified.');
        }

        if (!$this->isActive()) {
            throw new DomainException('User is not active.');
        }

        if ($this->resetToken && !$this->resetToken->isExpiredTo($date)) {
            throw new DomainException('Resetting is already requested.');
        }

        $this->resetToken = $token;
    }

    /**
     * @return ResetToken
     */
    public function getResetToken(): ?ResetToken
    {
        return $this->resetToken;
    }

    public function passwordReset(DateTimeImmutable $date, string $hash)
    {
        if (!$this->resetToken) {
            throw new DomainException('Resetting is not requested.');
        }

        if ($this->resetToken->isExpiredTo($date)) {
            throw new DomainException('Reset token is expired.');
        }

        $this->passwordHash = $hash;
        $this->resetToken   = null;
    }

    /**
     * @param \App\Model\User\Entity\User\Email $email
     * @param string $token
     * @return void
     */
    public function requestEmailChanging(Email $email, string $token)
    {
        if (!$this->isActive()) {
            throw new DomainException('User is not active.');
        }

        if ($this->email && $this->email->isEqual($email)) {
            throw new DomainException('Email is already same');
        }

        $this->newEmail      = $email;
        $this->newEmailToken = $token;
    }

    /**
     * @param string $token
     * @return void
     */
    public function confirmEmailChanging(string $token)
    {
        if (!$this->newEmailToken) {
            throw new DomainException('Changing is not requested');
        }

        if ($this->newEmailToken !== $token) {
            throw new DomainException('Incorrect changing token');
        }

        $this->email         = $this->newEmail;
        $this->newEmail      = null;
        $this->newEmailToken = null;
    }

    public function changeName(Name $name)
    {
        $this->name = $name;
    }

    public function changeRole(Role $role)
    {
        if ($this->role->isEqual($role)) {
            throw new DomainException('Role is already same.');
        }

        $this->role = $role;
    }

    /**
     * @return Role
     */
    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * @ORM\PostLoad()
     */
    public function checkEmbeds(): void
    {
        if ($this->resetToken->isEmpty()) {
            $this->resetToken = null;
        }
    }
}
