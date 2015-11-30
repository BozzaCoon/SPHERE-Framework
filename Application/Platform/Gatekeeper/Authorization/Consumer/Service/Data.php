<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createConsumer('DEMO', 'Mandant');
        $this->createConsumer('ESZC', 'Freie Evangelische Schulverein Chemnitz e.V.');
        $this->createConsumer('EGE', 'Evangelische Schulgemeinschaft Erzgebirge e.V.');
        $this->createConsumer('EVOSG', 'Ev. Oberschule Gersdorf / Christl. Schulverein e.V.');
        $this->createConsumer('ESVL', 'Ev. Schulverein Leukersdorf e.V.');
        $this->createConsumer('EVAMTL', 'Ev. Schulzentrum Muldental, Großbardau');
        $this->createConsumer('EVAP', 'Ev. Schulzentrum Pirna');
        $this->createConsumer('EZGH', 'Ev. Zinzendorf-Gymnasium Herrnhut');
        $this->createConsumer('LWSZ', 'Lebenswelt Schule e. V.');
        $this->createConsumer('FEGH', 'Freie Evangelische Grundschule Hormersdorf');

    }

    /**
     * @param string $Acronym
     * @param string $Name
     *
     * @return TblConsumer
     */
    public function createConsumer($Acronym, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblConsumer')
            ->findOneBy(array(TblConsumer::ATTR_ACRONYM => $Acronym));
        if (null === $Entity) {
            $Entity = new TblConsumer($Acronym);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblConsumer')
            ->findOneBy(array(TblConsumer::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByAcronym($Acronym)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblConsumer')
            ->findOneBy(array(TblConsumer::ATTR_ACRONYM => $Acronym));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblConsumer
     */
    public function getConsumerById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblConsumer', $Id);
    }

    /**
     * @return TblConsumer[]|bool
     */
    public function getConsumerAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblConsumer');
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblConsumer
     */
    public function getConsumerBySession($Session = null)
    {

        // 1. Level Cache
        $Memory = $this->getCache(new MemoryHandler());
        if (null === ($Entity = $Memory->getValue($Session, __METHOD__))) {

            if (false !== ($tblAccount = Account::useService()->getAccountBySession($Session))) {
                $Entity = $tblAccount->getServiceTblConsumer();
                $Memory->setValue($Session, $Entity, 0, __METHOD__);
            } else {
                $Entity = false;
            }
        }
        return $Entity;
    }
}
