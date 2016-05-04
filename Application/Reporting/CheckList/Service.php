<?php


namespace SPHERE\Application\Reporting\CheckList;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup as CompanyGroupEntity;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Group as PersonGroup;
use SPHERE\Application\People\Group\Service\Entity\TblGroup as PersonGroupEntity;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\CheckList\Service\Data;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblElementType;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListObjectElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListObjectList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblObjectType;
use SPHERE\Application\Reporting\CheckList\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\CheckList
 */
class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblList[]
     */
    public function getListAll()
    {

        return (new Data($this->getBinding()))->getListAll();
    }

    /**
     * @return false|TblObjectType[]
     */
    public function getObjectTypeAll()
    {

        return (new Data($this->getBinding()))->getObjectTypeAll();
    }

    /**
     * @param TblList $tblList
     * @param TblObjectType $tblObjectType
     * @param Element $tblObject
     *
     * @return bool|TblListObjectList
     */
    public function getListObjectListByListAndObjectTypeAndObject(
        TblList $tblList,
        TblObjectType $tblObjectType,
        Element $tblObject
    ) {

        return (new Data($this->getBinding()))->getListObjectListByListAndObjectTypeAndObject(
            $tblList, $tblObjectType, $tblObject
        );
    }

    /**
     * @param TblList $tblList
     * @param TblObjectType $tblObjectType
     *
     * @return bool|Element[]
     */
    public function getObjectAllByListAndObjectType(TblList $tblList, TblObjectType $tblObjectType)
    {

        return (new Data($this->getBinding()))->getObjectAllByListAndObjectType($tblList, $tblObjectType);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblElementType
     */
    public function getElementTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getElementTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblElementType[]
     */
    public function getElementTypeAll()
    {

        return (new Data($this->getBinding()))->getElementTypeAll();
    }

    /**
     * @param TblList $tblList
     *
     * @return int
     */
    public function countListElementListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->countListElementListByList($tblList);
    }

    /**
     * @param TblList $tblList
     *
     * @return int
     */
    public function countListObjectListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->countListObjectListByList($tblList);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $List
     *
     * @return IFormInterface|string
     */
    public function createList(IFormInterface $Stage = null, $List)
    {

        /**
         * Skip to Frontend
         */
        if (null === $List) {
            return $Stage;
        }

        $Error = false;
        if (isset($List['Name']) && empty($List['Name'])) {
            $Stage->setError('List[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } elseif (isset($List['Name']) && $this->getListByName(trim($List['Name']))) {
            $Stage->setError('List[Name]', 'Der Name ist schon vorhanden. Bitte geben sie einen anderen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createList(
                trim($List['Name']),
                trim($List['Description'])
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Check-Liste ist erfasst worden')
            . new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param $Name
     *
     * @return bool|TblList
     */
    public function getListByName($Name)
    {

        return (new Data($this->getBinding()))->getListByName($Name);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $List
     * @return IFormInterface|string
     */
    public function updateList(IFormInterface $Stage = null, $Id, $List)
    {

        /**
         * Skip to Frontend
         */
        if (null === $List || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($List['Name']) && empty($List['Name'])) {
            $Stage->setError('List[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblList = $this->getListById($Id);
        if (!$tblList) {
            return new Danger(new Ban() . ' Check-List nicht gefunden')
            . new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateList(
                $tblList,
                $List['Name'],
                $List['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Check-List ist erfolgreich gespeichert worden')
            . new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblList
     */
    public function getListById($Id)
    {

        return (new Data($this->getBinding()))->getListById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $Id
     * @param                     $Element
     *
     * @return IFormInterface|string
     */
    public function addElementToList(IFormInterface $Stage = null, $Id, $Element)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Id || null === $Element) {
            return $Stage;
        }

        $Error = false;
        if (isset($Element['Name']) && empty($Element['Name'])) {
            $Stage->setError('Element[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
            $Stage->setSuccess('Element[Name]');
        }
        if (!($tblElementType = $this->getElementTypeById($Element['Type']))){
            $Stage->setError('Element[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Stage->setSuccess('Element[Type]');
        }

        if (!$Error) {
            (new Data($this->getBinding()))->addElementToList(
                $this->getListById($Id),
                $tblElementType,
                $Element['Name']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Das Element ist zur Check-Liste hingefügt worden.')
            . new Redirect('/Reporting/CheckList/Element/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $Id));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblElementType
     */
    public function getElementTypeById($Id)
    {

        return (new Data($this->getBinding()))->getElementTypeById($Id);
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function removeElementFromList($Id = null)
    {

        $Stage = new Stage('Check-Listen', 'Ein Element von einer Check-Liste entfernen');

        if (!$Id) {
            return $Stage . new Danger(new Ban() . ' Element nicht gefunden')
            . new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }
        $tblListElementList = $this->getListElementListById($Id);
        if (!$tblListElementList) {
            return $Stage . new  Danger(new Ban() . ' Element nicht gefunden')
            . new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }

        $tblList = $tblListElementList->getTblList();
        $Stage = new Stage('Check-Listen', 'Element entfernen');
        if ((new Data($this->getBinding()))->removeElementFromList($tblListElementList)) {
            return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Das Element ist von Check-Liste entfernt worden.')
            . new Redirect('/Reporting/CheckList/Element/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblList->getId()));
        } else {
            return $Stage . new Danger(new Ban() . ' Das Element konnte nicht von Check-Liste entfernt werden.')
            . new Redirect('/Reporting/CheckList/Element/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblList->getId()));
        }
    }

    /**
     * @param $Id
     *
     * @return bool|TblListElementList
     */
    public function getListElementListById($Id)
    {

        return (new Data($this->getBinding()))->getListElementListById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $ListId
     * @param null $ObjectTypeSelect
     *
     * @return IFormInterface|Redirect|string
     */
    public function getObjectType(IFormInterface $Stage = null, $ListId = null, $ObjectTypeSelect = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ListId || null === $ObjectTypeSelect) {
            return $Stage;
        }

        $Error = false;
        if (!isset($ObjectTypeSelect['Id'])) {
            $Error = true;
            $Stage .= new Warning('Objekt-Typ nicht gefunden');
        }

        if ($Error) {
            return $Stage;
        }

        $tblList = $this->getListById($ListId);
        $tblObjectType = $this->getObjectTypeById($ObjectTypeSelect['Id']);

        if ($tblObjectType) {
            return new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS, array(
                'ListId' => $tblList->getId(),
                'ObjectTypeId' => $tblObjectType->getId()
            ));
        } else {
            return $Stage . new Warning('Bitte wählen Sie einen Typ aus', new Exclamation());
        }
    }

    /**
     * @param $Id
     *
     * @return bool|TblObjectType
     */
    public function getObjectTypeById($Id)
    {

        return (new Data($this->getBinding()))->getObjectTypeById($Id);
    }

    /**
     * @param TblList $tblList
     * @param TblObjectType $tblObjectType
     * @param Element $tblObject
     *
     * @return TblListElementList
     */
    public function addObjectToList(
        TblList $tblList,
        TblObjectType $tblObjectType,
        Element $tblObject
    ) {

        return (new Data($this->getBinding()))->addObjectToList($tblList, $tblObjectType, $tblObject);
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function removeObjectFromList($Id = null)
    {

        $Stage = new Stage('Check-Listen', 'Ein Object von einer Check-Liste entfernen');

        if (!$Id) {
            return $Stage . new Danger(new Ban() . ' Objekt nicht gefunden')
            . new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }
        $tblListObjectList = $this->getListObjectListById($Id);
        if (!$tblListObjectList) {
            return $Stage . new Danger(new Ban() . ' Objekt nicht gefunden')
            . new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }

        $tblList = $tblListObjectList->getTblList();
        $tblObjectType = $tblListObjectList->getTblObjectType();
        if ((new Data($this->getBinding()))->removeObjectFromList($tblListObjectList)) {
            return $Stage . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                ' Die ' . $tblObjectType->getName() . ' ist von Check-Liste entfernt worden.')
            . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
        } else {
            return $Stage . new Danger(new Ban() .
                ' Die ' . $tblObjectType->getName() . ' konnte nicht von Check-Liste entfernt werden.')
            . new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
        }
    }

    /**
     * @param $Id
     *
     * @return bool|TblListObjectList
     */
    public function getListObjectListById($Id)
    {

        return (new Data($this->getBinding()))->getListObjectListById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Id
     * @param null $Data
     * @param null $HasData
     * @param null $objectList
     * @param null $YearPersonId
     * @param null $LevelPersonId
     * @param null $SchoolOptionId
     *
     * @return IFormInterface|Redirect
     */
    public function updateListObjectElementList(
        IFormInterface $Stage = null,
        $Id = null,
        $Data = null,
        $HasData = null,
        $objectList = null,
        $YearPersonId = null,
        $LevelPersonId = null,
        $SchoolOptionId = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Id || (null === $Data && null === $HasData)) {
            return $Stage;
        }

        $tblList = $this->getListById($Id);

        // Reset CheckBoxen
        if ($objectList === null) {
            $tblListObjectElementListByList = $this->getListObjectElementListByList($tblList);
            if ($tblListObjectElementListByList) {
                foreach ($tblListObjectElementListByList as $tblListObjectElementList) {
                    if ($tblListObjectElementList->getServiceTblObject()) {
                        if (!isset($Data[$tblListObjectElementList->getTblObjectType()->getId()]
                            [$tblListObjectElementList->getServiceTblObject()->getId()]
                            [$tblListObjectElementList->getTblListElementList()->getId()])
                        ) {
                            (new Data($this->getBinding()))->updateObjectElementToList(
                                $tblList,
                                $tblListObjectElementList->getTblObjectType(),
                                $tblListObjectElementList->getTblListElementList(),
                                $tblListObjectElementList->getServiceTblObject(),
                                ''
                            );
                        }
                    }
                }
            }
        } else {
            // prospect with filter
            if (!empty($objectList)) {
                foreach ($objectList as $objectTypeId => $list) {
                    $tblObjectType = $this->getObjectTypeById($objectTypeId);
                    if ($tblObjectType->getId() == $this->getObjectTypeByIdentifier('PERSON')->getId()) {
                        if (is_array($list) && !empty($list)) {
                            foreach ($list as $objectId => $value) {
                                $tblPerson = Person::useService()->getPersonById($objectId);
                                if ($tblPerson) {
                                    $listObjectElementListList = $this->getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
                                        $tblList,
                                        $tblObjectType,
                                        Person::useService()->getPersonById($objectId)
                                    );
                                } else {
                                    $listObjectElementListList = false;
                                }
                                if ($listObjectElementListList) {
                                    foreach ($listObjectElementListList as $tblListObjectElementList) {
                                        if ($tblListObjectElementList->getServiceTblObject()) {
                                            if (!isset($Data[$tblListObjectElementList->getTblObjectType()->getId()]
                                                [$tblListObjectElementList->getServiceTblObject()->getId()]
                                                [$tblListObjectElementList->getTblListElementList()->getId()])
                                            ) {
                                                (new Data($this->getBinding()))->updateObjectElementToList(
                                                    $tblList,
                                                    $tblListObjectElementList->getTblObjectType(),
                                                    $tblListObjectElementList->getTblListElementList(),
                                                    $tblListObjectElementList->getServiceTblObject(),
                                                    ''
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($Data)) {
            foreach ($Data as $objectTypeId => $objects) {
                $tblObjectType = $this->getObjectTypeById($objectTypeId);
                if ($tblObjectType) {
                    if (!empty($objects)) {
                        foreach ($objects as $objectId => $elements) {
                            if ($tblObjectType->getIdentifier() === 'PERSON') {
                                $tblObject = Person::useService()->getPersonById($objectId);
                            } else {   // COMPANY
                                $tblObject = Company::useService()->getCompanyById($objectId);
                            }
                            if (!empty($elements) && $tblObject) {
                                foreach ($elements as $elementId => $value) {
                                    $tblListElementList = $this->getListElementListById($elementId);
                                    (new Data($this->getBinding()))->updateObjectElementToList(
                                        $tblList,
                                        $tblObjectType,
                                        $tblListElementList,
                                        $tblObject,
                                        $value
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return new Redirect('/Reporting/CheckList/Object/Element/Edit', Redirect::TIMEOUT_SUCCESS, array(
            'Id' => $Id,
            'YearPersonId' => $YearPersonId,
            'LevelPersonId' => $LevelPersonId,
            'SchoolOptionId' => $SchoolOptionId
        ));
    }

    /**
     * @param TblList $tblList
     *
     * @return bool|TblListObjectElementList[]
     */
    public function getListObjectElementListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->getListObjectElementListByList($tblList);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblObjectType
     */
    public function getObjectTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getObjectTypeByIdentifier($Identifier);
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     * @param Element       $tblObject
     *
     * @return bool|TblListObjectElementList[]
     */
    public function getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
        TblList $tblList,
        TblObjectType $tblObjectType,
        Element $tblObject
    ) {

        return (new Data($this->getBinding()))->getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
            $tblList, $tblObjectType, $tblObject
        );
    }

    /**
     * @param TblList $tblList
     * @param null $YearPersonId
     * @param null $LevelPersonId
     * @param null $SchoolOption1Id
     * @param null $SchoolOption2Id
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createCheckListExcel(
        TblList $tblList,
        $YearPersonId = null,
        $LevelPersonId = null,
        $SchoolOption1Id = null,
        $SchoolOption2Id = null
    ) {

        if ($tblList) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $columnCount = 0;
            $rowCount = 0;

            $isProspectList = false;
            $hasFilter = false;
            $filterYear = false;
            $filterLevel = false;
            $filterSchoolOption1 = false;
            $filterSchoolOption2 = false;

            // filter
            if ($YearPersonId !== null) {
                $yearPerson = Person::useService()->getPersonById($YearPersonId);
                if ($yearPerson) {
                    $hasFilter = true;
                    $tblProspect = Prospect::useService()->getProspectByPerson($yearPerson);
                    if ($tblProspect) {
                        $tblProspectReservation = $tblProspect->getTblProspectReservation();
                        if ($tblProspectReservation) {
                            $filterYear = trim($tblProspectReservation->getReservationYear());
                        }
                    }
                }
            }
            if ($LevelPersonId !== null) {
                $levelPerson = Person::useService()->getPersonById($LevelPersonId);
                if ($levelPerson) {
                    $hasFilter = true;
                    $tblProspect = Prospect::useService()->getProspectByPerson($levelPerson);
                    if ($tblProspect) {
                        $tblProspectReservation = $tblProspect->getTblProspectReservation();
                        if ($tblProspectReservation) {
                            $filterLevel = trim($tblProspectReservation->getReservationDivision());
                        }
                    }
                }
            }
            if ($SchoolOption1Id !== null) {
                $schoolOption = Type::useService()->getTypeById($SchoolOption1Id);
                if ($schoolOption) {
                    $hasFilter = true;
                    $filterSchoolOption1 = $schoolOption;
                }
            }
            if ($SchoolOption2Id !== null) {
                $schoolOption = Type::useService()->getTypeById($SchoolOption2Id);
                if ($schoolOption) {
                    $hasFilter = true;
                    $filterSchoolOption2 = $schoolOption;
                }
            }

            $tblListElementListByList = $this->getListElementListByList($tblList);
            if ($tblListElementListByList) {
                $tblListObjectListByList = $this->getListObjectListByList($tblList);
                $objectList = array();
                // get Objects
                $objectList = $this->getObjectList($tblListObjectListByList, $objectList);
                if ($hasFilter) {
                    $objectList = $this->filterObjectList($objectList, $filterYear, $filterLevel,
                        $filterSchoolOption1, $filterSchoolOption2);
                }

                // sort $objectList
                $objectList = $this->sortObjectList($objectList);

                if (!empty($objectList)) {

                    // prospectList
                    $isProspectList = true;
                    if (!$hasFilter) {
                        foreach ($objectList as $objectTypeId => $objects) {
                            $tblObjectType = $this->getObjectTypeById($objectTypeId);
                            if (!empty($objects)) {
                                foreach ($objects as $objectId => $value) {
                                    if ($tblObjectType->getIdentifier() === 'PERSON') {
                                        $tblPerson = Person::useService()->getPersonById($objectId);
                                        $prospectGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
                                        if ($tblPerson && !Group::useService()->existsGroupPerson($prospectGroup,
                                                $tblPerson)
                                        ) {
                                            $isProspectList = false;
                                        }
                                    } else {
                                        $isProspectList = false;
                                    }
                                }
                            }
                        }
                    }

                    if ($isProspectList) {
                        // set Header for prospectList
                        $export->setValue($export->getCell($columnCount++, $rowCount), 'Name');
                        $export->setValue($export->getCell($columnCount++, $rowCount), 'Schuljahr');
                        $export->setValue($export->getCell($columnCount++, $rowCount), 'Klassenstufe');
                        $export->setValue($export->getCell($columnCount++, $rowCount), 'Schulart');
                        $export->setValue($export->getCell($columnCount++, $rowCount), 'Eingangsdatum');

                        $tblListElementListByList = $this->getListElementListByList($tblList);
                        if ($tblListElementListByList) {
                            foreach ($tblListElementListByList as $tblListElementList) {
                                $export->setValue($export->getCell($columnCount++, $rowCount),
                                    $tblListElementList->getName());
                            }
                        }
                    } else {
                        // set header
                        $export->setValue($export->getCell($columnCount++, $rowCount), 'Name');
                        foreach ($tblListElementListByList as $tblListElementList) {
                            $export->setValue($export->getCell($columnCount++, $rowCount),
                                $tblListElementList->getName());
                        }

                    }
                }

                $rowCount = 1;
                if (!empty($objectList)) {
                    foreach ($objectList as $objectTypeId => $objects) {
                        $tblObjectType = $this->getObjectTypeById($objectTypeId);
                        if (!empty($objects)) {
                            foreach ($objects as $objectId => $value) {
                                $tblObject = false;
                                $columnCount = 0;
                                if ($tblObjectType->getIdentifier() === 'PERSON') {
                                    $tblPerson = Person::useService()->getPersonById($objectId);
                                    if ($tblPerson) {
                                        $tblObject = $tblPerson;
                                        $name = $tblPerson->getLastFirstName();
                                        $export->setValue($export->getCell($columnCount++, $rowCount), trim($name));

                                        if ($isProspectList) {
                                            // Prospect
                                            $level = false;
                                            $year = false;
                                            $option = false;
                                            $reservationDate = false;
                                            $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                                            if ($tblProspect) {
                                                $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                                if ($tblProspectReservation) {
                                                    $level = $tblProspectReservation->getReservationDivision();
                                                    $year = $tblProspectReservation->getReservationYear();
                                                    $optionA = $tblProspectReservation->getServiceTblTypeOptionA();
                                                    $optionB = $tblProspectReservation->getServiceTblTypeOptionB();
                                                    if ($optionA && $optionB) {
                                                        $option = $optionA->getName() . ', ' . $optionB->getName();
                                                    } elseif ($optionA) {
                                                        $option = $optionA->getName();
                                                    } elseif ($optionB) {
                                                        $option = $optionB->getName();
                                                    }
                                                }
                                                $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                                                if ($tblProspectAppointment) {
                                                    $reservationDate = $tblProspectAppointment->getReservationDate();
                                                }
                                            }
                                            $export->setValue($export->getCell($columnCount++, $rowCount), trim($year));
                                            $export->setValue($export->getCell($columnCount++, $rowCount),
                                                trim($level));
                                            $export->setValue($export->getCell($columnCount++, $rowCount),
                                                trim($option));
                                            if ($reservationDate) {
                                                $export->setValue($export->getCell($columnCount, $rowCount),
                                                    $reservationDate);
                                            }
                                        }
                                    }
                                } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {
                                    $tblCompany = Company::useService()->getCompanyById($objectId);
                                    $tblObject = $tblCompany;
                                    if ($tblCompany) {
                                        $export->setValue($export->getCell($columnCount, $rowCount),
                                            trim($tblCompany->getName()));
                                    }
                                }

                                if ($tblObject) {
                                    $tblListObjectElementList = $this->getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
                                        $tblList, $tblObjectType, $tblObject
                                    );
                                    if ($tblListObjectElementList) {
                                        foreach ($tblListObjectElementList as $item) {
                                            $columnCount = $isProspectList ? 5 : 1;
                                            foreach ($tblListElementListByList as $tblListElementList) {
                                                if ($tblListElementList->getId() === $item->getTblListElementList()->getId()) {
                                                    $export->setValue($export->getCell($columnCount, $rowCount),
                                                        $item->getValue());
                                                    break;
                                                } else {
                                                    $columnCount++;
                                                }
                                            }
                                        }
                                    }
                                    $rowCount++;
                                }
                            }
                        }
                    }
                }
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;

        }

        return false;
    }

    /**
     * @param TblList $tblList
     *
     * @return bool|TblListElementList[]
     */
    public function getListElementListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->getListElementListByList($tblList);
    }

    /**
     * @param TblList $tblList
     *
     * @return bool|TblListObjectList[]
     */
    public function getListObjectListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->getListObjectListByList($tblList);
    }

    /**
     * @param $tblListObjectListByList
     * @param array $objectList
     * @return array
     */
    public function getObjectList($tblListObjectListByList, $objectList)
    {
        if ($tblListObjectListByList) {
            /** @var TblListObjectElementList $tblListObjectList */
            foreach ($tblListObjectListByList as &$tblListObjectList) {
                if (($tblObject = $tblListObjectList->getServiceTblObject())) {
                    if ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSON') {
                        /** @var TblPerson $tblObject */
                        $objectList[$tblListObjectList->getTblObjectType()->getId()][$tblObject->getId()]
                            = $tblObject->getLastFirstName();
                    } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANY') {
                        /** @var TblCompany $tblObject */
                        $objectList[$tblListObjectList->getTblObjectType()->getId()][$tblObject->getId()]
                            = $tblObject->getName();
                    } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSONGROUP') {
                        /** @var PersonGroupEntity $tblObject */
                        $tblPersonAllByGroup = PersonGroup::useService()->getPersonAllByGroup($tblObject);
                        if ($tblPersonAllByGroup) {
                            foreach ($tblPersonAllByGroup as $tblPerson) {
                                $objectList[$this->getObjectTypeByIdentifier('PERSON')->getId()]
                                [$tblPerson->getId()] = $tblPerson->getLastFirstName();
                            }
                        }
                    } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANYGROUP') {
                        /** @var CompanyGroupEntity $tblObject */
                        $tblCompanyAllByGroup = CompanyGroup::useService()->getCompanyAllByGroup($tblObject);
                        if ($tblCompanyAllByGroup) {
                            foreach ($tblCompanyAllByGroup as $tblCompany) {
                                $objectList[$this->getObjectTypeByIdentifier('COMPANY')->getId()]
                                [$tblCompany->getId()] = $tblCompany->getName();
                            }
                        }
                    } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'DIVISIONGROUP') {
                        /** @var TblDivision $tblObject */
                        $tblStudentAllByDivision = Division::useService()->getStudentAllByDivision($tblObject);
                        if ($tblStudentAllByDivision) {
                            foreach ($tblStudentAllByDivision as $tblPerson) {
                                $objectList[$this->getObjectTypeByIdentifier('PERSON')->getId()]
                                [$tblPerson->getId()] = $tblPerson->getLastFirstName();
                            }
                        }
                    }
                }
            }

            return $objectList;
        }

        return $objectList;
    }

    /**
     * @param $objectList
     * @param string $filterYear
     * @param string $filterLevel
     * @param bool|TblType $filterSchoolOption1
     * @param bool|TblType $filterSchoolOption2
     *
     * @return array
     */
    public function filterObjectList($objectList, $filterYear, $filterLevel, $filterSchoolOption1, $filterSchoolOption2)
    {

        $resultList = array();

        $filterSchoolOption = $filterSchoolOption1 || $filterSchoolOption2;

        if (!empty($objectList)) {
            $tblObjectType = $this->getObjectTypeByIdentifier('PERSON');
            foreach ($objectList as $objectTypeId => $list) {
                if ($tblObjectType->getId() == $objectTypeId) {
                    if (is_array($list) && !empty($list)) {
                        foreach ($list as $personId => $value) {
                            $tblPerson = Person::useService()->getPersonById($personId);
                            $hasYear = false;
                            $hasLevel = false;
                            $hasSchoolOption = false;
                            if ($tblPerson) {
                                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                                if ($tblProspect) {
                                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                    if ($tblProspectReservation) {
                                        if ($filterYear) {
                                            $year = trim($tblProspectReservation->getReservationYear());
                                            if ($year == $filterYear) {
                                                $hasYear = true;
                                            }
                                        }
                                        if ($filterLevel) {
                                            $level = trim($tblProspectReservation->getReservationDivision());
                                            if ($level == $filterLevel) {
                                                $hasLevel = true;
                                            }
                                        }
                                        if ($filterSchoolOption1 || $filterSchoolOption2) {

                                            $schoolOptionA = $tblProspectReservation->getServiceTblTypeOptionA();
                                            $schoolOptionB = $tblProspectReservation->getServiceTblTypeOptionB();

                                            if ($filterSchoolOption1 && $filterSchoolOption2) {
                                                if ($schoolOptionA && $schoolOptionB) {
                                                    if (($schoolOptionA->getId() == $filterSchoolOption1->getId()
                                                            && $schoolOptionB->getId() == $filterSchoolOption2->getId())
                                                        || ($schoolOptionA->getId() == $filterSchoolOption2->getId()
                                                            && $schoolOptionB->getId() == $filterSchoolOption1->getId())
                                                    ) {
                                                        $hasSchoolOption = true;
                                                    }
                                                }
                                            } elseif ($filterSchoolOption1) {
                                                if ($schoolOptionA && $schoolOptionB) {
                                                    $hasSchoolOption = false;
                                                } elseif ($schoolOptionA) {
                                                    if ($schoolOptionA->getId() == $filterSchoolOption1->getId()) {
                                                        $hasSchoolOption = true;
                                                    }
                                                } elseif ($schoolOptionB) {
                                                    if ($schoolOptionB->getId() == $filterSchoolOption1->getId()) {
                                                        $hasSchoolOption = true;
                                                    }
                                                }
                                            } elseif ($filterSchoolOption2) {
                                                if ($schoolOptionA && $schoolOptionB) {
                                                    $hasSchoolOption = false;
                                                } elseif ($schoolOptionA) {
                                                    if ($schoolOptionA->getId() == $filterSchoolOption2->getId()) {
                                                        $hasSchoolOption = true;
                                                    }
                                                } elseif ($schoolOptionB) {
                                                    if ($schoolOptionB->getId() == $filterSchoolOption2->getId()) {
                                                        $hasSchoolOption = true;
                                                    }
                                                }
                                            }
                                        }

                                        // Filter "Und"-Verknüpfen
                                        if ($filterYear && $filterLevel && $filterSchoolOption) {
                                            if ($hasYear && $hasLevel && $hasSchoolOption) {
                                                $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                    = $tblPerson->getLastFirstName();
                                            }
                                        } elseif ($filterYear && $filterLevel) {
                                            if ($hasYear && $hasLevel) {
                                                $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                    = $tblPerson->getLastFirstName();
                                            }
                                        } elseif ($filterYear && $filterSchoolOption) {
                                            if ($hasYear && $hasSchoolOption) {
                                                $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                    = $tblPerson->getLastFirstName();
                                            }
                                        } elseif ($filterLevel && $filterSchoolOption) {
                                            if ($hasLevel && $hasSchoolOption) {
                                                $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                    = $tblPerson->getLastFirstName();
                                            }
                                        } elseif ($filterYear) {
                                            if ($hasYear) {
                                                $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                    = $tblPerson->getLastFirstName();
                                            }
                                        } elseif ($filterLevel) {
                                            if ($hasLevel) {
                                                $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                    = $tblPerson->getLastFirstName();
                                            }
                                        } elseif ($filterSchoolOption) {
                                            if ($hasSchoolOption) {
                                                $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                    = $tblPerson->getLastFirstName();
                                            }
                                        } else {
                                            $resultList[$tblObjectType->getId()][$tblPerson->getId()]
                                                = $tblPerson->getLastFirstName();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $resultList;
    }

    public function sortObjectList($objectList)
    {

        if (!empty( $objectList )) {
            foreach ($objectList as $objectTypeId => &$objects) {
                if (!empty( $objects )) {
                    asort($objects);
                }
            }
        }

        return $objectList;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $ListId
     * @param null                $Filter
     *
     * @return IFormInterface|Redirect
     */
    public function getFilteredCheckList(IFormInterface $Stage = null, $ListId = null, $Filter = null)
    {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Button']['Submit'] )) {
            return $Stage;
        }

        return new Redirect('/Reporting/CheckList/Object/Element/Edit', Redirect::TIMEOUT_SUCCESS, array(
            'Id'              => $ListId,
            'YearPersonId'    => $Filter['Year'],
            'LevelPersonId'   => $Filter['Level'],
            'SchoolOption1Id' => $Filter['SchoolOption1'],
            'SchoolOption2Id' => $Filter['SchoolOption2']
        ));
    }

    /**
     * @param TblList $tblList
     *
     * @return bool
     */
    public function destroyList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->destroyList($tblList);
    }

}
