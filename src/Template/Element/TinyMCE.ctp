<?php

  echo $this->Html->script('tinymce/tinymce.min')."\n";

  $imagesUrl = $this->Url->build(array('controller' => 'graph_files', 'action' => 'image_listing'));

?>
<script type="text/javascript">
function fileBrowserCallBack(field_name, url, type, win)
{
    browserField = field_name;
    browserWin = win;
    window.open(
        "<?= $imagesUrl ?>",
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
  toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor | template",
  entity_encoding : "raw",
  extended_valid_elements : "script[src|async|defer|type|charset]",
  templates: [
    {
      title: "Microsoft Store Badge", 
      description: "Produces a nice looking store badge for Microsoft Store", 
      url: "/tinymce_templates/microsoft_store_badge"
    }
  ],
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