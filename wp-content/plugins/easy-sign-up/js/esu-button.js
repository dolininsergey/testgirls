// JavaScript Document
(function() {
    tinymce.create('tinymce.plugins.esubutton', {
        init : function(ed, url) {
            ed.addButton('esubutton', {
                title : 'Easy Sign Up Short Code',
                image : url+'/esubutton.png',
                onclick : function() {
                  ed.selection.setContent(ed.selection.getContent() + ' [easy_sign_up title="Your Title Here" phone="1" fnln="1" esu_label="A unique identifier for your form" esu_class="your-class-here"] ');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('esubutton', tinymce.plugins.esubutton);
})();