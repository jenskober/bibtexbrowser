<?php
function JensBibliographyStyle($bibentry) {
  $type = $bibentry->getType();

  $entry=array();

  // author
  if ($bibentry->hasField('author')) {
    $entry[] = '<span class="bibauthor">'.$bibentry->getFormattedAuthorsString().'</span>.';
  }

  // title
  if ($type!="inbook") {
    $title = '<span class="bibtitle">'.$bibentry->getTitle().'</span>';
    $lastChar = mb_substr($bibentry->getTitle(),-1);
    if ($lastChar!="." && $lastChar!="!" && $lastChar!="?") $title .= ".";
    $entry[] = $title;
  }


  // origin of the publication
  $booktitle = '';

  if (($type=="misc") && $bibentry->hasField("note")) {
    $booktitle = $bibentry->getField("note");
  }

  if ($type=="inproceedings" && $bibentry->hasField(BOOKTITLE)) {
      $booktitle = 'In '.'<span class="bibbooktitle">'.$bibentry->getField(BOOKTITLE).'</span>';
  }

  if ($type=="incollection" && $bibentry->hasField(BOOKTITLE)) {
      $booktitle = 'In <span class="bibbooktitle">'.$bibentry->getField(BOOKTITLE).'</span>';
  }
  
  if ($type=="article" && $bibentry->hasField("journal")) {
      $booktitle = '<span class="bibbooktitle">'.$bibentry->getField("journal").'</span>';
  }
  
  // INBOOK (book title)
  if ($type=="inbook") {
    $booktitle = '<span class="bibbooktitle">'.$bibentry->getTitle().'</span>';
  }

  //// ******* EDITOR
  $editor='';
  if ($bibentry->hasField(EDITOR)) {
    $booktitle .=' ('.$bibentry->getFormattedEditors().')';
  }
  
  // INBOOK (book title)
  if ($type=="inbook") {
    if ($bibentry->hasField("chapter")) {
      $lastChar = mb_substr($bibentry->getTitle(),-1);
      if ($lastChar!="." && $lastChar!="!" && $lastChar!="?") $booktitle .= ",";
      $booktitle .= ' chapter <span class="bibtitle">'.$bibentry->getField('chapter').'</span>';
    }
  }

  if ($booktitle!='') {
    $lastChar = mb_substr($bibentry->getTitle(),-1);
    if ($lastChar!="." && $lastChar!="!" && $lastChar!="?") $booktitle .= ",";
    $entry[] = $booktitle;
  }

  $publisher='';
  if ($type=="phdthesis") {
      $publisher = 'PhD thesis, '.$bibentry->getField(SCHOOL);
  }

  if ($type=="mastersthesis") {
      $publisher = 'Master\'s thesis, '.$bibentry->getField(SCHOOL);
  }
  if ($type=="techreport") {
      $publisher = 'Technical report, ';
      $publisher .=$bibentry->getField("institution");
      if ($bibentry->hasField("number")) {
        $publisher .= ' '.$bibentry->getField("number");
      }
  }
  if ($bibentry->hasField("publisher")) {
    $publisher = $bibentry->getField("publisher");
  }

  if ($publisher!='') $entry[] = $publisher.',';

  if ($type=="article") {
    $volNoPages='';
    if ($bibentry->hasField('volume')) $volNoPages = $bibentry->getField("volume");
    if ($bibentry->hasField('number')) $volNoPages .= '('.$bibentry->getField("number").')';
    if ($bibentry->hasField('pages')) $volNoPages .= ':'.str_replace("--", "-", $bibentry->getField("pages"));
    if ($volNoPages!='') $entry[] = $volNoPages.',';
  }
  
  if ($bibentry->hasField('series')) {
    $series='';
    if ($bibentry->hasField('volume')) $series = "vol. ".$bibentry->getField("volume")." of ";
    $series .=$bibentry->getField("series");
    $entry[] = $series.',';
  }

  if ($bibentry->hasField('address')) $entry[] =  $bibentry->getField("address").',';

  if ($type!="article" && $bibentry->hasField('pages')) $entry[] = str_replace("--", "-", "pp. ".$bibentry->getField("pages")).',';

  if ($bibentry->hasField(YEAR)) $entry[] = $bibentry->getYear().'.';
  
  if (($type!="misc") && $bibentry->hasField("note")) {
    $entry[] = '<span class="bibnote">'.$bibentry->getField("note").'</span>.';
  }

  $result = implode(" ",$entry);

  // add the Coin URL
  $result .=  "\n".$bibentry->toCoins();

  return '<span itemscope="" itemtype="http://schema.org/ScholarlyArticle">'.$result.'</span>';
}

/** ^^adds a touch of AJAX in bibtexbrowser to display bibtex entries inline.
   It uses the HIJAX design pattern: the Javascript code fetches the normal bibtex HTML page
   and extracts the bibtex.
   In other terms, URLs and content are left perfectly optimized for crawlers
   Note how beautiful is this piece of code thanks to JQuery.^^
  */
function javascriptJens() {
  // we use jquery with the official content delivery URLs
  // Microsoft and Google also provide jquery with their content delivery networks
?><script type="text/javascript" src="<?php echo JQUERY_URI ?>"></script>
<script type="text/javascript" ><!--
// Javascript progressive enhancement for bibtexbrowser
$('a.biburl').each(function() { // for each url "[bibtex]"
  var biburl = $(this);
  if (biburl.attr('bibtexbrowser') === undefined)
  {
  biburl.click(function(ev) { // we change the click semantics
    ev.preventDefault(); // no open url
    if (biburl.nextAll('pre').length == 0) { // we don't have yet the bibtex data
      var bibtexEntryUrl = $(this).attr('href');
      $.ajax({url: bibtexEntryUrl,  dataType: 'html', success: function (data) { // we download it
        // elem is the element containing bibtex entry, creating a new element is required for Chrome and IE
        var elem = $('<pre class="purebibtex"/>');
        elem.text($('.purebibtex', data).text()); // both text() are required for IE
        elem.appendTo(biburl.parent());
      }, error: function() {window.location.href = biburl.attr('href');}});
    } else {biburl.nextAll('pre').toggle();}  // we toggle the view
  });
  biburl.attr('bibtexbrowser','done');
  } // end if biburl.bibtexbrowser;
});


--></script><?php
} // end function javascript

function echoSortedParagraph($query, $db) {
  $entries=$db->multisearch($query);
  uasort($entries, 'compare_bib_entries');

  foreach ($entries as $bibentry) {
    echo "<p>".$bibentry->toHTML()."</p>\n";
  }
}

function echoSortedBreak($query, $db) {
  $entries=$db->multisearch($query);
  uasort($entries, 'compare_bib_entries');

  foreach ($entries as $bibentry) {
    echo $bibentry->toHTML()."<br />\n";
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////

  define('BIBLIOGRAPHYSTYLE','JensBibliographyStyle');
  define('BIBTEXBROWSER_LINKS_TARGET','_blank');
  define('BIBTEXBROWSER_DOCUMENT_LINKS',true);
  define('BIBTEXBROWSER_CODE_LINKS',true);
  define('BIBTEXBROWSER_VIDEO_LINKS',true);
  define('ORDER_FUNCTION_FINE','compare_bib_entry_by_name');
  define('USE_OXFORD_COMMA',true);
  define('USE_FIRST_THEN_LAST',true);
  @define('REVERSE_SORT',true);
?>