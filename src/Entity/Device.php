<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\Table(name: 'devices')]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'devices_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isCurrent = true;

    /**
     * @var Collection<int, Reading>
     */
    #[ORM\OneToMany(targetEntity: Reading::class, mappedBy: 'device')]
    private Collection $readings;

    public function __construct()
    {
        $this->readings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;

        return $this;
    }

    /**
     * @return Collection<int, Reading>
     */
    public function getReadings(): Collection
    {
        return $this->readings;
    }

    public function addReading(Reading $reading): static
    {
        if (!$this->readings->contains($reading)) {
            $this->readings->add($reading);
            $reading->setDevice($this);
        }

        return $this;
    }

    public function removeReading(Reading $reading): static
    {
        if ($this->readings->removeElement($reading)) {
            // set the owning side to null (unless already changed)
            if ($reading->getDevice() === $this) {
                $reading->setDevice(null);
            }
        }

        return $this;
    }
}
