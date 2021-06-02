<?php

/**
 * Aragnet Michal Raska
 * lib
 * User: naith
 * Date: 30.10.17
 * Time: 11:56
 * Desc: Basket for eshop
 */
class basket extends core
{
  public $t;
  private static $modul = 'basket';
  private $accept_modes = array(
    'add',
    'edit',
    'view',
    'delivery',
    'contact',
    'check',
    'finish'
  );

  public function __construct($t)
  {
    $this->t = $t;
    $this->t->MDES = lang::IncludeResource('res_' . self::$modul);
    $this->t->modul = self::$modul;
  }

  /**
   * Run
   * @param string $mode
   */
  public function Run($mode = "")
  {

    if (in_array($mode, $this->accept_modes))
    {
      $mode = ucfirst($mode);
      $this->$mode();

    } else
    {
      $this->showBasket();
    }

    uiCore::DelErrMsg();
  }

  /**
   * Contact
   */
  public function Contact()
  {
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idBASKET');

    $order = storage::getValue('order');
    //var_dump($order);
    if (is_array($order) == false || $order == null)
    {
      $this->showBasket();
    } else
    {

      $page = $this->uiContact($order);

      uiCore::PageCore($this->t, $page);
    }
  }

  /**
   * uiContact
   * @param $order
   * @return string
   */
  public function uiContact($order)
  {
    $ret = uiT::box('contact')
      ->appendContent(
        uiT::head('checklabel', 2)
          ->appendContent(
            $this->uiOrderNavigation('contact')
          )
          ->render(false)
      )
      ->appendContent(uiCore::ErrMsg())
      ->appendContent(
        uiT::head('checklabel', 3, lang::Translate('idINBASKET'))
          ->render()
      )
      ->appendContent(
        uiT::Table('forms')
          ->appendContent(
            $this->uiBasketList($order, true)
          )
          ->appendContent(
            ((storage::getValue('act_delivery') == null)
              ? ''
              : $this->uiPriceTotal($order)
            )
          )
          ->appendContent(
            ((storage::getValue('act_delivery') == null)
              ? ""
              : delivery::getActualDelivery(
                storage::getValue('act_delivery')
              )
            )
          )
          ->appendContent(
            order::getTotalOrderPrice(
              prices::getPriceTotal()
              , storage::getValue('act_delivery')
            )
          )
          ->render(false)
      )
      ->appendContent(
        uiT::head('checklabel', 3, lang::Translate('idKONTAKT'))
          ->render()
      )
      ->appendContent(
        uiUser::uiTUserForm()
      )
      ->render(false);

    return $ret;
  }

  /**
   * Finish
   */
  public function Finish()
  {
    $order = storage::getValue('order');
    $user = storage::getValue('act_user');

    if (is_array($order) == false || $order == null)
    {
      $this->showBasket();
    } else
    {
      $page = $this->uiFinish($order, $user);

      //erase order, basket and others.
      storage::delValue('basket');
      storage::delValue('order');
      storage::delValue('act_delivery');
      storage::delValue('act_user');
      storage::delValue('form_data');
      storage::delValue('userorder');
      storage::delValue('basketmnu,basket');
      storage::delValue('basketmnu,delivery');
      storage::delValue('basketmnu,contact');
      storage::delValue('basketmnu,check');

      uiCore::PageCore($this->t, $page);
    }
  }

  /**
   * uiFinish
   * @param $order
   * @param $userorder
   * @return string
   */
  public function uiFinish($order, $user)
  {
    $ret = uiT::box('contact')
      ->appendContent(
        uiT::head('thanke')
          ->setLevel(1)
          ->setStyle('text-align: center')
          ->appendContent('Děkujeme za objednávku')
          ->render()
      /*uiT::Table('forms')
        ->appendContent($this->uiBasketList($order, true))
        ->render(false)*/
      )
      ->appendContent(
        uiT::box('thankyou_info')
          ->appendContent("Číslo objednávky je {$order['orderno']}<br><br>")
          ->appendContent("<p>Kopie objednávky vám byla zaslána na e-mailovou adresu: {$user['usre-mail']} .</p>")
          ->render(false)
      //user::uiTUserView($userorder)
      )
      ->render(false);

    return $ret;
  }

  /**
   * showBasket
   */
  public function showBasket()
  {
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idBASKET');
    $basket = basket::getBasket();
    $page = $this->uiBasket($basket);
    uiCore::PageCore($this->t, $page);
  }

  public function uiOrderNavigation($active)
  {
    //var_dump($_SESSION);

    $res = uiT::head('checklabel', 2)
      ->appendContent(
        ($active == ''
          ? uiT::boxInline('active')
            ->appendContent(lang::Translate('idBASKET'))
            ->render()
          : uiT::link('kontakt')
            ->appendContent(lang::Translate('idBASKET'))
            ->setRef('/basket/')
            ->render()
        )
      )
      ->appendContent(' &bull; ')
      ->appendContent(
        ($active == 'delivery'
          ? uiT::boxInline('active')
            ->appendContent(lang::Translate('idSHIPPAYMENT'))
            ->render()
          : (storage::getValue('basketmnu,delivery')
            ? uiT::link('kontakt')
              ->appendContent(lang::Translate('idSHIPPAYMENT'))
              ->setRef('/basket/delivery/')
              ->render()
            : uiT::boxInline('unactive')
              ->appendContent(lang::Translate('idSHIPPAYMENT'))
              ->render()
          )
        )
      )
      ->appendContent(' &bull; ')
      ->appendContent(
        ($active == 'contact'
          ? uiT::boxInline('active')
            ->appendContent(lang::Translate('idADRESS'))
            ->render()
          : (storage::getValue('basketmnu,contact')
            ? uiT::link('kontakt')
              ->appendContent(lang::Translate('idADRESS'))
              ->setRef('/basket/contact/')
              ->render()
            : uiT::boxInline('unactive')
              ->appendContent(lang::Translate('idADRESS'))
              ->render()
          )
        )
      )
      ->appendContent(' &bull; ')
      ->appendContent(
        ($active == 'check'
          ? uiT::boxInline('active')
            ->appendContent(lang::Translate('idCHECK'))
            ->render()
          : uiT::boxInline('unactive')
            ->appendContent(lang::Translate('idCHECK'))
            ->render()
        )
      )
      ->render(false);

    return $res;
  }

  /**
   * uiBasket
   * @param $basket
   * @param null $delivery
   * @return string
   */
  public function uiBasket($basket, $delivery = null)
  {
    //empty basket widget
    $emptybagfragment = uiT::newFragment()
      ->appendFragment(
        uiT::box('infobasketcont')
          ->appendContent(
            uiT::head('infobasket')
              ->setLevel(2)
              ->appendContent(lang::Translate('idEMPTYBAG'))
              ->render()
          )
          ->render(false)
      );

    //basket
    $bagfragment = uiT::newFragment()
      ->appendFragment(
        uiT::Table('forms')
          ->appendContent(
            uiT::box('itemform')
              ->appendContent(
                uiT::formContainer('additem')
                  //todo bubble nav

                  ->appendContent(
                    ($basket['countitems'] == 0
                      ? $emptybagfragment->renderFragment()
                      : ($this->uiBasketList($basket))
                    )
                  )
                  ->appendContent(
                    (($delivery == null || $delivery == '')
                      ? ''
                      : delivery::getActualDelivery($delivery))
                  )
                  ->appendContent(
                    ($basket['countitems'] == 0 || storage::getValue('act_delivery') == null
                      ? ''
                      : $this->uiPriceTotal($basket)
                    )
                  )
                  ->appendContent(
                    ((storage::getValue('act_delivery') == null)
                      ? ""
                      : delivery::getActualDelivery(
                        storage::getValue('act_delivery')
                      )
                    )
                  )
                  ->appendContent(
                    ($basket['countitems'] == 0
                      ? ''
                      : order::getTotalOrderPrice(
                        prices::getPriceTotal()
                        , storage::getValue('act_delivery')
                      )
                    )
                  )
                  ->appendContent(
                    ($basket['countitems'] == 0
                      ? ''
                      : $this->uiCheckOut($basket)
                    )
                  )
                  ->setAction('/runcore.php')
                  ->setMethod('post')
                  ->setEnctype('multipart/form-data')
                  ->render(false)
              )
              ->render(false)
          )
          ->render(false)
      );


    $res = uiT::box("usersection")
      ->appendContent(
      //uiCore::CommandCol()
      )
      ->appendContent(
        uiT::box('content_col')
          /*->appendContent(
            ui::Head('head', '1', lang::Translate("idBASKET"))
          )*/
          ->appendContent(
            uiT::box('basket')
              ->appendContent(
                $this->uiOrderNavigation('')
              )
              ->appendContent(
                uiT::head('checklabel', 3,
                  lang::Translate('idINBASKET')
                )
                  ->render()
              )
              ->appendContent(
                $bagfragment->renderFragment()
              )
              ->appendContent(uiT::boxKillFloat())
              ->render(false)
          )
          ->render(false)
      )
      ->appendContent(uiT::boxKillFloat())
      ->render(false);

    return $res;
  }

  /**
   * uiCheckOut
   * @param $basket
   * @return string
   */
  public function uiCheckOut($basket)
  {
    $checkoutfragment = uiT::newFragment()
      ->appendFragment(
        uiT::Tr('items')
          ->appendContent(
            uiT::Td('empty')
              ->colSpan(4)
              ->appendContent('')
              ->render(false)
          )
          ->appendContent(
            uiT::Td('suma')
              ->appendContent(
                uiT::formInputHidden('modul', 'basket')
                  ->render()
              )
              ->appendContent(
                uiT::formInputSubmit('to_delivery', lang::Translate('idDELIVERY'))
                  ->setClass('button')
                  ->render()
              )
              ->render(false)
          )
          ->appendContent(
            uiT::Td('del')
              ->appendContent()
              ->render(false)
          )
          ->render()
      );

    return $checkoutfragment->renderFragment();
  }

  /**
   * getWork
   * @param $get
   */
  public static function getWork($get)
  {
    $usr = user::getActUserInfo();

    if ($get['mode'] == 'del')
    {
      $basket = new basket('');
      $basket->Del($get);

      lib::RedirectWay();
    }
    
  }

  /**
   * postWork
   * @param $post
   */
  public static function postWork($post)
  {
    $usr = user::getActUserInfo();
    
    //add item to basket
    if (isset($post['tobasket']))
    {
      //add item to basket

      $basket = new basket("");
      $basket->Add($post);

      lib::RedirectWay('');
    }

    //
    if (isset($post['to_delivery']))
    {
      //Store basket

      $basket = new basket("");
      $basket->storeBasket($post);

      //basket ok
      storage::setValue('basketmnu,basket', true);

      //to contact ui
      lib::Redirect('/basket/delivery/');
    }
    
    if (isset($post['to_contact']))
    {
      //store delivery and payment
      $delivery = new delivery("");
      $result = $delivery->storeDelivery($post);

      if ($result == false)
      {
        //wrong input, must insert repeat
        lib::RedirectWay();
      } else
      {
        //delivery complete
        storage::setValue('basketmnu,delivery', true);
        //to contact
        lib::Redirect('/basket/contact/');
      }
    }
    
    if (isset($post['to_check']))
    {
      //store contact

      $basket = new basket("");
      $result = $basket->storeUserData($post);

      if ($result == false)
      {
        //wrong input, must insert repeat

        lib::RedirectWay();
      } else
      {
        //contact complete
        storage::setValue('basketmnu,contact', true);
        //to check ui
        lib::Redirect('/basket/check/');
      }

      exit;
    }

    if (isset($post['to_finish']))
    {
      //generate order

      $order = new order();

      $res = $order->generateOrder();

      storage::setValue('basketmnu,check', true);


      $mail = new my_mail();

      $mail->from = $res['mailseler'];

      $mail->subject = $res['subject'];
      $mail->body = $res['body'];
      $ok = true;

      if ($mail->sendMail($res['mailclient']) !== true)
      {
        $ok = false;
      }
      if ($mail->sendMail($res['mailseler']) !== true)
      {
        $ok = false;
      }

      if ($ok == false)
      {
        //wrong
        storage::setValue('err_msg', "idSENDMAILFAILED");
        lib::RedirectWay();
      } else
      {
        $order->storeOrder();

        //to finish ui
        lib::Redirect('/basket/finish/');
      }

      exit;
    }

  }

  /**
   * Delivery
   */
  public function Delivery()
  {
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idDELIVERY');
    $order = storage::getValue('order');

    if (is_array($order) == false || $order == null)
    {
      $this->showBasket();
    } else
    {
      $delivery = new delivery('');
      $page = $delivery->uiDelivery($order);

      uiCore::PageCore($this->t, $page);
    }
  }

  /**
   * Check
   */
  public function Check()
  {
    $this->t->data = user::getActUserInfo();
    $this->t->nazev = lang::Translate('idBASKET');

    $order = storage::getValue('order');

    if (is_array($order) == false || $order == null)
    {
      $this->showBasket();
    } else
    {

      $page = $this->uiCheck();

      uiCore::PageCore($this->t, $page);
    }

  }

  /**
   * uiCheck
   * @return string
   */
  public function uiCheck()
  {
    $order = storage::getValue('order');
    $user = storage::getValue('act_user');
    //$formdata = evform::FormData('act_note');

    $ret = uiT::box('check')
      ->appendContent(
        uiT::head('checklabel', 2)
          ->appendContent(
            $this->uiOrderNavigation('check')
          )
          ->render(false)
      )
      ->appendContent(
        uiT::head('checklabel', 3, lang::Translate('idINBASKET'))
          ->render()
      )
      ->appendContent(
        uiT::Table('forms')
          ->appendContent($this->uiBasketList($order, true))
          ->appendContent($this->uiPriceTotal($order))
          ->appendContent(
            delivery::getActualDelivery(
              storage::getValue('act_delivery')
            )
          )
          ->appendContent(
            order::getTotalOrderPrice(
              prices::getPriceTotal()
              , storage::getValue('act_delivery')
            )
          )
          ->render(false)
      )
      ->appendContent(
        uiT::head('checklabel', 3, lang::Translate('idKONTAKT'))
          ->render()
      )
      ->appendContent(uiCore::ErrMsg('bolder red_msg textCenter'))
      ->appendContent(
        uiT::box('userView')
          ->appendContent(
            uiT::formContainer('test')
              ->setAction('/runcore.php')
              ->setMethod('post')
              ->appendContent(
                $this->uiUserData($user)
              )
              ->appendContent(
                gdpr::getInstance()
                  ->uiForm()
                  ->render()
              )
              ->appendContent(
                uiT::formInputSubmit('to_finish', 'DOKONCIT')
                  ->setStyle("clear:both; margin-top: 2pt; margin-right: 2pt")
                  ->setClass('submitt right')
                  ->render()
              )
              ->render(false)
          )
          ->render(false)
      )
      ->render(false);

    return $ret;
  }


  /**
   * storeUserData
   * @param $data
   * @return bool
   */
  public function storeUserData($data)
  {
    //store user data for order
    //TODO add validate data

    //print_r('storeUserData');

    //var_dump($data, storage::getValue('order'));

    $usr = user::getActUserInfo();

    $dt = array();

    $correct = true;

    if (isset($data['to_check']) == false)
    {
      $correct = false;
    } else
    {
      foreach ($data as $k => $v)
      {
        switch ($k)
        {
          case 'usrname':
          case 'usrstreet':
          case 'usrcity':
          case 'usrpsc':
            if (strlen($v) < 3)
            {
              $correct = false;
            }
            break;
          case 'usre-mail':
            if (lib::isMail($v) == false)
            {
              $correct = false;
            }
            break;

          case 'usrphone':
            if (strlen($v) < 9)
            {
              $correct = false;
            }
            break;
        }


        if ($v == '' && $k != 'ordernote')
        {
          $correct = false;
          //break;
        } else
        {
          $dt[db::escapeVar($k)] = db::escapeVar($v);
        }
      }
    }

    if ($correct == false)
    {
      storage::setValue('err_msg', 'idWRONGVALUE');
      return false;

    } else
    {
      $obj = evform::FormData('act_user');
      $obj->setValues($dt);

      return true;
    }
  }

  /**
   * storeBasket
   * @param $basket
   * @return bool
   */
  public function storeBasket($basket)
  {

    //test for items
    if (isset($basket['total_items']) && (int)$basket['total_items'] > 0)
    {
      //ok exist

      //lock basket and prepare data for order
      $order = array();
      $total_price = evmath::bcGetZero();

      $bs = basket::getBasket();

      for ($i = 0; $i < (int)$basket['total_items']; $i++)
      {
        $item = array();

        $sql = "select * from msg where `hash` = '{$basket['hash_'.$i]}'";
        $msg = db::getRow($sql);

        if ($msg != false)
        {
          //TODO add check for missing data
          //ASK if missing skip wrong item or basket failed ?
          $item['hash'] = $basket['hash_' . $i];
          $item['idmsg'] = $bs['items'][$i]['idmsg'];
          $item['title'] = $msg['title'];
          $item['price'] = $basket['price_' . $i];
          $item['itmcount'] = $basket['itmcount_' . $i];

          $bs['items'][$i]['itmcount'] = $basket['itmcount_' . $i];

          $order['items'][$i] = $item;

          $total_price = bcadd(
            $total_price
            , bcmul(
              $item['price']
              , $item['itmcount']
            )
          );
        }
      }

      $order['countitems'] = $i;
      $order['totalbasket'] = $total_price;

      storage::setValue('order', $order);

      $bs['totalbasket'] = $total_price;
      storage::setValue('basket', $bs);
    } else
    {
      storage::setValue('err_msg', "idBASKET_IS_EMPTY");
    }

    return true;
  }

  /**
   * @param $get
   */
  public function Del($get)
  {
    $data = basket::getBasket();

    $new_basket = array();

    $items = $data['items'];
    $countitems = $data['countitems'];
    $totalbasket = $data['totalbasket'];

    $idmsg = $get['idmsg'];
    $id = message::getIdFromHash($idmsg);

    $i = 0;
    $newprice = evmath::bcGetZero();

    foreach ($items as $itm)
    {
      if ($itm['idmsg'] !== $id)
      {
        //diferent ids item not deleted and insert into new basket
        $new_basket['items'][$i] = $itm;
        $newprice = bcadd($itm['price'], $newprice);
        $i++;
      }
    }

    $new_basket['countitems'] = $i;

    if ($i == 0)
    {
      $new_basket['totalbasket'] = evmath::bcGetZero();

    } else
    {
      $new_basket['totalbasket'] = $newprice;
    }


    storage::setValue('basket', $new_basket);

  }

  /**
   * @param $data
   */
  public function Add($data)
  {

    $this->addToBasket($data);

  }

  public function addToBasket($data)
  {

    //get actual basket
    $basket = self::getBasket();

    //parse basket
    $items = $basket['items'];
    $countitems = $basket['countitems'];

    //set zero
    $subtotal = evmath::bcGetZero();

    $addedcount = false;

    $j = 0;
    foreach ($items as $v)
    {

      if (($v['idmsg'] == $data['idmsg'])
        && bccomp($v['price'], $data['price'] == 0)
      )
      {
        //if item exist add only quantity
        $subtotal = bcadd(
          $subtotal,
          (is_numeric($data['itmcount'])
            ? bcmul($v['price'],
              ($v['itmcount'] + $data['itmcount']))
            : $v['price'])
        );

        $items[$j]['itmcount'] = $v['itmcount'] + $data['itmcount'];

        $addedcount = true;

      } else
      {
        //sum exists items
        $subtotal = bcadd(
          $subtotal,
          (is_numeric($v['itmcount'])
            ? bcmul($v['price'], $v['itmcount'])
            : $v['price'])
        );
      }

      $j++;

    }

    if (!is_array($items))
    {
      $items = array();
      $countitems = 0;
    }

    if ($addedcount == false)
    {
      $items[$countitems] = $data;

      $totalbasket = bcadd(
        $subtotal,
        (is_numeric($data['itmcount'])
          ? bcmul($data['price'], $data['itmcount'])
          : $data['price'])
      );

      $countitems++;

    } else
    {

      $totalbasket = $subtotal;

    }

    $subbasket['items'] = $items;
    $subbasket['countitems'] = $countitems;
    $subbasket['totalbasket'] = $totalbasket;


    storage::setValue('basket', $subbasket);
    storage::setValue('err_msg', 'idADDEDTOBASKET');

    return $addedcount;

  }

  /**
   * uiBasketList
   * @param $basket
   * @param bool|false $show
   * @return string
   */
  public function uiBasketList($basket, $show = false)
  {
    $bfragment = uiT::newFragment();

    $i = 0;

    foreach ($basket['items'] as $vb)
    {

      $msg = message::getMessageById($vb['idmsg']);
      $imgdata = images::GetImageData($msg['hash'], 'idmsg');


      if (array_key_exists('tax', $vb))
      {
        $tax = prices::getTaxValue($vb['tax']);
      } else
      {
        $tax = '';
      }

      $bfragment->appendFragment(
        uiT::Tr('items shadows')
          ->appendContent(
            uiT::Td('image')
              ->appendContent(
                images::uiGetImageElement(
                  'list_item_img emuimage'
                  , $imgdata
                  , 'mini'
                  , "/item/{$msg['hash']}")
              )
              ->render(false)
          )
          ->appendContent(
            uiT::Td('title')
              ->appendContent(
                uiT::boxInline('labeltitle', $msg['title'])->render()
              //. uiT::BoxInline('labelperex', $msg['perex'])->render()
              )
              ->appendContent(
                ($show == false
                  ? uiT::formInputHidden("hash_{$i}")
                    ->setValue("{$msg['hash']}")
                    ->render()
                  : ""
                )
              )
              ->render()
          )
          ->appendContent(
            uiT::Td('price')
              ->appendContent(uiT::boxInline('label', 'Cena: ')->render())
              ->appendContent(
                uiT::boxInline(
                  'value'
                  , $vb['price']
                  . currency::uiActualCurrency())
                  ->render(false)
              )
              ->appendContent("<br>" . uiT::boxInline('label', 'DPH: ')->render())
              ->appendContent(
                uiT::boxInline(
                  'tax'
                  , $tax
                  . currency::uiActualCurrency())
                  ->render(false)
              )
              ->appendContent("<br>" . uiT::boxInline('label', 'Bez DPH: ')->render())
              ->appendContent(
                uiT::boxInline(
                  'tax'
                  , prices::getPriceWithoutTax($vb['price'], $tax)
                  . currency::uiActualCurrency())
                  ->render(false)
              )
              ->appendContent(
                ($show == false
                  ? uiT::formInputHidden("price_{$i}")
                    ->setValue("{$vb['price']}")
                    ->render()
                  : ""
                )
              )
              ->render(false)
          )
          ->appendContent(
            uiT::Td('itmcount')
              ->appendContent(
                uiT::boxInline('lblitmcount')
                  ->appendContent('Pocet: ')
                  ->render()
              )
              ->appendContent(
                ($show == false
                  ? uiT::formInputText("itmcount_{$i}")
                    ->setValue("{$vb['itmcount']}")
                    ->setClass('inputitmcount')
                    ->render()
                  : uiT::boxInline('emuinputitmcount')
                    ->appendContent($vb['itmcount'])
                    ->render()
                )
              )
              ->render(false)
          )
          ->appendContent(
            uiT::Td('suma')
              ->appendContent(uiT::boxInline('label', 'Soucet: ')->render())
              ->appendContent(
                uiT::boxInline(
                  'value'
                  , bcmul($vb['itmcount'], $vb['price'])
                  . currency::uiActualCurrency())
                  ->render(false)
              )
              ->appendContent("<br>" . uiT::boxInline('label', 'bez DPH: ')->render())
              ->appendContent(
                uiT::boxInline(
                  'value'
                  , prices::getPriceWithoutTax(bcmul($vb['itmcount'], $vb['price']), $tax)
                  . currency::uiActualCurrency())
                  ->render(false)
              )
              ->render(false)
          )
          ->appendContent(
            uiT::Td('del')
              ->appendContent(
                ($show == false
                  ? uiT::link('value')
                    ->setRef("/runcore.php?idmsg={$msg['hash']}&modul=basket&mode=del")
                    ->appendContent("Odstranit")
                    ->render(false)
                  : ""
                )
              )
              ->render(false)
          )
          ->appendContent()
          ->render(false)
      );

      $i++;

    };

    $bfragment->appendFragment(
      uiT::formInputHidden("total_items")
        ->setValue("{$i}")
        ->render()
    );

    return $bfragment->renderFragment();
  }

  /**
   * uiPriceTotal
   * @param $basket
   * @return string
   */
  public function uiPriceTotal($basket)
  {
    $pricetotalfragment = uiT::newFragment()
      ->appendFragment(
        uiT::Tr('items')
          ->appendContent(
            uiT::Td('empty')
              ->colSpan(2)
              ->appendContent('')
              ->render(false)
          )
          ->appendContent(
            uiT::Td('itmcountLbl')
              ->colSpan(2)
              ->appendContent(
                uiT::boxInline('label', "Košík součet: ")->render())
              ->render(false)
          )
          ->appendContent(
            uiT::Td('suma bold')
              ->appendContent(
                uiT::boxInline('value statusprice black')
                  ->appendContent(
                    evmath::bcRoundStat(
                      $basket['totalbasket'], 2)
                  )
                  ->render(false)
              )
              ->appendContent(
                uiT::boxInline('statusprice black')
                  ->appendContent(
                    currency::uiActualCurrency()
                  )
                  ->render(false)
              )
              ->appendContent()
              ->render(false)
          )
          ->appendContent(
            uiT::Td('del')
              ->appendContent()
              ->render(false)
          )
          ->render()
      );

    return $pricetotalfragment->renderFragment();
  }


  //public static functions

  /**
   * uiToBasket
   * @return string
   */
  public static function uiToBasket($data = '')
  {
    if ($data != '')
    {
      //emu
      $simuPost = array();
      $simuPost['idmsg'] = $data['idmsg'];
      $simuPost['modul'] = (@$data['modul'] != '' ? $data['modul'] : '');
      $simuPost['price'] = $data['price'];
      $simuPost['tax'] = $data['rprice']['idtax'];

      lib::FormStoreValue($simuPost);

    }

    $lang = new lang('res_' . self::$modul);

    $r = uiT::box('tobasketbox')
      ->appendContent(
        uiT::formInputHidden('modul')
          ->setValue('basket')
          ->render()
      )
      ->appendContent(
        uiT::formInputHidden('idmsg')
          ->setValue(lib::FormReturnValue('idmsg'))
          ->render()
      )
      ->appendContent(
        uiT::formInputHidden('price')
          ->setValue(lib::FormReturnValue('price'))
          ->render()
      )->appendContent(
        uiT::formInputHidden('tax')
          ->setValue(lib::FormReturnValue('tax'))
          ->render()
      )
      ->appendContent(
        uiT::box('sendbutton')
          ->appendContent(
          /* TODO in this time not need
           * uiT::formInputSubmit('tobasket')
            ->setValue($lang->getTranslate('idTOBASKET'))
            ->render()*/
          )
          ->render(false)
      )
      ->appendContent(
        uiT::box('itmcountbox')
          ->appendContent(
            uiT::formLabel('label')
              ->appendContent(
                $lang->getTranslate('idCOUNT') . ':'
              )
              ->render()
          )
          ->appendContent(
            uiT::formInputText('itmcount', 1)
              ->setClass('field')
              ->render()
          )
          ->appendContent(
            uiT::formInputSubmit('tobasket')
              ->setClass('button')
              ->setValue($lang->getTranslate('idTOBASKET'))
              ->render()
          )
          ->render(false)
      )
      ->appendContent(uiT::boxKillFloat())
      ->render(false);

    return $r;
  }

  /**
   * uiBaskeInfo
   * widget top
   * @return string
   */
  public static function uiBaskeInfo()
  {

    $basket = config::getAppSwitch('basket');

    if ($basket != true)
    {
      return "";
    }

    $inactive = uiT::boxInline('basket_inactive')
      ->appendContent(
        uiT::image("basketimg")
          ->setSrc("/images/ico/shopping-icon-inactive.svg")
          ->render()
      )
      ->appendContent(
        uiT::boxInline('price')
          ->appendContent(evmath::bcRoundStat(evmath::bcGetZero(), 2))
          ->render()
      )
      ->appendContent(
        currency::uiActualCurrency()
      )
      ->render(false);

    $price = prices::getPriceTotal();

    $active = uiT::link('basketlink')
      ->setRef(config::$baseurl . "basket/")
      ->appendContent(
        uiT::image("basketimg")
          ->setSrc("/images/ico/shopping-icon-active.svg")
          ->render()
      )
      ->appendContent(
        uiT::boxInline('price')
          ->appendContent(evmath::bcRoundStat($price, 2))
          ->render()
      )
      ->appendContent(
        currency::uiActualCurrency()
      )
      ->render(false);


    $res = uiT::box("basket")
      ->appendContent(
        (bccomp($price, evmath::bcGetZero()) == 0
          ? $inactive
          : $active)
      )
      ->render(false);

    return $res;
  }


  public static function initBasket($usr)
  {
    $r = false;

    if ($usr == null || $usr['login'] == false)
    {
      if (basket::getBasket() == false)
      {
        storage::setValue('basket', array());

        $r = true;

      }

    } else
    {
      $usr = storage::getValue('user');

      if (basket::getBasket() == false)
      {
        $basket = basket::getOldBasket($usr);

        if ($basket == false)
        {
          $basket = array();
        }

        storage::setValue('basket', $basket);

        $r = true;
      }
    }

    return $r;
  }

  /**
   * getBasket
   * @return null
   */
  public static function getBasket()
  {
    return storage::getValue('basket');
  }


  public static function getOldBasket($usr)
  {

    if (basket::getBasket() == false)
    {
      storage::setValue('basket', array());
    }

    $r = basket::getBasket();

    return $r;
  }

  /**
   * uiUserData
   * @param $user
   * @return string
   */
  public function uiUserData($user)
  {
    return uiT::box('usrBox')
      ->appendContent(
        uiUser::uiTUserView($user)
      )
      ->appendContent(
        uiT::box('note')
          ->appendContent(
            uiT::formLabel('notelbl')
              ->setClass('label doublewidth')
              ->appendContent("Poznámka:")
              ->render()
          )
          ->appendContent(
            uiT::box('ordernote')
              ->setClass('field fivewidth alignleft right')
              //->setDisable(true)
              ->appendContent($user['ordernote'])
              ->render()
          )
          ->render(false)
      )
      ->appendContent(
        uiT::formInputHidden('modul', 'basket')
          ->render()
      )
      ->render(false);
  }
}