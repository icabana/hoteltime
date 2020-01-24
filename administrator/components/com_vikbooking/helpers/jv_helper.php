<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

if (!class_exists('VboApplication')) {
	class VboApplication {
		
		/* CMS */
		public $cms = 'joomla';

		/* Framework version */
		public $jv = 3;

		/* Additional commands container for any methods */
		private $commands;
		
		function __construct($force_version = 0)
		{
			$version = new JVersion();
			$v = $version->getShortVersion();
			if (!empty($force_version)) {
				$v = $force_version;
			}
			if (version_compare($v, '1.5.0') >= 0 && version_compare($v, '1.6.0') < 0) {
				//any Joomla 1.5
				$this->jv = 1;
			} elseif (version_compare($v, '1.6.0') >= 0 && version_compare($v, '3.0') < 0) {
				//any Joomla from 1.6 to 2.5
				$this->jv = 2;
			} elseif (version_compare($v, '3.0') >= 0 && version_compare($v, '4.0') < 0) {
				//any Joomla 3.x
				$this->jv = 3;
			} elseif (version_compare($v, '4.0') >= 0) {
				//any Joomla 4.x
				$this->jv = 4;
			} else {
				die('UNSUPPORTED JOOMLA VERSION '.$v);
			}
			$this->commands = array();
		}

		/**
		 * This method loads an additional CSS file (if available)
		 * for the current CMS, and CMS version.
		 *
		 * @return void
		 **/
		public function normalizeBackendStyles()
		{
			if (file_exists(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $this->cms.'_'.$this->jv.'.css')) {
				JFactory::getDocument()->addStyleSheet(VBO_ADMIN_URI . 'helpers/' . $this->cms.'_'.$this->jv.'.css');
			}
		}

		/**
		* Sets additional commands for any methods. Like raise an error if the recipient email address is empty.
		* Returns this object for chainability.
		*/
		public function setCommand($key, $value)
		{
			if (!empty($key)) {
				$this->commands[$key] = $value;
			}
			return $this;
		}

		public function getAdminTableClass()
		{
			if ($this->jv < 3) {
				// 2.5
		 		return "adminlist";
			} else {
		  		// 3.x
		 		return "table table-striped";
		 	}
		}
		
		public function openTableHead()
		{
			if ($this->jv < 3) {
				// 2.5
		 		return "";
			} else {
		  		// 3.x
		 		return "<thead>";
		 	}
		}
		
		public function closeTableHead()
		{
			if ($this->jv < 3) {
				// 2.5
		 		return "";
			} else {
		  		// 3.x
		 		return "</thead>";
		 	}
		}
		
		public function getAdminThClass($h_align='center')
		{
			if ($this->jv < 3) {
				// 2.5
		 		return 'title';
			} else {
		  		// 3.x
		 		return 'title ' . $h_align;
		 	}
		}
		
		public function getAdminToggle($count)
		{
			if ($this->jv < 3) {
				// 2.5
		 		return '<input type="checkbox" name="toggle" value="" onclick="checkAll('.$count.');" />';
			} else {
		  		// 3.x
		 		return '<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle" />';
		 	}
		}
		
		public function checkboxOnClick($js_arg = 'this.checked')
		{
			if ($this->jv < 3) {
				// 2.5
		 		return 'isChecked('.$js_arg.');';
			} else {
		  		// 3.x
		 		return 'Joomla.isChecked('.$js_arg.');';
		 	}
		}
		
		public function sendMail($from_address, $from_name, $to, $reply_address, $subject, $hmess, $is_html=true, $encoding='base64', $attachment=null)
		{
			if (strpos($to, ',') !== false) {
				$all_recipients = explode(',', $to);
				foreach ($all_recipients as $k => $v) {
					if (empty($v)) {
						unset($all_recipients[$k]);
					}
				}
				if (count($all_recipients) > 0) {
					$to = $all_recipients;
				}
			}
			if ($this->jv < 3) {
				// 2.5
				JUtility::sendMail($from_address, $fromname, $to, $subject, $hmess, $is_html, null, null, $attachment, $reply_address, $from_name);
			} else {
				if (empty($to)) {
					//Prevent Joomla Exceptions that would stop the script execution
					if (isset($this->commands['print_errors'])) {
						VikError::raiseWarning('', 'The recipient email address is empty. Email message could not be sent. Please check your configuration.');
					}
					return false;
				}
				// 3.x
				if ($from_name == $from_address) {
					$mainframe = JFactory::getApplication();
					$attempt_fromn = $mainframe->get('fromname', '');
					if (!empty($attempt_fromn)) {
						$from_name = $attempt_fromn;
					}
				}
				$mailer = JFactory::getMailer();
				$sender = array($from_address, $from_name);
				$mailer->setSender($sender);
				$mailer->addRecipient($to);
				$mailer->addReplyTo($reply_address);
				if ($attachment !== null && !empty($attachment)) {
					if (is_array($attachment)) {
						foreach ($attachment as $path_attach) {
							if (!empty($path_attach)) {
								$mailer->addAttachment($path_attach);
							}
						}
					} else {
						$mailer->addAttachment($attachment);
					}
				}
				$mailer->setSubject($subject);
				$mailer->setBody($hmess);
				$mailer->isHTML($is_html);
				$mailer->Encoding = $encoding;
				$mailer->Send();
			}
		}
		
		public function addScript($path='', $arg1=false, $arg2=true, $arg3=false, $arg4=false)
		{
			if (empty($path) ) return; 
			
			if ($this->jv < 3) {
		 		$doc = JFactory::getDocument();
				$doc->addScript($path);
			} else {
		  		JHtml::_( 'script', $path, $arg1, $arg2, $arg3, $arg4 );
		 	}
		}
		
		public function emailToPunycode($email='')
		{
			if ($this->jv < 3) {
		 		// 2.5
		 		return $email;
			} else {
				// 3.x
		  		return JStringPunycode::emailToPunycode($email);
			}
		}

		public function printYesNoButtons($name, $label_yes, $label_no, $cur_value = '1', $yes_value = '1', $no_value = '0', $id_yes = '', $id_no = '', $oldjv_inp_type = 'checkbox')
		{
			$html = '';
			$id_yes = empty($id_yes) ? $name.'-on' : $id_yes;
			$id_no = empty($id_no) ? $name.'-off' : $id_no;
			if ($this->jv < 3) {
				//Joomla 2.5
				if ($oldjv_inp_type == 'checkbox') {
					$html = '<input type="checkbox" id="'.$name.'-field" value="'.$yes_value.'" name="'.$name.'"'.($cur_value === $yes_value ? ' checked="checked"' : '').'>';
				} else {
					//radio buttons
					$html = '<input type="radio" id="'.$id_yes.'" value="'.$yes_value.'" name="'.$name.'" class="btn-group"'.($cur_value === $yes_value ? ' checked="checked"' : '').'>
			<label style="display: inline-block; margin: 0;" for="'.$id_yes.'">'.$label_yes.'</label>&nbsp;&nbsp;
			<input type="radio" id="'.$id_no.'" value="'.$no_value.'" name="'.$name.'" class="btn-group"'.($cur_value === $no_value ? ' checked="checked"' : '').'>
			<label style="display: inline-block; margin: 0;" for="'.$id_no.'">'.$label_no.'</label>';
				}
			} elseif ($this->jv < 4) {
				//Joomla 3.x
				$html = '<div class="controls">
		<fieldset class="radio btn-group btn-group-yesno">
			<input type="radio" id="'.$id_yes.'" value="'.$yes_value.'" name="'.$name.'" class="btn-group"'.($cur_value === $yes_value ? ' checked="checked"' : '').'>
			<label style="display: inline-block; margin: 0;" for="'.$id_yes.'">'.$label_yes.'</label>
			<input type="radio" id="'.$id_no.'" value="'.$no_value.'" name="'.$name.'" class="btn-group"'.($cur_value === $no_value ? ' checked="checked"' : '').'>
			<label style="display: inline-block; margin: 0;" for="'.$id_no.'">'.$label_no.'</label>
		</fieldset>
	</div>';
			} elseif ($this->jv >= 4) {
				//Joomla 4.x
				JHtml::_('script', 'system/fields/switcher.js', array('version' => 'auto', 'relative' => true));
				$html = '<div class="controls">
		<fieldset id="'.$name.'">
			<span class="js-switcher switcher'.($cur_value === $yes_value ? ' active' : '').'">
				<input type="radio" id="'.$name.$no_value.'" value="'.$no_value.'" name="'.$name.'" class="active"'.($cur_value === $no_value ? ' checked="checked"' : '').'>
				<input type="radio" id="'.$name.$yes_value.'" value="'.$yes_value.'" name="'.$name.'"'.($cur_value === $yes_value ? ' checked="checked"' : '').'>
				<span class="switch"></span>
			</span>
			<span class="switcher-labels">
				<span class="switcher-label-'.$no_value.($cur_value === $no_value ? ' active' : '').'">'.JText::_('VBNO').'</span>
				<span class="switcher-label-'.$yes_value.($cur_value === $yes_value ? ' active' : '').'">'.JText::_('VBYES').'</span>
			</span>
		</fieldset>
	</div>';
			}

			return $html;
		}

		/**
		* @param $arr_values array
		* @param $current_key string
		* @param $empty_value string (J3.x only)
		* @param $default
		* @param $input_name string
		* @param $record_id = '' string
		*/
		public function getDropDown($arr_values, $current_key, $empty_value, $default, $input_name, $record_id = '')
		{
			$dropdown = '';
			$x = empty($record_id) ? rand(1, 999) : $record_id;
			if (defined('JVERSION') && version_compare(JVERSION, '2.6.0') < 0) {
				//Joomla 2.5
				$dropdown .= '<select name="'.$input_name.'" onchange="document.adminForm.submit();">'."\n";
				$dropdown .= '<option value="">'.$default.'</option>'."\n";
				$list = "\n";
				foreach ($arr_values as $k => $v) {
					$dropdown .= '<option value="'.$k.'"'.($k == $current_key ? ' selected="selected"' : '').'>'.$v.'</option>'."\n";
				}
				$dropdown .= '</select>'."\n";
			} else {
				//Joomla 3.x
				$dropdown .= '<script type="text/javascript">'."\n";
				$dropdown .= 'function dropDownChange'.$x.'(setval) {'."\n";
				$dropdown .= '	document.getElementById("dropdownval'.$x.'").value = setval;'."\n";
				$dropdown .= '	document.adminForm.submit();'."\n";
				$dropdown .= '}'."\n";
				$dropdown .= '</script>'."\n";
				$dropdown .= '<input type="hidden" name="'.$input_name.'" value="'.$current_key.'" id="dropdownval'.$x.'"/>'."\n";
				$list = "\n";
				foreach ($arr_values as $k => $v) {
					if ($k == $current_key) {
						$default = $v;
					}
					$list .= '<li><a href="javascript: void(0);" onclick="dropDownChange'.$x.'(\''.$k.'\');">'.$v.'</a></li>'."\n";
				}
				$list .= '<li class="divider"></li>'."\n".'<li><a href="javascript: void(0);" onclick="dropDownChange'.$x.'(\'\');">'.$empty_value.'</a></li>'."\n";
				$dropdown .= '<div class="btn-group">
			<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">'.$default.' <span class="caret"></span></button>
			<ul class="dropdown-menu" role="menu">'.
				$list.
			'</ul>
		</div>';
			}

			return $dropdown;
		}

		public function loadSelect2()
		{
			//load JS + CSS
			$document = JFactory::getDocument();
			$document->addStyleSheet(VBO_ADMIN_URI.'resources/select2.min.css');
			$this->addScript(VBO_ADMIN_URI.'resources/select2.min.js');
		}

		/**
		 * Returns the HTML code to render a regular dropdown
		 * menu styled through the jQuery plugin Select2.
		 *
		 * @param 	$arr_values 	array
		 * @param 	$current_key 	string
		 * @param 	$input_name 	string
		 * @param 	$placeholder 	string 		used when the select has no selected option (it's empty)
		 * @param 	$empty_name 	[string] 	the name of the option to set an empty value to the field (<option>$empty_name</option>)
		 * @param 	$empty_val 		[string]	the value of the option to set an empty value to the field (<option>$empty_val</option>)
		 * @param 	$onchange 		[string] 	javascript code for the onchange attribute
		 * @param 	$idattr 		[string] 	the identifier attribute of the select
		 *
		 * @return 	string
		 */
		public function getNiceSelect($arr_values, $current_key, $input_name, $placeholder, $empty_name = '', $empty_val = '', $onchange = 'document.adminForm.submit();', $idattr = '')
		{
			//load JS + CSS
			$this->loadSelect2();

			//attribute
			$idattr = empty($idattr) ? rand(1, 999) : $idattr;

			//select
			$dropdown = '<select id="'.$idattr.'" name="'.$input_name.'"'.(!empty($onchange) ? ' onchange="'.$onchange.'"' : '').'>'."\n";
			if (!empty($placeholder) && empty($current_key)) {
				//in order for the placeholder value to appear, there must be a blank <option> as the first option in the select
				$dropdown .= '<option></option>'."\n";
			} else {
				//unset the placeholder to not pass it to the select2 object, or the empty value will not be displayed
				$placeholder = '';
			}
			if (strlen($empty_name) || strlen($empty_val)) {
				$dropdown .= '<option value="'.$empty_val.'">'.$empty_name.'</option>'."\n";
			}
			foreach ($arr_values as $k => $v) {
				$dropdown .= '<option value="'.$k.'"'.($k == $current_key ? ' selected="selected"' : '').'>'.$v.'</option>'."\n";
			}
			$dropdown .= '</select>'."\n";

			//js code
			$dropdown .= '<script type="text/javascript">'."\n";
			$dropdown .= 'jQuery(document).ready(function() {'."\n";
			$dropdown .= '	jQuery("#'.$idattr.'").select2('.(!empty($placeholder) ? '{placeholder: "'.addslashes($placeholder).'"}' : '').');'."\n";
			$dropdown .= '});'."\n";
			$dropdown .= '</script>'."\n";

			return $dropdown;
		}

		/**
		 * Returns the Script tag to render the Bootstrap JModal window.
		 * The suffix can be passed to generate other JS functions.
		 * Optionally pass JavaScript code for the 'show' and 'hide' events.
		 * Only compatible with Joomla > 3.x. jQuery must be defined.
		 *
		 * @param 	$suffix 	string
		 * @param 	$hide_js 	string
		 * @param 	$show_js 	string
		 *
		 * @return 	string
		 */
		public function getJmodalScript ($suffix = '', $hide_js = '', $show_js = '')
		{
			$show_evname = 'show';
			$hide_evname = 'hide';
			$onshow_js = "
			var modal_win_width = jQuery(this).width();
			var modal_win_height = jQuery(this).height();
			if (jQuery(this).find('iframe').length) {
				jQuery(this).find('iframe').width(modal_win_width).height((modal_win_height - 100));
			}
			";
			if ($this->jv >= 4) {
				//BS4 has different event names and requirements
				$show_evname = 'shown.bs.modal';
				$hide_evname = 'hidden.bs.modal';
				$onshow_js = '';
			}

			$hide_ev = '';
			if (!empty($hide_js)) {
				$hide_ev = "\n\t\t\t";
				$hide_ev .= "jQuery('.vbo-jmodal').on('$hide_evname', function() {
				".$hide_js."
			});";
				$hide_ev .= "\n";
			}
			
			return "\t\t<script type=\"text/javascript\">
		function vboFocusModal(id) {
			var viframe = jQuery('#jmodal-box-' + id).find('iframe');
			if (viframe.length) {
				viframe.focus();
			}
		}
		function vboOpenJModal$suffix(id, modal_url, new_title) {
			if (modal_url) {
				if (jQuery('#jmodal-box-' + id).find('iframe').length) {
					jQuery('#jmodal-box-' + id).find('iframe').attr('src', modal_url);
				} else {
					jQuery('<iframe class=\'iframe\' src=\''+modal_url+'\'></iframe>').appendTo('#jmodal-box-' + id + ' .modal-body');
				}
				setTimeout(vboFocusModal.bind(id), 500);
			}
			if (new_title) {
				jQuery('#jmodal-title-' + id).text(new_title);
			}
			jQuery('#jmodal-' + id).modal('show');
			return false;
		}
		jQuery(document).ready(function() {
			jQuery('.vbo-jmodal').on('$show_evname', function() {
				$onshow_js
				$show_js
			});$hide_ev
		});
		</script>\n";

		}

		/**
		 * Returns the HTML code to render the Bootstrap JModal window.
		 * The $body HTML code should be passed when no iFrame is needed.
		 *
		 * @param 	$id 		string
		 * @param 	$title 		string
		 * @param 	$body 		string
		 * @param 	$style 		string
		 *
		 * @return 	string
		 */
		public function getJmodalHtml ($id, $title, $body = '', $style = '')
		{
			if ($this->jv < 4) {
				$style = empty($style) ? 'width: 98%; height: 94%; margin-left: -49%; top: 3% !important;' : $style;

				return "\t\t".'<div class="modal vbo-jmodal hide fade" id="jmodal-'.$id.'" style="'.$style.'">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h3 id="jmodal-title-'.$id.'">'.$title.'</h3>
					</div>
					<div id="jmodal-box-'.$id.'">
						<div class="modal-body">
							'.$body.'
						</div>
					</div>
				</div>'."\n";
			} else {
				//J4
				$style = 'display: none;'; //no inline styles because we use the class jviewport-width80
				return "\t\t".'<div class="joomla-modal vbo-jmodal modal fade" id="jmodal-'.$id.'" role="dialog" tabindex="-1" style="'.$style.'">
					<div class="modal-dialog modal-lg jviewport-width80" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h3 id="jmodal-title-'.$id.'" class="modal-title">'.$title.'</h3>
								<button type="button" class="close novalidate" data-dismiss="modal">&times;</button>
							</div>
							<div id="jmodal-box-'.$id.'">
								<div class="modal-body jviewport-height80" style="max-height: initial; overflow-y: initial;">
									'.$body.'
								</div>
							</div>
						</div>
					</div>
				</div>'."\n";
			}
		}

		/**
		 * Add javascript support for Bootstrap popovers.
		 *
		 * @param 	string 	$selector   Selector for the popover.
		 * @param 	array 	$options    An array of options for the popover.
		 * 					Options for the popover can be:
		 * 						animation  boolean          apply a css fade transition to the popover
		 *                      html       boolean          Insert HTML into the popover. If false, jQuery's text method will be used to insert
		 *                                                  content into the dom.
		 *                      placement  string|function  how to position the popover - top | bottom | left | right
		 *                      selector   string           If a selector is provided, popover objects will be delegated to the specified targets.
		 *                      trigger    string           how popover is triggered - hover | focus | manual
		 *                      title      string|function  default title value if `title` tag isn't present
		 *                      content    string|function  default content value if `data-content` attribute isn't present
		 *                      delay      number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
		 *                                                  If a number is supplied, delay is applied to both hide/show
		 *                                                  Object structure is: delay: { show: 500, hide: 100 }
		 *                      container  string|boolean   Appends the popover to a specific element: { container: 'body' }
		 *
		 * @since 	1.10
		 */
		public function attachPopover($selector = '.vboPopover', $options = array())
		{
			if (defined('JVERSION') && version_compare(JVERSION, '2.6.0') < 0) {
				// 2.5
				JFactory::getDocument()->addStyleDeclaration('jQuery(document).ready(function(){
					jQuery('.$selector.').tooltip();
				}');
			} else {
				// 3.x
				JHtml::_('bootstrap.popover', $selector, $options);
			}
		}

		/**
		 * Create a standard tag and attach a popover event.
		 * NOTE. IcoMoon or FontAwesome framework MUST be loaded in order to render the icon.
		 *
		 * @param 	array 	$options    An array of options for the popover
		 *
		 * @see 	VboApplication::attachPopover() for further details about options keys.
		 *
		 * @since 	1.10
		 */
		public function createPopover($options = array())
		{
			$options['content'] = isset($options['content']) ? $options['content'] : '';
			$options['icon_class'] = isset($options['icon_class']) ? $options['icon_class'] : 'vboicn-question';

			// attach an empty array option so that the data will be recovered 
			// directly from the tag during the runtime
			$this->attachPopover(".vbo-quest-popover", array());

			if (defined('JVERSION') && version_compare(JVERSION, '2.6.0') < 0) {
				// 2.5
				return '<i class="'.$options['icon_class'].' vbo-quest-popover" title="'.$options['content'].'"></i>';
			} else {
				// 3.x
				$attr = '';
				foreach ($options as $k => $v) {
					if ($k == 'icon_class') {
						continue;
					}
					$attr .= 'data-'.$k.'="'.str_replace('"', '&quot;', $v).'" ';
				}

				return '<i class="'.$options['icon_class'].' vbo-quest-popover" '.$attr.'></i>';
			}
		}

		/**
		 * Loads the necessary JS, CSS, Script for the jQuery UI Datepicker.
		 * NOTE: the main VikBooking Class must be defined, and this method is only for the back-end definitions.
		 *
		 * @since 	1.10
		 */
		public function loadDatePicker()
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
			$document->addStyleSheet(VBO_ADMIN_URI.'resources/jquery.highlighttextarea.min.css');
			JHtml::_('jquery.framework', true, true);
			$this->addScript(VBO_SITE_URI.'resources/jquery-ui.min.js');
			$vbo_df = VikBooking::getDateFormat();
			$juidf = $vbo_df == "%d/%m/%Y" ? 'dd/mm/yy' : ($vbo_df == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');
			$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikbooking"] = {'."\n".'
		closeText: "'.JText::_('VBJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VBJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VBJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VBJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VBMONTHONE').'","'.JText::_('VBMONTHTWO').'","'.JText::_('VBMONTHTHREE').'","'.JText::_('VBMONTHFOUR').'","'.JText::_('VBMONTHFIVE').'","'.JText::_('VBMONTHSIX').'","'.JText::_('VBMONTHSEVEN').'","'.JText::_('VBMONTHEIGHT').'","'.JText::_('VBMONTHNINE').'","'.JText::_('VBMONTHTEN').'","'.JText::_('VBMONTHELEVEN').'","'.JText::_('VBMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VBMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VBSUNDAY').'", "'.JText::_('VBMONDAY').'", "'.JText::_('VBTUESDAY').'", "'.JText::_('VBWEDNESDAY').'", "'.JText::_('VBTHURSDAY').'", "'.JText::_('VBFRIDAY').'", "'.JText::_('VBSATURDAY').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VBSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBSATURDAY'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VBSUNDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBMONDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBTUESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBWEDNESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBTHURSDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBFRIDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBSATURDAY'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VBJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikBooking::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikbooking"]);'."\n".'
});';
			$document->addScriptDeclaration($ldecl);
		}

		/**
		 * Loads the CMS's native datepicker calendar.
		 *
		 * @since 	1.10
		 */
		public function getCalendar($val, $name, $id, $df, $attributes = array())
		{
			JHtml::_('behavior.calendar');
			return JHtml::_('calendar', $val, $name, $id, $df, $attributes);
		}

		/**
		 * Returns a masked e-mail address. The e-mail are masked using 
		 * a technique to encode the bytes in hexadecimal representation.
		 * The chunk of the masked e-mail will be also encoded to be HTML readable.
		 *
		 * @param 	string 	 $email 	The e-mail to mask.
		 * @param 	boolean  $reverse 	True to reverse the e-mail address.
		 * 								Only if the e-mail is not contained into an attribute.
		 *
		 * @return 	string 	 The masked e-mail address.
		 */
		public function maskMail($email, $reverse = false)
		{
			if ($reverse)
			{
				// reverse the e-mail address
				$email = strrev($email);
			}

			// converts the e-mail address from bin to hex
			$email = bin2hex($email);
			// append ;&#x sequence after every chunk of the masked e-mail
			$email = chunk_split($email, 2, ";&#x");
			// prepend &#x sequence before the address and trim the ending sequence
			$email = "&#x" . substr($email, 0, -3);

			return $email;
		}

		/**
		 * Returns a safemail tag to avoid the bots spoof a plain address.
		 *
		 * @param 	string 	 $email 	The e-mail address to mask.
		 * @param 	boolean  $mail_to 	True if the address should be wrapped
		 * 								within a "mailto" link.
		 *
		 * @return 	string 	 The HTML tag containing the masked address.
		 *
		 * @uses 	maskMail()
		 */
		public function safeMailTag($email, $mail_to = false)
		{
			// include the CSS declaration to reverse the text contained in the <safemail> tags
			JFactory::getDocument()->addStyleDeclaration('safemail {direction: rtl;unicode-bidi: bidi-override;}');

			// mask the reversed e-mail address
			$masked = $this->maskMail($email, true);

			// include the address into a custom <safemail> tag
			$tag = "<safemail>$masked</safemail>";

			if ($mail_to)
			{
				// mask the address for mailto command (do not use reverse)
				$mailto = $this->maskMail($email);

				// wrap the safemail tag within a mailto link
				$tag = "<a href=\"mailto:$mailto\" class=\"mailto\">$tag</a>";
			}

			return $tag;
		}

		/**
		 * Loads and echoes the script necessary to render the Fancybox
		 * plugin for jQuery to open images or iframes within a modal box.
		 * This resolves conflicts with some Bootstrap or Joomla (4) versions
		 * that do not support the old-native CSS class .modal with "behavior.modal".
		 * Mainly made to open pictures in a modal box, so the default "type" is set to "image".
		 * By passing a custom $opts string, the "type" property could be set to "iframe", but
		 * in this case it's better to use the other method of this class (Jmodal).
		 * The base jQuery library should be already loaded when using this method.
		 *
		 * @param 	string 	 	$selector 	The jQuery selector to trigger Fancybox.
		 * @param 	string  	$opts 		The options object for the Fancybox setup.
		 * @param 	boolean  	$reloadfunc If true, an additional function is included in the script
		 *									to apply again Fancybox to newly added images to the DOM (via Ajax).
		 *
		 * @return 	void
		 *
		 * @uses 	addScript()
		 */
		public function prepareModalBox($selector = '.vbomodal', $opts = '', $reloadfunc = false)
		{
			if (empty($opts)) {
				$opts = '{
					"helpers": {
						"overlay": {
							"locked": false
						}
					},
					"width": "70%",
					"height": "75%",
					"autoScale": true,
					"transitionIn": "none",
					"transitionOut": "none",
					"padding": 0,
					"type": "image"
				}';
			}
			$document = JFactory::getDocument();
			$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
			$this->addScript(VBO_SITE_URI.'resources/jquery.fancybox.js');

			$reloadjs = '
			function reloadFancybox() {
				jQuery("'.$selector.'").fancybox('.$opts.');
			}
			';
			$js = '
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("'.$selector.'").fancybox('.$opts.');
			});'.($reloadfunc ? $reloadjs : '').'
			</script>';

			echo $js;
		}

		/**
		 * Gets an array of all the installed languages.
		 *
		 * @return 	array
		 */
		public function getKnownLanguages()
		{
			return JLanguage::getKnownLanguages();
		}

		/**
		 * Method used to handle the reCAPTCHA events.
		 *
		 * @param 	string 	$event 		The reCAPTCHA event to trigger.
		 * 								Here's the list of the accepted events:
		 * 								- display 	Returns the HTML used to 
		 *											display the reCAPTCHA input.
		 *								- check 	Validates the POST data to make sure
		 * 											the reCAPTCHA input was checked.
		 * @param 	array  	$options 	A configuration array.
		 *
		 * @return 	mixed 	The event response.
		 *
		 * @since 	1.10 - August 2018
		 * @joomlaonly  reCaptcha is only for Joomla
		 */
		public function reCaptcha($event = 'display', array $options = array())
		{
			// import captcha plugins
			JPluginHelper::importPlugin('captcha');
			// obtain global dispatcher
			$dispatcher = JDispatcher::getInstance();
			
			if ($event == 'check')
			{
				// check the reCAPTCHA answer
				$res = $dispatcher->trigger('onCheckAnswer');

				// Filter the responses returned by the plugins.
				// Return true if there is still a successful element within the list.
				return (bool) array_filter($res);
			}
			else if ($event == 'display')
			{
				// show reCAPTCHA input
				$dispatcher->trigger('onInit', array('dynamic_recaptcha_1'));
				$res = $dispatcher->trigger('onDisplay', array(null, 'dynamic_recaptcha_1', 'class="required"'));

				// return the last result in the list
				return (string) array_pop($res);
			}
		}

		/**
		 * Checks if the com_user captcha is configured.
		 * In case the parameter is set to global, the default one
		 * will be retrieved.
		 * 
		 * @param 	string 	 $plugin  The plugin name to check ('recaptcha' by default).
		 *
		 * @return 	boolean  True if configured, otherwise false.
		 *
		 * @since 	1.10 - August 2018
		 * @joomlaonly  reCaptcha is only for Joomla
		 */
		public function isCaptcha($plugin = 'recaptcha')
		{
			// get global captcha
			$defCaptcha = JFactory::getApplication()->get('captcha', null);
			// in case the user config is set to "use global", the default one will be used
			$captcha 	= JComponentHelper::getParams('com_users')->get('captcha', $defCaptcha);

			// make sure the given plugin matches the configured one
			return !empty($plugin) && !strcasecmp($captcha, $plugin);
		}

		/**
		 * Checks if the global captcha is configured.
		 * 
		 * @param 	string 	 $plugin  The plugin name to check ('recaptcha' by default).
		 *
		 * @return 	boolean  True if configured, otherwise false.
		 *
		 * @since 	1.10 - August 2018
		 * @joomlaonly  reCaptcha is only for Joomla
		 */
		public function isGlobalCaptcha($plugin = 'recaptcha')
		{
			// get global captcha
			$captcha = JFactory::getApplication()->get('captcha', null);

			// make sure the given plugin matches the configured one
			return !empty($plugin) && !strcasecmp($captcha, $plugin);
		}

	}
}
