<?php

namespace Krlove\EloquentModelGenerator\Processor;

use Krlove\CodeGenerator\Model\ArgumentModel;
use Krlove\CodeGenerator\Model\DocBlockModel;
use Krlove\CodeGenerator\Model\PropertyModel;
use Krlove\CodeGenerator\Model\VirtualMethodModel;
use Krlove\CodeGenerator\Model\VirtualPropertyModel;
use Krlove\EloquentModelGenerator\Config;
use Krlove\EloquentModelGenerator\Model\EloquentModel;

/**
 * Class CustomPropertyProcessor
 * @package Krlove\EloquentModelGenerator\Processor
 */
class CustomPropertyProcessor implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(EloquentModel $model, Config $config)
    {
        if ($config->get('no_timestamps') === true) {
            $pNoTimestamps = new PropertyModel('timestamps', 'public', false);
            $pNoTimestamps->setDocBlock(
                new DocBlockModel('Indicates if the model should be timestamped.', '', '@var bool')
            );
            $model->addProperty($pNoTimestamps);
        }

        if ($config->has('date_format')) {
            $pDateFormat = new PropertyModel('dateFormat', 'protected', $config->get('date_format'));
            $pDateFormat->setDocBlock(
                new DocBlockModel('The storage format of the model\'s date columns.', '', '@var string')
            );
            $model->addProperty($pDateFormat);
        }

        if ($config->has('connection')) {
            $pConnection = new PropertyModel('connection', 'protected', $config->get('connection'));
            $pConnection->setDocBlock(
                new DocBlockModel('The connection name for the model.', '', '@var string')
            );
            $model->addProperty($pConnection);
        }

        $class = $model->getName()->getName();
        $virtualMethod = new VirtualMethodModel('all', sprintf('static Collection|%s[]', $class));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);

        $virtualMethod = new VirtualMethodModel('find', sprintf('static Collection|%s[]|null', $class));
        $virtualMethod->addArgument(new ArgumentModel('id'));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);

        $virtualMethod = new VirtualMethodModel('findOrNew', sprintf('static Collection|%s', $class));
        $virtualMethod->addArgument(new ArgumentModel('id'));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);

        $virtualMethod = new VirtualMethodModel('findMany', sprintf('static Collection|%s[]', $class));
        $virtualMethod->addArgument(new ArgumentModel('ids'));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);

        $virtualMethod = new VirtualMethodModel('findOrFail', sprintf('static Collection|%s', $class));
        $virtualMethod->addArgument(new ArgumentModel('id'));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);

        $virtualMethod = new VirtualMethodModel('first', sprintf('static Collection|%s[]|null', $class));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);

        $virtualMethod = new VirtualMethodModel('firstOrFail', sprintf('static Collection|%s[]', $class));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);

        $virtualMethod = new VirtualMethodModel('get', sprintf('static Collection|%s[]', $class));
        $virtualMethod->addArgument(new ArgumentModel('columns', null, '[\'*\']'));
        $model->addMethod($virtualMethod);
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 5;
    }
}
