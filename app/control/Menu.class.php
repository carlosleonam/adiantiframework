<?php

/**
 * Menu
 *
 * @version    1.0
 * @package    control
 * @subpackage
 * @author     Gabriela Ronchi Milioli / Jorge Henrique da Silva Naspolini
 * @copyright  Copyright (c) 2017-2017
 * @license    http://www.adianti.com.br/framework-license
 */
class Menu extends TPage
{
  protected $form;
  protected $formFields;

  public function __construct()
  {
    parent::__construct();
    try
    {
      $this->form = new TForm('form_Menu');
      $this->form->class = 'tform';

      $this->form->style = 'display: table;width:100%';

      $table = new TTable;
      $this->form->add($table);

      $frame = new TFrame('frame');
      $frame->setLegend('Menu XML');// titulo do frame

      $row=$table->addRow();
      $cell=$row->addCell($frame);
      $cell->colspan=6;

      $tableframe = new TTable;
      $frame->add($tableframe);

      $cbMenuPai = new TCombo('cbMenuPai');
      $cbMenuPai->id="cbMenuPai";
      $cbMenuFilho = new TCombo('cbMenuFilho');
      $cbMenuFilho->id="cbMenuFilho";
      $antesdepois = new TCombo('antesdepois');
      $antesdepois->id="antesdepois";
      $antesdepois->addItems(array("1" => "Depois"));
      $antesdepois->setDefaultOption("Antes");

      $link = 'menu.xml';

      $xml = simplexml_load_file($link);

      $menuitem1 = array();
      foreach($xml as $tag){
        $menuitem1[$tag->attributes()['label'].""] = $tag->attributes()['label']."";
      }
      $cbMenuPai->addItems($menuitem1);

      $changebutton = TButton::create('change',  array($this, 'onChagecbMenuPai'), _t('Find'),  'fa:search fa-fw');
      $changebutton->id="changebutton";

      $savebutton = TButton::create('save',  array($this, 'onSave'), _t('Save'),  'fa:floppy-o fa-fw');
      $removebutton = TButton::create('remove',  array($this, 'onRemove'), _t('Delete'),  'fa:close fa-fw');

      $tableframe->addRowSet(array(new TLabel('Menu label: '), $label = new TEntry('label'), new TLabel('Menu icon: '), $icon = new TEntry('icon'), new TLabel('Menu action: '), $action = new TEntry('action')));

      $tableframe->addRowSet(array(new TLabel('Menu pai: '), $cbMenuPai, new TLabel('Menu filho: '), $cbMenuFilho, new TLabel('Colocar antes ou depois: '), $antesdepois));
      $tableframe->addRowSet($changebutton)->style="display:none";
      $tableframe->addRowSet(new TLabel('Escolha o Menu Pai e/ou Menu Filho para Excluir o Menu<br><strong>**Ao Excluir o Menu Pai, todos seus filhos também serão removidos**</strong>'))->style="background-color:#FFFBCB";

      TTransaction::open('permission');

      $SystemProgram = SystemProgram::all();

      $items = array();
      foreach ($SystemProgram as $object)
      {
        $items[$object->controller] = $object->controller;
      }
      TTransaction::close();

      $action->setCompletion( array_values( $items ));

      $icon->setValue("fa: fa-fw");

      $hbox = THBox::pack($savebutton, $removebutton);

      // add a row for the form action
      $row=$table->addRow();
      $row->class = 'tformaction';
      $cell=$row->addCell($hbox);
      $cell->width='30%';

      $this->formFields = array($cbMenuPai, $cbMenuFilho, $changebutton, $savebutton, $label, $icon, $action, $removebutton, $antesdepois);
      $this->form->setFields($this->formFields);

      TScript::create("
        $('#cbMenuPai').on('change', function(){
          $('#changebutton').click();
        });

      ");

      $container = new TVBox;
      $container->style = 'width: 90%';
      //$container->add(new TXMLBreadCrumb('menu.xml', 'Menu'));
      $container->add($this->form);
      parent::add($container);
    }
    catch (Exception $e)
    {
      new TMessage('error', $e->getMessage());
    }
  }

  /**
   * Remove itens do menu no arquivo menu.xml
   * $param = array () = $param['cbMenuPai'] e $param['cbMenuFilho']
   */
  public function onRemove($param){

    $cbMenuPai = $param['cbMenuPai'];
    $cbMenuFilho = $param['cbMenuFilho'];
    if(!empty($cbMenuPai) || !empty($cbMenuFilho)){

      $link = 'menu.xml';

      $xml = simplexml_load_file($link);

      $strxml = "<menu>";
      $menuitem1 = array();
      $menuitem2 = array();
      foreach($xml as $tag){
        $menuitem1[$tag->attributes()['label'].""] = $tag->attributes()['label']."";
        $label1 = $tag->attributes()['label']."";
        $icon1 = $tag->icon."";

        if(!empty($cbMenuFilho)){
          $strxml .= "<menuitem label='$label1'>";
          $strxml .= "<icon>".$icon1."</icon>";
          if(!empty($tag->action)){
            $strxml .= "<action>".$tag->action."</action>";
          }

          $menus = $tag->menu;
          if(!empty($menus)){
            $strxml .= "<menu>";
            foreach($menus->menuitem as $menu){
              $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";
              $label2 = $menu->attributes()['label']."";
              $icon2 = $menu->icon."";
              $action2 = $menu->action."";
              if($cbMenuFilho !== $label2){
                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
              }
            }
            $strxml .= "</menu>";
          }
          $strxml .= "</menuitem>";

        }else{
          if($cbMenuPai !== $label1){

            $strxml .= "<menuitem label='$label1'>";
            $strxml .= "<icon>".$icon1."</icon>";
            if(!empty($tag->action)){
              $strxml .= "<action>".$tag->action."</action>";
            }

            $menus = $tag->menu;
            if(!empty($menus)){
              $strxml .= "<menu>";
              foreach($menus->menuitem as $menu){
                $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";

                $label2 = $menu->attributes()['label']."";
                $icon2 = $menu->icon."";
                $action2 = $menu->action."";

                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
              }
              $strxml .= "</menu>";
            }
            $strxml .= "</menuitem>";
          }
        }
      }
      $strxml .= "</menu>";

      $strxml = $this->formatPrettyXML($strxml);

      $fp = fopen('menu.xml', 'w+');
      fwrite($fp, $strxml);
      fclose($fp);

      TCombo::reload('form_Menu', 'cbMenuFilho', $menuitem2);
      $obj = new StdClass;
      // $obj->cbMenuPai = $cbMenuPai;
      // $obj->label = $label;
      // $obj->icon = $icon;
      // $obj->action = $action;
      // $obj->cbMenuFilho = $cbMenuFilho;
      TForm::sendData('form_Menu', $obj);
    }
  }

  /**
   * Salva um novo arquivo menu.xml
   * $param = array () = $param['label'], $param['icon'],$param['action']
   *             $param['cbMenuPai'],$param['cbMenuFilho'] e $param['antesdepois']
   */

  public function onSave($param){

    $label  = $param['label'];
    $icon   = $param['icon'];
    $action = $param['action'];
    $cbMenuPai = $param['cbMenuPai'];
    $cbMenuFilho = $param['cbMenuFilho'];
    $antesdepois = $param['antesdepois'];
    if(!empty($cbMenuPai) || !empty($cbMenuFilho)){
      $link = 'menu.xml';
      $xml = simplexml_load_file($link);

      $strxml = "<menu>";
      $menuitem1 = array();
      $menuitem2 = array();
      $entrou = 0;
      $entrou2 = 0;
      foreach($xml as $tag){
        $menuitem1[$tag->attributes()['label'].""] = $tag->attributes()['label']."";
        $label1 = $tag->attributes()['label']."";
        $icon1 = $tag->icon."";

        if(!empty($cbMenuFilho)){
          $strxml .= "<menuitem label='$label1'>";
          $strxml .= "<icon>".$icon1."</icon>";
          if(!empty($tag->action)){
            $strxml .= "<action>".$tag->action."</action>";
          }

          $menus = $tag->menu;
          if(!empty($menus)){
            $strxml .= "<menu>";
            foreach($menus->menuitem as $menu){
              $label2 = $menu->attributes()['label']."";
              $icon2 = $menu->icon."";
              $action2 = $menu->action."";
              if($antesdepois == "" && $entrou2 == 0){
                if($cbMenuFilho == $label2){
                  $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";
                  $strxml .= "<menuitem label='$label'>";
                  $strxml .= "<icon>".$icon."</icon>";
                  $strxml .= "<action>".$action."</action>";
                  $strxml .= "</menuitem>";
                  $entrou2 = 1;
                }
                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
              }elseif($antesdepois == 1 && $entrou2 == 0){
                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
                if($cbMenuFilho == $label2){
                  $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";
                  $strxml .= "<menuitem label='$label'>";
                  $strxml .= "<icon>".$icon."</icon>";
                  $strxml .= "<action>".$action."</action>";
                  $strxml .= "</menuitem>";
                  $entrou2 = 1;
                }
              }else{
                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
              }
            }
            $strxml .= "</menu>";
          }
          $strxml .= "</menuitem>";

        }else{
          if($antesdepois == "" && $entrou == 0){
            $strxml .= "<menuitem label='$label'>";
            $strxml .= "<icon>".$icon."</icon>";
            if(!empty($action)){
              $strxml .= "<action>".$action."</action>";
            }
            $strxml .= "</menuitem>";
            $entrou = 1;

            $strxml .= "<menuitem label='$label1'>";
            $strxml .= "<icon>".$icon1."</icon>";
            if(!empty($tag->action)){
              $strxml .= "<action>".$tag->action."</action>";
            }

            $menus = $tag->menu;
            if(!empty($menus)){
              $strxml .= "<menu>";
              foreach($menus->menuitem as $menu){
                $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";

                $label2 = $menu->attributes()['label']."";
                $icon2 = $menu->icon."";
                $action2 = $menu->action."";

                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
              }
              $strxml .= "</menu>";
            }
            $strxml .= "</menuitem>";
          }elseif($antesdepois == 1 && $entrou == 0){
            $strxml .= "<menuitem label='$label1'>";
            $strxml .= "<icon>".$icon1."</icon>";
            if(!empty($tag->action)){
              $strxml .= "<action>".$tag->action."</action>";
            }

            $menus = $tag->menu;
            if(!empty($menus)){
              $strxml .= "<menu>";
              foreach($menus->menuitem as $menu){
                $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";

                $label2 = $menu->attributes()['label']."";
                $icon2 = $menu->icon."";
                $action2 = $menu->action."";

                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
              }
              $strxml .= "</menu>";
            }
            $strxml .= "</menuitem>";

            $strxml .= "<menuitem label='$label'>";
            $strxml .= "<icon>".$icon."</icon>";
            if(!empty($action)){
              $strxml .= "<action>".$action."</action>";
            }
            $strxml .= "</menuitem>";
            $entrou = 1;
          }else{
            $strxml .= "<menuitem label='$label1'>";
            $strxml .= "<icon>".$icon1."</icon>";
            if(!empty($tag->action)){
              $strxml .= "<action>".$tag->action."</action>";
            }

            $menus = $tag->menu;
            if(!empty($menus)){
              $strxml .= "<menu>";
              foreach($menus->menuitem as $menu){
                $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";

                $label2 = $menu->attributes()['label']."";
                $icon2 = $menu->icon."";
                $action2 = $menu->action."";

                $strxml .= "<menuitem label='$label2'>";
                $strxml .= "<icon>".$icon2."</icon>";
                $strxml .= "<action>".$action2."</action>";
                $strxml .= "</menuitem>";
              }
              $strxml .= "</menu>";
            }
            $strxml .= "</menuitem>";

          }
        }
      }
      $strxml .= "</menu>";

      $strxml = $this->formatPrettyXML($strxml);

      $fp = fopen('menu.xml', 'w+');
      fwrite($fp, $strxml);
      fclose($fp);

      TCombo::reload('form_Menu', 'cbMenuFilho', $menuitem2);
      $obj = new StdClass;
      $obj->cbMenuPai = $cbMenuPai;
      $obj->label = $label;
      $obj->icon = $icon;
      $obj->action = $action;
      $obj->cbMenuFilho = $cbMenuFilho;
      TForm::sendData('form_Menu', $obj);
    }
  }

  /**
   * Carrega combo cbMenuFilho
   * $param = array () = $param['cbMenuPai']
   */
  public function onChagecbMenuPai($param){
    $opcao = $param['cbMenuPai'];
    $link = 'menu.xml';

    $xml = simplexml_load_file($link);

    $menuitem1 = array();
    $menuitem2 = array();
    $menuitem2[] = "";
    foreach($xml as $tag){
      $menuitem1[$tag->attributes()['label'].""] = $tag->attributes()['label']."";
      $label1 = $tag->attributes()['label'];
      if($label1 == $opcao){
        $icon1 = $tag->icon;

        $menus = $tag->menu;
        foreach($menus->menuitem as $menu){
          $menuitem2[$menu->attributes()['label'].""] = $menu->attributes()['label']."";

          $label2 = $menu->attributes()['label'];
          $icon2 = $menu->icon;
          $action2 = $menu->action;
        }
      }
    }
    TCombo::reload('form_Menu', 'cbMenuFilho', $menuitem2);
    $obj = new StdClass;
    $obj->cbMenuPai = $opcao;
    $obj->label = $param['label'];
    $obj->icon = $param['icon'];
    $obj->action = $param['action'];
    TForm::sendData('form_Menu', $obj);
  }

  /**
   * Formata o XML para ficar no padrão Adianti Framework
   * @param string $xmlstring
   * @return string
   */
  public function formatPrettyXML($xmlstring){

    $xml = new SimpleXMLElement($xmlstring);
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $xml = new SimpleXMLElement($dom->saveXML());
    $xml = $xml->asXML();
    $xml = html_entity_decode($xml, ENT_NOQUOTES, 'UTF-8');

    return $xml;

  }

}
