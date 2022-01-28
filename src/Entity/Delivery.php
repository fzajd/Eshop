<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeliveryRepository::class)
 */
class Delivery
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="date")
     */
    private $predictedDate;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, mappedBy="delivery", cascade={"persist", "remove"})
     */
    private $orders;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPredictedDate(): ?\DateTimeInterface
    {
        return $this->predictedDate;
    }

    public function setPredictedDate(\DateTimeInterface $predictedDate): self
    {
        $this->predictedDate = $predictedDate;

        return $this;
    }

    public function getOrders(): ?Order
    {
        return $this->orders;
    }

    public function setOrders(?Order $orders): self
    {
        // unset the owning side of the relation if necessary
        if ($orders === null && $this->orders !== null) {
            $this->orders->setDelivery(null);
        }

        // set the owning side of the relation if necessary
        if ($orders !== null && $orders->getDelivery() !== $this) {
            $orders->setDelivery($this);
        }

        $this->orders = $orders;

        return $this;
    }


}
