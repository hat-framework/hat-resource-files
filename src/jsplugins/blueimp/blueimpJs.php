<?php

use classes\Classes\JsPlugin;
class blueimpJs extends JsPlugin{
    
    public $file_sample = "index.html";
    public $project_url = 'https://github.com/blueimp/jQuery-File-Upload/';
    
    static private $instance;
    public static function getInstanceOf($plugin){
        $class_name = __CLASS__;
        if (!isset(self::$instance)) self::$instance = new $class_name($plugin);
        return self::$instance;
    } 
    
    public function draw($form, $camp = "", $config = array()){
        $form->append($this->getForm($camp, $config));
    }
    
    public function drawForm($camp){
        echo $this->getForm($camp);
    }
    
    private function getForm($camp, $config){
        extract($config);
        $url = "$this->resource_url/lib/fileupload/index.php?folder=$folder";
        $var = '<div class="bootstrap">';
        //if($showform){
        $var .= 
            '<form id="fileupload" action="'.$url.'" method="POST" enctype="multipart/form-data">
            <div class="row fileupload-buttonbar">
                <div class="span7">
                    <span class="btn btn-success fileinput-button">
                        <i class="icon-plus icon-white"></i>
                        <span>Enviar Arquivos...</span>
                        <input type="file" name="files[]" multiple>
                    </span>
                    <button type="reset" class="btn btn-warning cancel">
                        <i class="icon-ban-circle icon-white"></i>
                        <span>Cancelar upload</span>
                    </button>
                </div>
                <div class="span5 fileupload-progress fade">
                    <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                        <div class="bar" style="width:0%;"></div>
                    </div>
                    <div class="progress-extended">&nbsp;</div>
                </div>
            </div>
            <div class="fileupload-loading"></div>
            <br>';
       // }
        
            $var .= '<table role="presentation" class="table table-striped"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>';
        $var .= '</form>';
        $var .= "</div>";
        return $var;
    }
    
    public function init(){
        $this->LoadJsPlugin('jqueryui/jqueryui', 'jui');
        $this->Html->LoadCss('plugins/blueimp/bootstrap.min');
        $this->Html->LoadCss('plugins/blueimp/jquery.fileupload-ui');
        $this->Html->LoadJs($this->url."/js/tmpl.min");
        $this->Html->LoadJs($this->url."/js/load-image.min");
        $this->Html->LoadJs($this->url."/js/canvas-to-blob.min");
        $this->Html->LoadJs($this->url."/js/bootstrap.min");
        $this->Html->LoadJs($this->url."/js/bootstrap-image-gallery.min");
        $this->Html->LoadJs($this->url."/js/jquery.iframe-transport");
        $this->Html->LoadJs($this->url."/js/jquery.fileupload");
        $this->Html->LoadJs($this->url."/js/jquery.fileupload-fp");
        $this->Html->LoadJs($this->url."/js/jquery.fileupload-ui");
        $this->Html->LoadJs($this->url."/js/locale");
        $this->Html->LoadJs($this->url."/js/main");
        $this->Html->LoadJsFunction('{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>{%=locale.fileupload.start%}</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>{%=locale.fileupload.cancel%}</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}', false, "text/x-tmpl", 'template-upload');
        
$this->Html->LoadJsFunction('
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade {%=file.ext%}">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&\'gallery\'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
            <td class="size"><span>{%=file.size%}</span></td>
            <td colspan="2"></td>
        {% } %}
        <td class="delete">
            <button class="btn btn-danger" id="excluir" filename="{%=file.name%}" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
                <i class="icon-trash icon-white"></i>
                <span>{%=locale.fileupload.destroy%}</span>
            </button>
            <!--<input type="checkbox" name="delete" value="1">-->
        </td>
    </tr>
{% } %}', false, "text/x-tmpl", "template-download");

        /*$this->Html->LoadJqueryFunction(
            "$('#excluir').live('click', function(){
                var name = $('#excluir').attr('filename');
                if(!confirm('Deseja realmente apagar o arquivo: \"'+name+'\"')){
                    return false;
                }
             });"
        );*/
    }
    
    public function loadPluginModel(){
        require_once $this->base_path . "/scripts/classes/ImageModel.php";
        return new ImageModel();
    }
}


?>
