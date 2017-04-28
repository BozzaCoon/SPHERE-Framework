<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.04.2017
 * Time: 16:14
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblPrepareAdditionalGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblPrepareAdditionalGrade extends Element
{

    const ATTR_TBL_PREPARE_CERTIFICATE = 'tblPrepareCertificate';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_RANKING = 'Ranking';

    /**
     * @Column(type="bigint")
     */
    protected $tblPrepareCertificate;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="string")
     */
    protected $Grade;

    /**
     * @Column(type="integer")
     */
    protected $Ranking;

    /**
     * @return bool|TblPrepareCertificate
     */
    public function getTblPrepareCertificate()
    {

        if (null === $this->tblPrepareCertificate) {
            return false;
        } else {
            return Prepare::useService()->getPrepareById($this->tblPrepareCertificate);
        }
    }

    /**
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     */
    public function setTblPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate = null)
    {

        $this->tblPrepareCertificate = (null === $tblPrepareCertificate ? null : $tblPrepareCertificate->getId());
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {

        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return string
     */
    public function getGrade()
    {
        return $this->Grade;
    }

    /**
     * @param string $Grade
     */
    public function setGrade($Grade)
    {
        $this->Grade = $Grade;
    }

    /**
     * @return int
     */
    public function getRanking()
    {

        return $this->Ranking;
    }

    /**
     * @param int $Ranking
     */
    public function setRanking($Ranking)
    {

        $this->Ranking = $Ranking;
    }
}