var tinymce_is_loaded = false;

jQuery(document).ready(function() {
    new Rte(jQuery('textarea.sonata-ba-field-rte'));
});

var Rte = function(){this.initialize.apply(this, arguments)};

Rte.prototype = {
  constructor:Rte.constructor,
  initialize:function(elm, options) {
    if (!elm.get(0) || !elm.attr('id')) {
        return;
    }

    if (!tinymce_is_loaded) {
      jQuery('head').prepend('<script type="text/javascript" src="/bundles/sonataadmin/tinymce/tiny_mce.js"></script>');
      tinymce_is_loaded = true;
    }

    this.options = this.options || {};

    this.options.config = {
        // General options
        mode : "exact",
        theme : "advanced",
        skin : "o2k7",
        skin_variant : "silver",
//        plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,undo,redo,|,link,unlink,anchor,cleanup,help,code",
//        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
//        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
//        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
//        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
//        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
//        template_external_list_url : "js/template_list.js",
//        external_link_list_url : "js/link_list.js",
//        external_image_list_url : "js/image_list.js",
//        media_external_list_url : "js/media_list.js",

        // Replace values for the template plugin
        template_replace_values : {
                username : "Some User",
                staffid : "991234"
        }
    };

    this.options.config.elements = elm.attr('id');

    tinyMCE.init(this.options.config);
  }
}