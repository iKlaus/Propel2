<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Generator\Builder\Om;

use Propel\Generator\Model\Inheritance;

/**
 * Generates the empty PHP5 stub query class for use with single table inheritance.
 *
 * This class produces the empty stub class that can be customized with application
 * business logic, custom behavior, etc.
 *
 *
 * @author     François Zaninotto
 * @package    propel.generator.builder.om
 */
class QueryInheritanceBuilder extends OMBuilder
{

    /**
     * The current child "object" we are operating on.
     */
    protected $child;

    /**
     * Returns the name of the current class being built.
     * @return     string
     */
    public function getUnprefixedClassname()
    {
        return $this->getBuildProperty('basePrefix') . $this->getNewStubQueryInheritanceBuilder($this->getChild())->getUnprefixedClassname();
    }

    /**
     * Gets the package for the [base] object classes.
     * @return     string
     */
    public function getPackage()
    {
        return ($this->getChild()->getPackage() ? $this->getChild()->getPackage() : parent::getPackage()) . ".Om";
    }

    public function getNamespace()
    {
        if ($namespace = parent::getNamespace()) {
            if ($this->getGeneratorConfig() && $omns = $this->getGeneratorConfig()->getBuildProperty('namespaceOm')) {
                return $namespace . '\\' . $omns;
            } else {
                return $namespace;
            }
        }
    }

    /**
     * Set the child object that we're operating on currrently.
     * @param      $child Inheritance
     */
    public function setChild(Inheritance $child)
    {
        $this->child = $child;
    }

    /**
     * Returns the child object we're operating on currently.
     * @return     Inheritance
     * @throws     BuildException - if child was not set.
     */
    public function getChild()
    {
        if (!$this->child) {
            throw new BuildException("The PHP5MultiExtendObjectBuilder needs to be told which child class to build (via setChild() method) before it can build the stub class.");
        }

        return $this->child;
    }

    /**
     * Returns classpath to parent class.
     * @return     string
     */
    protected function getParentClassName()
    {
        $ancestorClassName = ClassTools::classname($this->getChild()->getAncestor());
        if ($this->getDatabase()->hasTableByPhpName($ancestorClassName)) {
            return $this->getNewStubQueryBuilder($this->getDatabase()->getTableByPhpName($ancestorClassName))->getClassname();
        } else {
            // find the inheritance for the parent class
            foreach ($this->getTable()->getChildrenColumn()->getChildren() as $child) {
                if ($child->getClassName() == $ancestorClassName) {
                    return $this->getNewStubQueryInheritanceBuilder($child)->getClassname();
                }
            }
        }
    }

    /**
     * Adds class phpdoc comment and openning of class.
     * @param      string &$script The script will be modified in this method.
     */
    protected function addClassOpen(&$script)
    {
        $table = $this->getTable();
        $tableName = $table->getName();
        $tableDesc = $table->getDescription();

        $baseBuilder = $this->getStubQueryBuilder();
        $this->declareClassFromBuilder($baseBuilder);
        $baseClassname = $this->getParentClassName();

        $script .= "
/**
 * Skeleton subclass for representing a query for one of the subclasses of the '$tableName' table.
 *
 * $tableDesc
 *";
        if ($this->getBuildProperty('addTimeStamp')) {
            $now = strftime('%c');
            $script .= "
 * This class was autogenerated by Propel " . $this->getBuildProperty('version') . " on:
 *
 * $now
 *";
        }
        $script .= "
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.".$this->getPackage()."
 */
class "  .$this->getClassname() . " extends " . $baseClassname . " {
";
    }

    /**
     * Specifies the methods that are added as part of the stub object class.
     *
     * By default there are no methods for the empty stub classes; override this method
     * if you want to change that behavior.
     *
     * @see        ObjectBuilder::addClassBody()
     */
    protected function addClassBody(&$script)
    {
        $this->declareClassFromBuilder($this->getStubPeerBuilder());
        $this->declareClasses(
            '\Propel\Runtime\Connection\ConnectionInterface',
            '\Propel\Runtime\Query\Criteria'
        );
        $this->addFactory($script);
        $this->addPreSelect($script);
        $this->addPreUpdate($script);
        $this->addPreDelete($script);
        $this->addDoDeleteAll($script);
    }

    /**
     * Adds the factory for this object.
     * @param      string &$script The script will be modified in this method.
     */
    protected function addFactory(&$script)
    {
        $builder = $this->getNewStubQueryInheritanceBuilder($this->getChild());
        $this->declareClassFromBuilder($builder);
        $classname = $builder->getClassname();
        $script .= "
    /**
     * Returns a new " . $classname . " object.
     *
     * @param     string \$modelAlias The alias of a model in the query
     * @param     Criteria \$criteria Optional Criteria to build the query from
     *
     * @return    " . $classname . "
     */
    static public function create(\$modelAlias = null, \$criteria = null)
    {
        if (\$criteria instanceof " . $classname . ") {
            return \$criteria;
        }
        \$query = new " . $classname . "();
        if (null !== \$modelAlias) {
            \$query->setModelAlias(\$modelAlias);
        }
        if (\$criteria instanceof Criteria) {
            \$query->mergeWith(\$criteria);
        }

        return \$query;
    }
";
    }

    protected function addPreSelect(&$script)
    {
        $child = $this->getChild();
        $col = $child->getColumn();

        $script .= "
    /**
     * Filters the query to target only " . $child->getClassname() . " objects.
     */
    public function preSelect(ConnectionInterface \$con)
    {
        " . $this->getClassKeyCondition() . "
    }
";
    }

    protected function addPreUpdate(&$script)
    {
        $child = $this->getChild();
        $col = $child->getColumn();

        $script .= "
    /**
     * Filters the query to target only " . $child->getClassname() . " objects.
     */
    public function preUpdate(&\$values, ConnectionInterface \$con, \$forceIndividualSaves = false)
    {
        " . $this->getClassKeyCondition() . "
    }
";
    }

    protected function addPreDelete(&$script)
    {
        $child = $this->getChild();
        $col = $child->getColumn();

        $script .= "
    /**
     * Filters the query to target only " . $child->getClassname() . " objects.
     */
    public function preDelete(ConnectionInterface \$con)
    {
        " . $this->getClassKeyCondition() . "
    }
";
    }

    protected function getClassKeyCondition()
    {
        $child = $this->getChild();
        $col = $child->getColumn();

        return "\$this->addUsingAlias(" . $col->getConstantName() . ", " . $this->getPeerClassname()."::CLASSKEY_".strtoupper($child->getKey()).");";
    }

    protected function addDoDeleteAll(&$script)
    {
        $child = $this->getChild();

        $script .= "
    /**
     * Issue a DELETE query based on the current ModelCriteria deleting all rows in the table
     * Having the " . $child->getClassname() . " class.
     * This method is called by ModelCriteria::deleteAll() inside a transaction
     *
     * @param ConnectionInterface \$con a connection object
     *
     * @return integer the number of deleted rows
     */
    public function doDeleteAll(\$con)
    {
        // condition on class key is already added in preDelete()
        return parent::doDelete(\$con);
    }
";
    }

    /**
     * Closes class.
     * @param      string &$script The script will be modified in this method.
     */
    protected function addClassClose(&$script)
    {
        $script .= "
} // " . $this->getClassname() . "
";
    }

} // MultiExtensionQueryBuilder
