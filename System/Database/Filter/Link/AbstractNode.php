<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Logic\AndLogic;
use SPHERE\System\Database\Filter\Logic\OrLogic;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractNode
 *
 * @package SPHERE\System\Database\Filter\Pile
 */
abstract class AbstractNode extends Extension
{

    protected static $Cache = false;
    /** @var array $PathList */
    private $PathList = array();
    /** @var Probe[] $ProbeList */
    private $ProbeList = array();
    /** @var int|true $Timeout */
    private $Timeout = 0;

    /**
     *
     * @param AbstractService $Service
     * @param Element         $Entity
     *
     * @return $this
     */
    public function addProbe(AbstractService $Service, Element $Entity)
    {

        array_push($this->ProbeList, new Probe($Service, $Entity));
        return $this;
    }

    /**
     * @param null|string $ParentProperty
     * @param null|string $ChildProperty
     *
     * @return $this
     */
    public function addPath($ParentProperty = null, $ChildProperty = null)
    {

        array_push($this->PathList, array($ParentProperty, $ChildProperty));
        return $this;
    }

    /**
     * @return array
     */
    public function getPathList()
    {

        return $this->PathList;
    }

    /**
     * Convert Input to LIKE compatible DateTime-Value
     *
     * EXPERIMENTEL
     * @internal
     *
     * @param string $Value
     * @return string
     */
    private function findDateTime($Value)
    {

        $PatternList = false;
        $ResultList = array();
        $DateFormat = 'Y-m-d';
        $Value = trim( $Value );
        $Input = explode('.', $Value);
        $Input = array_filter( $Input );
        $Size = count( $Input );
        switch ( $Size ) {
            case 1: {
                // No Correction
                break;
            }
            case 2: {
                // MM-YY equals YY-MM => LIKE '%XX-YY%' { 15-12 AND 12-01 } matches e.g. 2015-12-01 :-)
                if( preg_match('!^(0?[1-9]|1[012])\.?((19|20)?[0-9]{2})$!is', $Value) ) {
                    $PatternList = array(
                        '(0?[1-9]|1[012])\.?((19|20)?[0-9]{2})', // (M)M.YYYY
                        '(0?[1-9]|1[012])\.?([0-9]{2})', // (M)M.YY
                    );
                    $ResultList = array(
                        'm' => 1,
                        'y' => 2
                    );
                    $DateFormat = 'y-m';
                } else {
                    $PatternList = array(
                        '([0-9]{1,2})\.?([0-9]{1,2})', // (D)D.(M)M
                    );
                    $ResultList = array(
                        'd' => 1,
                        'm' => 2
                    );
                    $DateFormat = 'm-d';
                }
                break;
            }
            case 3: {
                $PatternList = array(
                    '(0?[1-9]|[12][0-9]|3[01])\.?(0?[1-9]|1[012])\.?((19|20)[0-9]{2})', // (M)M.YYYY
                    '(0?[1-9]|[12][0-9]|3[01])\.?(0?[1-9]|1[012])\.?([0-9]{2})', // (M)M.YY
                );
                $ResultList = array(
                    'd' => 1,
                    'm' => 2,
                    'y' => 3
                );
                $DateFormat = 'y-m-d';
                break;
            }
        }

        $Found = false;
        if( $PatternList ) {
            foreach( $PatternList as $Pattern ) {
                if( preg_match('!^' . $Pattern . '$!is', $Value, $Matches) ) {
                    $Found = true;
                    if( isset($ResultList['d']) ) {
                        $Day = $Matches[$ResultList['d']];
                    } else {
                        $Day = 1;
                    }
                    if( isset($ResultList['m']) ) {
                        $Month = $Matches[$ResultList['m']];
                    } else {
                        $Month = 0;
                    }
                    if( isset($ResultList['y']) ) {
                        $Year = $Matches[$ResultList['y']];
                    } else {
                        $Year = 0;
                    }
                    $DateTime = new \DateTime();
                    $DateTime->setDate( $Year, $Month, $Day );
                    $Value = $DateTime->format( $DateFormat );
                    break;
                }
            }
        }
        if( !$Found ) {
            $Value = $Input;
        }
        return $Value;
    }

    /**
     * @param array $Search array( ProbeIndex => array( 'Column' => 'Value', ... ), ... )
     *
     * @param int $Timeout
     * @return bool|\SPHERE\System\Database\Fitting\Element[]
     */
    public function searchData($Search, $Timeout = 60)
    {

        $ProbeList = $this->getProbeList();

        $CacheKey = array();
        $CacheDependency = array();
        foreach ($ProbeList as $Probe) {
            array_push($CacheKey, $Probe->getEntity()->getEntityFullName());
            array_push($CacheDependency, $Probe->getEntity());
        }
        array_push($CacheKey, $Search);
        $Cache = new DataCacheHandler(json_encode($CacheKey), $CacheDependency);

        if (!self::$Cache || null === ( $Result = $Cache->getData() )) {

            $ResultCache = array();

            $Restriction = array();
            /**
             * @var int $Index
             * @var Probe $Probe
             */
            foreach ($ProbeList as $Index => $Probe) {
                if (isset( $Search[$Index] )) {
                    $Filter = $Search[$Index];
                } else {
                    $Filter = array();
                }

                // Rewrite DateTime to Database
                // EXPERIMENTEL
                if( !empty( $Filter ) ) {
                    foreach ($Filter as $Expression => $PartList) {
                        $PartStorage = array();
                        foreach ($PartList as $Part => $Value) {
                            if( preg_match( '!^[0-9\.]+$!is', $Value ) ) {
                                $ReFormat = $this->findDateTime( $Value );
                                if( is_array( $ReFormat ) ) {
                                    unset( $Filter[$Expression][$Part] );
                                    $PartStorage = array_merge( $PartStorage, $ReFormat );
                                } else {
                                    $Filter[$Expression][$Part] = $ReFormat;
                                }
                            }
                        }
                        $Filter[$Expression] = array_unique( array_merge( $Filter[$Expression], $PartStorage ) );
                    }
                }

                $Logic = $this->createLogic($Filter, $Restriction, $Index);

                $EntityList = $Probe->findLogic($Logic);
                // Exit if Path is Empty = NO Result
                if (empty( $EntityList )) {
                    return array();
                }
                $ResultCache[$Index] = $EntityList;

                $PathCurrent = $this->getPath($Index);
                if (isset( $ProbeList[$Index + 1] )) {
                    $PathNext = $this->getPath($Index + 1);

                    $Restriction = array(
                        $PathNext[0] => $Probe->findLogicColumn($Logic, $PathCurrent[1])
                    );
                }
            }

            $Result = $this->parseResult($ResultCache, $Timeout);
            if (!$this->isTimeout() && self::$Cache) {
                $Cache->setData($Result);
            }
        }
        return $Result;
    }

    /**
     * @return Probe[]
     */
    public function getProbeList()
    {

        return $this->ProbeList;
    }

    /**
     * @param array $Search
     * @param array $Restriction
     * @param int   $ProbeIndex
     *
     * @return AndLogic
     */
    public function createLogic($Search, $Restriction, $ProbeIndex)
    {

        $Logic = (new AndLogic($this->getProbe($ProbeIndex)->useBuilder()));
        if (!empty( $Restriction )) {
            $Logic->addLogic(
                (new OrLogic($this->getProbe($ProbeIndex)->useBuilder()))->addCriteriaList(
                    $Restriction, OrLogic::COMPARISON_IN
                )
            );
        }
        if (!empty( $Search )) {
            $Logic->addLogic(
                (new AndLogic($this->getProbe($ProbeIndex)->useBuilder()))->addCriteriaList(
                    $Search, AndLogic::COMPARISON_LIKE
                )
            );
        }
        $Logic->addLogic(
            (new AndLogic($this->getProbe($ProbeIndex)->useBuilder()))->addCriteria(
                'EntityRemove', null, AndLogic::COMPARISON_EXACT
            )
        );
        return $Logic;
    }

    /**
     * @param int $Index
     *
     * @return Probe
     */
    public function getProbe($Index)
    {

        return $this->ProbeList[$Index];
    }

    /**
     * @param int $Index
     *
     * @return array
     */
    public function getPath($Index)
    {

        return $this->PathList[$Index];
    }

    /**
     * @param  $List
     * @param  int $Timeout
     * @return array
     */
    abstract protected function parseResult($List, $Timeout = 60);

    /**
     * @return bool
     */
    public function isTimeout() {
        if( $this->Timeout === true ) {
        return true;
        }
        return false;
    }

    /**
     * @param int $Timeout
     */
    protected function setTimeout($Timeout)
    {

        $this->Timeout = time() + $Timeout;
    }

    /**
     * @return bool
     */
    protected function checkTimeout()
    {
        if( time() > $this->Timeout ) {
            $this->Timeout = true;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int   $ParentKey      Id of Parent-Property
     * @param array $List
     * @param int   $ChildListIndex Index of Child-View-List
     *
     * @return array
     */
    protected function filterNodeList($ParentKey, $List, $ChildListIndex)
    {

        return array_filter($List[$ChildListIndex], function (AbstractView $Item) use ($ParentKey, $ChildListIndex) {

            $ChildKey = $Item->__get($this->getPath($ChildListIndex)[0]);
            if ($ParentKey == $ChildKey) {
                return true;
            }
            return false;
        });
    }
}
