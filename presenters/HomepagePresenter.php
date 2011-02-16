<?php

use Nette\Application\AppForm;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter {
   
   
   public function createComponentPickFolder() {
      $f = new AppForm($this, 'pickFolder');
         
      $f->addText('path', 'Zadej cestu ke složce s ikonami, relativně k WWW_DIR', 80)
         ->addRule(AppForm::FILLED, 'Zadej cestu');
      
      $f->addText('outpath', 'Zadej cestu výstupu (včetně názvu souboru a přípony)', 80)
         ->addRule(AppForm::FILLED, 'Zadej cestu');
      
      $f->addSubmit('go', 'Pracuj!');
      
      $f['go']->onClick[] = callback($this, 'makeSprite');
      
      return $f;
   }
   
   
   public function makeSprite(\Nette\Forms\SubmitButton $btn) {
      $f = $btn->getForm();
      $v = $f->getValues();
      
      // absolutni cesta vstupu
      $path = $v['path'];
      if (strpos($path, DIRECTORY_SEPARATOR) !== 0)
         $path = WWW_DIR . DIRECTORY_SEPARATOR . $path;
      else
         $path = WWW_DIR . $path;            
      
      // absolutni cesta vystupu
      $outpath = $v['outpath'];
      if (strpos($outpath, DIRECTORY_SEPARATOR) !== 0)
         $outpath = WWW_DIR . DIRECTORY_SEPARATOR . $outpath;
      else
         $outpath = WWW_DIR . $outpath;
      
      // cesta k vystupu relativne ke koreni webu - pro css vystup
      $relativeOutpath = str_replace(WWW_DIR, '', $outpath);
      $relativeOutpath = str_replace(DIRECTORY_SEPARATOR, '/', $relativeOutpath);
      
      // najit vsechny obrazky - png
      $files = Nette\Finder::findFiles('*.png')->from($path);
      
      // chybi Finder->__toArray()
      $filesArray = array();
      foreach ($files as $file) {
         $filesArray[] = (string)$file;
      }
          
      $cnt = count($filesArray);      
      
      // z prvniho obrazku nacist rozmery
      list($width, $height) = getimagesize($filesArray[0]);    
      $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 127); 
      $image = \Nette\Image::fromBlank($width, $height * $cnt, $transparencyColor);      
      
      // pozice kam se vklada obrazek
      $pos = 0;
      
      // vystupni css
      $css = '.icon { width:' . $width . 'px; height:' . $height . 'px }' . "\n";
      
      // zkusebni div pro otestovani funkcnosti
      $test = '';
      
      // seskladat
      foreach ($filesArray as $file) {
         $tmpImage = \Nette\Image::fromFile($file);
         $image->place($tmpImage, 0, $pos);
         
         $info = pathinfo($file);
         
         $css .= '.icon.' . Nette\String::webalize($info['filename']) . ' { background-image:url(\'' . $relativeOutpath . '\'); background-position:0 -' . $pos . 'px; }' . "\n";
         $pos += $height;
         
         $test .= '<div title="'.$info['filename'].'" class="test icon ' . Nette\String::webalize($info['filename']) . '"></div>';
      }
      
      imagesavealpha($image->getImageResource(), TRUE);
      $image->save($outpath);
      
      $this->template->done = TRUE;
      $this->template->css = $css;      
      $this->template->test = $test;
   }

}
