<?php

namespace Krlove\EloquentModelGenerator\Processor;

use Illuminate\Database\DatabaseManager;
use Krlove\CodeGenerator\Model\DocBlockModel;
use Krlove\CodeGenerator\Model\PropertyModel;
use Krlove\CodeGenerator\Model\VirtualPropertyModel;
use Krlove\EloquentModelGenerator\Config;
use Krlove\EloquentModelGenerator\Helper\EmgHelper;
use Krlove\EloquentModelGenerator\Model\EloquentModel;
use Krlove\EloquentModelGenerator\TypeRegistry;

/**
 * Class FieldProcessor
 * @package Krlove\EloquentModelGenerator\Processor
 */
class FieldProcessor implements ProcessorInterface
{
    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var TypeRegistry
     */
    protected $typeRegistry;

    /**
     * FieldProcessor constructor.
     * @param DatabaseManager $databaseManager
     * @param TypeRegistry $typeRegistry
     */
    public function __construct(DatabaseManager $databaseManager, TypeRegistry $typeRegistry)
    {
        $this->databaseManager = $databaseManager;
        $this->typeRegistry    = $typeRegistry;
    }

    /**
     * @inheritdoc
     */
    public function process(EloquentModel $model, Config $config)
    {
        $schemaManager = $this->databaseManager->connection($config->get('connection'))->getDoctrineSchemaManager();
        $prefix        = $this->databaseManager->connection($config->get('connection'))->getTablePrefix();

        $tableDetails       = $schemaManager->listTableDetails($prefix . $model->getTableName());
        $primaryColumnNames = $tableDetails->getPrimaryKey() ? $tableDetails->getPrimaryKey()->getColumns() : [];

        $primaryKey  = null;
        $columnNames = [];
        foreach ($tableDetails->getColumns() as $column) {
            $model->addProperty(new VirtualPropertyModel(
                $column->getName(),
                $this->typeRegistry->resolveType($column->getType()->getName())
            ));

            if (!in_array($column->getName(), $primaryColumnNames)) {
                $columnNames[] = $column->getName();
            } else {
                if (!$primaryKey) {
                    $primaryKey = $column;
                }
            }
        }

        if ($primaryKey) {
            if ($primaryKey->getName() != EmgHelper::DEFAULT_PRIMARY_KEY) {
                $primaryType = $this->typeRegistry->resolveType($column->getType()->getName());
                $primaryProperty = new PropertyModel('primaryKey');
                $primaryProperty->setAccess('protected')
                   ->setValue($primaryKey->getName())
                   ->setDocBlock(new DocBlockModel('The primary key for the model.', '', '@var ' . $primaryType));
                $model->addProperty($primaryProperty);
            }

            $incrementProperty = new PropertyModel('incrementing');
            $incrementProperty->setAccess('protected')
               ->setValue($primaryKey->getAutoincrement() ? true : false)
               ->setDocBlock(new DocBlockModel('Indicates if the IDs are auto-incrementing.', '', '@var bool'));
            $model->addProperty($incrementProperty);
        }

        $fillableProperty = new PropertyModel('fillable');
        $fillableProperty->setAccess('protected')
            ->setValue($columnNames)
            ->setDocBlock(new DocBlockModel('@var array'));
        $model->addProperty($fillableProperty);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 5;
    }
}
