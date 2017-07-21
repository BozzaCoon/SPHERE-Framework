<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Api\Reporting\Individual\ApiIndividual;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Individual
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Flexible Auswertung');

        $Content = Individual::useService()->getView();

//        Individual::useService()->getView();

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ApiIndividual::receiverNavigation(ApiIndividual::pipelineNewNavigation())
                            , 2),
                        new LayoutColumn(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn('Filter'),
                                        new LayoutColumn(new Title('TableResult')),
                                        new LayoutColumn(new TableData($Content)),
                                    ))
                                )
                            )
                            , 10)
                    ))
                )
            ))
        );

        return $Stage;
    }
}
