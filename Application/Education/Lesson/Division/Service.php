<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Setup;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return (new Data($this->getBinding()))->getLevelAll();
    }

    /**
     * @return bool|TblDivision[]
     */
    public function getDivisionAll()
    {

        return (new Data($this->getBinding()))->getDivisionAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Level
     *
     * @return IFormInterface|string
     */
    public function createLevel(
        IFormInterface $Form,
        $Level
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Level) {
            return $Form;
        }

        $Error = false;

        $tblType = Type::useService()->getTypeById($Level['Type']);

        if (isset( $Level['Name'] ) && empty( $Level['Name'] )) {
            $Form->setError('Level[Name]', 'Bitte geben Sie einen eineindeutigen Namen in Bezug auf die Schulart an');
            $Error = true;
        } else {
            if ($this->checkLevelExists($tblType, $Level['Name'])) {
                $Form->setError('Level[Name]', 'Dieser Name wird bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->createLevel(
                $tblType, $Level['Name'], $Level['Description']
            )
            ) {
                return new Success('Die Klassenstufe wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 1);
            } else {
                return new Danger('Die Klassenstufe konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param TblType $tblType
     * @param string  $Name
     *
     * @return bool
     */
    public function checkLevelExists(TblType $tblType, $Name)
    {

        return (new Data($this->getBinding()))->checkLevelExists($tblType, $Name);
    }

    /**
     * @param IFormInterface $Form
     * @param                $Division
     *
     * @return IFormInterface|string
     */
    public function createDivision(IFormInterface $Form, $Division)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Division) {
            return $Form;
        }

        $Error = false;

        $tblYear = Term::useService()->getYearById($Division['Year']);
        $tblLevel = $this->getLevelById($Division['Level']);

        if (isset( $Division['Name'] ) && empty( $Division['Name'] )) {
            $Form->setError('Division[Name]', 'Bitte geben Sie einen eineindeutigen Namen in Bezug auf die Schulart an');
            $Error = true;
        } else {
            if ($this->getDivisionByGroupAndLevelAndYear($Division['Name'], $Division['Level'], $Division['Year'])) {
                $Form->setError('Division[Name]', 'Name wird in der Klassenstufe '.$tblLevel->getName().'
                vom Jahrgang '.$tblYear->getName().' bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->createDivision(
                $tblYear, $tblLevel, $Division['Name'], $Division['Description']
            )
            ) {
                return new Success('Die KlassenGruppe wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 1);
            } else {
                return new Danger('Die KlassenGruppe konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }
    /**
     * @param int $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        return (new Data($this->getBinding()))->getLevelById($Id);
    }
    /**
     * @param $Name
     * @param $Level
     *
     * @return bool|TblDivision
     */
    public function getDivisionByGroupAndLevelAndYear($Name, $Level, $Year)
    {

        $tblLevel = $this->getLevelById($Level);
        $tblYear = Term::useService()->getYearById($Year);
        return (new Data($this->getBinding()))->getDivisionByGroupAndLevelAndYear($Name, $tblLevel, $tblYear);
    }
    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getStudentAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return TblDivisionStudent
     */
    public function insertDivisionStudent(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addDivisionStudent($tblDivision, $tblPerson);
    }

    /**
     * @param IFormInterface $Form
     * @param                $Division
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function changeDivision(IFormInterface $Form, $Division, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Division) {
            return $Form;
        }

        $Error = false;

        if (isset( $Division['Name'] ) && empty( $Division['Name'] )) {
            $Form->setError('Division[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
            $tblDivisionTest =
                Division::useService()->getDivisionByGroupAndLevelAndYear($Division['Name'], $Division['Level'], $Division['Year']);
            if ($tblDivisionTest) {
                $Form->setError('Division[Name]', 'Name schon vergeben');
                $Error = true;
            }
        }

        if (!$Error) {
            $tblDivision = Division::useService()->getDivisionById($Id);
            if ($tblDivision) {
                $tblYear = Term::useService()->getYearById($Division['Year']);
                $tblLevel = $this->getLevelById($Division['Level']);
                if ((new Data($this->getBinding()))->updateDivision(
                    $tblDivision, $tblYear, $tblLevel, $Division['Name'], $Division['Description']
                )
                ) {
                    return new Success('Die Klassengruppe wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Division/Create/Division', 3);
                } else {
                    return new Danger('Die Klassengruppe konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Division/Create/Division');
                }
            } else {
                return new Danger('Die Klassengruppe wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Division/Create/Division');
            }
        }
        return $Form;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivision
     */
    public function getDivisionById($Id)
    {

        return (new Data($this->getBinding()))->getDivisionById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    public function destroyDivision(TblDivision $tblDivision)
    {

        if (null === $tblDivision) {
            return '';
        }
        $Error = false;

        if (!$Error) {
            if ((new Data($this->getBinding()))->destroyDivision($tblDivision)) {
                return new Success('Die Klassengruppe wurde erfolgreich gelöscht')
                .new Redirect('/Education/Lesson/Division/Create/Division', 1);
            } else {
                return new Danger('Die Klassengruppe konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Division/Create/Division');
            }
        }
        return new Danger('Die Klassengruppe wird benutzt!')
        .new Redirect('/Education/Lesson/Division/Create/Division');
    }
}
