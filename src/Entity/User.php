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
    public function getRoles(): array
    {
        $roleName = $this->role ? $this->role->getRole() : 'Utilisateur';
        
        // Mappez les noms de rôles à des rôles Symfony
        $roleMap = [
            'Administrateur' => 'ROLE_ADMIN',
            'Commercial' => 'ROLE_COMMERCIAL',
            'Marketing' => 'ROLE_MARKETING',
            'Redacteur' => 'ROLE_REDACTEUR',
            'Utilisateur' => 'ROLE_USER',
            // Ajoutez d'autres mappings si nécessaire ROLE_EDITOR
        ];
    
        $symfonyRole = $roleMap[$roleName] ?? 'ROLE_USER';
        
        return [$symfonyRole];
    }



    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles sur l'utilisateur, effacez-les ici
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;

        return $this;
    }

}



