<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * LiteCommerce
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@litecommerce.com so we can send you a copy immediately.
 * 
 * @category   LiteCommerce
 * @package    XLite
 * @subpackage Model
 * @author     Creative Development LLC <info@cdev.ru> 
 * @copyright  Copyright (c) 2010 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version    SVN: $Id$
 * @link       http://www.litecommerce.com/
 * @see        ____file_see____
 * @since      3.0.0
 */

namespace XLite\Model\Repo;

use XLite\Core\Database as DB,
    XLite\Core\Converter;

/**
 * Abstract repository
 * 
 * @package XLite
 * @see     ____class_see____
 * @since   3.0.0
 */
abstract class ARepo extends \Doctrine\ORM\EntityRepository
{
    /**
     * Cache default TTL (1 year)
     */
    const CACHE_DEFAULT_TTL = 2592000;


    /**
     * Cache cell fields names
     */
    const KEY_TYPE_CACHE_CELL = 'keyType';
    const ATTRS_CACHE_CELL    = 'attrs';
    const RELATION_CACHE_CELL = 'relation';
    const CONVERTER_CACHE_CELL = 'converter';
    const GENERATOR_CACHE_CELL = 'generator';


    /**
     *  Cache key types
     */
    const CACHE_ATTR_KEY       = 'attributesKey';
    const CACHE_HASH_KEY       = 'hashKey';
    const CACHE_CUSTOM_KEY     = 'customKey';


    const DEFAULT_KEY_TYPE = self::CACHE_ATTR_KEY;


    const EMPTY_CACHE_CELL = 'all';

    /**
     * Cache cells (local cache)
     * 
     * @var    array
     * @access protected
     * @see    ____var_see____
     * @since  3.0.0
     */
    protected $cacheCells = null;

    /**
     * Default 'order by' field name
     * 
     * @var    string
     * @access protected
     * @see    ____var_see____
     * @since  3.0.0
     */
    protected $defaultOrderBy = null;

    /**
     * Default model alias 
     * 
     * @var    string
     * @access protected
     * @see    ____var_see____
     * @since  3.0.0
     */
    protected $defaultAlias = null;

    /**
     * Define cache cells 
     * 
     * @return array
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function defineCacheCells()
    {
        return array();
    }

    /**
     * Get cache cells 
     * 
     * @param string $key Cell name
     *  
     * @return array of cells / cell data
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getCacheCells($key = null)
    {
        if (!isset($this->cacheCells)) {
            $this->cacheCells = $this->restoreCacheCells();
        }

        return $key
            ? (isset($this->cacheCells[$key]) ? $this->cacheCells[$key] : null)
            : $this->cacheCells;
    }

    /**
     * Restore cache cells info from cache
     * 
     * @return array
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function restoreCacheCells()
    {
        $key = $this->getHashPrefix('cells');

        $cacheCells = DB::getCacheDriver()->fetch($key);

        if (!is_array($cacheCells)) {

            $cacheCells = $this->defineCacheCells();

            list($cacheCells, $relations) = $this->postprocessCacheCells($cacheCells);

            DB::getCacheDriver()->save($key, $cacheCells, self::CACHE_DEFAULT_TTL);

            // Save relations to current model cache cells from related models
            foreach ($relations as $model => $cells) {
                DB::getRepo($model)->addCacheRelations($cells);
            }
        }

        return $cacheCells;
    }

    /**
     * Postprocess cache cells info
     * 
     * @param array $cacheCells Cache cells
     *  
     * @return array (cache cells & relations data)
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function postprocessCacheCells(array $cacheCells)
    {

        $relations = array();

        // Normalize cache cells
        foreach ($cacheCells as $name => $cell) {

            // Set default cell type
            if (!isset($cell[self::KEY_TYPE_CACHE_CELL])) {
                $cell[self::KEY_TYPE_CACHE_CELL] = self::DEFAULT_KEY_TYPE;
            }

            // Set default cell attributes list
            if (!isset($cell[self::ATTRS_CACHE_CELL])) {
                $cell[self::ATTRS_CACHE_CELL] = null;
            }

            // Set default cell relations list
            if (!isset($cell[self::RELATION_CACHE_CELL])) {
                $cell[self::RELATION_CACHE_CELL] = array();
            }

            // Collect related models
            foreach ($cell[self::RELATION_CACHE_CELL] as $model) {
                if (!isset($relations[$model])) {
                    $relations[$model] = array($this->_entityName => array($name));

                } elseif (!isset($relations[$model][$this->_entityName])) {
                    $relations[$model][$this->_entityName] = array($name);

                } else {
                    $relations[$model][$this->_entityName][] = $name;
                }
            }

            // Set cell attributes converter method name
            $method = $this->getCacheParamsConverterName($name);
            $cell[self::CONVERTER_CACHE_CELL] = method_exists($this, $method)
                ? $method
                : false;

            // Set cell hash generator method name
            if (self::CACHE_CUSTOM_KEY == $this->cacheCells[$name][self::KEY_TYPE_CACHE_CELL]) {
                $cell[self::GENERATOR_CACHE_CELL] = $this->getCacheHashGeneratorName($name);
            }

            $cacheCells[$name] = $cell;
        }

        return array($cacheCells, $relations);
    }

    /**
     * Add cache relations
     * 
     * @param array $externalCells External cells
     *  
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function addCacheRelations(array $externalCells)
    {
        $key = $this->getHashPrefix('externalCells');

        $cacheCells = DB::getCacheDriver()->fetch($key);
        if (!is_array($cacheCells)) {
            $cacheCells = array();
        }

        foreach ($externalCells as $model => $cells) {
            if (isset($cacheCells[$model])) {
                $cacheCells[$model] = array_merge($cacheCells[$model], $cells);

            } else {
                $cacheCells[$model] = $cells;
            }
        }

        DB::getCacheDriver()->save($key, $cacheCells, self::CACHE_DEFAULT_TTL);
    }

    /**
     * Get related cache cells 
     * 
     * @return array
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function getRelatedCacheCells()
    {
        $cacheCells = DB::getCacheDriver()->fetch(
            $this->getHashPrefix('externalCells')
        );

        return is_array($cacheCells) ? $cacheCells : array();
    }

    /**
     * Get data from cache 
     * 
     * @param string $name   Cache cell name
     * @param array  $params Cache cell parameters
     *  
     * @return mixed or null
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getFromCache($name, array $params = array())
    {
        $result = null;

        $cell = $this->getCacheCells($name);
        if ($cell) {

            $result = DB::getCacheDriver()->fetch(
                $this->getCellHash($name, $cell, $params)
            );

        } else {
            // TODO - add throw exception
        }

        return (isset($result) && false !== $result) ? $result : null;
    }

    /**
     * Save data to cache 
     * 
     * @param mixed  $data   Data
     * @param string $name   Cache cell name
     * @param array  $params Cache cell parameters
     *  
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function saveToCache($data, $name, array $params = array())
    {
        $cell = $this->getCacheCells($name);
        if ($cell) {

            $hash = $this->getCellHash($name, $cell, $params);

            if ($data instanceof \ArrayAccess) {
                $this->detachList($data);

            } elseif ($data instanceof \XLite\Model\AEntity) {
                $data->detach();
            }

            DB::getCacheDriver()->save(
                $this->getCellHash($name, $cell, $params),
                $data,
                self::CACHE_DEFAULT_TTL
            );

        } else {
            // TODO - add throw exception
        }
    }

    /**
     * Get cell hash 
     * 
     * @param string $name   Cell name
     * @param array  $cell   Cell
     * @param array  $params Cache parameters
     *  
     * @return string or null
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getCellHash($name, array $cell, array $params)
    {
        $hash = null;

        if (self::CACHE_ATTR_KEY == $cell[self::KEY_TYPE_CACHE_CELL]) {

            $hash = implode('.', $params);

        } elseif (self::CACHE_HASH_KEY == $cell[self::KEY_TYPE_CACHE_CELL]) {

            $hash = md5(implode('.', $params));

        } elseif (self::CACHE_CUSTOM_KEY == $cell[self::KEY_TYPE_CACHE_CELL]) {

            $hash = $this->{$cell[self::GENERATOR_CACHE_CELL]}($params);
        }

        if (isset($hash) && empty($hash)) {
            $hash = self::EMPTY_CACHE_CELL;
        }

        return $this->getHashPrefix() . '.' . $name . '.' . $hash;
    }

    /**
     * Get prefix for cache key
     *
     * @param strin $suffix Cache subsection name
     * 
     * @return string
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getHashPrefix($suffix = 'data')
    {
        return str_replace('\\', '_', substr($this->_entityName, 6)) . '.' . $suffix;
    }

    /**
     * Get cell cache key generator method name 
     * 
     * @param string $name Cell name
     *  
     * @return string
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getCacheHashGeneratorName($name)
    {
        return 'getCacheHash' . Converter::convertToCamelCase($name);
    }

    /**
     * Get cell cache parameters converter method name 
     * 
     * @param string $name Cell name
     *  
     * @return string
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getCacheParamsConverterName($name)
    {
        return 'convertRecordToParams' . Converter::convertToCamelCase($name);
    }

    /**
     * Check - has repository any cache cells or not
     * 
     * @return boolean
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function hasCacheCells()
    {
        return $this->getCacheCells();
    }

    /**
     * Delete cache by entity
     * 
     * @param \XLite\Model\AEntity $entity Record
     *  
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function deleteCacheByEntity(\XLite\Model\AEntity $entity)
    {
        foreach ($this->getCacheCells() as $name => $cell) {

            // Get cell arguments
            if ($cell[self::CONVERTER_CACHE_CELL]) {
                   $params = $this->{$cell[self::CONVERTER_CACHE_CELL]}($entity);

            } elseif (
                is_array($cell[self::ATTRS_CACHE_CELL])
                && $cell[self::ATTRS_CACHE_CELL]
            ) {
                $params = array();
                foreach ($cell[self::ATTRS_CACHE_CELL] as $key) {
                    $params[$key] = $entity->$key;
                }

            } else {
                $params = array();
            }

            // Delete cell
            DB::getCacheDriver()->delete(
                $this->getCellHash($name, $cell, $params)
            );
        }

        // Delete related cache cells
        foreach ($this->getRelatedCacheCells() as $model => $cells) {
            $repo = DB::getRepo($model);
            foreach ($cells as $cell) {
                $repo->deleteCache($cell);
            }
        }
    }

    /**
     * Delete repository cache or cell cache
     * 
     * @param string $name Cell name
     *  
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function deleteCache($name = '')
    {
        DB::getCacheDriver()->deleteByPrefix($this->getHashPrefix() . '.' . $name);
    }

    /**
     * Assign default orderBy 
     * 
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder
     * @param string                     $alias        Table short alias in query builder
     *  
     * @return \Doctrine\ORM\QueryBuilder
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function assignDefaultOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null)
    {
        if ($this->defaultOrderBy) {

            if (!isset($alias)) {
                $alias = $this->getMainAlias($qb);
            }

            if (is_string($this->defaultOrderBy)) {

                // One field
                $queryBuilder->addOrderBy($alias . '.' . $this->defaultOrderBy);

            } elseif (is_array($this->defaultOrderBy)) {

                // Many fields (field name => sort suffix)
                foreach ($this->defaultOrderBy as $field => $asc) {
                    $queryBuilder->addOrderBy($alias . '.' . $field, $asc ? 'ASC' : 'DESC');
                }

            }
        }

        return $queryBuilder;
    }

    /**
     * Get Query builder main alias 
     * 
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *  
     * @return string
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getMainAlias(\Doctrine\ORM\QueryBuilder $qb)
    {
        $from = $qb->getDQLPart('from');
        $from = explode(' ', array_shift($from), 2);

        return isset($from[1]) ? $from[1] : $from[0];
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     * 
     * @param string $alias Table alias
     *  
     * @return \Doctrine\ORM\QueryBuilder
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function createQueryBuilder($alias = null)
    {
        if (!isset($alias)) {
            $alias = $this->getDefaultAlias();
        }

        return $this->assignDefaultOrderBy(parent::createQueryBuilder($alias), $alias);
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     * NOTE: without any relative subqueries!
     * 
     * @param string $alias Table alias
     *  
     * @return \Doctrine\ORM\QueryBuilder
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function createPureQueryBuilder($alias = null)
    {
        $alias = $alias ?: $this->getDefaultAlias();

        return $this->assignDefaultOrderBy(parent::createQueryBuilder($alias), $alias);
    }

    /**
     * getDefaultAlias 
     * 
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function getDefaultAlias()
    {
        if (!isset($this->defaultAlias)) {
            $list = explode('\\', $this->_entityName);
            $this->defaultAlias = strtolower(substr(array_pop($list), 0, 1));
        }

        return $this->defaultAlias;
    }

    /**
     * Count records
     * 
     * @return integer
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function count()
    {
        try {
            $count = intval($this->defineCountQuery()->getQuery()->getSingleScalarResult());

        } catch (\Doctrine\ORM\NonUniqueResultException $exception) {
            $count = 0;
        }

        return $count;
    }

    /**
     * Define Query fo rcount() method
     * 
     * @return \Doctrine\ORM\QueryBuilder
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function defineCountQuery()
    {
        $qb = $this->createPureQueryBuilder();

        return $qb->select('COUNT(' . implode(', ', $this->getIdentifiersList($qb)) . ')')
            ->setMaxResults(1);
    }

    /**
     * Find entities by id's list
     * 
     * @param array $ids Id's list
     *  
     * @return array
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function findByIds(array $ids)
    {
        if (1 < count($this->_class->identifier)) {
            // TODO - add throw exception
        }

        $result = array();

        if ($ids) {
            $qb = $this->createQueryBuilder();
            $keys = DB::buildInCondition($qb, $ids);
            $alias = $this->getMainAlias($qb);
            $qb->andWhere($alias . '.' . $this->_class->identifier[0] . ' IN (' . implode(', ', $keys) . ')');

            $result = $qb->getQuery()->getResult();
        }

        return $result;
    }

    /**
     * Find data frame 
     * 
     * @param int $start Start offset
     * @param int $limit Frame length
     *  
     * @return array
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function findFrame($start = 0, $limit = 0)
    {
        return $this->defineFrameQuery($start, $limit)->getQuery()->getResult();
    }

    /**
     * Define query for 'findFrame()' finder
     * 
     * @param integer $start Start offset
     * @param integer $limit Frame length
     *  
     * @return \Doctrine\ORM\QueryBuilder
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function defineFrameQuery($start, $limit)
    {
        return $this->assignFrame($this->createPureQueryBuilder(), $start, $limit);
    }

    /**
     * Assign frame to query builder
     * 
     * @param \Doctrine\ORM\QueryBuilder $qb    Query builder
     * @param int                        $start Start offset
     * @param int                        $limit Frame length
     *  
     * @return \Doctrine\ORM\QueryBuilder
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function assignFrame(\Doctrine\ORM\QueryBuilder $qb, $start = 0, $limit = 0)
    {
        $start = max(0, intval($start));
        $limit = max(0, intval($limit));

        if (0 < $start) {
            $qb->setFirstResult($start);
        }

        if (0 < $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Get identifiers list for specified query builder object
     * 
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *  
     * @return array
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getIdentifiersList(\Doctrine\ORM\QueryBuilder $qb)
    {
        $alias = $this->getMainAlias($qb);

        $list = array();

        foreach ($this->_class->identifier as $i) {
            $list[] = $alias . '.' . $i;
        }

        return $list;
    }

    /**
     * Detach entities list
     * 
     * @param array $data Entites list
     *  
     * @return array
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function detachList(array $data)
    {
        foreach ($data as $item) {
            $item->detach();
        }

        return $data;
    }

    /**
     * Finds an entity by its primary key / identifier and resturn entity detached
     * 
     * @param mixed $id The identifier.
     *  
     * @return XLite\Model\AEntity or null
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function findDetached($id)
    {
        $entity = parent::find($id);

        if ($entity) {
            $this->_em->detach($entity);
        }

        return $entity;
    }

    /**
     * Adds support for magic finders
     * 
     * @param string $method    Method name
     * @param array  $arguments Arguments list
     *  
     * @return array|object The found entity/entities
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function __call($method, $arguments)
    {
        if (0 === strncmp($method, 'findBy', 6)) {
            $by = substr($method, 6);
            $method = 'findBy';

        } elseif (0 === strncmp($method, 'findOneBy', 9)) {

            $by = substr($method, 9);
            $method = 'findOneBy';

        } else {
            throw new \BadMethodCallException(
                'Undefined method \'' . $method . '\'. The method name must start with '
                . 'either findBy or findOneBy!'
            );
        }

        if (!isset($arguments[0])) {
            throw \Doctrine\ORM\ORMException::findByRequiresParameter($method . $by);
        }

        $fieldName = \XLite\Core\Converter::convertFromCamelCase($by);

        if (!$this->_class->hasField($fieldName)) {
            throw \Doctrine\ORM\ORMException::invalidFindByCall(
                $this->_entityName,
                $fieldName, 
                $method . $by
            );
        }

        // Method name is findBy or findOneBy
        return $this->$method(array($fieldName => $arguments[0]));
    }



    /**
     * Flushes all changes to objects that have been queued up to now to the database
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function flushChanges()
    {
        return $this->getEntityManager()->flush();
    }

    /**
     * Search entity by key.
     * If it's not found, the exception will be thrown
     *
     * @param int $id entity ID
     *
     * @return \XLite\Model\AEntity
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getById($id)
    {
        if (!($entity = $this->find($id))) {
            throw new \Exception(get_class($this) . '::updateById() - unknow ID (' . $id . ')');
        }

        return $entity;
    }

    /**
     * Insert single entity
     *
     * @param array $data data to use in action
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function performInsert(array $data)
    {
        $entity = new $this->_entityName;
        $entity->map($data);

        $this->getEntityManager()->persist($entity);

        return $entity;
    }

    /**
     * Update single entity
     *
     * @param int   $id   entity ID
     * @param array $data data to use in action
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function performUpdate($id, array $data)
    {
        $this->getById($id)->map($data);
    }

    /**
     * Remove single entity
     *
     * @param \XLite\Model\AEntity $entity entity to detach
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function performRemove(\XLite\Model\AEntity $entity)
    {
        $this->getEntityManager()->remove($entity);
    }

    /**
     * Delete single entity by ID
     *
     * @param int $id entity ID
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     *
     */
    protected function performDelete($id)
    {
        $this->performRemove($this->getById($id));
    }


    /**
     * Insert single entity
     *
     * NOTE: do not override this method: it will not affect the "insertInBatch()" one.
     * Override the "performInsert()" instead
     *
     * @param array $data data to use in action
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function insert(array $data)
    {
        $result = $this->performInsert($data);
        $this->flushChanges();

        return $result;
    }

    /**
     * Update single entity
     *
     * NOTE: do not override this method: it will not affect the "updateInBatch()" one.
     * Override the "performUpdate()" instead
     *
     * @param int   $id   entity ID
     * @param array $data data to use in action
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function update($id, array $data)
    {
        $this->performUpdate($id, $data);
        $this->flushChanges();
    }

    /**
     * Remove single entity
     *
     * NOTE: do not override this method: it will not affect the "removeInBatch()" one.
     * Override the "performRemove()" instead
     * 
     * @param \XLite\Model\AEntity $entity entity to detach
     *  
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function remove(\XLite\Model\AEntity $entity)
    {
        $this->performRemove(); 
        $this->flushChanges();
    }

    /**
     * Delete single entity by ID
     *
     * NOTE: do not override this method: it will not affect the "deleteInBatch()" one.
     * Override the "performDelete()" instead
     *
     * @param int $id entity ID
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     *
     */
    public function delete($id)
    {
        $this->performDelete($id);
        $this->flushChanges();
    }

    /**
     * Insert several items at once
     *
     * @param array $data data to save
     *
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function insertInBatch(array $data)
    {
        // TODO: check if it's really needed
    }

    /**
     * Update several items at once
     *
     * @param array $data data to save
     *
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function updateInBatch(array $data)
    {
        foreach ($data as $id => $fields) {
            $this->performUpdate($id, $fields);
        }

        $this->flushChanges();
    }

    /**
     * Remove several items at once
     *
     * @param \Doctrine\Common\Collections\Collection $entities entities to remove
     *  
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function removeInBatch(\Doctrine\Common\Collections\Collection $entities)
    {
        foreach ($entities as $entity) {
            $this->performRemove($entity);
        }

        $this->flushChanges();
    }

    /**
     * Delete several items at once (by ID)
     *
     * @param array $ids IDs to use
     *
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function deleteInBatch(array $ids)
    {
        foreach ($ids as $id) {
            $this->performDelete($id);
        }

        $this->flushChanges();
    }


    /**
     * Return name of the primary key field.
     * This method is used to determine entity persistence
     * 
     * @return string
     * @access public
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function getPrimaryKeyField()
    {
        return $this->getClassMetadata()->getSingleIdentifierFieldName();
    }
}
