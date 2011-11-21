<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Tests\Generator\Builder\Om;

use Propel\Tests\Helpers\Bookstore\BookstoreTestBase;

use Propel\Generator\Model\ForeignKey;
use Propel\Generator\Builder\Om\OMBuilder;
use Propel\Generator\Builder\Util\XmlToAppData;
use Propel\Generator\Platform\DefaultPlatform;

/**
 * Test class for OMBuilder.
 *
 * @author     François Zaninotto
 * @version    $Id: OMBuilderBuilderTest.php 1347 2009-12-03 21:06:36Z francois $
 * @package    generator.builder.om
 */
class OMBuilderRelatedByTest extends \PHPUnit_Framework_TestCase
{
    static $database;

    public function setUp()
    {
        // run only once to save execution time
        if (null == self::$database) {
            $xmlToAppData = new XmlToAppData(new DefaultPlatform());
            $appData = $xmlToAppData->parseFile(realpath(__DIR__ . '/../../../../../Fixtures/bookstore/schema.xml'));
            self::$database = $appData->getDatabase("bookstore");
        }
    }

    protected function getForeignKey($tableName, $index)
    {
        $fks = self::$database->getTable($tableName)->getForeignKeys();

        return $fks[$index];
    }

    static public function getRelatedBySuffixDataProvider()
    {
        return array(
            array('book', 0, '', ''),
            array('essay', 0, 'RelatedByFirstAuthor', 'RelatedByFirstAuthor'),
            array('essay', 1, 'RelatedBySecondAuthor', 'RelatedBySecondAuthor'),
            array('essay', 2, 'RelatedById', 'RelatedByNextEssayId'),
            array('bookstore_employee', 0, 'RelatedById', 'RelatedBySupervisorId'),
            array('composite_essay', 0, 'RelatedById0', 'RelatedByFirstEssayId'),
            array('composite_essay', 1, 'RelatedById1', 'RelatedBySecondEssayId'),
            array('man', 0, 'RelatedByWifeId', 'RelatedByWifeId'),
            array('woman', 0, 'RelatedByHusbandId', 'RelatedByHusbandId'),
        );
    }

    /**
     * @dataProvider getRelatedBySuffixDataProvider
     */
    public function testGetRelatedBySuffix($table, $index, $expectedSuffix, $expectedReverseSuffix)
    {
        $fk = $this->getForeignKey($table, $index);
        $this->assertEquals($expectedSuffix, TestableOMBuilder::getRefRelatedBySuffix($fk));
        $this->assertEquals($expectedReverseSuffix, TestableOMBuilder::getRelatedBySuffix($fk));
    }
}

class TestableOMBuilder extends OMBuilder
{
    static public function getRelatedBySuffix(ForeignKey $fk)
    {
        return parent::getRelatedBySuffix($fk);
    }

    static public function getRefRelatedBySuffix(ForeignKey $fk)
    {
        return parent::getRefRelatedBySuffix($fk);
    }

    public function getUnprefixedClassname()
    {
    }
}
