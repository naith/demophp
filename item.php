<?php

/**
 * Aragnet Michal Raska
 * v-najem
 * User: naith
 * Date: 3.3.16
 * Time: 11:52
 * Desc:
 */
class item extends core
{
  public $t;
  protected $modul = 'item';
  protected $accept_modes = array('add', 'template', 'edit', 'view');


  public function __construct($t = '')
  {
    //parent::__construct();
    $this->t = $t;
    $this->t->MDES = lang::IncludeResource('res_' . $this->modul);
    $this->t->modul = $this->modul;
  }

  /**
   * getOldPrice
   * @param $data
   * @return string
   */
  public static function getOldPrice($data)
  {
    //formula for calculate old prices is
    //oldPrice = newPrice / (1-(percentOff / 100))

    $oldprice = evmath::bcRoundStat(
      bcdiv(
        lib::FormReturnValue('price')
        , bcsub(
          "1"
          , bcdiv(
            lib::FormReturnValue($data["name"])
            , "100"
          )
        )
      )
      , 0
    );

    return $oldprice;
  }

  /**
   * getFormVariableFromPost
   * @param $gr
   * @param $item
   * @param $gra
   * @param $itemt
   * @return mixed
   */
  public static function getFormVariableFromPost($gr, $item, $gra, $itemt)
  {
    $j = 0;

    foreach ($gr as $k => $v)
    {
      $item_tag = db::getRow(
        "select * from 
tag_item WHERE iditem = '{$item['idmsg']}' 
and idtaggroup = '{$k}';"
      );

      if ($item_tag != false)
      {
        switch ($gra[$j]['typ'])
        {
          case '0':
            $itemt[$v] = $item_tag['idtag'];
            break;
          case '2':
          case '1':
            $itemt[$v] = $item_tag['value'];
            break;
        }
      }
      $j++;
    }
    return $itemt;
  }

  /**
   * Run
   * @param string $mode
   */
  public function Run($mode = "")
  {
    $user = self::$usr;

    //missing first and surname. User must insert it.
    if ($user['login'] == true && $user['fname'] == '' && $user['sname'] == '')
    {
      //var_dump("mode 0");
      $mode = 'profile';
    }

    if (in_array($mode, $this->accept_modes))
    {
      //var_dump("mode 1");

      $mode = ucfirst($mode);
      $this->$mode();

    } elseif ($mode != '')
    {
      //var_dump("mode 2");

      // mozna je to produkt
      $part = explode('-', $mode);
      $partCount = count($part);

      $id = ($partCount > 0) ? $part[$partCount - 1] : '0';
      if ($id == '0')
      {
        //404
      } else
      {
        $_GET['id'] = $id;
      }

      $this->View();

    } else
    {
      //var_dump("mode 3");
      $this->Item();
    }

    uiCore::DelErrMsg();

  }

  /**
   * Item
   */
  public function Item()
  {
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idITEM');

    $page = item::uiItem()->uiItemListItem();
    uiCore::PageCore($this->t, $page);
  }

  /**
   * Add
   */
  public function Add()
  {
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idADDITEM');

    //user must be logged
    if ($this->t->data['login'] == false || $this->t->data['login'] == '')
    {
      $usr = new user($this->t);
      $usr->Login();

      //lib::RedirectWay('/');
    } else
    {
      //previous data not need
      lib::FormResetValue();
      $page = item::uiItem()->uiAddEditItem();
      uiCore::PageCore($this->t, $page);
    }
  }

  /**
   *
   * View
   * @param string $iditem
   */
  public function View($iditem = "")
  {
    //var_dump("item, view");
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idEDITITEM');

    if ($iditem != '')
    {
      $gethash = db::escapeVar($iditem);
    } else
    {
      $gethash = db::escapeVar($_GET["id"]);
    }

    $item = item::getItemData($gethash);
    if (isset($item['title']) && $item['title'] != "")
    {
      $this->t->nazev = $item['title'];
    } else
    {
      $this->t->nazev = lang::Translate('idITEM');
    }

    if (isset($item['perex']) && $item['perex'] != "")
    {
      $this->t->perex = $item['perex'];
    } else
    {
      $this->t->perex = lang::Translate('idITITEM');
    }

    //var_dump($item);

    //Adapt db column to input fields TODO unify them
    $itemt = array();
    $itemt['idmsg'] = $item['idmsg'];
    $itemt['title'] = $item['title'];
    $itemt['perex'] = $item['perex'];
    $itemt['text'] = $item['text'];
    $itemt['idtemplate'] = $item['idtemplate'];
    $itemt['hash'] = $item['hash'];
    $itemt['active'] = $item['active'];

    //get data from termplate storage
    $template = db::getRow(
      "select * from template WHERE idtemplate = '{$item['idtemplate']}';"
    );

    $gra = template::GetTagsGroup($template['taggrouplevel']);
    $gr = evarray::ArrayMultiToKeyVal($gra);

    //var_dump($gr, $item, $gra, $itemt);

    $itemt = self::getFormVariableFromPost($gr, $item, $gra, $itemt);

    //var_dump($itemt);

    lib::FormStoreValue($itemt);

    $page = uiItem::uiShow();

    uiCore::PageCore($this->t, $page);
  }

  /**
   * @brief Edit
   * @return bool
   */
  public function Edit()
  {
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idEDITITEM');

    //user must be logged
    if ($this->t->data['login'] == false || $this->t->data['login'] == '')
    {
      $usr = new user($this->t);
      $usr->Login();
      return false;
    }

    $item = item::getItemData(db::escapeVar($_GET["id"]));

    //Adapt db column to input fields TODO unify them
    $itemt = array();
    $itemt['idmsg'] = $item['idmsg'];
    $itemt['title'] = $item['title'];
    $itemt['perex'] = $item['perex'];
    $itemt['text'] = $item['text'];
    $itemt['idtemplate'] = $item['idtemplate'];
    $itemt['hash'] = $item['hash'];
    $itemt['active'] = $item['active'];

    //get data from termplate storage
    $template = db::getRow(
      "select * from template WHERE idtemplate = '{$item['idtemplate']}';"
    );

    $gra = template::GetTagsGroup($template['taggrouplevel']);
    //var_dump($gra);

    $gr = evarray::ArrayMultiToKeyVal($gra);
    //var_dump($gr);

    $itemt = self::getFormVariableFromPost($gr, $item, $gra, $itemt);

    //log::sysLog($itemt);

    lib::FormStoreValue($itemt);

    $page = item::uiItem()->uiAddEditItem('EDITITEM');
    uiCore::PageCore($this->t, $page);

    return true;
  }


  /**
   * @brief modelGetItem
   * @param string $iduser
   * @param string $iditem
   * @param string $cols
   * @param int $limit
   * @return array|bool
   */
  public static function modelGetItem($iduser = 'self', $iditem = '*', $cols = '*', $limit = 50)
  {
    if ($iduser == 'self')
    {
      $idus = (int)self::$usr['iduser'];
      $user = " iduser = '{$idus}' ";

    } elseif ($iduser == '*')
    {
      //$idus = (int)$iduser;
      $user = "";
    } else
    {
      $idus = (int)$iduser;
      $user = " iduser = '{$idus}' ";
    }

    if ($iditem == '*')
    {
      $iditm = '';

    } else
    {
      $iditm = '';
      if ($user != '')
      {
        $iditm = " AND ";
      }
      $iditm .= " `hash` = '" . db::escapeVar($iditem) . "' ";
    }

    if ($cols == '*')
    {
      $colname = '*';

    } else
    {
      $colname = db::escapeVar($cols);
    }

    $sql = "SELECT {$colname} FROM 
msg where {$user} {$iditm} 
ORDER BY `idmsg` DESC LIMIT " . (int)$limit . "";

    //var_dump($sql);

    $r = db::getTable($sql);

    if ($r != false)
    {
      return $r;
    }

    return false;
  }

  /**
   * StoreParam
   * Store item data into message
   * @param $data
   * @return bool|string
   *
   */
  public function StoreParam($data)
  {
    $pom = array();

    //for security. Prevent SQL injection
    foreach ($data as $k => $v)
    {
      $pom[db::escapeVar($k)] = db::escapeVar(
      //zabraneni premnozeni \
        stripslashes(
          lib::replaceEOL($v, '')
        )
      );
    }

    $ok = true;
    $err_field = "";
    $set = null;

    //in first - title, perex, decription
    if ($pom['title'] != "")
    {
      $set['title'] = $pom['title'];
    } else
    {
      //important
      $ok = false;
      $err_field .= "Title, ";
    }

    if ($pom['perex'] != "")
    {
      $set['perex'] = $pom['perex'];
    }

    if ($pom['text'] != "")
    {
      $set['text'] = $pom['text'];
    } else
    {
      //important
      $ok = false;
      $err_field .= "Text, ";
    }

    if ($pom['active'] != "")
    {
      $set['active'] = 1;
    } else
    {
      $set['active'] = 0;
    }

    if ($pom['idtemplate'] != "")
    {
      $set['idtemplate'] = $pom['idtemplate'];
    } else
    {
      //important
      $ok = false;
      $err_field .= "Template, ";
    }


    if (isset($pom['data']) == false)
    {
      $set['data'] = "";
    }

    if ($ok === false)
    {
      storage::setValue('err_msg', "idDATANOTCOMPLETE");
      storage::setValue('err_val', trim($err_field, ", "));
      return false;
    }

    // **** START WORK ON STORE ITEM ****
    //in first must store item message
    if ($pom['id'] != '')
    {
      $hash = item::MsgUpdate($pom['id'], $set);
    } else
    {
      $hash = item::MsgStore($set);
    }

    if ($hash === null)
    {
      //insertion failed, return with error
      storage::setValue('err_msg', "idSTOREMSGFAIL");
      //exit;
      return false;
    }

    $pom['idmsg'] = $hash;

    lib::FormStoreValue($pom);

    //next store parameters
    if ($pom['id'] != '')
    {
      $r = item::UpdateParamToItemTag($pom['id'], $pom);
    } else
    {
      $r = item::StoreParamToItemTag($pom, $hash);
    }

    if ($r === false)
    {
      //store parameters failed, return with error
      storage::setValue('err_msg', "idSTOREMSGFAIL");
      //exit;
      return false;
    }

    //and in finish store images
    //store images
    $files = $_FILES;

    images::setPostImageName('images');
    images::StoreImageMultiple($hash, $files);

    storage::setValue('err_msg', "idSTOREPARAMSOK");

    //finish work

    return $hash;
  }

  public static function UpdateParamToItemTag($idmsg, $data)
  {
    //get taggroup from template
    $sql = "select taggrouplevel from template where idtemplate = '{$data['idtemplate']}'";
    //var_dump($sql);
    $r = db::getRow($sql);

    //result transform into IN clausule friendly form
    $in = str_replace("|", ",", $r['taggrouplevel']);

    //get tags group assigned by template
    $sql = "SELECT idtaggroup, name, typ FROM tag_group where idtaggroup in ({$in})";
    $rq = db::getTable($sql);

    if ($rq === false)
    {
      log::sysLog($rq);
      return false;
    }

    //TODO docasne
    $tax = null;
    $val = null;

    //transform it into array key->id form
    $tafe = evarray::ArrayMultiToKeyId($rq);

    $sql = "select idtaggroup from tag_item where iditem = '{$data['id']}' and idtaggroup in ({$in})";

    $tafi = evarray::ArrayColToRow(
      db::getTable($sql)
      , "idtaggroup"
    );

    $j = 0;

    foreach ($tafe as $k => $v)
    {
      if ($data[$k] == '')
      {
        //if value not selected then equal record deleted if exist
        $r = item::DeleteItem($k, $v, $data);

        if ($r === false)
        {
          log::sysLog($rq);
          return false;
        }

      } else
      {
        switch ($rq[$j]['typ'])
        {
          case '1':
            $val = $data[$k];
            $tax = $data['pricetax'];
            break;

          case '2':
            $val = $data[$k];
            $tax = 0;
            log::sysLog($val);
            break;

          default :
            $val = null;
            break;
        }

        if (in_array($v, $tafi))
        {
          log::sysLog($v, $rq[$j]['typ'], $k);

          //exist record is updated
          $r = item::UpdateItem($idmsg, $k, $v, $data, $tax, $val);

          if ($r === false)
          {
            log::sysLog("FALSE", $v);
            return false;
          }

          log::sysLog("TRUE", $v);

        } else
        {
          //record no exist -> insert new
          $r = item::InsertItem($idmsg, $k, $v, $data, $tax, $val);

          if ($r === false)
          {
            log::sysLog("FALSE", $r);
            return false;
          }
        }
      }
      $j++;
    }
    //exit;
    return true;
  }


  public static function DeleteItem($k, $v, $data)
  {
    $sql = "delete from tag_item where iditem = '{$data['id']}' and idtaggroup = '{$v}'";
    $r = db::queryDb($sql);

    //var_dump("delete", $sql);

    //Note: if queryDb not delete record return true. queryDb must be correct.

    return $r;
  }

  public static function InsertItem($idmsg, $k, $v, $data, $tax, $val)
  {
    if ($val != null || $val != "")
    {
      $idtag = 0;
      $taxm = (($tax == '' || $tax == null) ? 0 : db::escapeVar($tax));
      $varm = (($val == '' || $val == null) ? '0.0' : db::escapeVar($val));
    } else
    {
      $idtag = $data[$k];
      $taxm = 0;
      $varm = 0;
    }

    //TODO workaround sanitize taxm in no exist
    /*    if(is_numeric($taxm) == false || $taxm == ''){
          $taxm = 0;
          $varm = '0.0';
        }*/

    $sql = "insert into tag_item (iditem,idtaggroup,idtag,idtax,value) values ('{$idmsg}','{$v}','{$idtag}','{$taxm}','{$varm}')";

    $r = db::queryDb($sql);

    return $r;
  }

  public static function InsertItemTypePrice($idmsg, $k, $v, $data, $tax, $val)
  {
    log::sysLog("$data", __METHOD__, "data");
    if ($val != null || $val != "")
    {
      $idtag = 0;
      $taxm = (($tax == '' || $tax == null) ? 0 : $tax);
      $varm = (($val == '' || $val == null) ? '0.0' : $val);
    } else
    {
      $idtag = $data[$k];
      $taxm = 0;
      $varm = 0;
    }

    $sql = "insert into tag_item (iditem,idtaggroup,idtag,idtax,value) values ('{$idmsg}','{$v}','{$idtag}','{$taxm}','{$varm}')";

    $r = db::queryDb($sql);

    return $r;
  }

  public static function UpdateItem($idmsg, $k, $v, $data, $tax, $val)
  {
    if ($val != null || $val != "")
    {
      $idtag = 0;
      $taxm = (($tax == '' || $tax == null) ? 0 : $tax);
      $varm = (($val == '' || $val == null) ? '0.0' : $val);
    } else
    {
      $idtag = $data[$k];
      $taxm = 0;
      $varm = 0;
    }

    $sql = "update tag_item set idtag = '{$idtag}', idtax = '{$taxm}', value = '{$varm}' where iditem = '{$idmsg}' and idtaggroup = '{$v}';";

    $r = db::queryDb($sql);

    return $r;
  }

  /**
   * StoreParamToItemTag
   * @param $data array
   * @param $hash string
   * @return bool
   */
  public static function StoreParamToItemTag($data, $hash)
  {
    $idmsg = message::getIdFromHash($hash, -1);

    $sql = "SELECT idtaggroup, name, typ FROM tag_group";
    $rq = db::getTable($sql);

    if ($rq === false)
    {
      return false;
    }

    //TODO docasne
    $tax = null;
    $val = null;

    $tafe = evarray::ArrayMultiToKeyId($rq);

    //var_dump($tafe); //$taggroups, $taff,

    $j = 0;
    foreach ($tafe as $k => $v)
    {
      if ($data[$k] != '')
      {

        //TODO refactor insertItem to separate method for each type!!!
        //this status is very ugly and not easy readable !!!!!!
        if ($rq[$j]['typ'] == '1')
        {
          //$v = 0;
          $val = $data[$k];
          $tax = $data['pricetax'];
        } else
        {
          $val = null;
          $tax = 0;
        }

        $r = self::InsertItem($idmsg, $k, $v, $data, $tax, $val);

        if ($r === false)
        {
          return false;
        }
      }
      $j++;
    }


    return true;
  }

  /**
   * MsgStore
   * @param $data
   * @return null|string
   */
  public static function MsgStore($data)
  {
    $hash = null;

    if (is_array($data))
    {
      $set = $data;
      // empty sql fragments
      $col = "(";
      $val = "(";
      $set['idparent'] = 0;
      $set['iduser'] = storage::getValue("user.iduser");
      $set['type'] = 2; //item
      $set['privacy'] = 0; //public
      $set['hash'] = md5(time() . "txxt" . rand());

      //var_dump($set_local);

      foreach ($set as $k => $v)
      {
        $col .= "{$k},";
        $val .= "'{$v}',";
      }
      $col = trim($col, ', ') . ')';
      $val = trim($val, ', ') . ')';

      //var_dump($col, $val);

      //konstruction queryDb finished
      $sql = "insert into msg {$col} values {$val}";
      //var_dump($sql);

      $r = db::queryDb($sql);
      //var_dump($r);

      if ($r)
      {
        $hash = $set['hash'];
      }
    } else
    {
      $hash = null;
    }
    return $hash;
  }

  /**
   * MsgUpdate
   * @param $idmsg
   * @param $data
   * @return null
   */
  public static function MsgUpdate($idmsg, $data)
  {

    $hash = null;

    $idmsg = filter_var($idmsg, FILTER_SANITIZE_NUMBER_INT);
    $set = $data;
    $updated = "";

    foreach ($set as $k => $v)
    {
      $updated .= "{$k} = '{$v}', ";
    }

    $updated = trim($updated, ', ');

    //konstruction queryDb finished
    $sql = "update msg set {$updated} where idmsg = '{$idmsg}';";
    //var_dump($sql);
    //echo "MsgUpdate: TODO -> update data.<br><br>";
    $r = db::queryDb($sql);
    if ($r != false)
    {
      $sql = "select hash from msg WHERE idmsg = '{$idmsg}';";
      $r = db::getRow($sql);

      $hash = $r["hash"];   //md5(time() . "txxt" . rand());
    }

    return $hash;
  }

  /**
   * CreateTitle
   * create title for item
   * @param $data
   * @param string $lang
   * @return string
   */
  public static function CreateTitle($data, $lang = 'auto')
  {
    if ($lang == 'auto')
    {
      $lang = storage::getValue('lang');
    }

    $title = '';

    switch ($lang)
    {
      case'cs':
        //czech notation
        $title = lang::Translate('idPRONAJMU', 'DES') . " " . $data['homeconfig'] . " " . $data['city'] . ", " . $data['country'];
        break;
      default:
        //english notation for unsuported language
        break;
    }
    return $title;
  }

  /**
   * getItemsTitle
   * @param string $id
   * @param int $limit
   * @return array|bool
   */
  public static function getItemsTitle($id = 'self', $limit = 50)
  {
    $r = self::modelGetItem('self', '*', '*', $limit);

    if ($r != false)
    {
      return $r;
    }

    return false;
  }

  /**
   * getItemData
   * @param string $id
   * @param int $limit
   * @return bool
   */
  public static function getItemData($id = 'self', $limit = 50)
  {
    $r = self::modelGetItem('*', $id, '*', $limit);

    if ($r != false)
    {
      return $r[0];
    }

    return false;
  }

  /**
   * getWork
   * @param $get
   */
  public static function getWork($get)
  {
    $usr = user::getActUserInfo();

    if ($usr['login'] == true && $usr['iduser'] > 0)
    {
      if (@$get['par'] == "logout")
      {
        user::deleteLogData();
        lib::Redirect('/');
        exit;
      }

      if (@$get['par'] == 'delete')
      {
        if ((int)strlen(@$get['id']) == 32)
        {
          var_dump("Delete OK", $get);
          self::DeleteMessage($get['id']);
          exit;
        } else
        {
          var_dump("Delete ID FAIL", $get, strlen(@$get['id']));
          exit;
        }
      }

      if (@$get['par'] == 'visibility')
      {
        //Change visibility message from admin menu
        if (strlen(@$get['id']) == 32)
        {
          //var_dump("Visibility OK", $get);
          self::ChangeVisibility($get['id']);
          lib::RedirectWay('');
          exit;
        } else
        {
          //Silent error. Only send log in future
          //var_dump("Visibility ID FAIL", $get);
          lib::RedirectWay('');
          exit;
        }
      }
    } else
    {
      user::deleteLogData();
      lib::Redirect('/');
      exit;
    }
    var_dump($get);
    exit;
  }

  /**
   * postWork
   * @param $post
   */
  public static function postWork($post)
  {
    $usr = user::getActUserInfo();
    //var_dump($post, $_FILES);
    //pozadavek na prihlasen

    if (isset($post["login"]))
    {
      // login
      $status = user::UserLogin($post);
      //print_r($status);
      if ($status == true)
      {
        lib::Redirect(config::$baseurl . "center/");
      } else
      {
        lib::RedirectWay();
      }
      exit;
    }

    //function class for logged user
    if ($usr['login'] == true && $usr['iduser'] > 0)
    {
      if (isset($post['mode']) && ($post['mode'] == "ADDTEMPL" || $post['mode'] == "EDITTEMPL"))
      {
        //var_dump("ADDTEMPL", $post);

        $itm = new template("");

        if ($itm->StoreTemplate($post))
        {
          //ok Store form is success go edit for additional photo, file or information
          lib::RedirectWay('/item/template/add');
        } else
        {
          //FAIL form not valid
          lib::RedirectWay('');
        }
        exit;
      }
      if (isset($post["parametersubmit"]))
      {
        //store item include with image

        //store previous form data if form not valid
        lib::FormStoreValue($post);

        $t = "";
        $itm = new item($t);
        log::sysLog('test');

        //var_dump($post);

        //TODO WARNIG for non price type tags with price, modules updateItem and insertItem delete 'idtag' in 'tags_item' table. This bug must be solved !!!!!!!!!!!!!!!!

        $result = $itm->StoreParam($post);
        if ($result != false)
        {
          //ok Store form is success go edit for additional photo, file or information
          lib::Redirect("/item/edit?id={$result}");///item/edit/
        } else
        {
          //FAIL form not valid
          //exit;
          lib::RedirectWay('');
        }
        exit;

      } elseif (isset($post["imagesubmit"]))
      {
        //Store Image with additonal data, but in this time unused
        user::updateUserAdv($post);
        lib::RedirectWay('');
        exit;

      } elseif (isset($post["uploading"]))
      {
        //Store Files with additional data, but in this time unused
        lib::RedirectWay('');
        exit;

      } else
      {

        log::Event('user_worker 3 _ Undefined command...Please contact team for solve this issue. Thank you. Yours Every Team.');
      }
    }
  }

  /**
   * DeleteMessage
   * @param $id
   */
  public static function DeleteMessage($id)
  {
    var_dump("TODO delete with asociated images or not?!");
  }

  /**
   * ChangeVisibility
   * @param $id
   * @return bool|mixed|resource
   */
  public static function ChangeVisibility($id)
  {
    $id = db::escapeVar($id);
    $r = db::getCol('msg', 'active', " `hash` = '{$id}'");

    if ($r == '0')
    {
      $val = 1;
    } else
    {
      $val = 0;
    }

    $r = db::setCol('msg', 'active', $val, " `hash` = '{$id}'");
    return $r;
  }

  public static function uiItem()
  {
    return new uiItem();
  }
}

class uiItem
{

  /**
   * uiShow - view item
   * @return string
   */
  public static function uiShow()
  {
    //var_dump(lib::FormReturnValue('idmsg'));
    /*TODO This fraction code must be optimize sql queryDb for minimize count queryDb using clausule "WHERE name of column IN (val1,val2 ... )" In this time not be important, but in future does*/

    //take actual ID of template
    $url = new coreurl();
    $templ = $url->getBaseByIndex(2);

    $usr = storage::getValue('user');

    $price = false;


    if ($templ == null)
    {
      //template not selected use default or data defined if exist

      if (lib::FormReturnValue("idtemplate") != '')
      {
        $templ = (int)lib::FormReturnValue("idtemplate");
      } else
      {
        $templ = 0;
      }
    }

    //get active template
    $r = db::getRow(
      "select * from template WHERE idtemplate = '{$templ}';"
    );

    //write tags assigned with template
    //get tag groups
    $r = template::GetTagsGroup($r["taggrouplevel"]);
    //var_dump($r);

    $tagForm = uiT::newFragment();
    $tagFormPrice = uiT::newFragment();

    $isprice = false;

    foreach ($r as $data)
    {
      //must be in two queryDb, because 'tag_gropup' used no only in multiple selections tags
      $sql = "select * from tags where idtaggroup = '{$data["idtaggroup"]}';";
      $rtag = db::getTable($sql);

      $sql = "select * from tag_group where tag_group.idtaggroup = '{$data["idtaggroup"]}';";
      $rtgr = db::getRow($sql);

      //var_dump($rtag, $rtgr);

      if ($rtag && $rtgr)
      {
        //option form element
        //tags and group must exist

        $tags = array();

        //need optimize for single ask into the databese
        foreach ($rtag as $tg)
        {
          $tags[$tg["idtag"]] = lang::translateFromDB($tg["name"], true);
        }
        $tagForm->appendFragment(

          uiT::formFieldset('fieldpair')
            ->appendContent(
              uiT::box('label bold')
                ->appendContent(
                  uiT::formLabel('taglabel')
                    ->appendContent(lang::translateFromDB($data["name"]))
                    ->render()
                )
                ->appendContent(
                  uiT::formInputHidden("{$rtgr["name"]}")
                    ->setClass("inp {$rtgr["name"]}")
                    ->setValue(lib::FormReturnValue($data["name"]))
                    ->render()
                )
                ->render(false)
            )
            ->appendContent(uiT::boxKillFloat())
            ->setId(0)
            ->render(false)
        );
      } elseif ($rtgr)
      {
        // TODO Toto je klicova sekce, kde se rozhoduje o tom jak ten item bude vypadat.

        if (((int)$rtgr['typ'] == 1) && (lib::FormReturnValue($data["name"]) != ''))
        {
          $price = true;
          $isprice = true;
        }

        $tagFormPrice->appendFragment(
          uiT::formFieldset('fieldpair')
            ->appendContent(
              self::uiViewTagWithParams($rtgr, $data)
            )
            ->appendContent(
              self::uiHidenFormTagWithData($rtgr, $data)
            )
            ->appendContent(uiT::boxKillFloat())
            ->setId(0)
            ->render(false)
        );
      }
    }

    $img = images::GetImageElements(
      'image',
      lib::FormReturnValue('hash')
      , 'big'
      , true
    );

    $imageGallery = uiT::newFragment();

    if ($img != '')
    {
      $imgtst = $img;
      $script = '<script>Gall.Run("gal0");</script>';

      $imageGallery->appendFragment(
        uiT::box('itemimage')
          ->appendContent(
            uiT::box('imagebox')
              ->appendContent($imgtst)
              ->appendContent($script)
              ->setId("gal0")
              ->render(false)
          )
          ->render(false)
      );
    }
    /*else
    {
      $imgtst = '';
      $script = '';
    }*/

    //Item header
    $headerfragment = uiT::newFragment()
      ->appendFragment(
        uiT::box("title")
          ->appendContent(
            ui::Head("title_in", "2", lib::FormReturnValue("title")
            )
          )
          ->render(false)
      );

    //Item perex
    $perexfragment = uiT::newFragment()
      ->appendFragment(
        uiT::box("perex")
          ->appendContent(
            uiT::box("perexText")
              ->appendContent(
                lib::FormReturnValue("perex")
              )
              ->render()
          )
          ->render(false)
      );

    //Item description
    $descriptionfragment = uiT::newFragment()
      ->appendFragment(
        uiT::box("text")
          ->appendContent(
            uiT::head('descr')
              ->setLevel(3)
              ->appendContent("Popis")
              ->render()
          )
          ->appendContent(
            uiT::box("text_in")
              ->appendContent(
                stripslashes(lib::FormReturnValue("text"))
              )
              ->render(false)
          )
          ->render(false)
      );

    $res = uiT::box("usersection")
      ->appendContent(
        uiT::formContainer('additem')
          ->appendContent(uiT::hidenComent('Item::uiShow'))
          ->appendContent($headerfragment->renderFragment())
          ->appendContent($perexfragment->renderFragment())
          ->appendContent(
            uiT::box('paramcont')
              ->appendContent($tagForm->renderFragment())
              ->render(false)
          )
          ->appendContent($imageGallery->renderFragment())
          ->appendContent(
            uiT::box('pricecont')
              ->appendContent(
                $tagFormPrice->renderFragment()
              )
              ->appendContent(
                (($price && $isprice)
                  ? (basket::uiToBasket())
                  : ''
                )
              )
              ->render(false)
          )
          ->appendContent($descriptionfragment->renderFragment())
          ->appendContent(uiT::boxKillFloat())
          ->setAction('/runcore.php')
          ->setMethod('post')
          ->setEnctype('multipart/form-data')
          ->render(false)
      )
      ->appendContent(
        self::uiAdminControlBar($usr, lib::FormReturnValues())
      )
      ->render(false);

    //var_dump($usr);

    return $res;
  }

  /**
   * uiAddEditItem
   * @param string $mode
   * @return string
   */
  public static function uiAddEditItem($mode = 'ADDITEM')  //ADDITEM|EDITITEM
  {
    /*TODO This fraction code must be optimize sql queryDb for minimize count queryDb using clausule "WHERE name of column IN (val1,val2 ... )" In this time not be important, but in future does*/

    //vezmu aktualni id sablony
    $url = new coreurl();
    $templ = $url->getBaseByIndex(2);

    //var_dump($templ);

    if ($templ == null || $templ == '')
    {
      //template not selected use default or data defined if exist
      if (lib::FormReturnValue("idtemplate") != '')
      {
        $templ = (int)lib::FormReturnValue("idtemplate");
      } else
      {
        $templ = '0'; //info 0 must by string, old ui class not convert it.
      }
    }

    //var_dump($templ);

    //nacte aktivni sablonu
    $r = db::getRow(
      "select * from template WHERE idtemplate = '{$templ}';"
    );

    //vypise template selector
    $templatelink = template::uiTemplateSelector($templ);

    //dej mi skupiny tagu
    $r = template::GetTagsGroup($r["taggrouplevel"]); //db::getTable($sql);

    //var_dump("Test: ", $r); //

    $tag_form = "";

    foreach ($r as $data)
    {
      //must be in two queryDb, because 'tag_gropup' used no only in multiple selections tags

      $sql = "select * from tags where idtaggroup = '{$data["idtaggroup"]}';";
      $rtag = db::getTable($sql);

      $sql = "select * from tag_group where tag_group.idtaggroup = '{$data["idtaggroup"]}';";
      $rtgr = db::getRow($sql);

      //var_dump($rtgr);

      if ($rtag && $rtgr)
      {
        //multiple option form fragment
        //tags and group must exist
        $tags = array();

        foreach ($rtag as $tg)
        {
          $tags[$tg["idtag"]] = lang::translateFromDB($tg["name"], true);
        }

        $tag_form .= ui::formFieldset('fieldpair',
          ui::formLabel('taglabel'
            , lang::translateFromDB($data["name"]))
          . ui::formOptions("{$rtgr["name"]}"
            , $tags
            , lib::FormReturnValue($data["name"]))
          . ui::Box('killfloat', '')
          , 0
        );

      } elseif ($rtgr)
      {
        // value form fragment
        // group must exist
        if ((int)$rtgr['typ'] == 1)
        {
          $tag_form .= prices::uiAdminPriceFormWithTax($rtgr, $data);
        } else
        {
          //log::sysLog($data, lib::FormReturnValue($data["name"]));
          $tag_form .= uiT::formFieldset('fieldpair')
            ->appendContent(
              ui::formLabel('taglabel'
                , lang::translateFromDB($data["name"])
              )
            )
            ->appendContent(
              uiT::formInputText($rtgr["name"])
                ->setClass('field fivewidth')
                ->setValue(lib::FormReturnValue($data["name"]))
                ->render()
            )
            ->appendContent(uiT::boxKillFloat())
            ->render(false);
        }
      }
    }

    //var_dump(lib::FormReturnValue("active"));

    //general fields in every items
    //TODO transform for multilanguage... ASAP

    /*var_dump(((lib::FormReturnValue("active") == '1')
      ? true
      : false));*/

    $general_form = uiT::newFragment()
      ->appendFragment(
        ui::formFieldset("titlecnt",
          ui::formLabel("titlelbl", lang::Translate("idTITLE")) .
          uiT::formInputText("title")
            ->setClass('field maxwidth')
            ->setValue(lib::FormReturnValue("title"))
            ->render()
          , 0
        )
      )
      ->appendFragment(
        ui::formFieldset("perexcnt",
          ui::formLabel("perexlbl", lang::Translate("idPEREX")) .
          ui::formInput("perex", "textarea", lib::FormReturnValue("perex"), "perex")
          , 0
        )
      )
      ->appendFragment(
        ui::formFieldset("textcnt",
          ui::formLabel("textlbl", lang::Translate("idTEXT")) .
          ui::formInput("text", "textarea", lib::FormReturnValue("text"), 'editor'),
          0
        )
      )
      ->appendFragment(
        "<script>CKEDITOR.replace( 'in_text_editor' );</script>"
      )
      ->appendFragment(
        ui::formFieldset("activecnt",
          uiT::formCheckBox("active")
            ->checked(
              ((lib::FormReturnValue("active") == '1')
                ? true
                : false)
            )
            ->setValue('1')
            ->render()
          . ui::formLabel("label", '<span><span></span></span>'
            . lang::Translate("idACTIVE"))
          , 0
        )
      );
    //var_dump($templ);

    $res = uiT::box("usersection")
      ->appendContent(uiT::hidenComent('Item::uiAddEditItem'))
      //->appendContent(uiCore::CommandCol())
      ->appendContent(
        uiT::box('content_col')
          ->appendContent(
            ui::Head('head', '1',
              lang::Translate("id" . $mode)
            )
          )
          ->appendContent(
          //widget for inserts items
            uiT::box('forms_edit')
              ->appendContent(
                uiT::box('itemform')
                  ->appendContent(
                    uiT::formContainer('additem')
                      ->appendContent(
                        uiCore::ErrMsg('bolder red_msg')
                      )
                      ->appendContent(
                      //template selector
                        $templatelink
                      )
                      ->appendContent(
                      //basic fields
                        $general_form->renderFragment()
                      )
                      ->appendContent(
                      //tags
                        $tag_form
                      )
                      ->appendContent(
                        ui::formFieldset('images',
                          ui::formLegend('images', lang::Translate('idIMAGES'), 0) .
                          ui::formLabel('image', lang::Translate('idIMAGES')) .
                          ui::formInput('images', 'file_multiple', 'file'),      //obrazky
                          0
                        )
                        . ui::formInput('id', 'hidden', lib::FormReturnValue('idmsg'))
                        . ui::formInput('idtemplate', 'hidden', $templ)
                        . ui::formInput('mode', 'hidden', $mode)
                        . ui::formInput('modul', 'hidden', "item")
                        . uiT::formFieldset('send')
                          ->appendContent(
                            uiT::formInputSubmit('parametersubmit')
                              ->setClass('button')
                              ->setValue(lang::Translate('idSUBMIT'))
                              ->render()
                          )
                          ->render(false)
                      )
                      ->setAction('/runcore.php')
                      ->render(false)
                  )
                  ->render(false)
              )
              ->appendContent(uiT::boxKillFloat())
              ->render(false)
          )
          ->appendContent(
            (($mode === 'EDITITEM')
              ? uiT::box('itemimage')
                ->appendContent(
                  uiT::box('imagebox')
                    ->appendContent(
                      images::GetImageElements('image'
                        , lib::FormReturnValue('hash')
                        , 'big'
                        , true
                      )
                    )
                    ->setId("gal0")
                    ->appendContent(
                      '<script>Gall.Run("gal0");</script>'
                    )
                    ->render(false)
                )
                ->render(false)
              : ''
            )
          )
          ->render(false)
      )
      ->appendContent(uiT::boxKillFloat())
      ->render(false);
    return $res;
  }

  public static function uiItemListItem()
  {
    $menutyptop = new menu(3);

    $res = uiT::box("usersection")
      ->appendContent(uiT::hidenComent('Item::uiItemListItem'))
      ->appendContent(
      //uiCore::CommandCol()
      )
      ->appendContent(
        uiT::box('content_col')
          ->appendContent(
            ui::Head('head', '1',
              lang::Translate("idCENTER")
            )
          )
          ->appendContent(uiCore::ErrMsg())
          //widget for inserted items
          ->appendContent(
            uiT::box('item_list')
              ->appendContent(
                uiT::head('myitems')
                  ->setLevel(2)
                  ->appendContent(lang::Translate('idMYITEMS'))
                  ->render()
              )
              ->appendContent(
                self::uiListItemFragment(
                  item::getItemsTitle('self', 100), 150)
              )
              ->render(false)
          )
          ->render(false)
      )
      ->appendContent(uiT::boxKillFloat())
      ->render(false);
    return $res;
  }

  /**
   * uiRelatedProduct
   * @return mixed
   */
  public static function uiRelatedProduct()
  {
    $r = uiT::box('related_product')
      ->appendContent("TODO RELATED PRODUCT")
      ->render(false);

    return $r;
  }

  public static function uiAdminControlBar($usr, $data)
  {

    $res = '';

    //only for admin
    if ($usr['status'] >= 4)
    {
      $res .= uiT::link('edit')
        ->setStyle('float:right;')
        ->setRef('/item/edit?id=' . $data['hash'])
        ->appendContent('ED')
        ->render();

      $res .= (($data['active'] == 0)
        ? uiT::link('edit')
          ->setStyle('float:right; right: 35pt')
          ->setRef('/runcore.php?par=visibility&modul=item&id=' . $data['hash'])
          ->appendContent('[ - ]')
          ->render()
        : uiT::link('edit')
          ->setStyle('float:right; right: 35pt')
          ->setRef('/runcore.php?par=visibility&modul=item&id=' . $data['hash'])
          ->appendContent('[ O ]')
          ->render()
      );

      $res .= uiT::link('edit')
        ->setStyle('float:right; right: 70pt')
        ->setRef('/runcore.php?par=delete&modul=item&id=' . $data['hash'])
        ->appendContent('&nbsp;X&nbsp;')
        ->render(false);
    };

    return $res;
  }

  /**
   * uiListItemFragment List item
   * @param $data
   * @param int $textsixe
   * @return string
   */
  public static function uiListItemFragment($data, $textsixe = 200)
  {
    $dt = "";
    $i = 0;

    $usr = storage::getValue("user");

    //var_dump($data);
    if (count($data) > 0 && $data !== false)
    {
      //$currency_row = currency::modGetCurrencyPar(storage::getValue("user.pref.currency"));
      //$currency = explode(":", $currency_row['val']);

      foreach ($data as $d)
      {
        $imgdata = images::GetImageData($d['hash'], 'idmsg');

        $dt .= uiT::box('item relative')
          ->appendContent(
            images::uiGetImageElement(
              "list_item_img bxwidth{$i}"
              , $imgdata
              , 'mini'
              , "/item/{$d['hash']}"
            )
          )
          ->appendContent(prices::uiDiscountWidget($d['idmsg']))
          ->appendContent(
            uiT::box('item_info')
              ->appendContent(
                uiT::link('title')
                  ->appendContent(
                    uiT::head('header')
                      ->appendContent($d['title'])
                      ->setLevel(3)
                      ->render()
                  )
                  ->setRef("/item/{$d['hash']}")
                  ->render(false)
              )
              ->appendContent(
                (($textsixe == 0)
                  ? ""
                  : uiT::box('text')
                    ->appendContent(stringev::truncate($d['perex'], $textsixe))
                    ->render()
                )
              )
              ->appendContent(
                uiT::box('statusbar')
                  ->appendContent(
                    uiT::formContainer('additem')
                      ->appendContent(
                        self::uiStatusBar($d)
                      )
                      ->setAction('/runcore.php')
                      ->setMethod('post')
                      ->setEnctype('multipart/form-data')
                      ->render(false)
                  )
                  ->render(false)
              )
              ->render(false)
          )
          ->appendContent(uiT::boxKillFloat())
          ->setId($i)
          ->render(false);

        $i++;
      }
    } else
    {
      $dt = uiT::box('item', lang::Translate('idNOITEMS'))->render();
    }

    $dt .= uiT::boxKillFloat();
    $dt .= (($usr['status'] >= 4)
      ? uiT::link('newitem')
        ->appendContent(lang::Translate('idADDITEM'))
        ->setRef('/item/add')
        ->render()
      : "");

    $res = uiT::box('listitembox', $dt)->render(false);
    return $res;
  }

  public static function uiStatusBar($data)
  {
    //var_dump($data);

    $sql = "select *,currency.name as cname from tag_item
            INNER JOIN currency ON tag_item.currency = currency.idcurrency
            INNER JOIN tax ON tag_item.idtax = tax.idtax
            INNER JOIN tag_group ON tag_item.idtaggroup = tag_group.idtaggroup
            where tag_item.iditem = '{$data['idmsg']}' and `tag_group`.`typ` = '1'";

    //var_dump($sql);

    $rprice = db::getRow($sql);
    //var_dump($rprice);

    $data['price'] = $rprice['value'];
    $data['rprice'] = $rprice;

    $usr = storage::getValue("user");
    //var_dump($usr);

    $basket = config::getAppSwitch('basket');
    //var_dump($basket);

    $ui = uiT::newFragment();

    if ($basket != true)
    {
      //no basket
      $ui->appendFragment(
        uiItem::uiAdminControlBar($usr, $data)
      );
    } else
    {
      $ui->appendFragment(
        uiT::boxInline('pricebox')
          ->appendContent(
            ($rprice['value'] != ''
              ? uiT::boxInline('label')
                ->appendContent(lang::Translate('idPRICE', 'MDES'))
                ->render(false)

              : uiT::boxInline('priceboxlblbold')
                ->appendContent(lang::Translate('idWAITON', 'MDES'))
                ->render(false)
            )
          )
          ->appendContent(
            uiT::boxInline('')
              ->appendContent(
                uiT::boxInline("label big bold green")
                  ->appendContent(
                    "{$rprice['value']} {$rprice['cname']}"
                  )
                  ->render()
              )
              ->appendContent(
                prices::uiTaxListWidget($rprice)
              )
              ->render(false)
          )
          ->render(false)
      );
      $ui->appendFragment(
        ($rprice['value']
          ? (basket::uiToBasket($data))
          : ''
        )
      );
      //first line
      $ui->appendFragment(
        uiItem::uiAdminControlBar($usr, $data)
      );
    }
    return $ui->renderFragment();
  }

  /**
   * uiInfoBar
   * @return string
   */
  public static function uiInfoBar()
  {
    $infobar = uiT::newFragment()
      ->appendFragment(uiT::boxInline('ico child_active')->render())
      ->appendFragment(uiT::boxInline('ico smoking_none')->render())
      ->appendFragment(uiT::boxInline('ico animal_none')->render())
      ->appendFragment(uiT::boxInline('ico student_active')->render())
      ->appendFragment(uiT::boxInline('ico company_active')->render())
      ->appendFragment(uiT::boxInline('ico holiday_none')->render());

    return $infobar->renderFragment();
  }

  /**
   * uiDiscountWidget
   * @param $data
   * @return string
   */
  public static function uiDiscountWidget($data)
  {
    //todo must solve for minimize queryDb into DB
    //this is dump way, but in this time faster

    $templ = lib::FormReturnValue('idtemplate');
    $r = db::getRow(
      "select * from template WHERE idtemplate = '{$templ}';"
    );
    $r = template::GetTagsGroup($r["taggrouplevel"]);

    $widget = uiT::box("discountBox big bold")
      ->appendContent(
        uiT::box('taglabel percent')
          ->appendContent(lang::translateFromDB($data["name"]) . ": ")
          ->appendContent(
            prices::trimLastZeros(
              lib::FormReturnValue($data["name"])
            )
          )
          ->appendContent("% ")
          ->render()
      )
      ->appendContent(
        uiT::box("taglabel oldprice")
          ->appendContent(lang::translateFromDB('oldprice'))
          ->appendContent(item::getOldPrice($data))
          ->appendContent(currency::uiActualCurrency())
          ->render(false)
      )
      ->render(false);
    return $widget;
  }

  /**
   * uiPriceWidget
   * @param $data
   * @param $rtgr
   * @return string
   */
  public static function uiPriceWidget($data, $rtgr)
  {
    $ret = "";

    if (lib::FormReturnValue($data["name"]) != '')
    {
      $ret = uiT::box('priceBox')
        ->appendContent(
          uiT::box("label lbl{$rtgr["name"]} right")
            ->appendContent(lang::translateFromDB($data["name"]) . ": ")
            ->appendContent(
              uiT::boxInline("label xxbig bold green")
                ->appendContent(lib::FormReturnValue($data["name"]))
                ->appendContent(currency::uiActualCurrency())
                ->render(false)
            )
            ->render(false)
        )
        ->appendContent(
          uiT::box("label spanBox right")
            ->appendContent(
              prices::getPriceWithoutTax(
                lib::FormReturnValue($data["name"]), "21"
              )
            )
            ->appendContent(currency::uiActualCurrency())
            ->appendContent(lang::Translate('idWITHOUTTAX', 'DES'))
            ->render(false)
        )
        ->render(false);
    }

    return $ret;
  }

  /**
   * uiHidenFormTagWithData
   * @param $rtgr
   * @param $data
   * @return string
   */
  public static function uiHidenFormTagWithData($rtgr, $data)
  {
    return uiT::formInputHidden("{$rtgr["name"]}")
      ->setClass("inp {$rtgr["name"]}")
      ->setValue(lib::FormReturnValue($data["name"]))
      ->render();
  }

  /**
   * uiViewTagWithParams
   * @param $rtgr
   * @param $data
   * @return string
   */
  public static function uiViewTagWithParams($rtgr, $data)
  {
    $widget = '';

    if ((int)$rtgr['typ'] == 1)
    {
      //type price
      if (lib::FormReturnValue($data["name"]) != '')
      {
        $widget = uiItem::uiPriceWidget($data, $rtgr);
      } else
      {
        $widget = self::uiWaitForItem();
      }
    } elseif ((int)$rtgr['typ'] == 2)
    {
      //type discount
      if (lib::FormReturnValue($data["name"]) != '')
      {
        $widget = uiItem::uiDiscountWidget($data);
      }
    }
    return $widget;
  }

  /**
   * uiWaitForItem
   * @return string
   */
  public static function uiWaitForItem()
  {
    $widget = uiT::boxInline("label big bold green")
      ->appendContent(lang::Translate('idWAITON'))
      ->render();
    return $widget;
  }
}

class itemvars
{
  public static $REGION = array(
    '0' => "[ * ] Region:",
    '1' => "Jihočeský",
    '2' => "Plzeňský",
    '3' => "Karlovarský",
    '4' => "Ústecký",
    '5' => "Liberecký",
    '6' => "Královéhradecký",
    '7' => "Pardubický",
    '8' => "Olomoucký",
    '9' => "Zlínský",
    '10' => "Praha",
    '11' => "Středočeský",
    '12' => "Moravskoslezský",
    '13' => "Vysočina",
    '14' => "Jihomoravský",
  );

  public static $VELIKOST = array(
    '0' => "[ * ] Vyberte:",
    '1' => "1 pokoj",
    '1+kk' => "1+kk",
    '1+1' => "1+1",
    '2+kk' => '2+kk',
    '2+1' => '2+1',
    '3+kk' => '3+kk',
    '3+1' => '3+1',
    '4+kk' => '4+kk',
    '4+1' => '4+1',
    '5+kk' => '5+kk',
    '5+1' => '5+1',
    'inf' => 'Jiný',
  );

  public static $TYPE = array(
    'idLEASE' => 'Pronájem',
    'idSALE' => 'Prodej',
    'idAUCTION' => 'Aukce',
  );

  public static $TYPEAD = array(
    'idOFFER' => 'Nabídka',
    'idDEMAND' => 'Poptávka',
  );
  public static $TYPEPROPERTY = array(
    'idAPARTMENT' => 'Byt',
    'idHOUSE' => 'Dům',
    //'idHOUSE' => 'Dům',
    'idOFFICE' => 'Kancelář',
    'idHEADQUARTERS' => 'Sídlo firmy',
    'idHISTORICAL' => 'Historický',
    'idCHURCH' => 'Církevní',
    'idFACTORY' => 'Tovární hala',
    'idWAREHOUSE' => 'Sklad',
    'idLAND' => 'Pozemek',
    'idOTHER' => 'Jiná',
  );
}
