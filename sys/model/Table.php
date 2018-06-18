<?php

namespace _M;


class Table {
  private static $caches = [];
  public $class;
  public $table;
  public $columns;
  public $primaryKeys;

  public function __construct($className) {
    $this->class = Reflections::instance()->add($className)->get($className);

    $this->setTableName($className)
         ->getMetaData()
         ->setPrimaryKeys();
  }

  private function setPrimaryKeys() {
    $this->primaryKeys = ($primaryKeys = $this->class->getStaticPropertyValue('primaryKeys', null)) ? is_array($primaryKeys) ? $primaryKeys : [$primaryKeys] : \M\modelsColumn(array_filter($this->columns, function ($column) { return $column->primaryKey; }), 'name');
    return $this;
  }

  private function getMetaData () {
    $table = Connection::instance()->quoteName($this->table);
    $this->columns = Connection::instance()->columns($table);
    return $this;
  }

  private function setTableName($className){
    $this->table = ($table = $this->class->getStaticPropertyValue('tableName', null)) ? $table : \M\denamespace($this->class->getName());
    return $this;
  }

  public static function load($modelClassName) {
    return isset(self::$caches[$modelClassName]) ? self::$caches[$modelClassName] : self::$caches[$modelClassName] = new Table($modelClassName);
  }
  
  public function find($options) {
    $sql = $this->optionsToSql($options);

    $readonly = (array_key_exists('readonly',$options) && $options['readonly']) ? true : false;
    $eager_load = array_key_exists('include',$options) ? $options['include'] : null;

    return $this->find_by_sql($sql->to_s(),$sql->get_where_values(), $readonly, $eager_load);
  }
  
  public function createJoins($joins) {
    if (!is_array($joins))
      return $joins;

    $ret = $space = '';

    $existingTables = [];

    foreach ($joins as $value) {
      $ret .= $space;

      if (stripos($value, 'JOIN ') === false) {

        if (array_key_exists($value, $this->relationships)) {

          $rel = $this->get_relationship($value);

          if (array_key_exists($rel->class_name, $existingTables)) {
            $alias = $value;
            $existingTables[$rel->class_name]++;
          } else {
            $existingTables[$rel->class_name] = true;
            $alias = null;
          }

          $ret .= $rel->construct_inner_join_sql($this, false, $alias);
        }
        else
          throw new RelationshipException("Relationship named $value has not been declared for class: {$this->class->getName()}");
      }
      else
        $ret .= $value;

      $space = ' ';
    }
    return $ret;
  }
  public function optionsToSql($options) {
    $table = Connection::instance()->quoteName(array_key_exists('from', $options) ? $options['from'] : $this->table);
    
    $sql = new SqlBuilder($table);


    if (isset($options['joins'])) {
      $sql->joins($this->createJoins($options['joins']));

      isset($options['select']) || $options['select'] = Connection::instance()->quoteName($this->table) . '.*';
    }

    isset($options['select']) && $sql->select($options['select']);

    if (isset($options['where'])) {
      is_string($options['where']) && $options['where'] = [$options['where']];
      // call_user_func_array([$sql, 'where'], $options['where']);
      $sql->where($options['where']);
    }

    if (isset ($options['order']))
      $sql->order((string)$options['order']);

    if (isset ($options['limit']))
      $sql->limit($options['limit']);

    if (isset ($options['offset']))
      $sql->offset($options['offset']);

    if (isset ($options['group']))
      $sql->group($options['group']);

    if (isset ($options['having']))
      $sql->having($options['having']);

    return $sql;
  }


}

//   private function set_associations()
//   {
//     require_once __DIR__ . '/Relationship.php';
//     $namespace = $this->class->getNamespaceName();

//     foreach ($this->class->getStaticProperties() as $name => $definitions)
//     {
//       if (!$definitions)# || !is_array($definitions))
//         continue;

//       foreach (wrap_strings_in_arrays($definitions) as $definition)
//       {
//         $relationship = null;
//         $definition += array('namespace' => $namespace);

//         switch ($name)
//         {
//           case 'has_many':
//             $relationship = new HasMany($definition);
//             break;

//           case 'has_one':
//             $relationship = new HasOne($definition);
//             break;

//           case 'belongs_to':
//             $relationship = new BelongsTo($definition);
//             break;

//           case 'has_and_belongs_to_many':
//             $relationship = new HasAndBelongsToMany($definition);
//             break;
//         }

//         if ($relationship)
//           $this->add_relationship($relationship);
//       }
//     }
//   }
// }



// /**
//  * @package ActiveRecord
//  */
// namespace ActiveRecord;

// /**
//  * Manages reading and writing to a database table.
//  *
//  * This class manages a database table and is used by the Model class for
//  * reading and writing to its database table. There is one instance of Table
//  * for every table you have a model for.
//  *
//  * @package ActiveRecord
//  */
// class Table
// {

//   public $pk;
//   public $last_sql;

//   // Name/value pairs of columns in this table
//   public $columns = array();

//   /**
//    * Name of the table.
//    */

//   /**
//    * Name of the database (optional)
//    */
//   public $db_name;

//   /**
//    * Name of the sequence for this table (optional). Defaults to {$table}_seq
//    */
//   public $sequence;

//   /**
//    * Whether to cache individual models or not (not to be confused with caching of table schemas).
//    */
//   public $cache_individual_model;

//   /**
//    * Expiration period for model caching.
//    */
//   public $cache_model_expire;

//   /**
//    * A instance of CallBack for this model/table
//    * @static
//    * @var object ActiveRecord\CallBack
//    */
//   public $callback;

//   /**
//    * List of relationships for this table.
//    */
//   private $relationships = array();


//   public static function clear_cache($model_class_name=null)
//   {
//     if ($model_class_name && array_key_exists($model_class_name,self::$cache))
//       unset(self::$cache[$model_class_name]);
//     else
//       self::$cache = array();
//   }




//   public function cache_key_for_model($pk)
//   {
//     if (is_array($pk))
//     {
//       $pk = implode('-', $pk);
//     }
//     return $this->class->name . '-' . $pk;
//   }

//   public function find_by_sql($sql, $values=null, $readonly=false, $includes=null)
//   {
//     $this->last_sql = $sql;

//     $collect_attrs_for_includes = is_null($includes) ? false : true;
//     $list = $attrs = array();
//     $sth = $this->conn->query($sql,$this->process_data($values));

//     $self = $this;
//     while (($row = $sth->fetch()))
//     {
//       $cb = function() use ($row, $self)
//       {
//         return new $self->class->name($row, false, true, false);
//       };
//       if ($this->cache_individual_model)
//       {
//         $key = $this->cache_key_for_model(array_intersect_key($row, array_flip($this->pk)));
//         $model = Cache::get($key, $cb, $this->cache_model_expire);
//       }
//       else
//       {
//         $model = $cb();
//       }

//       if ($readonly)
//         $model->readonly();

//       if ($collect_attrs_for_includes)
//         $attrs[] = $model->attributes();

//       $list[] = $model;
//     }

//     if ($collect_attrs_for_includes && !empty($list))
//       $this->execute_eager_load($list, $attrs, $includes);

//     return $list;
//   }

//   /**
//    * Executes an eager load of a given named relationship for this table.
//    *
//    * @param $models array found modesl for this table
//    * @param $attrs array of attrs from $models
//    * @param $includes array eager load directives
//    * @return void
//    */
//   private function execute_eager_load($models=array(), $attrs=array(), $includes=array())
//   {
//     if (!is_array($includes))
//       $includes = array($includes);

//     foreach ($includes as $index => $name)
//     {
//       // nested include
//       if (is_array($name))
//       {
//         $nested_includes = count($name) > 0 ? $name : array();
//         $name = $index;
//       }
//       else
//         $nested_includes = array();

//       $rel = $this->get_relationship($name, true);
//       $rel->load_eagerly($models, $attrs, $nested_includes, $this);
//     }
//   }

//   public function get_column_by_inflected_name($inflected_name)
//   {
//     foreach ($this->columns as $raw_name => $column)
//     {
//       if ($column->inflected_name == $inflected_name)
//         return $column;
//     }
//     return null;
//   }


//   /**
//    * Retrieve a relationship object for this table. Strict as true will throw an error
//    * if the relationship name does not exist.
//    *
//    * @param $name string name of Relationship
//    * @param $strict bool
//    * @throws RelationshipException
//    * @return HasOne|HasMany|BelongsTo Relationship or null
//    */
//   public function get_relationship($name, $strict=false)
//   {
//     if ($this->has_relationship($name))
//       return $this->relationships[$name];

//     if ($strict)
//       throw new RelationshipException("Relationship named $name has not been declared for class: {$this->class->getName()}");

//     return null;
//   }

//   /**
//    * Does a given relationship exist?
//    *
//    * @param $name string name of Relationship
//    * @return bool
//    */
//   public function has_relationship($name)
//   {
//     return array_key_exists($name, $this->relationships);
//   }

//   public function insert(&$data, $pk=null, $sequence_name=null)
//   {
//     $data = $this->process_data($data);

//     $sql = new SqlBuilder($this->conn,$this->get_fully_qualified_table_name());
//     $sql->insert($data,$pk,$sequence_name);

//     $values = array_values($data);
//     return $this->conn->query(($this->last_sql = $sql->to_s()),$values);
//   }

//   public function update(&$data, $where)
//   {
//     $data = $this->process_data($data);

//     $sql = new SqlBuilder($this->conn,$this->get_fully_qualified_table_name());
//     $sql->update($data)->where($where);

//     $values = $sql->bind_values();
//     return $this->conn->query(($this->last_sql = $sql->to_s()),$values);
//   }

//   public function delete($data)
//   {
//     $data = $this->process_data($data);

//     $sql = new SqlBuilder($this->conn,$this->get_fully_qualified_table_name());
//     $sql->delete($data);

//     $values = $sql->bind_values();
//     return $this->conn->query(($this->last_sql = $sql->to_s()),$values);
//   }

//   /**
//    * Add a relationship.
//    *
//    * @param Relationship $relationship a Relationship object
//    */
//   private function add_relationship($relationship)
//   {
//     $this->relationships[$relationship->attribute_name] = $relationship;
//   }


//   /**
//    * Replaces any aliases used in a hash based where.
//    *
//    * @param $hash array A hash
//    * @param $map array Hash of used_name => real_name
//    * @return array Array with any aliases replaced with their read field name
//    */
//   private function map_names(&$hash, &$map)
//   {
//     $ret = array();

//     foreach ($hash as $name => &$value)
//     {
//       if (array_key_exists($name,$map))
//         $name = $map[$name];

//       $ret[$name] = $value;
//     }
//     return $ret;
//   }

//   private function &process_data($hash)
//   {
//     if (!$hash)
//       return $hash;

//     $date_class = Config::instance()->get_date_class();
//     foreach ($hash as $name => &$value)
//     {
//       if ($value instanceof $date_class || $value instanceof \DateTime)
//       {
//         if (isset($this->columns[$name]) && $this->columns[$name]->type == Column::DATE)
//           $hash[$name] = $this->conn->date_to_string($value);
//         else
//           $hash[$name] = $this->conn->datetime_to_string($value);
//       }
//       else
//         $hash[$name] = $value;
//     }
//     return $hash;
//   }





//   /**
//    * Rebuild the delegates array into format that we can more easily work with in Model.
//    * Will end up consisting of array of:
//    *
//    * array('delegate' => array('field1','field2',...),
//    *       'to'       => 'delegate_to_relationship',
//    *       'prefix' => 'prefix')
//    */
//   private function set_delegates()
//   {
//     $delegates = $this->class->getStaticPropertyValue('delegate',array());
//     $new = array();

//     if (!array_key_exists('processed', $delegates))
//       $delegates['processed'] = false;

//     if (!empty($delegates) && !$delegates['processed'])
//     {
//       foreach ($delegates as &$delegate)
//       {
//         if (!is_array($delegate) || !isset($delegate['to']))
//           continue;

//         if (!isset($delegate['prefix']))
//           $delegate['prefix'] = null;

//         $new_delegate = array(
//           'to'    => $delegate['to'],
//           'prefix'  => $delegate['prefix'],
//           'delegate'  => array());

//         foreach ($delegate as $name => $value)
//         {
//           if (is_numeric($name))
//             $new_delegate['delegate'][] = $value;
//         }

//         $new[] = $new_delegate;
//       }

//       $new['processed'] = true;
//       $this->class->setStaticPropertyValue('delegate',$new);
//     }
//   }

//   /**
//    * @deprecated Model.php now checks for get|set_ methods via method_exists so there is no need for declaring static g|setters.
//    */
// }
