<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
        fields:'email',
        message:'l\'émail est déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Returns the roles granted to the user.
     * This method is required by the UserInterface.
     * The roles returned here are used by Symfony's security system to determine
     * @var array<string> An array of roles, e.g. ['ROLE_USER', 'ROLE_ADMIN'].
     */
    public function getRoles(): array
    {
        $roleName = $this->roleUser ? $this->roleUser->getRole() : 'Utilisateur';
        
        // Mappez les noms de rôles à des rôles Symfony
        $roleMap = [
            'Administrateur' => 'ROLE_ADMIN',
            'Commercial' => 'ROLE_COMMERCIAL',
            'Marketing' => 'ROLE_MARKETING',
            'Rédacteur' => 'ROLE_REDACTEUR',
            'Utilisateur' => 'ROLE_USER',
            'En attente' => 'ROLE_PENDING'
        ];
    
        $symfonyRole = $roleMap[$roleName] ?? 'ROLE_USER';
        
        return [$symfonyRole];
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles sur l'utilisateur, effacez-les ici
    }

    /**
     * Returns the unique identifier for the user.
     * This method is required by the UserInterface.
     * It is used by Symfony's security system to identify the user.
     * @return string The unique identifier for the user, typically the email address.
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * The Unique database identifier for the user.
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idUser = null;

    /**
     * Unique email address for the user.
     * This is used as the identifier for login.
     * This field is required and cannot be blank.
     * @var string|null
     */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * Hashed password of user.
     * This field is required and cannot be blank.
     * @var string|null
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * firstname of user
     * This field is required and cannot be blank.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    /**
     * lastname of user
     * This field is required and cannot be blank.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    /**
     * Role of the user.
     * This field is required and cannot be blank.
     * It is a many-to-one relationship with the Role entity.
     * @var Role|null
     */
    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName:'id_role')]
    private ?Role $roleUser = null;

    /**
     * Unique token for email verification after registration.
     * This field is optional and can be null.
     * become null once the email is verified.
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $registrationToken = null;

    /**
     * Unique token for password reset.
     * This field is optional and can be null.
     * It is used to verify the user's identity when resetting their password.
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetPasswordToken = null;

    /**
     * Indicates whether the user's email has been verified.
     * This field is required and defaults to false.
     * This is used to ensure that the user has a valid email address before allowing certain actions.
     * This field is required and defaults to false.
     * It is true when administrator has validate the user registration.
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    /**
     * The date and time when the user registered.
     * This field is required and defaults to the current date and time.
     * It is used to track when the user created their account.
     * @var \DateTimeInterface
     */
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $registrationDate;

    /**
     * Constructor to initialize the registration date.
     * This sets the registration date to the current date and time when a new User object is created.
     */
    public function __construct()
    {
        $this->registrationDate = new \DateTime();
    }

    /**
     * Get the unique identifier of the user.
     * This is used by Symfony's security system to identify the user.
     * @return int|null The unique identifier of the user, or null if not set.
     */
    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    /**
     * Set the unique identifier of the user.
     * This is typically set by the database when the user is created.
     * @param int $idUser The unique identifier to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdUser(int $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }

    /**
     * Get the email address of the user.
     * This is used as the identifier for login.
     * @return string|null The email address of the user, or null if not set.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email address of the user.
     * This is used as the identifier for login.
     * @param string $email The email address to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the hashed password of the user.
     * This is used for authentication.
     * @return string The hashed password of the user.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the hashed password of the user.
     * This is used for authentication.
     * @param string $password The hashed password to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get the first name of the user.
     * This is used for display purposes.
     * @return string|null The first name of the user, or null if not set.
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set the first name of the user.
     * This is used for display purposes.
     * @param string $firstname The first name to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * Get the last name of the user.
     * This is used for display purposes.
     * @return string|null The last name of the user, or null if not set.
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set the last name of the user.
     * This is used for display purposes.
     * @param string $lastname The last name to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get the role of the user.
     * This is used to determine the user's permissions and access levels.
     * @return Role|null The role of the user, or null if not set.
     */
    public function getRoleUser(): ?Role
    {
        return $this->roleUser;
    }

    /**
     * Set the role of the user.
     * This is used to determine the user's permissions and access levels.
     * @param Role|null $roleUser The role to set, or null if not applicable.
     * @return static Returns the current instance for method chaining.
     */
    public function setRoleUser(?Role $roleUser): static
    {
        $this->roleUser = $roleUser;
        return $this;
    }

    /**
     * Get the registration token for email verification.
     * this is used to make unique link for administrateur validation
     * @return string|null The registration token, or null if not set.
     */
    public function getRegistrationToken(): ?string
    {
        return $this->registrationToken;
    }

    /**
     * Set the registration token for email verification.
     * This is used to make unique link for administrateur validation
     * @param string|null $registrationToken The registration token to set, or null if not applicable.
     * @return static Returns the current instance for method chaining.
     */
    public function setRegistrationToken(?string $registrationToken): static
    {
        $this->registrationToken = $registrationToken;
        return $this;
    }

    /**
     * Get the reset password token.
     * This is used to verify the user's identity when resetting their password
     * and make unique link for reset password.
     * It can be null if the user has not requested a password reset.
     * @return string|null The reset password token, or null if not set.
     */
    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    /**
     * Set the reset password token.
     * This is used to verify the user's identity when resetting their password
     * and make unique link for reset password.
     * @param string|null $resetPasswordToken The reset password token to set, or null if not applicable.
     * @return static Returns the current instance for method chaining.
     */
    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;
        return $this;
    }

    /**
     * Check if the user is verified by administrator.
     * This is used to ensure that the user has a administrator validation before allowing certain actions.
     * @return bool True if verified, false otherwise.
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * Set the verification status of the user.
     * This is used to ensure that the user has a administrator validation before allowing certain actions.
     * @param bool $isVerified True if the user is verified, false otherwise.
     * @return static Returns the current instance for method chaining.
     */
    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * Get the registration date of the user.
     * This is used to track when the user created their account.
     * @return \DateTimeInterface The registration date of the user.
     */
    public function getRegistrationDate(): \DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * Set the registration date of the user.
     * This is used to track when the user created their account.
     * @param \DateTimeInterface $registrationDate The registration date to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setRegistrationDate(\DateTimeInterface $registrationDate): static
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    /**
     * Get the full name of the user.
     * This combines the first name and last name for display purposes.
     * @return string The full name of the user.
     */
    public function getFullName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }
}
