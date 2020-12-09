<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;

/**
 * Class SDataFachschule
 * @package SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate
 */
class SDataFachschule
{

    /**
     * @param Data $Data
     */
    public static function setCertificateStandard(Data $Data)
    {

        self::setFsHjInfo($Data);
        self::setFsHj($Data);
        self::setFsJ(($Data));
        self::setFsAbs(($Data));
        self::setFsAbsFhr(($Data));
        self::setFsAbg(($Data));
    }

    /**
     * @param Data $Data
     */
    private static function setFsHjInfo(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Fachschule Halbjahresinformation', '',
            'FsHjInfo');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeFachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFachschule(), null, true);
                // Automaitk soll hier nicht entscheiden
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFachschule(), '1'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFachschule(), '2'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 300);
            }
        }

        // Inforamtionen auf mehrere "Sonnstige Informationen" aufgliedern
        // Seite 2
        $Data->createCertificateInformation($tblCertificate, 'FsDestination', 2);
        $Data->createCertificateInformation($tblCertificate, 'SubjectArea', 2);
        $Data->createCertificateInformation($tblCertificate, 'Focus', 2);
        $Data->createCertificateInformation($tblCertificate, 'ChosenArea', 2);
        $Data->createCertificateInformation($tblCertificate, 'JobEducation', 3);
        $Data->createCertificateInformation($tblCertificate, 'JobEducationDuration', 3);
        $Data->createCertificateInformation($tblCertificate, 'AddAducation', 3);
        $Data->createCertificateInformation($tblCertificate, 'ChosenArea1', 3);
        $Data->createCertificateInformation($tblCertificate, 'ChosenArea2', 3);

    }

    /**
     * @param Data $Data
     */
    private static function setFsHj(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Fachschule Halbjahreszeugnis', '',
            'FsHj');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeFachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFachschule(), null, true);
                // Automaitk soll hier nicht entscheiden
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFachschule(), '1'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFachschule(), '2'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 100);
            }

            // Inforamtionen auf mehrere "Sonnstige Informationen" aufgliedern
            // Seite 2
            $Data->createCertificateInformation($tblCertificate, 'FsDestination', 2);
            $Data->createCertificateInformation($tblCertificate, 'SubjectArea', 2);
            $Data->createCertificateInformation($tblCertificate, 'Focus', 2);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea', 2);
//            $Data->createCertificateInformation($tblCertificate, 'JobEducation', 3);
//            $Data->createCertificateInformation($tblCertificate, 'JobEducationDuration', 3);
            $Data->createCertificateInformation($tblCertificate, 'AddEducation', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea1', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea2', 3);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setFsJ(Data $Data)
    {
        $tblCertificate = $Data->createCertificate('Fachschule Jahreszeugnis', '',
            'FsJ');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeFachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeFachschule(), null, false);
                // Automaitk soll hier nicht entscheiden
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
//                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFachschule(), '1'))) {
//                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                }
//                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFachschule(), '2'))) {
//                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 300);
            }

            // Inforamtionen auf mehrere "Sonnstige Informationen" aufgliedern
            // Seite 2
            $Data->createCertificateInformation($tblCertificate, 'FsDestination', 2);
            $Data->createCertificateInformation($tblCertificate, 'SubjectArea', 2);
            $Data->createCertificateInformation($tblCertificate, 'Focus', 2);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea', 2);
            $Data->createCertificateInformation($tblCertificate, 'JobEducation', 3);
            $Data->createCertificateInformation($tblCertificate, 'JobEducationDuration', 3);
            $Data->createCertificateInformation($tblCertificate, 'AddAducation', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea1', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea2', 3);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setFsAbsFhr(Data $Data)
    {

        if (($tblCertificate = $Data->createCertificate('Fachschule Abschlusszeugnis FHR', 'Fachhochschulreife', 'FsAbsFhr',
            null, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeFachschule()))
        ) {
            // ToDO hinterlegung irgendwelcher Fächer?

//            'DateFrom' ist auf Seite 1
//            'DateTo' ist auf Seite 1

            $Data->createCertificateInformation($tblCertificate, 'FsDestination', 2);
            $Data->createCertificateInformation($tblCertificate, 'SubjectArea', 2);
            $Data->createCertificateInformation($tblCertificate, 'Focus', 2);

            $Data->createCertificateInformation($tblCertificate, 'JobEducationDuration', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea1', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea2', 3);

            $Data->createCertificateInformation($tblCertificate, 'SkilledWork', 4);
            $Data->createCertificateInformation($tblCertificate, 'SkilledWork_Grade', 4);
            $Data->createCertificateInformation($tblCertificate, 'SkilledWork_GradeText', 4);

            $Data->createCertificateInformation($tblCertificate, 'RemarkWithoutTeam', 5);

            // Fachhochschulreife auf Seite 6, da es ja Schüler mit und ohne geben kann
            // + Durchschnittsnote
            $Data->createCertificateInformation($tblCertificate, 'AddEducation_Average', 6);
            $Data->createCertificateInformation($tblCertificate, 'AddEducation', 6);
            $Data->createCertificateInformation($tblCertificate, 'AddEducation_Grade', 6);
            $Data->createCertificateInformation($tblCertificate, 'AddEducation_GradeText', 6);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setFsAbs(Data $Data)
    {

        if (($tblCertificate = $Data->createCertificate('Fachschule Abschlusszeugnis', '', 'FsAbs',
            null, false, false, true, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeFachschule()))
        ) {
            // ToDO hinterlegung irgendwelcher Fächer?

//            'DateFrom' ist auf Seite 1
//            'DateTo' ist auf Seite 1

            $Data->createCertificateInformation($tblCertificate, 'FsDestination', 2);
            $Data->createCertificateInformation($tblCertificate, 'SubjectArea', 2);
            $Data->createCertificateInformation($tblCertificate, 'Focus', 2);

            $Data->createCertificateInformation($tblCertificate, 'JobEducationDuration', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea1', 3);
            $Data->createCertificateInformation($tblCertificate, 'ChosenArea2', 3);

            $Data->createCertificateInformation($tblCertificate, 'SkilledWork', 4);
            $Data->createCertificateInformation($tblCertificate, 'SkilledWork_Grade', 4);
            $Data->createCertificateInformation($tblCertificate, 'SkilledWork_GradeText', 4);

            $Data->createCertificateInformation($tblCertificate, 'RemarkWithoutTeam', 5);
            $Data->createCertificateInformation($tblCertificate, 'AdditionalRemarkFhr', 5);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setFsAbg(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Fachschule Abgangszeugnis', '', 'FsAbg',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeFachschule());
        if ($tblCertificate) {
            // ToDO hinterlegung irgendwelcher Fächer?

            // Begrenzung Eingabefelder
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 100);
            }
        }
    }

}