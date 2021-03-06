<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DashboardBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Sonata\DashboardBundle\Entity\BlockManager;

/**
 * Class BlockManagerTest.
 */
final class BlockManagerTest extends TestCase
{
    public function testGetPager(): void
    {
        $self = $this;
        $this
            ->getBlockManager(function ($qb) use ($self): void {
                $qb->expects($self->never())->method('join');
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo([]));
            })
            ->getPager(['root' => true], 1);
    }

    private function getBlockManager($qbCallback)
    {
        $query = $this->getMockForAbstractClass('Doctrine\ORM\AbstractQuery', [], '', false, true, true, ['execute']);
        $query->expects($this->any())->method('execute')->will($this->returnValue(true));

        $qb = $this->createMock('Doctrine\ORM\QueryBuilder', [], [
            $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock(),
        ]);

        $qb->expects($this->any())->method('select')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('getQuery')->will($this->returnValue($query));
        $qb->expects($this->any())->method('getRootAliases')->will($this->returnValue([]));

        $qbCallback($qb);

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->any())->method('createQueryBuilder')->will($this->returnValue($qb));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())->method('getRepository')->will($this->returnValue($repository));

        $registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($em));

        return new BlockManager('Sonata\DashboardBundle\Entity\BaseDashboard', $registry);
    }
}
