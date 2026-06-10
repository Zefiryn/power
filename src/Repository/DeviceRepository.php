<?php

namespace App\Repository;

use App\Entity\Device;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Device>
 *
 * @method Device|null find($id, $lockMode = null, $lockVersion = null)
 * @method Device|null findOneBy(array $criteria, array $orderBy = null)
 * @method Device[]    findAll()
 * @method Device[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    public function findDevices(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.id', 'DESC')
            ->getQuery();
    }

    public function resetCurrentDevices(int $currentDeviceId): void
    {
        $this->createQueryBuilder('d')
            ->update()
            ->set('d.isCurrent', 'false')
            ->where('d.id != :currentDeviceId')
            ->setParameter('currentDeviceId', $currentDeviceId)
            ->getQuery()
            ->execute();
    }
}
