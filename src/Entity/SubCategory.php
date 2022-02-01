<?php

namespace App\Entity;

use App\Repository\SubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubCategoryRepository::class)
 */
class SubCategory
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
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="subCategory")
     */
    private $category;

    /**
     * @ORM\OneToOne(targetEntity=Promo::class, mappedBy="subCategory")
     */
    private $promo;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category[] = $category;
            $category->setSubCategory($this);
        }

        return $this;
    }



    public function getPromo(): ?Promo
    {
        return $this->promo;
    }

    public function setPromo(?Promo $promo): self
    {
        // unset the owning side of the relation if necessary
        if ($promo === null && $this->promo !== null) {
            $this->promo->setSubCategory(null);
        }

        // set the owning side of the relation if necessary
        if ($promo !== null && $promo->getSubCategory() !== $this) {
            $promo->setSubCategory($this);
        }

        $this->promo = $promo;

        return $this;
    }


}
