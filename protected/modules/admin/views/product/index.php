<!-- START Template Container -->
<div class="container-fluid">
    <?php $this->renderPartial("_search", array("model" => $model)); ?>
    <!-- START row -->
    <?php $this->renderPartial("/layouts/_message"); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-toolbar-wrapper pl0 pt5 pb5">
                    <div class="panel-toolbar pl10">
                        <div class="pull-left">
                            <span class="semibold">&nbsp;&nbsp;Products</span>  
                        </div>
                    </div>
                    <div class="panel-toolbar text-right">
                        <?php
                        echo CHtml::Link("Add Product" . ' <i class="ico-plus"></i>', array("add"), array(
                            "title" => "Add Product",
                            "data-placement" => "bottom",
                            "rel" => "tooltip",
                            "class" => "btn btn-sm btn-default",
                            "data-original-title" => "Add Product"
                        ));
                        ?>
                    </div>
                </div>
                <!-- panel body with collapse capabale -->
                <div class="table-responsive panel-collapse pull out">                  
                    <?php
                    $updateRight = true;
                    $deleteRight = true;
                    $columnClass = (!$updateRight && !$deleteRight) ? "hide" : "";
                    $this->widget("zii.widgets.grid.CGridView", array(
                        "id" => "product-grid",
                        "dataProvider" => $model->search(),
                        "columns" => array(
                            "title",
                            "description",
                            "price",
                             array(
                                'name' => 'price',
                                'value' => '!empty($data->price) ? $data->price : "--"',
                                "htmlOptions" => array("width" => "10%")
                            ),  
                             array(
                                'name' => 'vendor',
                                'value' => '!empty($data->vendor)? $data->vendorRel->name :"--"',
                                "htmlOptions" => array("width" => "10%")
                            ),  
                            array(
                                "name" => "photo",
                                "type" => "raw",
                                "value" => '"<div class=\"media-object\">".CHtml::Image($data->getImage($data->photo,$data->id),"--",array("class"=>"img-circle","height"=>"30","width"=>"100"))."</div>"',
                                "htmlOptions" => array("width" => "1%", "class" => "text-center")
                            ),
                            array(
                                'name' => 'status',
                                'value' => '!empty($data->statusArr[$data->status]) ? $data->statusArr[$data->status]:"--"',
                                "htmlOptions" => array("width" => "10%")
                            ),
                            array(
                                "class" => "CButtonColumn",
                                "header" => "Action",
                                "htmlOptions" => array("width" => "10%", "class" => "text-center $columnClass"),
                                "headerHtmlOptions" => array("width" => "10%", "class" => "text-center $columnClass"),
                                "template" => '<div class="toolbar">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-default" type="button">Action</button>
                                                <button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle" type="button">
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li>{viewRecord}</li>
                                                    <li>{favoriteRecord}</li>
                                                    <li>{unfavoriteRecord}</li>
                                                    <li>{updateRecord}</li>
                                                    <li>{deleteRecord}</li>
                                                </ul>
                                            </div>
                                        </div>',
                                "buttons" => array(
                                    "viewRecord" => array(
                                        "label" => '<i class="icon ico-eye"></i> View ',
                                        "imageUrl" => false,
                                        "url" => 'Yii::app()->createUrl("/".Yii::app()->controller->module->id."/Product/viewproduct", array("id"=>$data->id))',
                                        "options" => array("class" => "addUpdateRecord mr5", "title" => "View Product"),
                                        "visible" => ($updateRight) ? 'true' : 'false',
                                    ),
                                    "favoriteRecord" => array(
                                        "label" => '<i class="icon ico-heart"></i> Like',
                                        "imageUrl" => false,
                                        "url" => 'Yii::app()->createUrl("/".Yii::app()->controller->module->id."/Product/markasfavorite", array("id"=>$data->id))',
                                        "options" => array("class" => "addUpdateRecord mr5", "title" => "View Product"),
                                        'visible' => '($data->favoriteRel=="")?true:false',
                                    ),
                                    "unfavoriteRecord" => array(
                                        "label" => '<i class="icon ico-heart"></i> UnLike ',
                                        "imageUrl" => false,
                                        "url" => 'Yii::app()->createUrl("/".Yii::app()->controller->module->id."/Product/markasunfavorite", array("id"=>$data->id))',
                                        "options" => array("class" => "addUpdateRecord mr5", "title" => "View Product"),
                                        'visible' => '($data->favoriteRel)? true : false ',
                                    ),
                                    "updateRecord" => array(
                                        "label" => '<i class="icon ico-pencil"></i> ' . common::translateText("UPDATE_BTN_TEXT"),
                                        "imageUrl" => false,
                                        "url" => 'Yii::app()->createUrl("/".Yii::app()->controller->module->id."/Product/update", array("id"=>$data->id))',
                                        "options" => array("class" => "addUpdateRecord mr5", "title" => "Update Product"),
                                        "visible" => ($updateRight) ? 'true' : 'false',
                                    ),
                                    "deleteRecord" => array(
                                        "label" => '<i class="icon ico-trash"></i> ' . common::translateText("DELETE_BTN_TEXT"),
                                        "imageUrl" => false,
                                        "url" => 'Yii::app()->createUrl("/".Yii::app()->controller->module->id."/Product/delete", array("id"=>$data->id))',
                                        "options" => array("class" => "deleteRecord text-danger mr5", "title" => "Delete Product"),
                                        "visible" => ($deleteRight) ? 'true' : 'false',
                                    ),
                                ),
                            ),
                        ),
                    ));
                    Yii::app()->clientScript->registerScript('actions', "
                        $('.deleteRecord').live('click',function() {
                            if(!confirm('" . common::translateText("DELETE_CONFIRM") . "')) return false;                        
                            var url = $(this).attr('href');
                            $.post(url,function(res){
                                $.fn.yiiGridView.update('users-grid');
                                $('#flash-message').html(res).animate({opacity: 1.0}, 3000).fadeOut('slow');
                            });
                            return false;
                        });
                        var idList = '';
                        $('.changeStatus').live('click',function() 
                        {
                            if($(this).hasClass('changeStatus'))
                            {
                                var idList    = $('input[type=checkbox]:checked').serialize();
                                if(!idList){
                                    alert('" . common::translateText("INVALID_SELECTION") . "'); return false;  
                                }
                            }
                            if(!confirm('Are you sure to perform this action ?')) return false;                        
                            var url = $(this).attr('href');
                            $.post(url,idList,function(res){
                                $.fn.yiiGridView.update('Product-grid');
                                $('#flash-message').html(res).animate({opacity: 1.0}, 3000).fadeOut('slow');
                            });
                            return false;
                        });
                    ");
                    ?>                    
                </div>
                <!--/ panel body with collapse capabale -->
            </div> 
        </div>      
    </div>
    <!--/ END row -->
</div>
<!--/ END Template Container -->