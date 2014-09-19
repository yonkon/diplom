<?php

namespace Components\Classes;

use Components\Interfaces\ObjectRepositoryInterface;

use Components\Exceptions\EntityNotFoundException;
use Components\Exceptions\InvalidArgumentException;

class EntityRepository implements ObjectRepositoryInterface {
  /**
   * @param mixed $id
   * @return array
   * @throws EntityNotFoundException
   * @throws InvalidArgumentException
   */
  public static function find($id) {
    if (!empty($id) and is_numeric($id)) {
      $entity = db::get_single_row("SELECT * FROM " . static::TABLE . " WHERE id = " . db::input($id));

      if (empty($entity)) {
        throw new EntityNotFoundException($id, self::getEntity());
      } else {
        return $entity;
      }
    } else {
      throw new InvalidArgumentException(self::getEntity());
    }
  }

  /**
   * Finds all entities in the repository.
   *
   * @return array The entities.
   */
  public static function findAll() {
    return self::findBy(array());
  }

  /**
   * Finds entities by a set of criteria.
   *
   * @param array $criteria
   * @param array|null $orderBy
   * @param int|null $limit
   * @param int|null $offset
   *
   * @throws InvalidArgumentException
   * @return array The objects.
   */
  public static function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
    if (is_array($criteria)) {
      $where = '';
      foreach($criteria as $key => $value) {
        if (is_null($value)) {
          continue;
        }
        if (!empty($where)) {
          $where .= " AND ";
        }
        $where .= $key . " = '" . db::input($value) . "'";
      }
      if (!empty($where)) {
        $where = "WHERE " . $where;
      } else {
        $where = "WHERE 1";
      }

      $order = '';
      if (is_array($orderBy) && count($orderBy)) {
        $order = 'ORDER BY ';
        foreach($orderBy as $key => $value) {
          $order .= $key . ' ' . $value;
        }
      }

      $sLimit = '';
      if (!is_null($offset) || !is_null($limit)) {
        $sLimit = 'LIMIT ';
        if (!is_null($offset)) {
          $sLimit .= $offset . ', ';
        }
        if (!is_null($limit)) {
          $sLimit .= $limit;
        }
      }

      return db::get_arrays("
        SELECT *
        FROM " . static::TABLE . "
        " . $where . "
        " . $order . "
        " . $sLimit . "
      ");
    } else {
      throw new InvalidArgumentException(self::getEntity());
    }
  }

  /**
   * Finds a single entity by a set of criteria.
   *
   * @param array $criteria
   * @param array|null $orderBy
   * @return array
   */
  public static function findOneBy(array $criteria, array $orderBy = null) {
    $rows = self::findBy($criteria, $orderBy, 1);
    return isset($rows[0]) ? $rows[0] : array();
  }

  /**
   * @param mixed $id
   * @param array $data
   *
   * @throws InvalidArgumentException
   * @return bool
   */
  public static function update($id, array $data) {
    if (empty($id) || empty($data) || !is_array($data)) {
      throw new InvalidArgumentException(self::getEntity());
    }


    return db::update(static::TABLE, $data, 'id = ' . $id);
  }

  /**
   * @param array $data
   *
   * @return int
   * @throws InvalidArgumentException
   */
  public static function create(array $data) {
    if (empty($data) || !is_array($data)) {
      throw new InvalidArgumentException(self::getEntity());
    }

    db::insert(static::TABLE, $data);
    return db::insert_id();
  }

  /**
   * @param $id
   *
   * @return bool
   * @throws InvalidArgumentException
   */
  public static function delete($id) {
    if (empty($id) || !is_numeric($id)) {
      throw new InvalidArgumentException(self::getEntity());
    }

    db::delete(static::TABLE, 'id = ' . $id);

    return true;
  }

  /**
   * @return string
   */
  public static function getTable() {
    return static::TABLE;
  }

  /**
   * @return string
   */
  public static function getEntity() {
    return get_called_class();
  }
}