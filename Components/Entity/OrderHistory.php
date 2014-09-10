<?php

namespace Components\Entity;

use Components\Classes\EntityRepository;

class OrderHistory extends EntityRepository {
  const TABLE = TABLE_ORDERS_CHANGES_HISTORY;
}