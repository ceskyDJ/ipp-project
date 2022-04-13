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
use Test\Exceptions\InvalidInputFileException;

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
     * @noinspection PhpUnusedParameterInspection Parameters are used in the template file
     * @throws InvalidInputFileException Not readable file (propagated from template)
     */
    public function create(TestReport $testReport): string
    {
        ob_start();

        include __DIR__ . '/../../templates/test-report.phtml';

        return ob_get_clean();
    }
}
