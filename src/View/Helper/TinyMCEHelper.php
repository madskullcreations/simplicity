<?php
namespace App\View\Helper;

use Cake\View\Helper;

// TODO: an element could be better? Read up about it.
// NOTE: The GraphFilesController don't exist, where did you get this hack from?!
class TinyMCEHelper extends Helper
{
	public $helpers = ['Html', 'Url'];
	
	public function GetScript()
	{
		$html = $this->Html->script('tinymce/tinymce.min')."\n";
	
		$imagesUrl = $this->Url->build(array('controller' => 'graph_files', 'action' => 'image_listing'));
	
		$html .= '
      <script type="text/javascript">
      function fileBrowserCallBack(field_name, url, type, win)
      {
          browserField = field_name;
          browserWin = win;
          window.open(
              "'.$imagesUrl.'",
              "browserWindow",
              "modal,width=1200,height=800,scrollbars=yes");
      }
	
      // Make sure TinyMCE is saving in utf8 by adding "raw" as entity_encoding.
      tinymce.init({
        selector: "textarea",
        plugins: [
            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
            "searchreplace wordcount visualblocks visualchars fullscreen insertdatetime media nonbreaking",
            "save table contextmenu directionality emoticons template paste",
            
            "code codesample textcolor colorpicker",
        ],
        relative_urls: false,
        file_browser_callback: fileBrowserCallBack,
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor",
        entity_encoding : "raw",
        setup: function (editor) {
            editor.on("init", function(args) {
                editor = args.target;
	
                editor.on("NodeChange", function(e) {
                    if (e && e.element.nodeName.toLowerCase() == "img") {
                        width = e.element.width;
                        height = e.element.height;
                        tinyMCE.DOM.setAttribs(e.element, {"width": null, "height": null});
                        tinyMCE.DOM.setAttribs(e.element,
                            {"style": "width:" + width + "px; height:" + height + "px;"});
                    }
                });
            });
        }
      });
      </script>
    ';
	
		return $html;
	}
}