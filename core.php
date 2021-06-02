<?php

/**
 * Aragnet Michal Raska
 * User: naith
 * Date: 23.7.13
 * Time: 0:53
 * Desc: Basic class
 */
class core
{
  private static $instsnce = null;
  public $t;
  public static $tm;      //staticke uloziste templatu
  public static $url;     //aktualni url
  public static $usr;     //uzivatelska data
  public static $lang;    //jazyk uzivatele
  public static $ip;
  public static $zacatek = 0;
  public static $konec = 0;

  public function __construct()
  {
    self::$instsnce = $this; //global instance of this core class

    self::$zacatek = lib::microtime_float();

    bcscale(4);

    //startDb
    unset($_SESSION['db.tm']);
    unset($_SESSION['db']);
    $_SESSION['db']['tm'] = '<<';
    $this->t = new stdClass();
    self::$lang = storage::getValue('lang');

    $remip = $_SERVER['REMOTE_ADDR'];
    //var_dump($remip);
    if (strpos($remip, ':') !== false)
    {
      //IPv6
      self::$ip = $ip = db::escapeVar($remip);
    } else
    {
      //IPv4
      self::$ip = $ip = explode(".", $remip);
    }
    //var_dump(self::$ip,$ip);
    self::StoreLocalization($ip);

    if (isset($_GET['ip']))
    {
      //var_dump("write ip",$_GET['ip']);
      self::StoreLocalization(db::escapeVar($_GET['ip']));
    }
  }

  //var_dump($_SERVER);
  public function run()
  {
    self::$url = new coreurl();
    $bases = self::$url->getBase();

    $app = $bases[0];
    if (count($bases) > 1)
    {
      $mode = $bases[1];
    } else
    {
      $mode = "";
    }

    $usr = storage::getValue('user');

    basket::initBasket($usr);

    self::$usr = $usr;

    lang::initTranslator();

    $this->t->urlparam = coreurl::getSUrlParam();
    $this->t->APP = res_basic::$APP;

    //var_dump($app, config::getAppSwitch($app));
    //var_dump($app, config::getPublicApps($app));

    if (config::getAppSwitch($app) === true && $usr['login'] == true)
    {
      //platna app, prihlasen
      $r = new $app($this->t);
      $r->Run($mode);

    } elseif (config::getAppSwitch($app) === true)
    {
      //platna app, neprihlasen
      if ($app == 'basket')  //todo add to public items
      {
        //public modules without ask
        $r = new $app($this->t);
        $r->Run($mode);
      } elseif ($app === "user" && $bases[1] === "login")
      {
        //musi se dat prihlasit, nebo zaregistrovat
        $r = new user($this->t);
        $r->Run("login");

      } elseif ($app == 'item')  //todo add to public items
      {
        //public items
        //var_dump($app);
        $r = new $app($this->t);
        $r->Run($mode);

      } elseif (config::getPublicApps($app) == true)
      {
        // pokud je to cokoliv jineho, test zda neni public v cfg
        // je to public zobraz
        $r = new $app($this->t);
        $r->Run();
      } else
      {
        // zbytek vyhodit na face nebo intro
        $r = new intro_shop($this->t);
        $r->Run();
      }
    } elseif ($app == "")
    {
      //print_r("live");
      $r = new intro_shop($this->t);
      $r->Run();
      //exit;
    } else
    {
      //je neplatna takze na prihlaseni nezalezi
      //ale mozna je to Tag
      $tag = menu::getAllTags();
      //var_dump($tag,$app);

      if (in_array($app, $tag))
      {
        $r = new face($this->t);
        $r->Run();

      } else
      {

        $r = new coreurl();

        $r404 = new router404();
        $r404->checkUrl($r->getBase());

        $r = new page_404($this->t);
        $r->Run();
      }
    }

    self::$konec = lib::microtime_float();

    if (true)
    {
      $ms = round((self::$konec - self::$zacatek), 6);
      print_r("<div style=\"text-align: center;\"><br>Total time: {$ms}s</div>");
    }
    exit;
  }

  public static function isLocal($ip = '')
  {
    $iploc = array();

    if ($ip != '' && is_array($ip) && count($ip) == 4)
    {
      $iploc = $ip;
    } elseif (is_array(self::$ip) && count(self::$ip) == 4)
    {
      $iploc = self::$ip;
    } else
    {
      $iploc = null;
      return null;
    }

    if ($iploc[0] == '127' || $iploc[0] == '5' || $iploc[0] == '192')
    {
      return true;
    } else
    {
      return false;
    }
  }

  public static function StoreLocalization($ip)
  {
    if (is_array($ip) && count($ip) === 4)
    {
      for ($i = 0; $i < 4; $i++)
      {
        $ip[$i] = (int)$ip[$i];
      }
      $ipd = implode('.', $ip);
    } else
    {
      $ipd = db::escapeVar($ip);
    }

    if (!self::isLocal())
    {
      $aftermonth = 2629743 * config::getConfigSwitch('cfg_check_location_after');

      $r = self::modelReadGeoData($ipd);
      //var_dump($r);
      if ((bool)$r)
      {
        if (($r['lastupdate'] + $aftermonth) < time())
        {
          $geores = self::Location($ip);
          if ((bool)$geores)
          {
            //$geores['ip'] = $ipd;
            self::modelUpdateGeoData($geores);
            return true;
          }
        }
      } else
      {
        $geores = self::Location($ip);
        //var_dump($geores);

        if ((bool)$geores)
        {
          //$geores['ip'] = $ipd;
          self::modelStoreGeoData($geores);
          return true;
        }
      }
    } else
    {
      return true;
    }
    return false;
  }


  public static function Location($ip = "")
  {
    //var_dump("LOKACE",$ip);
    return self::LocationIPAPIcom($ip);
  }

  public static function LocationFreegeoipNet($ip = "")
  {
    //var_dump($ip);
    if ($ip === "")
    {
      $ipp = self::$ip;

    } else
    {
      $ipp = trim($ip);
    }
    if (count($ipp) === 4)
    {
      $ip = "{$ipp[0]}.{$ipp[1]}.{$ipp[2]}.{$ipp[3]}";
      //var_dump($ip);
    } else
    {
      $ip = db::escapeVar($ipp);
    }

    $fp = fsockopen("freegeoip.net", 80, $errno, $errstr, 5);

    //var_dump($fp);
    if (!$fp)
    {
      return false;

    } else
    {
      $data = "";
      $out = "GET /json/{$ip} HTTP/1.1\r\n";
      $out .= "Host: freegeoip.net\r\n";
      $out .= "Connection: Close\r\n\r\n";
      fwrite($fp, $out);
      while (!feof($fp))
      {
        $data .= fgets($fp, 128);
      }
      fclose($fp);
    }


    $count = strpos($data, '{');
    $data = substr($data, $count);

    if ($geo_data = json_decode($data, true))
    {
      $geo_data['ip'] = $ip;
      return $geo_data;
    } else
    {
      return false;
    }
  }

  public static function LocationIPAPIcom($ip = "")
  {
    //var_dump("LocationIPAPIcom > ip",$ip);

    if ($ip === "")
    {
      $ipp = self::$ip;
    } else
    {
      $ipp = $ip;
    }

    //var_dump("LocationIPAPIcom > ipp check",$ipp);

    if (is_array($ipp) && count($ipp) === 4)
    {
      $ip = "{$ipp[0]}.{$ipp[1]}.{$ipp[2]}.{$ipp[3]}";
      //var_dump($ip);
    } else
    {
      $ip = trim($ipp);
    }
    //var_dump("LocationIPAPIcom > ip trans",$ip);

    $fp = fsockopen("ip-api.com", 80, $errno, $errstr, 5);

    //var_dump($fp);
    if (!$fp)
    {
      return false;

    } else
    {
      $data = "";
      $out = "GET /json/{$ip} HTTP/1.1\r\n";
      $out .= "Host: ip-api.com\r\n";
      $out .= "Connection: Close\r\n\r\n";
      fwrite($fp, $out);
      while (!feof($fp))
      {
        $data .= fgets($fp, 128);
      }
      fclose($fp);
    }


    $count = strpos($data, '{');
    $data = substr($data, $count);
    //var_dump($ip);
    //var_dump(db::escapeVar($ip));

    if ($geo_data = json_decode($data, true))
    {
      $geo_data_trans['ip'] = db::escapeVar($ip);
      $geo_data_trans['country_code'] = db::escapeVar($geo_data['countryCode']);
      $geo_data_trans['country_name'] = db::escapeVar($geo_data['country']);
      $geo_data_trans['region_code'] = db::escapeVar($geo_data['region']);
      $geo_data_trans['region_name'] = db::escapeVar($geo_data['regionName']);
      $geo_data_trans['city'] = db::escapeVar($geo_data['city']);
      $geo_data_trans['zip_code'] = db::escapeVar($geo_data['zip']);
      $geo_data_trans['time_zone'] = db::escapeVar($geo_data['timezone']);
      $geo_data_trans['latitude'] = db::escapeVar($geo_data['lat']);
      $geo_data_trans['longitude'] = db::escapeVar($geo_data['lon']);
      $pom = $geo_data['isp'] . " | " . $geo_data['org'];
      if (strlen($pom > 255))
      {
        $pom = str_pad($pom, 253, "..");
      }
      $geo_data_trans['metro_code'] = db::escapeVar($pom);

      return $geo_data_trans;
    } else
    {
      return false;
    }

    //from
    /*{"as":"AS3292 TDC A/S","city":"Hellerup","country":"Denmark","countryCode":"DK","isp":"Tele Danmark","lat":55.732,"lon":12.5709,"org":"Tele Danmark","queryDb":"188.178.237.254","region":"84","regionName":"Capital Region","status":"success","timezone":"Europe/Copenhagen","zip":"2900"}*/

    //to
    /*{"ip":"89.103.34.79","country_code":"CZ","country_name":"Czech Republic","region_code":"ZL","region_name":"ZlĂ­n","city":"ZlĂ­n","zip_code":"760 01","time_zone":"Europe/Prague","latitude":49.2167,"longitude":17.6667,"metro_code":0}*/
  }

  public static function modelStoreGeoData($geo_data)
  {
    $gd = array();

    foreach ($geo_data as $k => $v)
    {
      $kd = db::escapeVar($k);
      $vd = db::escapeVar($v);
      $gd[$kd] = $vd;
    }
    //var_dump('gd', $gd);
    $geo_data = $gd;

    $sql = "INSERT INTO `geoinfo` (`ip`, `country_code`, `country_name`, `region_code`, `region_name`, `city`, `zip_code`, `time_zone`, `latitude`, `longitude`, `metro_code`,`lastupdate`) VALUES('{$geo_data['ip']}','{$geo_data['country_code']}','{$geo_data['country_name']}','{$geo_data['region_code']}','{$geo_data['region_name']}','{$geo_data['city']}','{$geo_data['zip_code']}','{$geo_data['time_zone']}','{$geo_data['latitude']}','{$geo_data['longitude']}','{$geo_data['metro_code']}', '" . time() . "');";

    //var_dump('sql', $sql);

    $r = db::queryDb($sql);

    return $r;

  }

  public static function modelUpdateGeoData($geo_data)
  {

    $gd = array();

    foreach ($geo_data as $k => $v)
    {
      $kd = db::escapeVar($k);
      $vd = db::escapeVar($v);
      $gd[$kd] = $vd;
    }

    $geo_data = $gd;

    $sql = "UPDATE `geoinfo` SET `country_code` = '{$geo_data['country_code']}', `country_name` = '{$geo_data['country_name']}', `region_code` = '{$geo_data['region_code']}', `region_name` = '{$geo_data['region_name']}', `city` = '{$geo_data['city']}', `zip_code` = '{$geo_data['zip_code']}', `time_zone` = '{$geo_data['time_zone']}', `latitude` = '{$geo_data['latitude']}', `longitude` = '{$geo_data['longitude']}', `metro_code` = '{$geo_data['metro_code']}', `lastupdate` = '" . time() . "'
WHERE `ip` = '{$geo_data['ip']}';";

    $r = db::queryDb($sql);

    return $r;
  }

  public static function modelReadGeoData($ip)
  {
    $ipd = db::escapeVar($ip);
    $sql = "select * from `geoinfo` where `ip` = '{$ipd}';";
    $r = db::getRow($sql);

    return $r;
  }


  public static function getInstance()
  {
    if (self::$instsnce === null)
    {
      $inst = new core();
      self::$instsnce = $inst;
    }

    return self::$instsnce;
  }
}
