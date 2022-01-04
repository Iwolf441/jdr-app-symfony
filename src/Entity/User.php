<?php

namespace App\Entity;
use App\Entity\Book;
use App\Entity\Commentary;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mail;

    /**
     * @ORM\Column(type="date")
     */
    private $inscriptionDate;

    /**
     * @ORM\OneToMany(targetEntity=commentary::class, mappedBy="user")
     */
    private $commentaries;

    private $plainpassword;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\ManyToMany(targetEntity=Book::class, inversedBy="users")
     */
    private $collection;

    /**
     * @ORM\OneToOne(targetEntity=Photo::class, inversedBy="user", cascade={"persist", "remove"})
     */
    private $profilePicture;

    /**
     * @return mixed
     */
    public function getPlainpassword()
    {
        return $this->plainpassword;
    }
    /**
     * @param mixed $plainpassword
     */
    public function setPlainpassword($plainpassword): void
    {
        $this->plainpassword = $plainpassword;
    }

    public function __construct()
    {
        $this->commentaries = new ArrayCollection();
        $this->collection = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getInscriptionDate(): ?\DateTimeInterface
    {
        return $this->inscriptionDate;
    }

    public function setInscriptionDate(\DateTimeInterface $inscriptionDate): self
    {
        $this->inscriptionDate = $inscriptionDate;

        return $this;
    }
    
    /**
     * @return Collection|commentary[]
     */
    public function getCommentaries(): Collection
    {
        return $this->commentaries;
    }

    public function addCommentary(commentary $commentary): self
    {
        if (!$this->commentaries->contains($commentary)) {
            $this->commentaries[] = $commentary;
            $commentary->setUser($this);
        }

        return $this;
    }

    public function removeCommentary(commentary $commentary): self
    {
        if ($this->commentaries->removeElement($commentary)) {
            // set the owning side to null (unless already changed)
            if ($commentary->getUser() === $this) {
                $commentary->setUser(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        $this->plainpassword=null;
    }

    public function getUsername():string
    {
        return $this->pseudo;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }
    
    public function getUserIdentifier(): string
    {
        return $this->getMail();
    }

    /**
     * @return Collection|Book[]
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function addCollection(Book $collection): self
    {
        if (!$this->collection->contains($collection)) {
            $this->collection[] = $collection;
        }

        return $this;
    }

    public function removeCollection(Book $collection): self
    {
        $this->collection->removeElement($collection);

        return $this;
    }

    public function getProfilePicture(): ?Photo
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?Photo $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }
}
