<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Core\Reflection\Analyzer;

/**
 * Type analyzer processes annotated methods.
 */
class Type
{
    const CLASS_MAGE_BASE = \Magento\Framework\Reflection\MethodsMap::BASE_MODEL_CLASS;
    const CLASS_PRXGT_BASE = 'Flancer32\Lib\DataObject';
    const PATTERN_METHOD = "/\@method\s+(.+)\s+(.+)\((.*)\)(.*)/";
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_manObj;
    /** @var \Praxigento\Core\Reflection\Tool\Type */
    protected $_toolsType;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $manObj,
        \Praxigento\Core\Reflection\Tool\Type $toolsType
    ) {
        $this->_manObj = $manObj;
        $this->_toolsType = $toolsType;
    }

    /**
     * Determines if the method is suitable to be used by the processor.
     * (see \Magento\Framework\Reflection\MethodsMap::isSuitableMethod)
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    public function _isSuitableMethod(\ReflectionMethod $method)
    {
        /* '&&' usage is shorter then '||', if first part is 'false' then all equity is false */
        $isSuitableMethodType = (
            !$method->isStatic() &&
            !$method->isFinal() &&
            !$method->isConstructor() &&
            !$method->isDestructor()
        );
        $isExcludedMagicMethod = (strpos($method->getName(), '__') === 0);
        $result = $isSuitableMethodType && !$isExcludedMagicMethod;
        return $result;
    }

    /**
     * Analyze class documentation block and extract annotated methods.
     *
     * @param \Zend\Code\Reflection\DocBlockReflection $block Documentation block.
     * @return \Praxigento\Core\Reflection\Data\Method[]
     */
    public function _processClassDocBlock(\Zend\Code\Reflection\DocBlockReflection $block)
    {
        $result = [];
        if ($block) {
            $docBlockLines = $block->getContents();
            $docBlockLines = explode("\n", $docBlockLines);
            foreach ($docBlockLines as $line) {
                $methodData = $this->_processClassDocLine($line);
                if ($methodData) {
                    $name = $methodData->getName();
                    $result[$name] = $methodData;
                }
            }
        }
        return $result;
    }

    /**
     * Analyze class level documentation line and extract method data.
     *
     * @param string $line
     * @return \Praxigento\Core\Reflection\Data\Method|null
     */
    public function _processClassDocLine($line)
    {
        $result = null;
        if (preg_match(self::PATTERN_METHOD, $line, $matches)) {
            /* parse and transform template's data */
            $isRequired = true;
            $returnType = $matches[1];
            if (substr($returnType, -0, strlen('|null')) == '|null') {
                $returnType = str_replace('|null', '', $returnType);
                $isRequired = false;
            }
            $returnType = $this->_toolsType->normalizeType($returnType);
            $methodName = lcfirst($matches[2]);
            $paramsCount = 0; // TODO: params count & desc
            $desc = $matches[4]??'';
            /* compose result  */
            /** @var \Praxigento\Core\Reflection\Data\Method $result */
            $result = $this->_manObj->create(\Praxigento\Core\Reflection\Data\Method::class);
            $result->setName($methodName);
            $result->setIsRequired($isRequired);
            $result->setType($returnType);
            $result->setDescription($desc);
            $result->setParameterCount($paramsCount);
        }
        return $result;
    }

    /**
     * @param \Zend\Code\Reflection\MethodReflection[] $methods
     * @return \Praxigento\Core\Reflection\Data\Method[]
     */
    public function _processClassMethods($methods)
    {
        $result = [];
        /* see \Magento\Framework\Reflection\MethodsMap::getMethodMapViaReflection */
        foreach ($methods as $method) {
            // Include all the methods of classes inheriting from AbstractExtensibleObject.
            // Ignore all the methods of AbstractExtensibleModel's parent classes
            $class = $method->class;
            if (
                ($class === self::CLASS_MAGE_BASE) ||
                ($class === self::CLASS_PRXGT_BASE)
            ) {
                // ReflectionClass::getMethods() sorts the methods by class
                // (lowest in inheritance tree first)
                // then by the order they are defined in the class definition
                break;
            }
            if ($this->_isSuitableMethod($method)) {
                $name = $method->getName();
                $params = $method->getNumberOfRequiredParameters();
                $docBlock = $method->getDocBlock();
                /** @var \Praxigento\Core\Reflection\Data\Method $entry */
                $entry = $this->_processMethodDocBlock($docBlock);
                $entry->setName($name);
                $entry->setParameterCount($params);
                $result[$name] = $entry;
            }
        }
        return $result;
    }

    /**
     * @param $docBlock
     * @return \Praxigento\Core\Reflection\Data\Method
     */
    public function _processMethodDocBlock($docBlock)
    {
        /** @var \Praxigento\Core\Reflection\Data\Method $result */
        $result = $this->_manObj->create(\Praxigento\Core\Reflection\Data\Method::class);
        if ($docBlock) {
            $returnAnnotations = $docBlock->getTags('return');
            if (!empty($returnAnnotations)) {
                /** @var \Zend\Code\Reflection\DocBlock\Tag\ReturnTag $returnTag */
                $returnTag = current($returnAnnotations);
                $types = $returnTag->getTypes();
                $returnType = current($types);
                $returnType = $this->_toolsType->normalizeType($returnType);
                $nullable = in_array('null', $types);
                $desc = $returnTag->getDescription();
                $result->setType($returnType);
                $result->setIsRequired(!$nullable);
                $result->setDescription($desc);
            }
        }
        return $result;
    }

    /**
     * Analyze given type and return methods meta data (name, return type, description, etc.)
     *
     * @param string $type
     * @return \Praxigento\Core\Reflection\Data\Method[]
     */
    public function getMethods($type)
    {
        $typeNorm = $this->_toolsType->normalizeType($type);
        /** @var \Zend\Code\Reflection\ClassReflection $reflection */
        $reflection = $this->_manObj->create(
            \Zend\Code\Reflection\ClassReflection::class,
            ['argument' => $typeNorm]
        );
        $docBlock = $reflection->getDocBlock();
        $annotatedMethods = $this->_processClassDocBlock($docBlock);
        /* process normal methods (not annotated) */
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $generalMethods = $this->_processClassMethods($publicMethods);
        $result = array_merge($generalMethods, $annotatedMethods);
        return $result;
    }
}