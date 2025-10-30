<?php
namespace SPHERE\Application\Setting\UniventionTransfer\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\UniventionTransfer\Service\Entity\TblUniventionAccount;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\UniventionTransfer\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @return bool|TblUniventionAccount[]
     */
    public function getUniventionAccountAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUniventionAccount',
            array(TblUniventionAccount::ATTR_DLLP_SERVICE_ACCOUNT => 0)
//            ,array(Element::ENTITY_CREATE => self::ORDER_ASC)
        );
    }

    /**
     * @param string $Role
     * @return false|TblUniventionAccount[]
     */
    public function getUniventionAccountListByRole(string $Role)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUniventionAccount',
            array(
                TblUniventionAccount::ATTR_ROLE => $Role,
                TblUniventionAccount::ATTR_DLLP_SERVICE_ACCOUNT => 0
            )
            , array('Name' => self::ORDER_ASC)
        );
    }

    /**
     * @return bool|TblUniventionAccount[]
     */
    public function getUniventionAccountListByRoleLike($Role)
    {

        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $queryBuilder->select('tUA')
            ->from(__NAMESPACE__ . '\Entity\TblUniventionAccount', 'tUA')
            ->where($queryBuilder->expr()->like('tUA.Role', '?1'));
        $queryBuilder->setParameter(1, '%' . $Role . '%');

        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }

    /**
     * @return false|TblUniventionAccount
     */
    public function getUniventionAccount()
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUniventionAccount', array());
    }

    /**
     * @param string $UserName
     * @return mixed
     */
    public function getUniventionAccountByName(string $UserName)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUniventionAccount',
            array(TblUniventionAccount::ATTR_NAME => $UserName)
        );
    }


    /**
     * @param array   $ImportList
     *
     * @return bool
     */
    public function createUniventionAccountBulk(array $ImportList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Row) {
                $Entity = new TblUniventionAccount();
                $Entity->setDn($Row['Dn']);
                $Entity->setName($Row['Name']);
                $Entity->setRecordUid($Row['Record_uid']);
                $Entity->setFirstname($Row['Firstname']);
                $Entity->setLastname($Row['Lastname']);
                $Entity->setRole($Row['Role']);
                $Entity->setRoleUrl($Row['RoleUrl']);
                $Entity->setSchoolClasses($Row['School_classes']);
                $Entity->setSchools($Row['Schools']);
                $Entity->setWorkgroups($Row['Workgroups']);
                $Entity->setGuardians($Row['GuardianList']);
                $Entity->setWards($Row['WardList']);
//                $Entity->setUrl($Row['Url']);
                $Entity->setMail($Row['Mail']);
                $Entity->setMailRecovery($Row['MailRecovery']);
                $Entity->setDllpServiceAccount($Row['DllpServiceAccount']);
                $Entity->setDllpDISCH($Row['DllpDISCH']);

                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function destroyUniventionAccountAllBulk()
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblUniventionAccount')->findAll();
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }
}
