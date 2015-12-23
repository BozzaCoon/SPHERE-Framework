<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:33
 */

namespace SPHERE\Application\Reporting\CheckList;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\Application\People\Group\Group as PersonGroup;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup as CompanyGroupEntity;
use SPHERE\Application\People\Group\Service\Entity\TblGroup as PersonGroupEntity;

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
     * @param $Id
     *
     * @return bool|TblList
     */
    public function getListById($Id)
    {

        return (new Data($this->getBinding()))->getListById($Id);
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
     * @return bool|TblList[]
     */
    public function getListAll()
    {

        return (new Data($this->getBinding()))->getListAll();
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
     * @param $Identifier
     *
     * @return bool|TblObjectType
     */
    public function getObjectTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getObjectTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblObjectType[]
     */
    public function getObjectTypeAll()
    {

        return (new Data($this->getBinding()))->getObjectTypeAll();
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
     * @param TblList $tblList
     *
     * @return bool|TblListElementList[]
     */
    public function getListElementListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->getListElementListByList($tblList);
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
     * @param TblList $tblList
     *
     * @return bool|TblListObjectList[]
     */
    public function getListObjectListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->getListObjectListByList($tblList);
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     * @param Element       $tblObject
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
     *
     * @return bool|TblListObjectElementList[]
     */
    public function getListObjectElementListByList(TblList $tblList)
    {

        return (new Data($this->getBinding()))->getListObjectElementListByList($tblList);
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
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     *
     * @return bool|Element[]
     */
    public function getObjectAllByListAndObjectType(TblList $tblList, TblObjectType $tblObjectType)
    {

        return (new Data($this->getBinding()))->getObjectAllByListAndObjectType($tblList, $tblObjectType);
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
        if (isset( $List['Name'] ) && empty( $List['Name'] )) {
            $Stage->setError('List[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } elseif (isset( $List['Name'] ) && $this->getListByName(trim($List['Name']))) {
            $Stage->setError('List[Name]', 'Der Name ist schon vorhanden. Bitte geben sie einen anderen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createList(
                trim($List['Name']),
                trim($List['Description'])
            );
            return new Stage('Die Check-Liste ist erfasst worden')
            .new Redirect('/Reporting/CheckList', 0);
        }

        return $Stage;
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
        if (isset( $Element['Name'] ) && empty( $Element['Name'] )) {
            $Stage->setError('List[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->addElementToList(
                $this->getListById($Id),
                $this->getElementTypeById($Element['Type']),
                $Element['Name']
            );
            return new Stage('Das Element ist zur Check-Liste hingefügt worden.')
            .new Redirect('/Reporting/CheckList/Element/Select', 0, array('Id' => $Id));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function removeElementFromList($Id = null)
    {

        $tblListElementList = $this->getListElementListById($Id);
        $tblList = $tblListElementList->getTblList();
        if ((new Data($this->getBinding()))->removeElementFromList($tblListElementList)) {
            return new Stage('Das Element ist von Check-Liste entfernt worden.')
            .new Redirect('/Reporting/CheckList/Element/Select', 0, array('Id' => $tblList->getId()));
        } else {
            return new Stage('Das Element konnte nicht von Check-Liste entfernt werden.')
            .new Redirect('/Reporting/CheckList/Element/Select', 0, array('Id' => $tblList->getId()));
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $ListId
     * @param null                $ObjectTypeSelect
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
        if (!isset( $ObjectTypeSelect['Id'] )) {
            $Error = true;
            $Stage .= new Warning('Objekt-Typ nicht gefunden');
        }

        if ($Error) {
            return $Stage;
        }

        $tblList = $this->getListById($ListId);
        $tblObjectType = $this->getObjectTypeById($ObjectTypeSelect['Id']);

        return new Redirect('/Reporting/CheckList/Object/Select', 0, array(
            'ListId'       => $tblList->getId(),
            'ObjectTypeId' => $tblObjectType->getId()
        ));
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     * @param Element       $tblObject
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

        $tblListObjectList = $this->getListObjectListById($Id);
        $tblList = $tblListObjectList->getTblList();
        $tblObjectType = $tblListObjectList->getTblObjectType();
        if ((new Data($this->getBinding()))->removeObjectFromList($tblListObjectList)) {
            return new Stage('Die '.$tblObjectType->getName().' ist von Check-Liste entfernt worden.')
            .new Redirect('/Reporting/CheckList/Object/Select', 0,
                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
        } else {
            return new Stage('Die '.$tblObjectType->getName().' konnte nicht von Check-Liste entfernt werden.')
            .new Redirect('/Reporting/CheckList/Object/Select', 3,
                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Id
     * @param null                $Data
     *
     * @return IFormInterface|Redirect
     */
    public function updateListObjectElementList(IFormInterface $Stage = null, $Id = null, $Data = null, $HasData = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Id || ( null === $Data && null === $HasData )) {
            return $Stage;
        }

        $tblList = CheckList::useService()->getListById($Id);

        // Reset CheckBoxen
        $tblListObjectElementListByList = CheckList::useService()->getListObjectElementListByList($tblList);
        if ($tblListObjectElementListByList) {
            foreach ($tblListObjectElementListByList as $tblListObjectElementList) {
                if (!isset( $Data[$tblListObjectElementList->getTblObjectType()->getId()]
                    [$tblListObjectElementList->getServiceTblObject()->getId()]
                    [$tblListObjectElementList->getTblListElementList()->getId()] )
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

        if (!empty( $Data )) {
            foreach ($Data as $objectTypeId => $objects) {
                $tblObjectType = $this->getObjectTypeById($objectTypeId);
                if ($tblObjectType) {
                    if (!empty( $objects )) {
                        foreach ($objects as $objectId => $elements) {
                            if ($tblObjectType->getIdentifier() === 'PERSON') {
                                $tblObject = Person::useService()->getPersonById($objectId);
                            } else {   // COMPANY
                                $tblObject = Company::useService()->getCompanyById($objectId);
                            }
                            if (!empty( $elements )) {
                                foreach ($elements as $elementId => $value) {
                                    $tblListElementList = CheckList::useService()->getListElementListById($elementId);
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

        return new Redirect('/Reporting/CheckList/Object/Element/Edit', 0, array('Id' => $Id));
    }

    /**
     * @param $tblList
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createCheckListExcel($tblList)
    {

        if ($tblList) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $columnCount = 0;
            $rowCount = 0;
            $tblListElementListByList = $this->getListElementListByList($tblList);
            if ($tblListElementListByList) {
                $export->setValue($export->getCell($columnCount++, $rowCount), 'Name');
                $export->setValue($export->getCell($columnCount++, $rowCount), 'Typ');
                foreach ($tblListElementListByList as $tblListElementList) {
                    // Header
                    $export->setValue($export->getCell($columnCount++, $rowCount),
                        $tblListElementList->getName());
                }

                $tblListObjectListByList = $this->getListObjectListByList($tblList);
                if ($tblListObjectListByList) {
                    foreach ($tblListObjectListByList as $tblListObjectList) {
                        $columnCount = 0;
                        $tblObject = $tblListObjectList->getServiceTblObject();
                        $tblObjectType = $tblListObjectList->getTblObjectType();
                        if (strpos($tblObjectType->getIdentifier(), 'GROUP') === false) {

                            $rowCount++;
                            $name = '';
                            if ($tblObjectType->getIdentifier() === 'PERSON') {
                                /** @var TblPerson $tblObject */
                                $name = $tblObject->getFullName();
                            } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {
                                /** @var TblCompany $tblObject */
                                $name = $tblObject->getName();
                            }
                            $export->setValue($export->getCell($columnCount++, $rowCount),
                                trim($name));
                            $export->setValue($export->getCell($columnCount, $rowCount),
                                $tblObjectType->getName());

                            $tblListObjectElementList = $this->getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
                                $tblList, $tblObjectType, $tblObject
                            );
                            if ($tblListObjectElementList) {
                                foreach ($tblListObjectElementList as $item) {
                                    $columnCount = 2;
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
                        } else {

                            if ($tblObjectType->getIdentifier() === 'PERSONGROUP') {
                                /** @var PersonGroupEntity $tblObject */
                                $tblPersonAllByGroup = PersonGroup::useService()->getPersonAllByGroup($tblObject);
                                if ($tblPersonAllByGroup) {
                                    foreach ($tblPersonAllByGroup as $tblPerson) {

                                        $personObjectType = $this->getObjectTypeByIdentifier('PERSON');
                                        if (!$this->getListObjectListByListAndObjectTypeAndObject(
                                            $tblList, $personObjectType, $tblPerson
                                        )
                                        ) {
                                            $rowCount++;
                                            $columnCount = 0;
                                            $export->setValue($export->getCell($columnCount++, $rowCount),
                                                trim($tblPerson->getFullName()));
                                            $export->setValue($export->getCell($columnCount, $rowCount),
                                                $personObjectType->getName());

                                            $tblListObjectElementList = $this->getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
                                                $tblList, $personObjectType, $tblPerson
                                            );
                                            if ($tblListObjectElementList) {
                                                foreach ($tblListObjectElementList as $item) {
                                                    $columnCount = 2;
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
                                        }
                                    }
                                }
                            } elseif ($tblObjectType->getIdentifier() === 'COMPANYGROUP') {
                                /** @var CompanyGroupEntity $tblObject */
                                $tblCompanyAllByGroup = CompanyGroup::useService()->getCompanyAllByGroup($tblObject);
                                if ($tblCompanyAllByGroup) {
                                    foreach ($tblCompanyAllByGroup as $tblCompany) {

                                        $companyObjectType = $this->getObjectTypeByIdentifier('COMPANY');
                                        if (!$this->getListObjectListByListAndObjectTypeAndObject(
                                            $tblList, $companyObjectType, $tblCompany
                                        )
                                        ) {
                                            $rowCount++;
                                            $columnCount = 0;
                                            $export->setValue($export->getCell($columnCount++, $rowCount),
                                                trim($tblCompany->getName()));
                                            $export->setValue($export->getCell($columnCount, $rowCount),
                                                $companyObjectType->getName());

                                            $tblListObjectElementList = $this->getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
                                                $tblList, $companyObjectType, $tblCompany
                                            );
                                            if ($tblListObjectElementList) {
                                                foreach ($tblListObjectElementList as $item) {
                                                    $columnCount = 2;
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
                                        }
                                    }
                                }
                            } elseif ($tblObjectType->getIdentifier() === 'DIVISIONGROUP') {
                                /** @var TblDivision $tblObject */
                                $tblStudentAllByDivision = Division::useService()->getStudentAllByDivision($tblObject);
                                if ($tblStudentAllByDivision) {
                                    foreach ($tblStudentAllByDivision as $tblPerson) {

                                        $personObjectType = $this->getObjectTypeByIdentifier('PERSON');
                                        if (!$this->getListObjectListByListAndObjectTypeAndObject(
                                            $tblList, $personObjectType, $tblPerson
                                        )
                                        ) {
                                            $rowCount++;
                                            $columnCount = 0;
                                            $export->setValue($export->getCell($columnCount++, $rowCount),
                                                trim($tblPerson->getFullName()));
                                            $export->setValue($export->getCell($columnCount, $rowCount),
                                                $personObjectType->getName());

                                            $tblListObjectElementList = $this->getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
                                                $tblList, $personObjectType, $tblPerson
                                            );
                                            if ($tblListObjectElementList) {
                                                foreach ($tblListObjectElementList as $item) {
                                                    $columnCount = 2;
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
                                        }
                                    }
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
}
