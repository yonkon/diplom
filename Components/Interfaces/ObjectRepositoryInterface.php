<?php

namespace Components\Interfaces;

interface ObjectRepositoryInterface {
  /**
   * Finds an object by its primary key / identifier.
   *
   * @param mixed $id The identifier.
   *
   * @return array The associative array.
   */
  public static function find($id);

  /**
   * Finds all objects in the repository.
   *
   * @return array The associative arrays.
   */
  public static function findAll();

  /**
   * Finds objects by a set of criteria.
   *
   * @param array $criteria
   * @param array|null $orderBy
   * @param int|null $limit
   * @param int|null $offset
   *
   * @return array The associative arrays.
   *
   */
  public static function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

  /**
   * Finds a single object by a set of criteria.
   *
   * @param array $criteria The criteria.
   *
   * @return array The associative array.
   */
  public static function findOneBy(array $criteria);
}