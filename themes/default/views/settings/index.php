<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$wm = array('0' => lang('no'), '1' => lang('yes'));
$ps = array('0' => lang("disable"), '1' => lang("enable"));
?>
<script>
    $(document).ready(function () {
        <?php if(isset($message)) { echo 'localStorage.clear();'; } ?>
        var timezones = <?= json_encode(DateTimeZone::listIdentifiers(DateTimeZone::ALL)); ?>;
        $('#timezone').autocomplete({
            source: timezones
        });
        if ($('#protocol').val() == 'smtp') {
            $('#smtp_config').slideDown();
        } else if ($('#protocol').val() == 'sendmail') {
            $('#sendmail_config').slideDown();
        }
        $('#protocol').change(function () {
            if ($(this).val() == 'smtp') {
                $('#sendmail_config').slideUp();
                $('#smtp_config').slideDown();
            } else if ($(this).val() == 'sendmail') {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideDown();
            } else {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideUp();
            }
        });
        $('#overselling').change(function () {
            if ($(this).val() == 1) {
                if ($('#accounting_method').select2("val") != 2) {
                    bootbox.alert('<?=lang('overselling_will_only_work_with_AVCO_accounting_method_only')?>');
                    $('#accounting_method').select2("val", '2');
                }
            }
        });
        $('#accounting_method').change(function () {
            var oam = <?=$Settings->accounting_method?>, nam = $(this).val();
            if (oam != nam) {
                bootbox.alert('<?=lang('accounting_method_change_alert')?>');
            }
        });
        $('#accounting_method').change(function () {
            if ($(this).val() != 2) {
                if ($('#overselling').select2("val") == 1) {
                    bootbox.alert('<?=lang('overselling_will_only_work_with_AVCO_accounting_method_only')?>');
                    $('#overselling').select2("val", 0);
                }
            }
        });
        $('#item_addition').change(function () {
            if ($(this).val() == 1) {
                bootbox.alert('<?=lang('product_variants_feature_x')?>');
            }
        });
        var sac = $('#sac').val()
        if(sac == 1) {
            $('.nsac').slideUp();
        } else {
            $('.nsac').slideDown();
        }
        $('#sac').change(function () {
            if ($(this).val() == 1) {
                $('.nsac').slideUp();
            } else {
                $('.nsac').slideDown();
            }
        });
        
        $('#generate_api_privatekey').click(function(){
                 
                $('#apiprivatekey').html('<span class="text-danger">wait, key generating...</span>');
                
                $.ajax({
                    type: "get", 
                    async: false,
                    url: '<?php echo base_url('system_settings/generate_privatekey') ?>',
                    dataType: "json",
                    success: function (data) { 
                        
                        setTimeout(function(){
                            $('#apiprivatekey').html(data.api_privatekey);
                        }, 5000);
                        
                    }
                });
            
        });
    });
    
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('system_settings'); ?></h2>
        <div class="box-icon" style="display:none;">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= site_url('system_settings/paypal') ?>" class="toggle_up"><i
                            class="icon fa fa-paypal"></i><span
                            class="padding-right-10"><?= lang('paypal'); ?></span></a></li>
                <li class="dropdown"><a href="<?= site_url('system_settings/skrill') ?>" class="toggle_down"><i
                            class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('skrill'); ?></span></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('update_info'); ?></p>
                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("system_settings", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-info" role="alert"><p>
                            <strong>Cron Job:</strong> <code>0 1 * * * wget -qO- <?= site_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</code> to run at 1:00 AM daily. For local installation, you can run cron job manually at any time.
                            <?php if (!DEMO) { ?>
                                <a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= site_url('cron/run'); ?>">Run cron job now</a>
                            <?php } ?>
                        </p></div>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('site_config') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("site_name", "site_name"); ?>
                                    <?= form_input('site_name', $Settings->site_name, 'class="form-control tip" id="site_name"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("language", "language"); ?>
                                    <?php
                                    $lang = array(
                                        'arabic'                    => 'Arabic',
                                        'english'                   => 'English',
                                        'german'                    => 'German',
                                        'portuguese-brazilian'      => 'Portuguese (Brazil)',
                                        'simplified-chinese'        => 'Simplified Chinese',
                                        'spanish'                   => 'Spanish',
                                        'thai'                      => 'Thai',
                                        'traditional-chinese'       => 'Traditional Chinese',
                                        'turkish'                   => 'Turkish',																				'vietnamese'                => 'Vietnamese',
                                        'gujarati'                  => 'Gujarati',																				'hindi'                     => 'Hindi',										'marthi'                    => 'Marathi'
                                    ); 
                                    echo form_dropdown('language', $lang, $Settings->language, 'class="form-control tip" id="language" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="currency"><?= lang("default_currency"); ?></label>

                                    <div class="controls"> <?php
                                        foreach ($currencies as $currency) {
                                            $cu[$currency->code] = $currency->name;
                                        }
                                        echo form_dropdown('currency', $cu, $Settings->default_currency, 'class="form-control tip" id="currency" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("accounting_method", "accounting_method"); ?>
                                    <?php
                                    $am = array(0 => 'FIFO (First In First Out)', 1 => 'LIFO (Last In First Out)', 2 => 'AVCO (Average Cost Method)');
                                    echo form_dropdown('accounting_method', $am, $Settings->accounting_method, 'class="form-control tip" id="accounting_method" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="email"><?= lang("default_email"); ?></label>

                                    <?= form_input('email', $Settings->default_email, 'class="form-control tip" required="required" id="email"'); ?>
                            </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="customer_group"><?= lang("default_customer_group"); ?></label>
                            <?php
                                foreach ($customer_groups as $customer_group) {
                                    $pgs[$customer_group->id] = $customer_group->name;
                                }
                                echo form_dropdown('customer_group', $pgs, $Settings->customer_group, 'class="form-control tip" id="customer_group" style="width:100%;" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="price_group"><?= lang("default_price_group"); ?></label>
                            <?php
                                foreach ($price_groups as $price_group) {
                                    $cgs[$price_group->id] = $price_group->name;
                                }
                                echo form_dropdown('price_group', $cgs, $Settings->price_group, 'class="form-control tip" id="price_group" style="width:100%;" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('maintenance_mode', 'mmode'); ?>
                            <div class="controls">  <?php
                                echo form_dropdown('mmode', $wm, (isset($_POST['mmode']) ? $_POST['mmode'] : $Settings->mmode), 'class="tip form-control" required="required" id="mmode" style="width:100%;"');
                                ?> </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="theme"><?= 'POS '. lang("theme"); ?></label>

                            <div class="controls">
                                <?php
                                $themes[""];
                                 foreach($post_theme as $pt){
                                    $pt->theme_name;
                                     $themes[$pt->theme_name] = ucfirst($pt->theme_label);
                                }
                               /* $themes = array(
                                    'default' => 'Default',
                                  
                                );*/
                                echo form_dropdown('theme', $themes, $Settings->theme, 'id="theme" class="form-control tip" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="rtl"><?= lang("rtl_support"); ?></label>

                            <div class="controls">
                                <?php
                                echo form_dropdown('rtl', $ps, $Settings->rtl, 'id="rtl" class="form-control tip" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="captcha"><?= lang("login_captcha"); ?></label>

                            <div class="controls">
                                <?php
                                echo form_dropdown('captcha', $ps, $Settings->captcha, 'id="captcha" class="form-control tip" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="disable_editing"><?= lang("disable_editing"); ?></label>
                            <?= form_input('disable_editing', $Settings->disable_editing, 'class="form-control tip" id="disable_editing" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="rows_per_page"><?= lang("rows_per_page"); ?></label>
                            <?= form_input('rows_per_page', $Settings->rows_per_page, 'class="form-control tip" id="rows_per_page" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="dateformat"><?= lang("dateformat"); ?></label>

                            <div class="controls">
                                <?php
                                foreach ($date_formats as $date_format) {
                                    $dt[$date_format->id] = $date_format->js;
                                }
                                echo form_dropdown('dateformat', $dt, $Settings->dateformat, 'id="dateformat" class="form-control tip" style="width:100%;" required="required"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="timezone"><?= lang("timezone"); ?></label>
                            <?php
                            $timezone_identifiers = DateTimeZone::listIdentifiers();
                            foreach ($timezone_identifiers as $tzi) {
                                $tz[$tzi] = $tzi;
                            }
                            ?>
                            <?= form_dropdown('timezone', $tz, TIMEZONE, 'class="form-control tip" id="timezone" required="required"'); ?>
                        </div>
                    </div>
                    <!--<div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reg_ver', 'reg_ver'); ?>
                            <div class="controls">  <?php
                                echo form_dropdown('reg_ver', $wm, (isset($_POST['reg_ver']) ? $_POST['reg_ver'] : $Settings->reg_ver), 'class="tip form-control" required="required" id="reg_ver" style="width:100%;"');
                                ?> </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('allow_reg', 'allow_reg'); ?>
                            <div class="controls">  <?php
                                echo form_dropdown('allow_reg', $wm, (isset($_POST['allow_reg']) ? $_POST['allow_reg'] : $Settings->allow_reg), 'class="tip form-control" required="required" id="allow_reg" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reg_notification', 'reg_notification'); ?>
                            <div class="controls">  <?php
                                echo form_dropdown('reg_notification', $wm, (isset($_POST['reg_notification']) ? $_POST['reg_notification'] : $Settings->reg_notification), 'class="tip form-control" required="required" id="reg_notification" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>-->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"
                                   for="restrict_calendar">Calendar</label>

                            <div class="controls">
                                <?php
                                $opt_cal = array(1 => lang('private'), 0 => lang('shared'));
                                echo form_dropdown('restrict_calendar', $opt_cal, $Settings->restrict_calendar, 'class="form-control tip" required="required" id="restrict_calendar" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"
                                   for="warehouse"><?= 'POS '. lang("default_warehouse"); ?></label>

                            <div class="controls"> <?php
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                }
                                echo form_dropdown('warehouse', $wh, $Settings->default_warehouse, 'class="form-control tip" id="warehouse" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?=  '<b>POS</b> '. lang("default_biller", "biller"); ?>
                            <?php
                            $bl[""] = "";
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                            }
                            echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="default_printer">Default Bill Print Option </label>
                            <?php
                            $pl[""] = "";
                            foreach ($printers as $printer) {
                                $pl[$printer->id] = $printer->name;
                            }
                            echo form_dropdown('default_printer', $pl, (isset($_POST['default_printer']) ? $_POST['default_printer'] : $Settings->default_printer), 'id="default_printer" data-placeholder="' . lang("select") . ' ' . lang("default_printer") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang("Offline_Warehouse", "Offline_Warehouse"); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />

                            <div class="controls"> <?php
                                foreach ($warehouses as $warehouse) {
                                    $offwh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                }
                                echo form_dropdown('offlinepos_warehouse', $offwh, (isset($_POST['offlinepos_biller']) ? $_POST['offlinepos_biller'] : $Settings->offlinepos_warehouse), 'class="form-control tip" id="offlinepos_warehouse" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?=   lang("Offline Biller", "Offline Biller"); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                            <?php
                            $bl[""] = "";
                            foreach ($billers as $biller) {
                                $offbl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                            }
                            echo form_dropdown('offlinepos_biller', $offbl, (isset($_POST['offlinepos_biller']) ? $_POST['offlinepos_biller'] : $Settings->offlinepos_biller), 'id="offlinepos_biller" data-placeholder="' . lang("select") . ' ' . lang("offlinepos_biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div> 
                    </fieldset>

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('products') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("product_tax", "tax_rate"); ?>
                                <?php
                                echo form_dropdown('tax_rate', $ps, $Settings->default_tax_rate, 'class="form-control tip" id="tax_rate" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="racks"><?= lang("racks"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('racks', $ps, $Settings->racks, 'id="racks" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="attributes"><?= lang("attributes"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('attributes', $ps, $Settings->attributes, 'id="attributes" class="form-control tip"  required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="product_expiry"><?= lang("product_expiry"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('product_expiry', $ps, $Settings->product_expiry, 'id="product_expiry" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="remove_expired"><?= lang("remove_expired"); ?></label>

                                <div class="controls">
                                    <?php
                                    $re_opts = array(0 => lang('no').', '.lang('i_ll_remove'), 1 => lang('yes').', '.lang('remove_automatically'));
                                    echo form_dropdown('remove_expired', $re_opts, $Settings->remove_expired, 'id="remove_expired" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="image_size"><?= lang("image_size"); ?> (Width :
                                    Height) *</label>

                                <div class="row">
                                    <div class="col-xs-6">
                                        <?= form_input('iwidth', $Settings->iwidth, 'class="form-control tip" id="iwidth" placeholder="image width" required="required"'); ?>
                                    </div>
                                    <div class="col-xs-6">
                                        <?= form_input('iheight', $Settings->iheight, 'class="form-control tip" id="iheight" placeholder="image height" required="required"'); ?></div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="thumbnail_size"><?= lang("thumbnail_size"); ?>
                                    (Width : Height) *</label>

                                <div class="row">
                                    <div class="col-xs-6">
                                        <?= form_input('twidth', $Settings->twidth, 'class="form-control tip" id="twidth" placeholder="thumbnail width" required="required"'); ?>
                                    </div>
                                    <div class="col-xs-6">
                                        <?= form_input('theight', $Settings->theight, 'class="form-control tip" id="theight" placeholder="thumbnail height" required="required"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('watermark', 'watermark'); ?>
                                <?php
                                    echo form_dropdown('watermark', $wm, (isset($_POST['watermark']) ? $_POST['watermark'] : $Settings->watermark), 'class="tip form-control" required="required" id="watermark" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('display_all_products', 'display_all_products'); ?>
                                <?php
                                    $dopts = array(0 => lang('hide_with_0_qty'), 1 => lang('show_with_0_qty'));
                                    echo form_dropdown('display_all_products', $dopts, (isset($_POST['display_all_products']) ? $_POST['display_all_products'] : $Settings->display_all_products), 'class="tip form-control" required="required" id="display_all_products" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('barcode_separator', 'barcode_separator'); ?>
                                <?php
                                    $bcopts = array('-' => lang('-'), '.' => lang('.'), '~' => lang('~'), '_' => lang('_'));
                                    echo form_dropdown('barcode_separator', $bcopts, (isset($_POST['barcode_separator']) ? $_POST['barcode_separator'] : $Settings->barcode_separator), 'class="tip form-control" required="required" id="barcode_separator" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('barcode_renderer', 'barcode_renderer'); ?>
                                <?php
                                    $bcropts = array(1 => lang('image'), 0 => lang('svg'));
                                    echo form_dropdown('barcode_renderer', $bcropts, (isset($_POST['barcode_renderer']) ? $_POST['barcode_renderer'] : $Settings->barcode_img), 'class="tip form-control" required="required" id="barcode_renderer" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('update_cost_with_purchase', 'update_cost'); ?>
                                <?= form_dropdown('update_cost', $wm, $Settings->update_cost, 'class="form-control" id="update_cost" required="required"'); ?>
                            </div>
                        </div>
                        
                        <!--<div class="col-md-4">
                            <div class="form-group">
                                 <label class="control-label" for="Show_Total_Unit_Quantity" ><?= lang('Show_Total_Unit_Quantity', 'show_total_unit_quantity'); ?></label>
                                <?= form_dropdown('show_total_unit_quantity', $wm, $Settings->show_total_unit_quantity, 'class="form-control" id="show_total_unit_quantity" required="required"'); ?>
                            </div>
                        </div>-->
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label" for="Product_External_Platform"><?= lang("External Platform"); ?>(Ex.Zomato,Swiggy,etc.)<img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                            <div class="controls">
                            <?php
                            $product_Platform[0] = 'Hide';
                            $product_Platform[1] = 'Show';  
                            echo form_dropdown('product_external_platform', $product_Platform, $Settings->product_external_platform, 'class="form-control tip" required="required" id="product_external_platform" style="width:100%;"');
                            ?>
                            </div>
                          </div>
                       </div>
                </fieldset>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border">Show/Hide Product Images</legend>       
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="sales_image">Sales Product Image </label>
                            <?php
                            $addsaleimg[0] = 'Hide';
                            $addsaleimg[1] = 'Show';                             
                            echo form_dropdown('sales_image', $addsaleimg, (isset($_POST['sales_image']) ? $_POST['sales_image'] : $Settings->sales_image), 'id="sales_image" required="required" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="purchase_image">Purchase Product Image</label>
                        <?php
                            $purchaseimg[0] = 'Hide';
                            $purchaseimg[1] = 'Show';                             
                            echo form_dropdown('purchase_image', $purchaseimg, (isset($_POST['purchase_image']) ? $_POST['purchase_image'] : $Settings->purchase_image), 'id="purchase_image" required="required" class="form-control input-tip select" style="width:100%;"');
                        ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="quotation_image">Quotations Product Image</label>
                        <?php
                            $quotation[0] = 'Hide';
                            $quotation[1] = 'Show';                             
                            echo form_dropdown('quotation_image', $quotation, (isset($_POST['quotation_image']) ? $_POST['quotation_image'] : $Settings->quotation_image), 'id="quotation_image" required="required" class="form-control input-tip select" style="width:100%;"');
                        ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="invoice_product_image">Invoice Product Image</label>
                        <?php
                            $invoiceimg[0] = 'Hide';
                            $invoiceimg[1] = 'Show';                             
                            echo form_dropdown('invoice_product_image', $invoiceimg, (isset($_POST['invoice_product_image']) ? $_POST['invoice_product_image'] : $Settings->invoice_product_image), 'id="invoice_product_image" required="required" class="form-control input-tip select" style="width:100%;"');
                        ?>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="scheduler-border" style="display:none;">
                    <legend class="scheduler-border"><?= lang('product_custom_fileds') ?></legend>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                              <?= lang('pcf1', 'cf1'); ?>
                            <?= form_input('prd_cmfield1', $Settings->prd_cmfield1, 'class="form-control tip" id="vcf1"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('pcf2', 'cf2'); ?>
                            <?= form_input('prd_cmfield2', $Settings->prd_cmfield2, 'class="form-control tip" id="vcf2"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('pcf3', 'cf3'); ?>
                            <?= form_input('prd_cmfield3', $Settings->prd_cmfield3, 'class="form-control tip" id="vcf3"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('pcf4', 'cf4'); ?>
                            <?= form_input('prd_cmfield4', $Settings->prd_cmfield4, 'class="form-control tip" id="vcf4"'); ?>
                        </div>
                    </div>
                     <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('pcf5', 'cf5'); ?>
                            <?= form_input('prd_cmfield5', $Settings->prd_cmfield5, 'class="form-control tip" id="vcf5"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('pcf6', 'cf6'); ?>
                            <?= form_input('prd_cmfield6', $Settings->prd_cmfield6, 'class="form-control tip" id="vcf6"'); ?>
                        </div>
                    </div>
                </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('sales') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="overselling"><?= lang("over_selling"); ?></label>

                                <div class="controls">
                                    <?php
                                    $opt = array(1 => lang('yes'), 0 => lang('no'));
                                    echo form_dropdown('restrict_sale', $opt, $Settings->overselling, 'class="form-control tip" id="overselling" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="reference_format"><?= lang("reference_format"); ?></label>

                                <div class="controls">
                                    <?php
                                    $ref = array(1 => lang('prefix_year_no'), 2 => lang('prefix_month_year_no'), 3 => lang('sequence_number'), 4 => lang('random_number'));
                                    echo form_dropdown('reference_format', $ref, $Settings->reference_format, 'class="form-control tip" required="required" id="reference_format" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("invoice_tax", "tax_rate2"); ?>
                                <?php $tr['0'] = lang("disable");
                                foreach ($tax_rates as $rate) {
                                    $tr[$rate->id] = $rate->name;
                                }
                                echo form_dropdown('tax_rate2', $tr, $Settings->default_tax_rate2, 'id="tax_rate2" class="form-control tip" required="required" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="product_discount"><?= lang("product_level_discount"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('product_discount', $ps, $Settings->product_discount, 'id="product_discount" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="product_serial"><?= lang("product_serial"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('product_serial', $ps, $Settings->product_serial, 'id="product_serial" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="detect_barcode"><?= lang("auto_detect_barcode"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('detect_barcode', $ps, $Settings->auto_detect_barcode, 'id="detect_barcode" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="bc_fix"><?= lang("bc_fix"); ?></label>


                                <?= form_input('bc_fix', $Settings->bc_fix, 'class="form-control tip" required="required" id="bc_fix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="item_addition"><?= lang("item_addition"); ?></label>

                                <div class="controls">
                                    <?php
                                    $ia = array(0 => lang('add_new_item'), 1 => lang('increase_quantity_if_item_exist'));
                                    echo form_dropdown('item_addition', $ia, $Settings->item_addition, 'id="item_addition" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("set_focus", "set_focus"); ?>
                                <?php
                                $sfopts = array(0 => lang('add_item_input'), 1 => lang('last_order_item'));
                                echo form_dropdown('set_focus', $sfopts, (isset($_POST['set_focus']) ? $_POST['set_focus'] : $Settings->set_focus), 'id="set_focus" data-placeholder="' . lang("select") . ' ' . lang("set_focus") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="invoice_view"><?= lang("invoice_view"); ?></label>

                                <div class="controls">
                                    <?php
                                    $opt_inv = array(1 => lang('tax_invoice'), 0 => lang('standard'));
                                    echo form_dropdown('invoice_view', $opt_inv, $Settings->invoice_view, 'class="form-control tip" required="required" id="invoice_view" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="tax_classification_view"><?= lang("tax_classification_view"); ?></label>
                                <div class="controls">
                                    <?php
                                    $opt_inv = array(1 => lang('Yes'), 0 => lang('No'));
                                    echo form_dropdown('tax_classification_view', $opt_inv, $Settings->tax_classification_view, 'class="form-control tip" required="required" id="tax_classification_view" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="tax_classification_view"><?= lang("Include_Tax_in_Cart_Unit_Price"); ?></label>
                                <div class="controls">
                                    <?php
                                    $addunittax = array(1 => lang('Yes'), 0 => lang('No'));
                                    echo form_dropdown('add_tax_in_cart_unit_price', $addunittax, $Settings->add_tax_in_cart_unit_price, 'class="form-control tip" required="required" id="add_tax_in_cart_unit_price" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="tax_classification_view" style="margin-top:17px;"><?= lang("Include Discount in Cart Unit Price"); ?></label>
                                <div class="controls">
                                    <?php
                                    $addunitdiscount = array(1 => lang('Yes'), 0 => lang('No'));
                                    echo form_dropdown('add_discount_in_cart_unit_price', $addunitdiscount, $Settings->add_discount_in_cart_unit_price, 'class="form-control tip" required="required" id="add_tax_in_cart_unit_price" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="Invoice_Length" style="margin-top:17px;"><?= lang("Invoice_Length"); ?></label>
                                <div class="controls">
                                    <?php
                                    $no_of_invoice_digits = array( 4 =>'4 (0001)',
                                        5=>'5 (00001)',6=>'6 (000001)', 7=>'7 (0000001)', 8=>'8 (00000001)',
                                        );
                                       echo form_dropdown('invoice_length', $no_of_invoice_digits, $Settings->invoice_length, 'class="form-control tip" required="required" id="add_tax_in_cart_unit_price" style="width:100%;"');
                               
                                    ?>
                                 
                                </div>
                            </div>
                        </div>
                        
                       <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="invoice_format"  style="margin-top:17px;"><?= lang("Invoice_Format"); ?></label>
                                <div class="controls">
                                    <?php
                                    $invoice_format = array(
                                           
                                            'inv' => '154 (Invoice No)',   
                                            'short-fy-inv' => '19-20/154 (FY/Invoice No)',
                                            'long-fy-inv' => '2019-2020/154 (FY/Invoice No)',
                                            'y-m-inv' => '2020/01/154 (Y/M/Invoice No)',
                                        
                                            'prepend-inv' => '000154 (Invoice No)',
                                            'short-fy-prepend-inv' => '19-20/000154 (FY/Invoice No)',
                                            'long-fy-prepend-inv' => '2019-2020/000154 (FY/Invoice No)',
                                            'y-m-prepend-inv' => '2020/01/000154  (Y/M/Invoice No)',
                                     
                                        );
                                    echo form_dropdown('invoice_format', $invoice_format, $Settings->invoice_format, 'class="form-control tip" title="" required="required" id="add_tax_in_cart_unit_price" style="width:100%;"');
                                    ?>
                                    
                                 
                                </div>
                            </div>
                        </div>

<div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="tax_classification_view"><?= lang("Display_Zero_Sale_for_Product_Report"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                <div class="controls">
                                    <?php
                                    $Display_Zero_Sale_for_Product_Report_Arr = array(1 => lang('Yes'), 0 => lang('No'));
                                    echo form_dropdown('display_zero_sale_for_product_report', $Display_Zero_Sale_for_Product_Report_Arr, $Settings->display_zero_sale_for_product_report, 'class="form-control tip" required="required" id="display_zero_sale_for_product_report" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="order_discount"><?= lang("Sales Order Discount"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                <div class="controls">
                                    <?php
                                    $orderdiscount[0] = 'Hide';
                                    $orderdiscount[1] = 'Show';  
                                    echo form_dropdown('sales_order_discount', $orderdiscount, $Settings->sales_order_discount, 'class="form-control tip" required="required" id="sales_order_discount" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                         </div>
                       
                        
                      
                    </fieldset>

                   
                   <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('purchase') ?></legend>
                            <div class="col-md-4">
                                    <div class="form-group">
                                            <label class="control-label" for="invoice_view_purchase"  style="margin-top:16px;"><?= lang("invoice_view_purchase"); ?></label>
                                            <div class="controls">
                                                    <?php
                                                    $opt_inv = array(1 => lang('tax_invoice'), 0 => lang('standard'));
                                                    echo form_dropdown('invoice_view_purchase', $opt_inv, $Settings->invoice_view_purchase, 'class="form-control tip" required="required" id="invoice_view_purchase" style="width:100%;"');
                                                    ?>
                                            </div>
                                    </div>
                            </div>
                            <div class="col-md-4">
                                    <div class="form-group">
                                            <label class="control-label" for="tax_classification_view__purchase"  style="margin-top:16px;"><?= lang("tax_classification_view__purchase"); ?></label>
                                            <div class="controls">
                                                    <?php
                                                    $opt_inv = array(1 => lang('Yes'), 0 => lang('No'));
                                                    echo form_dropdown('tax_classification_view__purchase', $opt_inv, $Settings->tax_classification_view__purchase, 'class="form-control tip" required="required" id="tax_classification_view__purchase" style="width:100%;"');
                                                    ?>
                                            </div>
                                    </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="purchase_discount"><?= lang("Purchase Order Discount"); ?><img src="<?= $assets ?>images/new.gif" height="30px" alt="new" /></label>
                                    <div class="controls">
                                    <?php
                                    $purchasediscount[0] = 'Hide';
                                    $purchasediscount[1] = 'Show';  
                                    echo form_dropdown('purchase_order_discount', $purchasediscount, $Settings->purchase_order_discount, 'class="form-control tip" required="required" id="purchase_order_discount" style="width:100%;"');
                                    ?>
                                    </div>
                                </div>
                            </div>
                    </fieldset>
                   
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('prefix') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="sales_prefix"><?= lang("sales_prefix"); ?></label>

                                <?= form_input('sales_prefix', $Settings->sales_prefix, 'class="form-control tip" id="sales_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="return_prefix"><?= lang("return_prefix"); ?></label>

                                <?= form_input('return_prefix', $Settings->return_prefix, 'class="form-control tip" id="return_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="payment_prefix"><?= lang("payment_prefix"); ?></label>
                                <?= form_input('payment_prefix', $Settings->payment_prefix, 'class="form-control tip" id="payment_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="ppayment_prefix"><?= lang("ppayment_prefix"); ?></label>
                                <?= form_input('ppayment_prefix', $Settings->ppayment_prefix, 'class="form-control tip" id="ppayment_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="delivery_prefix"><?= lang("delivery_prefix"); ?></label>

                                <?= form_input('delivery_prefix', $Settings->delivery_prefix, 'class="form-control tip" id="delivery_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="quote_prefix"><?= lang("quote_prefix"); ?></label>

                                <?= form_input('quote_prefix', $Settings->quote_prefix, 'class="form-control tip" id="quote_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="purchase_prefix"><?= lang("purchase_prefix"); ?></label>

                                <?= form_input('purchase_prefix', $Settings->purchase_prefix, 'class="form-control tip" id="purchase_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="returnp_prefix"><?= lang("returnp_prefix"); ?></label>

                                <?= form_input('returnp_prefix', $Settings->returnp_prefix, 'class="form-control tip" id="returnp_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="transfer_prefix"><?= lang("transfer_prefix"); ?></label>
                                <?= form_input('transfer_prefix', $Settings->transfer_prefix, 'class="form-control tip" id="transfer_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('expense_prefix', 'expense_prefix'); ?>
                                <?= form_input('expense_prefix', $Settings->expense_prefix, 'class="form-control tip" id="expense_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('qa_prefix', 'qa_prefix'); ?>
                                <?= form_input('qa_prefix', $Settings->qa_prefix, 'class="form-control tip" id="qa_prefix"'); ?>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('money_number_format') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="decimals"><?= lang("decimals"); ?></label>

                                <div class="controls"> <?php
                                    $decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                    echo form_dropdown('decimals', $decimals, $Settings->decimals, 'class="form-control tip" id="decimals"  style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="qty_decimals"><?= lang("qty_decimals"); ?></label>

                                <div class="controls"> <?php
                                    $qty_decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                    echo form_dropdown('qty_decimals', $qty_decimals, $Settings->qty_decimals, 'class="form-control tip" id="qty_decimals"  style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('sac', 'sac'); ?>
                                <?= form_dropdown('sac', $ps, set_value('sac', $Settings->sac), 'class="form-control tip" id="sac"  required="required"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="nsac">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="decimals_sep"><?= lang("decimals_sep"); ?></label>

                                    <div class="controls"> <?php
                                        $dec_point = array('.' => lang('dot'), ',' => lang('comma'));
                                        echo form_dropdown('decimals_sep', $dec_point, $Settings->decimals_sep, 'class="form-control tip" id="decimals_sep"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="thousands_sep"><?= lang("thousands_sep"); ?></label>
                                    <div class="controls"> <?php
                                        $thousands_sep = array('.' => lang('dot'), ',' => lang('comma'), '0' => lang('space'));
                                        echo form_dropdown('thousands_sep', $thousands_sep, $Settings->thousands_sep, 'class="form-control tip" id="thousands_sep"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('display_currency_symbol', 'display_symbol'); ?>
                                <?php $opts = array(0 => lang('disable'), 1 => lang('before'), 2 => lang('after')); ?>
                                <?= form_dropdown('display_symbol', $opts, $Settings->display_symbol, 'class="form-control" id="display_symbol" style="width:100%;" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('currency_symbol', 'symbol'); ?>
                                <?= form_input('symbol', $Settings->symbol, 'class="form-control" id="symbol" style="width:100%;"'); ?>
                            </div>
                        </div>
                    </fieldset>
                    
		

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('email') ?> & SMS</legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="protocol"><?= lang("email_protocol"); ?></label>

                                <div class="controls"> <?php
                                    $popt = array('mail' => 'PHP Mail Function', 'sendmail' => 'Send Mail', 'smtp' => 'SMTP');
                                    echo form_dropdown('protocol', $popt, $Settings->protocol, 'class="form-control tip" id="protocol"  style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="sms_sender"><?= 'SMS Sender'; ?> *</label>

                                <div class="controls">                                   
                                   <?= form_input('sms_sender', $Settings->sms_sender, 'class="form-control tip" id="sms_sender" maxlength="6"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row" id="sendmail_config" style="display: none;">
                            <div class="col-md-12">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="mailpath"><?= lang("mailpath"); ?></label>
                                        <?= form_input('mailpath', $Settings->mailpath, 'class="form-control tip" id="mailpath"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row" id="smtp_config" style="display: none;">
                            <div class="col-md-12">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_host"><?= lang("smtp_host"); ?></label>

                                        <?= form_input('smtp_host', $Settings->smtp_host, 'class="form-control tip" id="smtp_host"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_user"><?= lang("smtp_user"); ?></label>

                                        <?= form_input('smtp_user', $Settings->smtp_user, 'class="form-control tip" id="smtp_user"'); ?> </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_pass"><?= lang("smtp_pass"); ?></label>

                                        <?= form_password('smtp_pass', $smtp_pass, 'class="form-control tip" id="smtp_pass"'); ?> </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_port"><?= lang("smtp_port"); ?></label>

                                        <?= form_input('smtp_port', $Settings->smtp_port, 'class="form-control tip" id="smtp_port"'); ?> </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_crypto"><?= lang("smtp_crypto"); ?></label>

                                        <div class="controls"> <?php
                                            $crypto_opt = array('' => lang('none'), 'tls' => 'TLS', 'ssl' => 'SSL');
                                            echo form_dropdown('smtp_crypto', $crypto_opt, $Settings->smtp_crypto, 'class="form-control tip" id="smtp_crypto"');
                                            ?> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="scheduler-border" >
                        <legend class="scheduler-border">Customer <?= lang('award_points') ?></legend>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><?= lang("customer_award_points"); ?></label>

                                <div class="row">
                                    <div class="col-sm-4 col-xs-6">
                                        <?= lang('each_spent'); ?><br>
                                        <?= form_input('each_spent', $this->sma->formatDecimal($Settings->each_spent), 'class="form-control"'); ?>
                                    </div>
                                    <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                    </div>
                                    <div class="col-sm-4 col-xs-5">
                                        <?= lang('award_points'); ?><br>
                                        <?= form_input('ca_point', $Settings->ca_point, 'class="form-control"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><?= lang("staff_award_points"); ?></label>

                                <div class="row">
                                    <div class="col-sm-4 col-xs-6">
                                        <?= lang('each_in_sale'); ?><br>
                                        <?= form_input('each_sale', $this->sma->formatDecimal($Settings->each_sale), 'class="form-control"'); ?>
                                    </div>
                                    <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                    </div>
                                    <div class="col-sm-4 col-xs-5">
                                        <?= lang('award_points'); ?><br>
                                        <?= form_input('sa_point', $Settings->sa_point, 'class="form-control"'); ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                         
                        <div class="col-md-12">
                            <hr>
                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <?= lang('Synch Award Points With Consumer App', 'Synch Award Points'); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                    <?php $poptRwdpt = array(0 => lang('no'), 1 => lang('yes')); ?>
                                    <?= form_dropdown('synch_reward_points', $poptRwdpt, $Settings->synch_reward_points, 'class="form-control" id="synch_reward_points" required="required" disabled'); ?>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <?= lang('Synch Customers With Consumer App', 'Synch Customers'); ?> <img src="<?= $assets ?>images/new.gif" height="30px" alt="new" />
                                    <?php $poptsyncu = array(0 => lang('no'), 1 => lang('yes')); ?>
                                    <?= form_dropdown('synch_customers', $poptsyncu, $Settings->synch_customers, 'class="form-control" id="synch_customers" required="required" disabled'); ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                     <?php if($Owner || $Admin ){ ?>    
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('API 3') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="api_privatekey"><?= "API3 Access Private key"; ?></label>                                
                                <?php
                                if($Settings->api_privatekey) {
                                ?>
                                <span class="form-control"><?= $Settings->api_privatekey ?></span> 
                                <?php                                 
                                } else {
                                ?> 
                                <div id="apiprivatekey"><button class="btn btn-warning" id="generate_api_privatekey">Generate Private Key</button></div>
                                <?php
                                }//end else
                                ?>                                
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <!--<span class="pull-right"><a href="https://simplypos.in/api/api3/POS_Eshop_API_3_Documents.pdf" target="_new">Documents</a></span>-->
                                <label class="control-label " for="api_privatekey"><?= "Eshop API3 URL"; ?> </label>                              
                                <span class="form-control"><?= base_url('api3/eshop'); ?></span> 
                                
                            </div>
                        </div><div class="col-md-4">
                            <div class="form-group">                                
                                <label class="control-label" for="api_url"><?= "Offline POS API3 URL"; ?></label>                              
                                <span class="form-control"><?= base_url('api3/offline'); ?></span> 
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="api_access"><?= "API3 Access Status"; ?></label>                              
                                <span class="form-control"><?= $Settings->api_access ? 'Active' : 'Blocked' ?></span>
                            </div>
                        </div>
                         
                    </fieldset>
                    <?php } ?>    
                </div>
            </div>
            <div style="clear: both; height: 10px;"></div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        <?= form_submit('update_settings', lang("update_settings"), 'class="btn btn-primary"'); ?>
                    </div>
                </div>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
    <div class="alert alert-info" role="alert"><p>
        <strong>Cron Job:</strong> <code>0 1 * * * wget -qO- <?= site_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</code> to run at 1:00 AM daily. For local installation, you can run cron job manually at any time.
        <?php if (!DEMO) { ?>
            <a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= site_url('cron/run'); ?>">Run cron job now</a>
        <?php } ?>
    </p></div>
</div>
</div>
