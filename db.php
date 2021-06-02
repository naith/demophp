<?php

/**
 * Aragnet Michal Raska
 * User: naith
 * Date: 23.7.13
 * Time: 0:53
 * Desc: Database class
 */
class db
{

  public static $db;
  private static $db_sel;
  public static $dbdev = null;
  public static $dbprod = null;
  protected static $actualDB = null;


  public static function Start()
  {
    $ip = explode(".", $_SERVER['REMOTE_ADDR']);
    //var_dump($ip);
    if ($ip[0] == '127' || $ip[0] == '5' || $ip[0] == "192")
    {
      $dev = self::$dbdev;
      if ($dev === null)
      {
        print_r("Connection data not found");
        exit;
      }
      $link = mysqli_connect($dev["host"], $dev["user"], $dev["password"], $dev["db"]);

      self::$actualDB = $dev["db"];

    } else
    {
      $prod = self::$dbprod;
      if ($prod === null)
      {
        print_r("Connection data not found");
        exit;
      }
      $link = mysqli_connect($prod["host"], $prod["user"], $prod["password"], $prod["db"]);

      self::$actualDB = $prod["db"];
    }

    if (!$link)
    {
      print_r("Connection to database failed "); //. print_r($ip, true)
      self::$db = null; //konektor
      exit;

    } else
    {
      mysqli_set_charset($link, "utf8");
      self::$db = $link; //konektor
    }
  }

  public static function getActualDB()
  {
    return self::$actualDB;
  }

  public static function tableExist($database, $tablename)
  {
    if ($database != '' && $tablename != '')
    {
      $sql = "SELECT IF(EXISTS(SELECT *
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = '{$database}' AND TABLE_NAME = '{$tablename}'
                 LIMIT 1), 1, 0) AS if_exists";

      $r = db::getRow($sql);

      if ($r['if_exists'] == '0')
      {
        return false;

      } else
      {
        return true;
      }
    } else
    {
      return null;
    }
  }

  /**
   * queryDb() Provede SQL dotaz pro specialni operace vcetne raw operaci
   * @param string $sql
   * @return bool|resource
   */
  public static function queryDb($sql)
  {
    $zacatek = lib::microtime_float();
    $result = mysqli_query(self::$db, $sql);

    if (!$result)
    {
      $msg = 'Invalid queryDb: ' . mysqli_error(self::$db) . "\n\r";
      $msg .= "SQL queryDb: " . $sql . "\n\r";
      self::StoreLog($msg);
    }

    $konec = lib::microtime_float();

    if (core::isLocal())
    {
      $ms = ($konec - $zacatek);
      $_SESSION['db']['tm'] = $_SESSION['db']['tm'] . " | " . $ms;
    }

    return ($result);
  }

  public static function getTable($sql, &$count = null)
  {
    $zacatek = lib::microtime_float();
    $result = array();
    $r = mysqli_query(self::$db, $sql);
    if ($r)
    {
      while ($res = mysqli_fetch_assoc($r))
      {
        //$result[] = stripslashes($res);
        $result[] = $res;
      }
      $count = count(@$result);
    } else
    {
      $msg = 'Invalid queryDb: ' . mysqli_error(self::$db) . "\n\r";
      $msg .= "SQL getTable: " . $sql . "\n\r";
      self::StoreLog($msg);
      $result = false;
    }

    $konec = lib::microtime_float();

    if (core::isLocal())
    {
      $ms = ($konec - $zacatek);
      $_SESSION['db']['tm'] = $_SESSION['db']['tm'] . " | " . $ms;
    }

    if (isset($result))
    {
      return ($result);
    } else
    {
      return (false);
    }
  }

  /**
   * getCol
   * @param $table
   * @param $col
   * @param $where
   * @return bool|mixed
   */
  public static function getCol($table, $col, $where)
  {
    $sql = "select {$col} from {$table} where {$where};";
    var_dump($sql);
    $r = db::getRow($sql);
    if ($r === false)
    {
      $res = false;
    } else
    {
      $res = @$r[$col];
    }

    return ($res);
  }

  public static function setCol($table, $col, $val, $where)
  {
    $sql = "update {$table} set {$col} = '{$val}' where {$where}";
    //var_dump($sql);
    $r = db::queryDb($sql);
    return ($r);
  }

  public static function getCols($sql, $col, &$count = null)
  {
    $result = array();
    $r = mysqli_query(self::$db, $sql);
    if ($r)
    {
      while ($res = mysqli_fetch_assoc($r))
      {
        $result[] = $res[$col];
      }
      $count = count(@$result);
    } else
    {
      $msg = 'Invalid queryDb: ' . mysqli_error(self::$db) . "\n\r";
      $msg .= "SQL getCols: " . $sql . "\n\r";
      self::StoreLog($msg);
      $result = false;
    }

    if (isset($result))
    {
      return ($result);
    } else
    {
      return (false);
    }
  }


  /**
   * getRow()
   * @param string $sql
   * @return array()|bool
   */
  public static function getRow($sql)
  {
    $r = mysqli_query(self::$db, $sql);
    if ($r)
    {
      $res = mysqli_fetch_assoc($r);
    } else
    {
      $msg = 'Invalid queryDb: ' . mysqli_error(self::$db) . "\n\r";
      $msg .= "SQL getRow: " . $sql . "\n\r";
      self::StoreLog($msg);
      $res = false;
    }

    if (isset($res))
    {
      //return (stripslashes($res));
      return ($res);
    } else
    {
      return (false);
    }
  }

  public static function Escape($s)
  {
    //legacy support for runcore TODO remove it in future
    return self::escapeVar($s);
  }

  public static function EscapeArray($field)
  {
    //legacy support for runcore TODO remove it in future
    return self::escapeArrays($field);
  }

  public static function escapeVar($s)
  {
    return (mysqli_real_escape_string(db::$db, $s));
  }

  public static function escapeArrays($field)
  {
    foreach ($field as $value)
    {
      if (is_array($value))
      {
        self::escapeArrays($value);
      } else
      {
        $value = self::escapeVar($value);
      }
    }
    return ($field);
  }

  public static function StoreLog($msg, $logname = "db_err.log")
  {
    if (($fp = fopen(WWW . '/logs/' . $logname, 'a+')))
    {
      //fwrite($fp, date('[Y-m-d H:i.s]') . "BEGIN " . $msg . "END :" . $_SERVER['SCRIPT_NAME'] . " : _GET " . print_r(@$_GET, true) . " : _POST " . print_r(@$_POST, true) . "\n\r");

      fwrite($fp, date('[Y-m-d H:i.s]') . "MESSAGE BEGIN " . $msg . "END \n\r SYSTEM BEGIN" . $_SERVER['SCRIPT_NAME'] . " END \n\r");
      fclose($fp);
    }
  }

  public static function getConfigPar($name)
  {
    $sql = "SELECT val
            FROM config
            WHERE `key` = '{$name}';";

    if (($data = db::getRow($sql)) != false)
    {
      return ($data['val']);
    } else
    {
      return (false);
    }
  }

  public static function setConfigPar($name, $par)
  {
    $name = mysqli_real_escape_string(db::$db, $name);
    $par = mysqli_real_escape_string(db::$db, $par);
    //$act = mysqli_real_escape_string(db::$db, $act);

    $sql = "SELECT key FROM config WHERE key = '{$name}';";
    if (db::getRow($sql) != false)
    {
      $sql = "UPDATE config SET val = '{$par}' WHERE name = '{$name}';";
      db::queryDb($sql);
      return (true);
    } else
    {
      $sql = "INSERT `config` SET `val` = '{$par}', `name` = '{$name}'";
      db::queryDb($sql);
      return (false);
    }
  }

  /**
   * implodeDb
   * @param $glue
   * @param $array
   * @param string $wrapStart
   * @param string $wrapEnd
   * @return null|string
   */

  public static function implodeDb($glue, $array, $wrapStart = '', $wrapEnd = '')
  {
    $arrayCount = count($array);
    $outString = '';

    if (is_array($array) && $arrayCount >= 1)
    {
      foreach ($array as $v)
      {
        if ($wrapStart != '' && $wrapEnd == '')
        {
          $wrapEnd = $wrapStart;
        }
        $outString .= "{$wrapStart}{$v}{$wrapEnd}{$glue}";
      }
      return trim($outString, $glue);

    } elseif (is_array($array) === false)
    {
      if ($wrapStart != '' && $wrapEnd == '')
      {
        $wrapEnd = $wrapStart;
      }
      $outString = "{$wrapStart}{$array}{$wrapEnd}";
      return $outString;
    }
    return null;
  }

  protected static function microtime()
  {
    return time() + microtime();
  }

  protected static function calc($time_start, $sql)
  {
    $time = (self::microtime() - $time_start);
    if ($time > 1000)
    {
      self::report($time . $sql, 'sql::slow ' . $time);
    }
    return number_format($time, 4, '.', ',');
  }

}