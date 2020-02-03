<?php 
//**** Grid Modules ***/
$document = JFactory::getDocument();

$background_color = $this->params->get('background-color');
$menu_color = $this->params->get('menu-color');


$tot_content = 100;
$sidebar_1cl = 27;
$sidebar_2cl = 20;
$css_string = "";
$sidebar = 0;
$numb_sidebar = 0;
if($this->countModules('sidebar-left') xor $this->countModules('sidebar-right')) {	
	$sidebar = $sidebar_1cl;
	$numb_sidebar = 1;	
	if($this->countModules('sidebar-left')) {
		$css_string .="#main {left:".$sidebar."%;}";
	}
} elseif(($this->countModules('sidebar-left'))  && ($this->countModules('sidebar-right'))) {
	$sidebar = $sidebar_2cl;
	$numb_sidebar = 2;
	$css_string .="#sidebar-right, #main {left:".$sidebar."%;}";
}
$mainbody = $tot_content - ($sidebar * $numb_sidebar);
$sidebar_left =  $tot_content - $sidebar;
$css_string .="#main {width:".$mainbody."%;} 
.sidebar {width:".$sidebar."%} 
#sidebar-left {left:-".$sidebar_left."%}";

//**** XML -> TEXT HEADING SIZE ***/
$h_textsize = $this->params->get('headertxt');
$b_textsize = $this->params->get('bodytxt');
$css_string .= ".moduletable > h3, #cnt-container .e4j-menusubtitle, .module .e4j-titlesplit .e4j-menutitle {font-size:".$h_textsize.";}";
$css_string .= "#cnt-container .e4j-menusubtitle, .module .e4j-titlesplit .e4j-menutitle {font-size:".$h_textsize.";}";
$css_string .= "body {font-size:".$b_textsize.";}";
$css_string .= "
#headt-part .upmenu-content h3 .e4j-menutitle, #headt-part .upmenu-content ul li > span, 
#headt-part .moduletable_menu > ul > li > a, #headt-part .moduletable_menu > ul > li > span, 
#headt-part #mainmenu .moduletable > ul > li > a, #headt-part #mainmenu .moduletable > ul > li > span, 
#headt-part #mainmenu .moduletable_menu > ul > li > span, #headt-part #mainmenu .moduletable h3, #headt-part .nav-devices-list .moduletable > ul > li > a, 
#headt-part .nav-devices-list .moduletable > ul > li > span, #headt-part .nav-devices-list .moduletable > h3 { color:".$menu_color.";}
body {
	background: ".$background_color.";
}";

$document->addStyleDeclaration($css_string);

?>

<script>
document.addEventListener('DOMContentLoaded', function(){
  /*** Contant Tabs Fix problem ***/

  var contactBodyTab = 'accordion-body';
  var tabHeader = 'accordion-heading';
  var numbHeadTab = document.getElementsByClassName(tabHeader);

  for(var i = 0; i < numbHeadTab.length; i++) {
    numbHeadTab[i].addEventListener('click', fixClass);

  }

  function fixClass(evento) {
    deactivateAllClasses();

    document.getElementsByClassName(contactBodyTab).className = contactBodyTab + ' in collapse';

    function deactivateAllClasses() {
      var numbContBodyTabs = document.getElementsByClassName(contactBodyTab);

      for(var i = 0; i < numbContBodyTabs.length; i++) {
        numbContBodyTabs[i].className = contactBodyTab + ' collapse';
      }
    }
  }
});
</script>
