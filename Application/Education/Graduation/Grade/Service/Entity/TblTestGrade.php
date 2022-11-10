<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTestGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblTestGrade extends Element
{
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationTest;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $Date = null;
    /**
     * @Column(type="string")
     */
    protected ?string $Grade = null;
    /**
     * @Column(type="string")
     */
    protected ?string $Comment = null;
    /**
     * @Column(type="string")
     */
    protected ?string $PublicComment = null;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblPersonTeacher = null;

    /**
     * @param bool $IsForce
     *
     * @return false|TblPerson
     */
    public function getServiceTblPerson(bool $IsForce = false)
    {
        return Person::useService()->getPersonById($this->serviceTblPerson, $IsForce);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson)
    {
        $this->serviceTblPerson = $tblPerson->getId();
    }

    /**
     * @return TblTest
     */
    public function getTblTest(): TblTest
    {
        return Grade::useService()->getTestById($this->tblGraduationTest);
    }

    /**
     * @param TblTest $tblTest
     */
    public function setTblTest(TblTest $tblTest)
    {
        $this->tblGraduationTest = $tblTest->getId();
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->Date;
    }

    /**
     * @return string
     */
    public function getDateString(): string
    {
        return $this->Date instanceof DateTime ? $this->Date->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $Date
     */
    public function setDate(?DateTime $Date): void
    {
        $this->Date = $Date;
    }

    /**
     * @return string|null
     */
    public function getGrade(): ?string
    {
        return $this->Grade;
    }

    /**
     * @param string|null $Grade
     */
    public function setGrade(?string $Grade): void
    {
        $this->Grade = $Grade;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->Comment;
    }

    /**
     * @param string|null $Comment
     */
    public function setComment(?string $Comment): void
    {
        $this->Comment = $Comment;
    }

    /**
     * @return string|null
     */
    public function getPublicComment(): ?string
    {
        return $this->PublicComment;
    }

    /**
     * @param string|null $PublicComment
     */
    public function setPublicComment(?string $PublicComment): void
    {
        $this->PublicComment = $PublicComment;
    }

    /**
     * @param bool $IsForce
     *
     * @return false|TblPerson
     */
    public function getServiceTblPersonTeacher(bool $IsForce = false)
    {
        return Person::useService()->getPersonById($this->serviceTblPersonTeacher, $IsForce);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPersonTeacher(TblPerson $tblPerson)
    {
        $this->serviceTblPersonTeacher = $tblPerson->getId();
    }
}