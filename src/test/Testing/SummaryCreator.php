<?php
/**
 * This is a part of IPP project
 *
 * @author Michal Šmahel (xsmahe01)
 * @date 2022
 */

declare(strict_types=1);

namespace Test\Testing;

use Test\Entity\TestReport;

/**
 * Creator of the summary HTML output
 */
class SummaryCreator
{

    /**
     * Creates HTML test summary from test report
     *
     * @param TestReport $testReport Test report with run tests
     *
     * @return string HTML summary
     */
    public function create(TestReport $testReport): string
    {
        // TODO: implement this method
    }
}
