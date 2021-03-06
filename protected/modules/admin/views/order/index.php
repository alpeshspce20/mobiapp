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
                            <span class="semibold">&nbsp;&nbsp;Orders</span>  
                        </div>
                    </div>
                    <div class="panel-toolbar text-right">
                        <?php
                        if (!common::isDeliveryBoy()) {
                            echo CHtml::Link("Add Order" . ' <i class="ico-plus"></i>', array("add"), array(
                                "title" => "Add Order",
                                "data-placement" => "bottom",
                                "rel" => "tooltip",
                                "class" => "btn btn-sm btn-default",
                                "data-original-title" => "Add Order"
                            ));
                        }
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
                        "id" => "order-grid",
                        "dataProvider" => $model->search(),
                        "columns" => array(
                            array(
                                'name' => 'order_date',
                                'value' => '!empty($data->order_date)?common::getDateTimeFromTimeStamp($data->order_date,"d/m/Y"):"--"',
                                "htmlOptions" => array("width" => "10%")
                            ),
                            array(
                                'name' => 'user_id',
                                'value' => '!empty($data->user_id) ? $data->users->full_name : "--"',
                                "htmlOptions" => array("width" => "10%")
                            ),
                            array(
                                'name' => 'product_id',
                                'value' => 'CHtml::link($data->product->title, Yii::app()->createUrl("admin/Product/viewproduct/",array("id"=>$data->product_id)))',
                                'type' => 'raw',
                                "htmlOptions" => array("width" => "10%")
                            ),
                            array(
                                'name' => 'qty',
                                'value' => '!empty($data->qty)?$data->qty:"--"',
                                "htmlOptions" => array("width" => "10%")
                            ),
                            array(
                                'name' => 'address',
                                'value' => '!empty($data->address)?$data->address:"--"',
                                "htmlOptions" => array("width" => "20%")
                            ),
                            array(
                                'name' => 'order_amount',
                                'value' => '!empty($data->order_amount)?$data->order_amount:"--"',
                                "htmlOptions" => array("width" => "10%")
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
                                                    <li>{updateRecord}</li>
                                                    <li>{deleteRecord}</li>
                                                </ul>
                                            </div>
                                        </div>',
                                "buttons" => array(
                                    "updateRecord" => array(
                                        "label" => '<i class="icon ico-pencil"></i> ' . common::translateText("UPDATE_BTN_TEXT"),
                                        "imageUrl" => false,
                                        "url" => 'Yii::app()->createUrl("/".Yii::app()->controller->module->id."/Order/update", array("id"=>$data->id))',
                                        "options" => array("class" => "addUpdateRecord mr5", "title" => "Update Order"),
                                        "visible" => ($updateRight) ? 'true' : 'false',
                                    ),
                                    "deleteRecord" => array(
                                        "label" => '<i class="icon ico-trash"></i> ' . common::translateText("DELETE_BTN_TEXT"),
                                        "imageUrl" => false,
                                        "url" => 'Yii::app()->createUrl("/".Yii::app()->controller->module->id."/Order/delete", array("id"=>$data->id))',
                                        "options" => array("class" => "deleteRecord text-danger mr5", "title" => "Delete Order"),
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
                                $.fn.yiiGridView.update('Order-grid');
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