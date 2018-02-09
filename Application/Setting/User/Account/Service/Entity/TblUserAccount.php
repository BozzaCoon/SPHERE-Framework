<?php
namespace SPHERE\Application\Setting\User\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblUserAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblUserAccount extends Element
{

    const VALUE_TYPE_STUDENT = 'STUDENT';
    const VALUE_TYPE_CUSTODY = 'CUSTODY';

    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TYPE = 'Type';
    const ATTR_USER_PASSWORD = 'UserPassword';
    const ATTR_ACCOUNT_PASSWORD = 'AccountPassword';
    const ATTR_EXPORT_DATE = 'ExportDate';
    const ATTR_LAST_DOWNLOAD_ACCOUNT = 'LastDownloadAccount';
    const ATTR_GROUP_BY_TIME = 'GroupByTime';
    const ATTR_GROUP_BY_COUNT = 'GroupByCount';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $Type;
    /**
     * @Column(type="string")
     */
    protected $UserPassword;
    /**
     * @Column(type="string")
     */
    protected $AccountPassword;
    /**
     * @Column(type="datetime")
     */
    protected $ExportDate;
    /**
     * @Column(type="string")
     */
    protected $LastDownloadAccount;
    /**
     * @Column(type="datetime")
     */
    protected $GroupByTime;
    /**
     * @Column(type="integer")
     */
    protected $GroupByCount;

    /**
     * @return false|TblPerson
     */
    public function getServiceTblPerson()
    {

        $tblPerson = ($this->serviceTblPerson != null
            ? Person::useService()->getPersonById($this->serviceTblPerson)
            : false);
        if ($tblPerson) {
            return $tblPerson;
        }
        return false;
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return false|TblAccount
     */
    public function getServiceTblAccount()
    {

        $tblAccount = ($this->serviceTblAccount != null
            ? Account::useService()->getAccountById($this->serviceTblAccount)
            : false);
        if ($tblAccount) {
            return $tblAccount;
        }
        return false;
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param mixed $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }

    /**
     * @return string
     */
    public function getUserPassword()
    {
        return $this->UserPassword;
    }

    /**
     * @param string $UserPassword
     */
    public function setUserPassword($UserPassword)
    {
        $this->UserPassword = $UserPassword;
    }

    /**
     * @return mixed
     */
    public function getAccountPassword()
    {
        return $this->AccountPassword;
    }

    /**
     * @param mixed $AccountPassword
     */
    public function setAccountPassword($AccountPassword)
    {
        $this->AccountPassword = $AccountPassword;
    }

    /**
     * @return mixed
     */
    public function getExportDate()
    {
        /** @var \DateTime $ExportDate */
        $ExportDate = $this->ExportDate;
        if ($ExportDate instanceof \DateTime) {
            return $ExportDate->format('d.m.Y H:i:s');
        }
        return false;
    }

    /**
     * @param mixed $ExportDate
     */
    public function setExportDate($ExportDate)
    {
        $this->ExportDate = $ExportDate;
    }

    /**
     * @return string
     */
    public function getLastDownloadAccount()
    {
        return $this->LastDownloadAccount;
    }

    /**
     * @param string $LastDownloadAccount
     */
    public function setLastDownloadAccount($LastDownloadAccount)
    {
        $this->LastDownloadAccount = $LastDownloadAccount;
    }

    /**
     * @return bool|string
     */
    public function getGroupByTime()
    {

        /** @var \DateTime $GroupByTime */
        $GroupByTime = $this->GroupByTime;
        if ($GroupByTime instanceof \DateTime) {
            return $GroupByTime->format('d.m.Y H:i:s');
        }
        return false;
    }

    /**
     * @param \DateTime $DateTime
     */
    public function setGroupByTime($DateTime)
    {

        $this->GroupByTime = $DateTime;
    }

    /**
     * @return int|null
     */
    public function getGroupByCount()
    {
        return $this->GroupByCount;
    }

    /**
     * @param int|null $GroupByCount
     */
    public function setGroupByCount($GroupByCount)
    {
        $this->GroupByCount = $GroupByCount;
    }
}
