<?php
/**
 * Aragnet Michal Raska
 * lib
 * User: naith
 * Date: 9.8.17
 * Time: 12:28
 * Desc:
 * Object oriented library for creating WEB User Interface
 */

/**
 * Class uiT container class for static and dynamic usage ui elements
 */
class uiT
{
  private $fragment = '';

  /**
   * @param $content
   * @return string
   */
  public static function hidenComent($content)
  {
    if ($content != '')
    {
      return "<!--" . htmlspecialchars($content) . "-->";
    }
    return "";
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTBox
   */
  public static function box($name, $content = '')
  {
    $obj = new uiTBox();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTPBox
   */
  public static function PBox($name, $content = '')
  {
    $obj = new uiTPBox();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @return string
   */
  public static function boxKillFloat()
  {
    $obj = new uiTBox();
    $obj->setName('killfloat');
    return $obj->render();
  }

  /**
   * @param $name
   * @param string $src
   * @return uiTImage
   */
  public static function image($name, $src = '')
  {
    $obj = new uiTImage();
    $obj->setName($name);
    $obj->setSrc($src);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTBoxInline
   */
  public static function boxInline($name, $content = '')
  {
    $obj = new uiTBoxInline();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param int $level
   * @param string $content
   * @return uiTHead
   */
  public static function head($name, $level = 1, $content = '')
  {
    $obj = new uiTHead();
    $obj->setName($name);
    $obj->appendContent($content);
    $obj->setLevel((int)$level);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTLink
   */
  public static function link($name, $content = '')
  {
    $obj = new uiTLink();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $index
   * @return uiTListItem
   */
  public static function listItem($name, $index)
  {
    $obj = new uiTListItem();
    $obj->setName($name);
    $obj->appendContent();

    return $obj;
  }

  /**
   * @param string $name
   * @return uiTOrderedList
   */
  public static function orderedList($name)
  {
    $obj = new uiTOrderedList();
    $obj->setName($name);

    return $obj;
  }

  /**
   * @param string $name
   * @return uiTUnorderedList
   */
  public static function unorderedLIst($name)
  {
    $obj = new uiTUnorderedList();
    $obj->setName($name);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTFormContainer
   */
  public static function formContainer($name, $content = '')
  {
    $obj = new uiTFormContainer();
    $obj->setName($name);
    $obj->setMethod('post');
    $obj->setEnctype('multipart/form-data');
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTFormFieldset
   */
  public static function formFieldset($name, $content = '')
  {
    $obj = new uiTFormFieldset();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTFormLabel
   */
  public static function formLabel($name, $content = '')
  {
    $obj = new uiTFormLabel();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $value
   * @return uiTFormInputText
   */
  public static function formInputText($name, $value = '')
  {
    $obj = new uiTFormInputText();
    $obj->setName($name);
    $obj->setValue($value);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $value
   * @return uiTFormTextHint
   */
  public static function formInputTextHint($name, $value = '')
  {
    $obj = new uiTFormTextHint();
    $obj->setName($name);
    $obj->setValue($value);
    return $obj;
  }

  /**
   * @param string $name
   * @return uiTFormInputRadio
   */
  public static function formInputRadio($name)
  {
    $obj = new uiTFormInputRadio();
    $obj->setName($name);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $value
   * @return uiTformInputTextArea
   */
  public static function formInputTextArea($name, $value = '')
  {
    $obj = new uiTformInputTextArea();
    $obj->setName($name);
    $obj->setValue($value);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $value
   * @return uiTFormCheckBox
   */
  public static function formCheckBox($name, $value = '')
  {
    $obj = new uiTFormCheckBox();
    $obj->setName($name);
    $obj->setValue($value);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $value
   * @return uiTFormInputHidden
   */
  public static function formInputHidden($name, $value = '')
  {
    $obj = new uiTFormInputHidden();
    $obj->setName($name);
    $obj->setValue($value);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $value
   * @return uiTFormInputPassword
   */
  public static function formInputPassword($name, $value = '')
  {
    $obj = new uiTFormInputPassword();
    $obj->setName($name);
    $obj->setValue($value);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $value
   * @return uiTFormInputSubmit
   */
  public static function formInputSubmit($name, $value = '')
  {
    $obj = new uiTFormInputSubmit();
    $obj->setName($name);
    $obj->setValue($value);

    return $obj;
  }

  /**
   * @return uiT
   */
  public static function newFragment()
  {
    $obj = new uiT();
    return $obj;
  }

  /**
   * @param string $fragment
   * @return $this
   */
  public function appendFragment($fragment)
  {
    $this->fragment .= $fragment;
    return $this;
  }

  /**
   * @return string
   */
  public function renderFragment()
  {
    return $this->fragment;
  }

  /**
   * @return string
   */
  public static function insertLine()
  {
    return (
      self::newLine()
      . self::newLine()
    );
  }

  /**
   * @return string
   */
  public static function newLine()
  {
    return (
      PHP_EOL . "<br>"
    );
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTTable
   */
  public static function Table($name, $content = '')
  {
    $obj = new uiTTable();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTTr
   */
  public static function Tr($name, $content = '')
  {
    $obj = new uiTTr();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }

  /**
   * @param string $name
   * @param string $content
   * @return uiTTd
   */
  public static function Td($name, $content = '')
  {
    $obj = new uiTTd();
    $obj->setName($name);
    $obj->appendContent($content);

    return $obj;
  }
}


/**
 * Class uiTBox <div> UI elemnt.
 * Superclass for other UI elements
 */
class uiTBox
{
  protected $name = '';
  protected $nameraw = '';  //for matroska tag (radio button etc.)
  protected $sclass = '';
  protected $id = '';
  protected $content = '';
  protected $style = '';

  /**
   * setName
   * @param $name
   * @return $this
   */
  public function setName($name)
  {
    $this->name = "class = \"{$name}\" ";
    $this->nameraw = $name;
    return $this;
  }

  /**
   * setStyle
   * @param $style
   * @return $this
   */
  public function setStyle($style)
  {
    $this->style = "style = \"{$style}\" ";
    return $this;
  }

  public function setClass($classname)
  {
    $this->sclass = "class = \"{$classname}\" ";
    return $this;
  }

  /**
   * setId
   * @param $id
   * @return $this
   */
  public function setId($id)
  {
    $this->id = "id = \"{$id}\" ";
    return $this;
  }

  /**
   * @deprecated No use in new project
   * setContent
   * @param $content
   * @return $this
   */
  public function setContent($content)
  {
    $this->content = $content;
    return $this;
  }

  /**
   * appendContent
   * @param $content
   * @return $this
   */
  public function appendContent($content = "")
  {
    $this->content .= $content;
    return $this;
  }

  /**
   * getBasicPar
   * Helper for geting basic parameters repetely used
   * @return string
   */
  protected function getBasicPar()
  {
    if ($this->sclass != '')
    {
      $r = " {$this->sclass} {$this->id} {$this->style} ";
    } else
    {
      $r = " {$this->name} {$this->id} {$this->style} ";
    }

    return $r;
  }

  /**
   * SafeContent
   * @param bool $safe
   * @param string $namepar
   * @return string
   */
  protected function SafeContent($safe, $namepar = 'content')
  {
    if ($safe === true)
    {
      $out = htmlspecialchars($this->$namepar);
    } else
    {
      $out = $this->content;
    }
    return $out;
  }

  /**
   * render
   * @param bool|true $safe
   * @return string
   */
  public function render($safe = true)
  {
    $out = "\n\r<div {$this->getBasicPar()}>{$this->SafeContent($safe)}</div>";
    return $out;
  }
}

class uiTPBox extends uiTBox
{
  /**
   * render
   * @param bool|true $safe
   * @return string
   */
  public function render($safe = true)
  {
    return "<p {$this->getBasicPar()}>{$this->SafeContent($safe)}</p>";
  }
}

class uiTUnorderedList extends uiTBox
{

  protected $items = array(); //uiTItemList object
  protected $countItem = 0;  //count of stored object

  /**
   * AppendListItem
   * @param $item string
   * @return $this
   */
  public function appendListItem($item)
  {
    $this->items[] = $item;
    $this->countItem++;
    return $this;
  }

  /**
   * getListItem
   * Return uiTItemList object or false if not exist
   * @param string $index
   * @return bool | object
   */
  public function getListItem($index)
  {
    return (
    ((($this->countItem - 1) >= $index) && ($index >= 0)
      ? $this->items[$index]
      : false
    )
    );
  }

  /**
   * getItems
   * @return array
   */
  public function getListItems()
  {
    return ($this->items);
  }

  /**
   * render
   * @param bool|true $safe
   * @return string
   */
  public function render($safe = false)
  {
    $result = "";

    //all jobs done, render all object
    foreach ($this->items as $itm)
    {
      $result .= $itm . PHP_EOL;
    }

    return "<ul {$this->getBasicPar()}>{$result}</ul>";
  }
}

class uiTOrderedList extends uiTUnorderedList
{
  public function render($safe = false)
  {
    $result = "";

    //all jobs done, render all object
    foreach ($this->items as $itm)
    {
      $result .= $itm->render . PHP_EOL;
    }

    return "<ol {$this->getBasicPar()}>{$result}</ol>";
  }
}

class uiTListItem extends uiTBox
{
  public function render($safe = true)
  {
    $out = PHP_EOL
      . "<li {$this->getBasicPar()}>"
      . PHP_EOL
      . "{$this->SafeContent($safe)}"
      . PHP_EOL
      . "</li>";

    return $out;
  }
}

/**
 * Class uiTBoxInline Inline block <span>
 */
class uiTBoxInline extends uiTBox
{
  /**
   * render
   * @param bool|true $safe
   * @return string
   */
  public function render($safe = true)
  {
    return "<span {$this->getBasicPar()}>{$this->SafeContent($safe)}</span>";
  }
}

/**
 * Class uiTHead Inline block <hx> x = {1,2,3,4,...}
 */
class uiTHead extends uiTBox
{
  private $level = 1;

  public function setLevel($level)
  {
    $this->level = $level;
    return $this;
  }

  public function render($safe = true)
  {
    if ($this->level == '')
    {
      die("Level head must be defined");
    }

    return "<h{$this->level} {$this->getBasicPar()}>{$this->SafeContent($safe)}</h{$this->level}>";
  }
}

class uiTLink extends uiTBox
{
  private $alt = '';
  private $ref = '';

  public function setRef($ref)
  {
    $this->ref = $ref;
    return $this;
  }


  public function setAlt($alt)
  {
    $this->alt = "alt= \"{$alt}\" ";
    return $this;
  }

  public function render($safe = true)
  {
    return "\n\r<a href=\"{$this->ref}\" {$this->getBasicPar()} {$this->alt}>{$this->SafeContent($safe)}</a>";
  }
}

class uiTImage extends uiTBox
{
  private $alt = '';
  private $src = '';
  private $title = '';

  public function setSrc($src)
  {
    $this->src = $src;
    return $this;
  }

  public function setAlt($alt)
  {
    $this->alt = "alt= \"{$alt}\" ";
    return $this;
  }


  public function setTitle($title)
  {
    $this->id = "title = \"{$title}\" ";
    return $this;
  }

  public function render($safe = true)
  {
    if ($this->src == '')
    {
      die("Source of image not defined");
    }

    return "<img src=\"{$this->src}\" {$this->getBasicPar()} {$this->alt}{$this->title} />";

  }
}

class uiTFormContainer extends uiTBox
{
  private $action = '';
  private $method = 'post';
  private $enctype = 'default';

//($name, $content, $action, $method = "post", $enctype = 'default')

  public function setMethod($method)
  {
    //jsou mozne dalsi metody, ale moc se u nas nepouziji
    if ($method != 'post')
    {
      $this->method = " method=\"get\"";
    } else
    {
      $this->method = " method=\"post\"";
    }
    return $this;
  }

  public function setAction($action)
  {
    $this->action = $action;
    return $this;
  }

  public function setEnctype($enctype)
  {
    //pokud neni default pak vybrat a overit naplatnost
    if ($enctype == 'default')
    {
      $this->enctype = ' enctype="multipart/form-data"';
    } else
    {
      $this->enctype = " enctype=\"{$enctype}\"";
    }
    return $this;
  }


  public function render($safe = true)
  {
    //I know, content is never safe, because more elements are defined in container. But developer have to know about this.

    $out = "<form action=\"{$this->action}\" {$this->method} {$this->getBasicPar()}{$this->enctype}>\n{$this->SafeContent($safe)}</form>\n";

    return ($out);
  }
}

class uiTFormFieldset extends uiTBox
{
  public function render($safe = true)
  {

    $out = "\t<fieldset {$this->getBasicPar()}>\n{$this->SafeContent($safe)}\t</fieldset>\n";
    return ($out);
  }
}

class uiTFormLabel extends uiTBox
{
  public function render($safe = true)
  {
    return "<label {$this->getBasicPar()}>{$this->SafeContent($safe)}</label>";
  }
}

/**
 * Class uiTFormInputText
 */
class uiTFormInputText extends uiTBox
{
  protected $value = '';
  protected $target = '';
  protected $disable = '';
  protected $required = '';

  public function setValue($value)
  {
    $this->value = $value;

    return $this;
  }

  /**
   * getBasicPar
   * Helper for geting basic parameters repetely used
   * @return string
   */
  protected function getBasicPar()
  {
    if ($this->sclass != '')
    {
      $r = " {$this->name} {$this->sclass} {$this->id} {$this->style} ";
    } else
    {
      $r = " {$this->name} {$this->id} {$this->style} ";
    }

    return $r;
  }

  /**
   * setName
   * @param $name
   * @return $this
   */
  public function setName($name)
  {
    $this->name = "name = \"{$name}\" ";
    $this->sclass = "class = \"{$name}\" ";
    $this->nameraw = $name;
    return $this;
  }

  /**
   * setDisable
   * @param bool $disable
   * @return $this
   */
  public function setDisable($disable)
  {
    if ($disable == true)
    {
      $this->disable = ' disabled ';
    } else
    {
      $this->disable = '';
    }

    return $this;
  }

  /**
   * setTargetForm
   * @param string $target
   * @return $this
   */
  public function setTargetForm($target)
  {
    $this->target = $target;
    return $this;
  }

  public function setRequired($required)
  {
    if ($required == true)
    {
      $this->required = ' required ';
    } else
    {
      $this->required = '';
    }
    return $this;
  }

  /**
   * render
   * @param bool|true $safe
   * @return string
   */
  public function render($safe = true)
  {
    $out = "<input type=\"text\" {$this->getBasicPar()} value=\"{$this->SafeContent(true,'value')}\" {$this->target} {$this->disable} {$this->required}/>\n";

    return $out;
  }
}

class uiTFormInputHidden extends uiTFormInputText
{
  public function render($safe = true)
  {
    $out = "<input type=\"hidden\" {$this->getBasicPar()} value=\"{$this->SafeContent($safe,'value')}\" {$this->target} />\n";
    return $out;
  }
}

class uiTFormInputPassword extends uiTFormInputText
{
  public function render($safe = true)
  {
    $out = "<input type=\"password\" {$this->getBasicPar()} value=\"{$this->SafeContent($safe,'value')}\" {$this->target} />\n";
    return $out;
  }
}

class uiTFormInputSubmit extends uiTFormInputText
{
  public function render($safe = true)
  {
    $out = "<input type=\"submit\" {$this->getBasicPar()} value=\"{$this->SafeContent($safe,'value')}\" {$this->target} />\n";

    return $out;
  }
}

class uiTFormCheckBox extends uiTFormInputText
{
  protected $checked;

  public function checked($check)
  {
    if (strval($check) == '1')
    {
      //var_dump('True val');
      $this->checked = " checked = 'checked' ";
    } else
    {
      //var_dump('whatever');
      $this->checked = "";
    }

    return $this;
  }

  public function render($safe = true)
  {
    $out = "<input type=\"checkbox\" {$this->getBasicPar()} value=\"{$this->SafeContent($safe,'value')}\" {$this->target} {$this->checked} />\n";

    return $out;
  }


}

class uiTformInputTextArea extends uiTFormInputText
{
  public function render($safe = true)
  {
    $out = "<textarea {$this->getBasicPar()}{$this->target} >" . "{$this->SafeContent($safe,'value')}</textarea>\n";
    return $out;
  }
}


class uiTFormFile extends uiTFormInputText
{
  public function render($safe = true)
  {

    $out = "<input type=\"file\"  name = \"{$this->name}[]\" {$this->value}{$this->style}{$this->id} multiple {$this->target} />\n";
    return $out;
  }
}

class uiTFormInputRadio extends uiTFormCheckBox
{

  public function render($safe = true)
  {
    $out = uiT::listItem("{$this->nameraw} box", 1)
      ->appendContent(
        "<input type=\"radio\" {$this->getBasicPar()} value=\"{$this->SafeContent($safe,'value')}\" {$this->target} {$this->checked} />\n"
      )
      ->appendContent(
        uiT::formLabel($this->nameraw . ' label')
          ->appendContent($this->SafeContent($safe))
          ->render()
      )
      ->render(false);
    return $out;
  }

  public function renderST($safe = true)
  {
    $out = uiT::listItem("{$this->nameraw} box", 1)
      ->appendContent(
        "<input type=\"radio\" {$this->getBasicPar()} value=\"{$this->SafeContent($safe,'value')}\" {$this->target} {$this->checked} />\n"
      )
      ->appendContent(
        uiT::formLabel($this->nameraw . ' label')
          ->appendContent("<span><span></span></span>")
          ->appendContent($this->SafeContent($safe))
          ->render(false)
      )
      //->appendContent('<div class="check"></div>')
      ->render(false);
    return $out;
  }
}

class uiTOptions extends uiTFormInputText
{
  function formOptions($name, $data, $keyselect = '', $id = '', $style = '')
  {
    $stylef = self::uiStyleBasic($name, $id, $style, 'sel');

    if ($name != '')
    {
      $namef = " name = \"$name\"";
    }
    $valuedefault = lang::Translate("idSELECTVALUE", 'DES');

    $options = "\t\t\t<option value=\"\">{$valuedefault}</option>\n";

    foreach ($data as $key => $val)
    {
      if ($keyselect == $key)
      {
        $selected = " selected = \"selected\" ";
      } else
      {
        $selected = "";
      }

      $options .= "\t\t\t<option value=\"{$key}\"{$selected}>{$val}</option>\n";
    }

    $select = "\t\t<select{$namef}{$stylef}>\n{$options}\t\t</select>\n";
    return $select;
  }
}

class uiTTable extends uiTBox
{
  public function render($safe = false)
  {
    $out = "<table {$this->getBasicPar()}>{$this->SafeContent($safe,'value')}</table>\n";
    return $out;
  }
}

class uiTTr extends uiTBox
{
  public function render($safe = false)
  {
    $out = "<tr {$this->getBasicPar()}>{$this->SafeContent($safe,'value')}</tr>\n";
    return $out;
  }
}

class uiTTd extends uiTBox
{
  protected $span = '';

  public function colSpan($span)
  {
    $this->span = $span;
    return $this;
  }

  public function render($safe = false)
  {
    $parspan = (($this->span != '')
      ? " colspan = '{$this->span}' "
      : '');
    $out = "<td {$this->getBasicPar()} {$parspan}>{$this->SafeContent($safe,'value')}</td>\n";
    return $out;
  }
}


class uiTFormTextHint extends uiTFormInputText
{
  protected $hint = "";
  protected $label = null;
  protected $cssclass = "myHint";


  public function setHint($hint)
  {
    $this->hint = $hint;
    return $this;
  }

  public function setLabel($label)
  {
    $this->label = $label;
    return $this;
  }

  public function setClass($sclass)
  {
    $this->cssclass = $sclass;
    return $this;
  }


  /**
   * render
   * @param bool|true $safe
   * @return string
   *
   * NOTE:
   * base mixin: form.less>.inputTextHint
   * default call mixin: form.less>.myHint
   */
  public function render($safe = true)
  {
    $lbl = "";

    if ($this->label != null)
    {
      $lbl = uiT::boxInline('labelHint')
        ->appendContent($this->label)
        ->render();
    }

    $out = uiT::box('inputTextHint')
      //base css class for this type field
      ->setClass("{$this->cssclass}")// {$this->nameraw}")
      ->appendContent($lbl)
      ->appendContent(
        uiT::box('hint')
          ->appendContent(
            uiT::box('text')
              ->appendContent($this->hint)
              ->render()
          )
          ->appendContent(
            uiT::formInputText($this->nameraw)
              ->setClass('textInput')
              ->setRequired(true)
              ->setValue($this->value)
              ->render()

          )
          ->render(false)
      )
      ->render(false);
    return $out;
  }
}