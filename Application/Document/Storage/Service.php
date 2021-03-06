<?php
namespace SPHERE\Application\Document\Storage;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Document\Storage\Service\Data;
use SPHERE\Application\Document\Storage\Service\Entity\TblBinary;
use SPHERE\Application\Document\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Storage\Service\Entity\TblFileCategory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFileType;
use SPHERE\Application\Document\Storage\Service\Entity\TblPartition;
use SPHERE\Application\Document\Storage\Service\Entity\TblReferenceType;
use SPHERE\Application\Document\Storage\Service\Setup;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Document\Storage
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param int $Id
     *
     * @return false|TblDirectory
     */
    public function getDirectoryById($Id)
    {

        return (new Data($this->getBinding()))->getDirectoryById($Id);
    }

    /**
     * @param TblPartition $tblPartition
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByPartition(TblPartition $tblPartition)
    {

        return ( new Data($this->getBinding()) )->getDirectoryAllByPartition($tblPartition);
    }

    /**
     * @param int $Id
     *
     * @return false|TblFile
     */
    public function getFileById($Id)
    {

        return (new Data($this->getBinding()))->getFileById($Id);
    }

    /**
     * @param TblDirectory $tblDirectory
     *
     * @return false|TblFile[]
     */
    public function getFileAllByDirectory(TblDirectory $tblDirectory)
    {

        return ( new Data($this->getBinding()) )->getFileAllByDirectory($tblDirectory);
    }

    /**
     * @param int $Id
     *
     * @return false|TblPartition
     */
    public function getPartitionById($Id)
    {

        return (new Data($this->getBinding()))->getPartitionById($Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblBinary
     */
    public function getBinaryById($Id)
    {

        return (new Data($this->getBinding()))->getBinaryById($Id);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $IsLocked
     * @param string $Identifier
     *
     * @return TblPartition
     */
    public function createPartition($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        return (new Data($this->getBinding()))->createPartition($Name, $Description, $IsLocked, $Identifier);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param Certificate $Certificate
     * @param FilePointer $File
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     *
     * @return bool|TblFile
     */
    public function saveCertificateRevision(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        Certificate $Certificate,
        FilePointer $File,
        TblPrepareCertificate $tblPrepareCertificate = null
    ) {

        // Load Tmp
        if ($File->getFileExists()) {
            $File->loadFile();

            $tblPartition = $this->getPartitionByIdentifier(
                TblPartition::IDENTIFIER_CERTIFICATE_STORAGE
            );
            $tblYear = $tblDivision->getServiceTblYear();
            if ($tblYear) {
                $tblDirectory = $this->createDirectory(
                    $tblPartition, $tblYear->getYear(), $tblYear->getDescription(), null, true,
                    'TBL-YEAR-ID:'.$tblYear->getId()
                );
                $tblDirectory = $this->createDirectory(
                    $tblPartition, $tblPerson->getLastFirstName(), '', $tblDirectory, true,
                    'TBL-PERSON-ID:'.$tblPerson->getId()
                );
                $tblFileType = $this->getFileTypeByMimeType($File->getMimeType());
                if ($tblFileType && $tblDirectory) {
                    $tblFile = false;
                    $tblBinary = $this->createBinary($File->getFileContent());
                    if ($tblBinary) {
                        $name = $tblYear->getYear() . ' - ' . $tblPerson->getLastFirstName() . ' - '
                            . $Certificate->getCertificateName() . ' - '
                            . ($tblPrepareCertificate ? $tblPrepareCertificate->getId() : $tblDivision->getId());

                        if (($tblFile = $this->exitsFile($tblDirectory, $tblFileType, $name))) {
                            $this->updateFile($tblFile, $tblBinary, 'Zuletzt erstellt: ' . date('d.m.Y H:i:s'));
                        } else {
                            $tblFile = $this->createFile(
                                $tblBinary,
                                $tblDirectory,
                                $tblFileType,
                                $name,
                                'Erstellt: ' . date('d.m.Y H:i:s'),
                                true
                            );
                        }
                    }
                    if ($tblFile) {
                        return $tblFile;
                    } else {
                        return false;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblPartition
     */
    public function getPartitionByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getPartitionByIdentifier($Identifier);

    }

    /**
     * @param TblPartition $tblPartition
     * @param string       $Name
     * @param string       $Description
     * @param TblDirectory $tblDirectory
     * @param bool         $IsLocked
     * @param string       $Identifier
     *
     * @return TblDirectory
     */
    public function createDirectory(
        TblPartition $tblPartition,
        $Name,
        $Description,
        TblDirectory $tblDirectory = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        return (new Data($this->getBinding()))->createDirectory(
            $tblPartition,
            $Name,
            $Description,
            $tblDirectory,
            $IsLocked,
            $Identifier
        );
    }

    /**
     * @param string $MimeType
     *
     * @return false|TblFileType
     */
    public function getFileTypeByMimeType($MimeType)
    {

        return (new Data($this->getBinding()))->getFileTypeByMimeType($MimeType);
    }

    /**
     * @param string $BinaryBlob
     *
     * @return TblBinary
     */
    public function createBinary($BinaryBlob)
    {

        return (new Data($this->getBinding()))->createBinary($BinaryBlob);
    }

    /**
     * @param TblBinary    $tblBinary
     * @param TblDirectory $tblDirectory
     * @param TblFileType  $tblFileType
     * @param string       $Name
     * @param string       $Description
     * @param bool         $IsLocked
     *
     * @return TblFile
     */
    public function createFile(
        TblBinary $tblBinary,
        TblDirectory $tblDirectory,
        TblFileType $tblFileType,
        $Name,
        $Description = '',
        $IsLocked = false
    ) {

        return (new Data($this->getBinding()))->createFile(
            $tblBinary, $tblDirectory, $tblFileType, $Name, $Description, $IsLocked
        );
    }

    /**
     * @param int $Id
     *
     * @return false|TblFileType
     */
    public function getFileTypeById($Id)
    {

        return (new Data($this->getBinding()))->getFileTypeById($Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblFileCategory
     */
    public function getFileCategoryById($Id)
    {

        return (new Data($this->getBinding()))->getFileCategoryById($Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblReferenceType
     */
    public function getReferenceTypeById($Id)
    {

        return (new Data($this->getBinding()))->getReferenceTypeById($Id);
    }

    /**
     * @return false|TblFile[]
     */
    public function getCertificateRevisionFileAll()
    {
        $tblPartition = $this->getPartitionByIdentifier(
            TblPartition::IDENTIFIER_CERTIFICATE_STORAGE
        );

        $resultList = array();
        $tblDirectoryList = $this->getDirectoryAllByPartition($tblPartition);
        if ($tblDirectoryList) {
            foreach ($tblDirectoryList as $tblDirectory) {
                $tblFileList = $this->getFileAllByDirectory($tblDirectory);
                if ($tblFileList) {
                    foreach ($tblFileList as $tblFile) {
                        $resultList[] = $tblFile;
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblFile[]
     */
    public function getCertificateRevisionFileAllByPerson(TblPerson $tblPerson)
    {

        $tblPartition = $this->getPartitionByIdentifier(
            TblPartition::IDENTIFIER_CERTIFICATE_STORAGE
        );

        $resultList = array();
        $tblDirectoryList = $this->getDirectoryAllByPartition($tblPartition);
        if ($tblDirectoryList) {
            foreach ($tblDirectoryList as $tblDirectory) {
                if (strpos($tblDirectory->getIdentifier(), 'TBL-PERSON-ID:') !== false) {
                    $personId = substr($tblDirectory->getIdentifier(), strlen('TBL-PERSON-ID:'));
                    if ($personId == $tblPerson->getId()) {
                        $tblFileList = $this->getFileAllByDirectory($tblDirectory);
                        if ($tblFileList) {
                            foreach ($tblFileList as $tblFile) {
                                $resultList[] = $tblFile;
                            }
                        }
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDirectory $tblDirectory
     * @param TblFileType $tblFileType
     * @param $Name
     *
     * @return false|TblFile
     */
    public function exitsFile(
        TblDirectory $tblDirectory,
        TblFileType $tblFileType,
        $Name
    ) {

        return (new Data($this->getBinding()))->exitsFile($tblDirectory, $tblFileType, $Name);
    }

    /**
     * @param TblFile $tblFile
     * @param TblBinary $tblBinary
     * @param $Description
     *
     * @return bool
     */
    public function updateFile(
        TblFile $tblFile,
        TblBinary $tblBinary,
        $Description
    ) {

        return (new Data($this->getBinding()))->updateFile($tblFile, $tblBinary, $Description);
    }
}
