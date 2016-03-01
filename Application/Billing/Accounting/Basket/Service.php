<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\Billing\Accounting\Basket\Service\Data;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Accounting\Basket\Service\Setup;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Basket
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
     * @param TblBasket $tblBasket
     *
     * @return bool|TblCommodity[]
     */
    public function getCommodityAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getCommodityAllByBasket($tblBasket);
    }

    /**
     * @return bool|TblBasket[]
     */
    public function getBasketAll()
    {

        return (new Data($this->getBinding()))->getBasketAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        return (new Data($this->getBinding()))->getBasketItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketPerson
     */
    public function getBasketPersonById($Id)
    {

        return (new Data($this->getBinding()))->getBasketPersonById($Id);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return int
     */
    public function countPersonByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->countPersonByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketVerificationByBasket($tblBasket);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblBasket $tblBasket
     *
     * @return false|Service\Entity\TblBasketVerification[]
     */
    public function getBasketVerificationByPersonAndBasket(TblPerson $tblPerson, TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketPersonList = $this->getBasketPersonAllByBasket($tblBasket);
        $tblPerson = array();
        if ($tblBasketPersonList) {
            foreach ($tblBasketPersonList as $tblBasketPerson) {
                array_push($tblPerson, $tblBasketPerson->getServicePeople_Person());
            }
        }


        return ( empty( $tblPerson ) ? false : $tblPerson );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemList = $this->getBasketItemAllByBasket($tblBasket);
        $tblItem = array();
        if ($tblBasketItemList) {
            foreach ($tblBasketItemList as $tblBasketItem) {
                array_push($tblItem, $tblBasketItem->getServiceInventoryItem());
            }

        }
        return ( empty( $tblItem ) ? false : $tblItem );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketPerson[]
     */
    public function getBasketPersonAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketPersonAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     *
     * @return bool|TblBasketPerson
     */
    public function getBasketPersonByBasketAndPerson(TblBasket $tblBasket, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getBasketPersonByBasketAndPerson($tblBasket, $tblPerson);

    }

    /**
     * @param IFormInterface $Stage
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function createBasket(IFormInterface &$Stage = null, $Basket)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Basket
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Basket['Name'] ) && empty( $Basket['Name'] )) {
            $Stage->setError('Basket[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            if (!isset( $Basket['Description'] )) {
                $Basket['Description'] = '';
            }

            $tblBasket = (new Data($this->getBinding()))->createBasket(
                $Basket['Name'], $Basket['Description']
            );
            return new Success('Der Warenkorb wurde erfolgreich erstellt')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_SUCCESS
                , array('Id' => $tblBasket->getId()));
        }

        return $Stage;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return string
     */
    public function createBasketVerification(TblBasket $tblBasket)
    {

        $tblPersonList = $this->getPersonAllByBasket($tblBasket);
        $tblItemList = $this->getItemAllByBasket($tblBasket);

        if (!$tblPersonList && !$tblItemList) {
            return new Warning('Keine Personen und Artikel im Warenkorb')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        if (!$tblPersonList) {
            return new Warning('Keine Personen im Warenkorb')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        if (!$tblItemList) {
            return new Warning('Keine Artikel im Warenkorb')
            .new Redirect('/Billing/Accounting/Basket/Content', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $PersonCount = Count($tblPersonList);

        foreach ($tblPersonList as $tblPerson) {
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            $PersonChildRank = false;
            $PersonCourse = false;
            if ($tblStudent) {
                $tblBilling = $tblStudent->getTblStudentBilling();
                if ($tblBilling) {
                    $tblSiblingRank = $tblBilling->getServiceTblSiblingRank();
                    if ($tblSiblingRank) {
                        $PersonChildRank = $tblSiblingRank->getId();
                    }
                }
                $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                if ($tblTransferType) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType);
                    if ($tblStudentTransfer) {
                        $tblType = $tblStudentTransfer->getServiceTblType();
                        if ($tblType) {
                            $PersonCourse = $tblType->getId();
                        }
                    }
                }
            }
            foreach ($tblItemList as $tblItem) {
                $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);

                /** @var TblCalculation $tblCalculation */
                if (is_array($tblCalculationList)) {
                    // Berechnung für Sammelleistung
                    if ($tblItem->getTblItemType()->getName() === 'Sammelleistung') {
                        foreach ($tblCalculationList as $tblCalculation) {
                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                                break;
                            }
                            $ItemChildRankId = false;
                            $ItemCourseId = false;
                            $ItemChildRankName = '';
                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                            if ($tblItemCourseType) {
                                $ItemCourseId = $tblItemCourseType->getId();
                            }
                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                            if ($tblItemChildRank) {
                                $ItemChildRankId = $tblItemChildRank->getId();
                                $ItemChildRankName = $tblItemChildRank->getName();
                            }

                            if (count($tblCalculationList) === 1) {
                                $Price = $tblCalculation->getValue();
                                $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                            } else {
                                // Bedinungen stimmen
                                if ($PersonChildRank === $ItemChildRankId && $PersonCourse === $ItemCourseId) {
                                    if ($PersonChildRank !== false && $PersonCourse !== false) {
                                        $Price = $tblCalculation->getValue();
                                        if ($ItemChildRankId === false && $ItemCourseId === false) {
                                            $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
                                        }
                                        (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                    }
                                }
                                // Fehlende Geschwisterangabe = 1.Geschwisterkind
                                if ($PersonChildRank === false && $ItemChildRankName === '1. Geschwisterkind' && $PersonCourse === $ItemCourseId) {
                                    $Price = $tblCalculation->getValue();
                                    if ($ItemChildRankId === false && $ItemCourseId === false) {
                                        $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
                                    }
                                    (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                }
                            }
                        }

                        foreach ($tblCalculationList as $tblCalculation) {
                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                                break;
                            }
                            $ItemChildRankId = false;
                            $ItemCourseId = false;
                            $ItemChildRankName = '';
                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                            if ($tblItemCourseType) {
                                $ItemCourseId = $tblItemCourseType->getId();
                            }
                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                            if ($tblItemChildRank) {
                                $ItemChildRankId = $tblItemChildRank->getId();
                                $ItemChildRankName = $tblItemChildRank->getName();
                            }
                            if ($PersonChildRank !== false && $PersonCourse !== false) {
                                if (false === $ItemChildRankId && $PersonCourse === $ItemCourseId) {    // Ignoriert Geschwisterkinder
                                    $Price = $tblCalculation->getValue();
                                    (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                } elseif ($PersonChildRank === $ItemChildRankId && false === $ItemCourseId) { // Ignoriert SchulTyp
                                    $Price = $tblCalculation->getValue();
                                    (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                } elseif ($PersonChildRank === false                                       //false = Geschwisterkind 1
                                    && $ItemChildRankName === '1. Geschwisterkind'
                                    && $PersonCourse === $ItemCourseId
                                ) {
                                    $Price = $tblCalculation->getValue();
                                    (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                }
                            }
                        }

                        // Bedinungen nicht getroffen (Preisaufteilung)
                        foreach ($tblCalculationList as $tblCalculation) {
                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                                break;
                            }
                            $ItemChildRankId = false;
                            $ItemCourseId = false;
                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                            if ($tblItemCourseType) {
                                $ItemCourseId = $tblItemCourseType->getId();
                            }
                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                            if ($tblItemChildRank) {
                                $ItemChildRankId = $tblItemChildRank->getId();
                            }
                            if (false === $ItemChildRankId && false === $ItemCourseId) {
                                $Price = $tblCalculation->getValue();
                                $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                            }
                        }
                    }
                    // Berechnung für Einzelleistung
                    if ($tblItem->getTblItemType()->getName() === 'Einzelleistung') {
                        foreach ($tblCalculationList as $tblCalculation) {
                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                                break;
                            }
                            $ItemChildRankId = false;
                            $ItemChildRankName = '';
                            $ItemCourseId = false;
                            $Changed = false;
                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                            if ($tblItemCourseType) {
                                $ItemCourseId = $tblItemCourseType->getId();
                                $Changed = true;
                            }
                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                            if ($tblItemChildRank) {
                                $ItemChildRankId = $tblItemChildRank->getId();
                                $ItemChildRankName = $tblItemChildRank->getName();
                                $Changed = true;
                            }
                            if (count($tblCalculationList) === 1) {
                                $Price = $tblCalculation->getValue();
                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                break;
                            }
                            // Bedinungen stimmen
                            if ($PersonChildRank === $ItemChildRankId && $PersonCourse === $ItemCourseId && $Changed === true) {
                                $Price = $tblCalculation->getValue();
                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                            }   // Fehlende Geschwisterangabe = 1.Geschwisterkind
                            if ($PersonChildRank === false && $ItemChildRankName === '1. Geschwisterkind' && $PersonCourse === $ItemCourseId && $Changed === true) {
                                $Price = $tblCalculation->getValue();
                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                            }
                        }
                        foreach ($tblCalculationList as $tblCalculation) {
                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
                                break;
                            }
                            $ItemChildRankId = false;
                            $ItemCourseId = false;
                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
                            if ($tblItemCourseType) {
                                $ItemCourseId = $tblItemCourseType->getId();
                            }
                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
                            if ($tblItemChildRank) {
                                $ItemChildRankId = $tblItemChildRank->getId();
                            }
                            if ($PersonChildRank !== false && $PersonCourse !== false) {
                                if (false === $ItemChildRankId && $PersonCourse === $ItemCourseId) {    // Ignoriert Geschwisterkinder
                                    $Price = $tblCalculation->getValue();
                                    (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                } elseif ($PersonChildRank === $ItemChildRankId && false === $ItemCourseId) { // Ignoriert SchulTyp
                                    $Price = $tblCalculation->getValue();
                                    (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
                                }
                            }
                        }
                    }
                }


//                    if (count($tblCalculationList) === 1) {
//                        foreach ($tblCalculationList as $tblCalculation) {
//                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
//                                break;
//                            }
//                            $ItemChildRankId = false;
//                            $ItemCourseId = false;
//                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
//                            if ($tblItemCourseType) {
//                                $ItemCourseId = $tblItemCourseType->getId();
//                            }
//                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
//                            if ($tblItemChildRank) {
//                                $ItemChildRankId = $tblItemChildRank->getId();
//                            }
//                            // Sammelleistung ohne Bedinungen auf alle Personen verteilen
//                            if (false === $ItemChildRankId && false === $ItemCourseId) {
//                                if ($tblItem->getTblItemType()->getName() === 'Sammelleistung') {
//                                    $Price = $tblCalculation->getValue();
//                                    $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
//                                } else {
//                                    $Price = $tblCalculation->getValue();
//                                }
//                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
//                            }
//                        }
//                    }
//                    if (count($tblCalculationList) > 1) {
//                        foreach ($tblCalculationList as $tblCalculation) {
//                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
//                                break;
//                            }
//                            $ItemChildRankId = false;
//                            $ItemChildRankName = '';
//                            $ItemCourseId = false;
//                            $Changed = false;
//                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
//                            if ($tblItemCourseType) {
//                                $ItemCourseId = $tblItemCourseType->getId();
//                                $Changed = true;
//                            }
//                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
//                            if ($tblItemChildRank) {
//                                $ItemChildRankId = $tblItemChildRank->getId();
//                                $ItemChildRankName = $tblItemChildRank->getName();
//                                $Changed = true;
//                            }
//                            // Bedinungen stimmen
//                            if ($PersonChildRank === $ItemChildRankId && $PersonCourse === $ItemCourseId && $Changed === true) {
//                                $Price = $tblCalculation->getValue();
//                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
//                            }   // Fehlende Geschwisterangabe = 1.Geschwisterkind
//                            if ($PersonChildRank === false && $ItemChildRankName === '1. Geschwisterkind' && $PersonCourse === $ItemCourseId && $Changed === true) {
//                                $Price = $tblCalculation->getValue();
//                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
//                            }
//                        }
//
//
//                        foreach ($tblCalculationList as $tblCalculation) {
//
//                            if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
//                                break;
//                            }
//                            $ItemChildRankId = false;
//                            $ItemChildRankName = '';
//                            $ItemCourseId = false;
//                            $Changed = false;
//
//                            $tblItemCourseType = $tblCalculation->getServiceSchoolType();
//                            if ($tblItemCourseType) {
//                                $ItemCourseId = $tblItemCourseType->getId();
//                                $Changed = true;
//                            }
//                            $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
//                            if ($tblItemChildRank) {
//                                $ItemChildRankId = $tblItemChildRank->getId();
//                                $ItemChildRankName = $tblItemChildRank->getName();
//                                $Changed = true;
//                            }
//                            // Ignoriert Geschwisterkinder
//                            if (false === $ItemChildRankId && $PersonCourse === $ItemCourseId && $Changed === true) {
//                                $Price = $tblCalculation->getValue();
//                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
//                            }
//                            // Ignoriert SchulTyp
//                            if ($PersonChildRank === $ItemChildRankId && false === $ItemCourseId && $Changed === true) {
//                                $Price = $tblCalculation->getValue();
//                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
//                            }
//                            // Fehlende Geschwisterangabe = 1.Geschwisterkind
//                            if ($PersonChildRank === false && $ItemChildRankName === '1. Geschwisterkind' && false === $ItemCourseId && $Changed === true) {
//                                $Price = $tblCalculation->getValue();
//                                (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
//                            }
//                        }
//
//                        if ($tblItem->getTblItemType()->getName() === 'Sammelleistung') {
//                            foreach ($tblCalculationList as $tblCalculation) {
//                                if ((new Data($this->getBinding()))->checkBasketVerificationIsSet($tblBasket, $tblPerson, $tblItem)) {
//                                    break;
//                                }
//                                $ItemChildRankId = false;
//                                $ItemCourseId = false;
//                                $tblItemCourseType = $tblCalculation->getServiceSchoolType();
//                                if ($tblItemCourseType) {
//                                    $ItemCourseId = $tblItemCourseType->getId();
//                                }
//                                $tblItemChildRank = $tblCalculation->getServiceStudentChildRank();
//                                if ($tblItemChildRank) {
//                                    $ItemChildRankId = $tblItemChildRank->getId();
//                                }
//                                // Das Verteilen der Sammelleistung bei vergebenen Bedingungen an alle nicht der Bedingung zutreffenden Personen
//                                if (false === $ItemChildRankId && false === $ItemCourseId) {
//                                    $Price = $tblCalculation->getValue();
//                                    $Price = ( ceil(( $Price / $PersonCount ) * 100) ) / 100; // Centbetrag immer aufrunden
//                                    (new Data($this->getBinding()))->createBasketVerification($tblBasket, $tblPerson, $tblItem, $Price);
//                                }
//                            }
//                        }
            }
        }
        //ToDO Personen ohne einträge automatisch entfernen?
//        $PersonList = Basket::useService()->getPersonAllByBasket($tblBasket);
//        if($PersonList)
//        {
//            foreach($PersonList as $Person)
//            {
//                if(!Basket::useService()->getBasketVerificationByPersonAndBasket($Person, $tblBasket)){
//                    $tblBasketPerson = Basket::useService()->getBasketPersonByBasketAndPerson($tblBasket, $Person);
//                    $this->removeBasketPerson($tblBasketPerson);
//                }
//            }
//        }
        return new Success('Berechnung bereitmachen für Bearbeitung')
        .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return array|bool
     */
    public function getUnusedCommodityByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket);
        $ItemList = array();
        if ($tblBasketItemList) {
            foreach ($tblBasketItemList as $tblBasketItem) {
                $ItemList[] = $tblBasketItem->getServiceInventoryItem();
            }
        }

        $tblCommodityAll = Commodity::useService()->getCommodityAll();
        $tblCommodityList = array();

        if (!empty( $tblCommodityAll )) {
            if (empty( $ItemList )) {
                $tblCommodityList = $tblCommodityAll;
            } else {
                foreach ($tblCommodityAll as $tblCommodity) {
                    $CommodityItemList = Commodity::useService()->getItemAllByCommodity($tblCommodity);
                    if (!empty( $CommodityItemList )) {
                        $CommodityItemList = array_udiff($CommodityItemList, $ItemList,
                            function (TblItem $ObjectA, TblItem $ObjectB) {

                                return $ObjectA->getId() - $ObjectB->getId();
                            }
                        );
                        if (!empty( $CommodityItemList )) {
                            $tblCommodityList[] = $tblCommodity;
                        }
                    }
                }
            }
        }

        return ( ( $tblCommodityList === null ) ? false : $tblCommodityList );
    }

    /**
     * @param IFormInterface $Stage
     * @param TblBasket      $tblBasket
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function changeBasket(IFormInterface &$Stage = null, TblBasket $tblBasket, $Basket)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Basket
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Basket['Name'] ) && empty( $Basket['Name'] )) {
            $Stage->setError('Basket[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateBasket(
                $tblBasket,
                $Basket['Name'],
                $Basket['Description']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_SUCCESS);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden');
            };
        }
        return $Stage;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return string
     */
    public function destroyBasket(TblBasket $tblBasket)
    {

        $tblBasket = (new Data($this->getBinding()))->destroyBasket($tblBasket);
        if ($tblBasket) {
            return new Success('Der Warenkorb wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Warning('Der Warenkorb konnte nicht gelöscht werden')
            .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return string
     */
    public function destroyBasketVerification(TblBasketVerification $tblBasketVerification)
    {

        $tblPerson = $tblBasketVerification->getServicePeoplePerson();
        $tblBasket = $tblBasketVerification->getTblBasket();


        if ((new Data($this->getBinding()))->destroyBasketVerification($tblBasketVerification)) {
            $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
            if (!$tblBasketVerificationList) {
                $tblBasketPerson = Basket::useService()->getBasketPersonByBasketAndPerson($tblBasket, $tblPerson);
                $this->removeBasketPerson($tblBasketPerson);
                return new Success('Der Eintrag wurde erfolgreich gelöscht')
                .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblBasketVerification->getTblBasket()->getId()));
            }
            return new Success('Der Eintrag wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Basket/Verification/Person', Redirect::TIMEOUT_SUCCESS,
                array('PersonId' => $tblBasketVerification->getServicePeoplePerson()->getId(),
                      'BasketId' => $tblBasketVerification->getTblBasket()->getId()));
        } else {
            return new Warning('Der Eintrag konnte nicht gelöscht werden')
            .new Redirect('/Billing/Accounting/Basket/Verification/Person', Redirect::TIMEOUT_ERROR,
                array('PersonId' => $tblBasketVerification->getServicePeoplePerson()->getId(),
                      'BasketId' => $tblBasketVerification->getTblBasket()->getId()));
        }
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return bool
     */
    public function destroyBasketVerificationList(TblBasketVerification $tblBasketVerification)
    {

        return (new Data($this->getBinding()))->destroyBasketVerification($tblBasketVerification);
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function addCommodityToBasket(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        if ((new Data($this->getBinding()))->addBasketItemsByCommodity($tblBasket, $tblCommodity)) {
            return new Success('Die Artikelgruppe '.$tblCommodity->getName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
        } else {
            return new Warning('Die Artikelgruppe '.$tblCommodity->getName().' konnte nicht kmplett hinzugefügt werden')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return string
     */
    public function addItemToBasket(TblBasket $tblBasket, TblItem $tblItem)
    {

        (new Data($this->getBinding()))->addItemToBasket($tblBasket, $tblItem);

        return new Success('Der Artikel '.$tblItem->getName().' wurde erfolgreich hinzugefügt')
        .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return string
     */
    public function removeBasketItem(TblBasketItem $tblBasketItem)
    {

        if ((new Data($this->getBinding()))->removeBasketItem($tblBasketItem)) {
            return new Success('Der Artikel '.$tblBasketItem->getServiceInventoryItem()->getName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasketItem->getTblBasket()->getId()));
        } else {
            return new Warning('Der Artikel '.$tblBasketItem->getServiceInventoryItem()->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Basket/Item/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasketItem->getTblBasket()->getId()));
        }
    }

    /**
     * @param IFormInterface|null   $Stage
     * @param TblBasketVerification $tblBasketVerification
     * @param                       $Item
     *
     * @return IFormInterface|string
     */
    public function changeBasketVerification(IFormInterface &$Stage = null, TblBasketVerification $tblBasketVerification, $Item)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Item
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Item['Price'] ) && empty( $Item['Price'] )) {
            $Stage->setError('Item[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        } else {
            $Item['Price'] = str_replace(',', '.', $Item['Price']);
        }
        if (isset( $Item['Quantity'] ) && empty( $Item['Quantity'] )) {
            $Stage->setError('Item[Quantity]', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        } else {
            $Item['Quantity'] = round(str_replace(',', '.', $Item['Quantity']), 0);
            if (!is_numeric($Item['Quantity']) || $Item['Quantity'] < 1) {
                $Stage->setError('Item[Quantity]', 'Bitte geben Sie eine Natürliche Zahl an');
                $Error = true;
            }
        }
        if (!$Error) {

            if ($Item['PriceChoice'] === 'Einzelpreis') {
                $Item['Price'] = $Item['Price'] * $Item['Quantity'];
            }
            if ((new Data($this->getBinding()))->updateBasketVerification(
                $tblBasketVerification,
                $Item['Price'],
                $Item['Quantity']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Accounting/Basket/Verification/Person', Redirect::TIMEOUT_SUCCESS,
                        array('PersonId' => $tblBasketVerification->getServicePeoplePerson()->getId(),
                              'BasketId' => $tblBasketVerification->getTblBasket()->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Accounting/Basket/Verification/Person', Redirect::TIMEOUT_ERROR,
                        array('PersonId' => $tblBasketVerification->getServicePeoplePerson()->getId(),
                              'BasketId' => $tblBasketVerification->getTblBasket()->getId()));
            };
        }
        return $Stage;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function addBasketPerson(TblBasket $tblBasket, TblPerson $tblPerson)
    {

        (new Data($this->getBinding()))->addBasketPerson($tblBasket, $tblPerson);

        return new Success('Die Person '.$tblPerson->getFullName().' wurde erfolgreich hinzugefügt')
        .new Redirect('/Billing/Accounting/Basket/Person/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
    }

    /**
     * @param TblBasketPerson $tblBasketPerson
     *
     * @return bool
     */
    public function removeBasketPerson(TblBasketPerson $tblBasketPerson)
    {

        $tblPerson = $tblBasketPerson->getServicePeople_Person();
        $tblBasket = $tblBasketPerson->getTblBasket();
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
        if ($tblBasketVerificationList) {
            foreach ($tblBasketVerificationList as $tblBasketVerification) {
                Basket::useService()->destroyBasketVerificationList($tblBasketVerification);
            }
        }
        return (new Data($this->getBinding()))->removeBasketPerson($tblBasketPerson);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByBasket($tblBasket);
    }

//    /**
//     * @param IFormInterface $Stage
//     * @param                $Id
//     * @param                $Date
//     * @param                $Data
//     * @param                $Save
//     *
//     * @return IFormInterface|string
//     */
//    public function checkDebtors(
//        IFormInterface &$Stage = null,
//        $Id,
//        $Date,
//        $Data,
//        $Save
//    ) {
//
//        /**
//         * Skip to Frontend
//         */
//        if (null === $Data && null === $Save
//        ) {
//            return $Stage;
//        }
//
//        $isSave = $Save == 2;
//        $tblBasket = Basket::useService()->getBasketById($Id);
//
//        if ((new Data($this->getBinding()))->checkDebtors($tblBasket, $Data, $isSave)) {
//            if (Invoice::useService()->createOrderListFromBasket($tblBasket, $Date)) {
//                $Stage .= new Success('Die Rechnungen wurden erfolgreich erstellt')
//                    .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
//            } else {
//                $Stage .= new Success('Die Rechnungen konnten nicht erstellt werden')
//                    .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
//            }
//        }
//
//        return $Stage;
//    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        return (new Data($this->getBinding()))->getBasketById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketVerification
     */
    public function getBasketVerificationById($Id)
    {

        return (new Data($this->getBinding()))->getBasketVerificationById($Id);
    }
}
