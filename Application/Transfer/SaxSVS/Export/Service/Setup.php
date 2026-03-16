<?php
namespace SPHERE\Application\Transfer\SaxSVS\Export\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Transfer\SaxSVS\Export\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableTransferSax($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTransferSax(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTransferSax');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'PersonUuid', self::FIELD_TYPE_STRING); // Idee dahinter: einfacher vergleich mit AntwortJSON
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_TEXT);
        return $Table;
    }
}
