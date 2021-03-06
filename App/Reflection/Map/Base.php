<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Core\App\Reflection\Map;

/**
 * Base class for mappers that create and cache maps used in JSON-DO-JSON conversion.
 * (see \Magento\Framework\Reflection\MethodsMap)
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
abstract class Base
{
    /**
     * Prefix for keys to store parsed data in the app. cache. We use the same prefix, so we need have the same
     * data structure with \Magento\Framework\Reflection\MethodsMap.
     */
    const CACHE_PREFIX = \Magento\Framework\Reflection\MethodsMap::SERVICE_INTERFACE_METHODS_CACHE_PREFIX;
    /**
     * Praxigento analyzer to parse types consider annotated methods.
     *
     * @var \Praxigento\Core\App\Reflection\Analyzer\Type
     */
    protected $_analyzer;
    /**
     * Application cache that stores previously analyzed results.
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;
    /**
     * Internal registry for mapped types (uses type name as key instead of md5-hash).
     *
     * @var array
     */
    protected $_map = [];

    public function __construct(
        \Magento\Framework\App\Cache\Type\Reflection $cache,
        \Praxigento\Core\App\Reflection\Analyzer\Type $analyzer
    ) {
        $this->_cache = $cache;
        $this->_analyzer = $analyzer;
    }

    /**
     * Convert common format to required format (class data or props data).
     *
     * @param array $saved
     * @return array
     */
    public abstract function _parseMetaData($saved);

    /**
     * Get methods map for the type: [methodName=>[type,isRequired, description,parameterCount], ...]
     *
     * @param string $typeName class/interface name (\Vendor\Package\Space\Type)
     * @return array
     */
    public function getMap($typeName)
    {
        if (!isset($this->_map[$typeName])) {
            /* try to load from cache */
            $key = self::CACHE_PREFIX . "-" . sha1($typeName);
            $cached = $this->_cache->load($key);
            if ($cached) {
                /* get, un-serialize and register cached data */
                $meta = unserialize($cached);
                $parsed = $this->_parseMetaData($meta);
                $this->_map[$typeName] = $parsed;
            } else {
                /* launch type methods analyzer and save results to the cache */
                $meta = $this->_analyzer->getMethods($typeName);
                $parsed = $this->_parseMetaData($meta);
                $this->_map[$typeName] = $parsed;
                $cached = serialize($meta);
                $this->_cache->save($cached, $key);
            }
        }
        $result = $this->_map[$typeName];
        return $result;
    }
}