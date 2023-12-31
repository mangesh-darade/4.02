<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .bold {
        font-weight: bold;
    }
</style>
<div class="col-xs-12">
    <h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('profit_loss'); ?> (
        <small><?= ($start ? $this->sma->hrld($start) : '') . ' - ' . ($end ? $this->sma->hrld($end) : ''); ?></small>
        )
    </h2>

    <div class="row">

        <div class="col-xs-6" style="padding-left:0; padding-right:0; padding-bottom:10px; margin-top:5px;">
            <div style="margin: 3px 10px;padding:5px 10px; color: #FFF; background: #fa603d;">
                <h4 class="bold text-muted"><?= lang('purchases') ?></h4>
                <i class="fa fa-star"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_purchases->total_amount) ?></h3>

                <p class="text-center"><?= $this->sma->formatMoney($total_purchases->total) . ' ' . lang('purchases') ?>
                    & <?= $this->sma->formatMoney($total_purchases->paid) . ' ' . lang('paid') ?>
                    & <?= $this->sma->formatMoney($total_purchases->tax) . ' ' . lang('tax') ?></p>
            </div>
        </div>
        <div class="col-xs-6" style="padding-left:0; padding-right:0; padding-bottom:10px;">
            <div style="margin: 6px 10px;padding:6px 10px; color: #FFF; background: #78cd51;">
                <h4 class="bold text-muted"><?= lang('sales') ?></h4>
                <i class="fa fa-heart"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_sales->total_amount) ?></h3>

                <p class="text-center"><?= $this->sma->formatMoney($total_sales->total) . ' ' . lang('sales') ?>
                    & <?= $this->sma->formatMoney($total_sales->paid) . ' ' . lang('paid') ?>
                    & <?= $this->sma->formatMoney($total_sales->tax) . ' ' . lang('tax') ?> </p>
            </div>
        </div>

    </div>
    <div class="row">
       <div class="col-xs-6" style="padding-left:0; padding-right:0; padding-bottom:7px;float:left;">
            <div style="margin: 5px 11px;padding:24px 10px; color: #FFF; background: #78cd51; padding-right:5px;">
                <h4 class="bold text-muted"><?= lang('payments_received') ?></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_received->total_amount) ?></h3>

                <p class="bold text-center"><?= $total_received->total . ' ' . lang('received') ?> </p>

                <p class="text-center"><?= $this->sma->formatMoney($total_received_cash->total_amount) . ' ' . lang('cash') ?>
                    , <?= $this->sma->formatMoney($total_received_cc->total_amount) . ' ' . lang('CC') ?>
                    , <?= $this->sma->formatMoney($total_received_cheque->total_amount) . ' ' . lang('cheque') ?>
                    , <?= $this->sma->formatMoney($total_received_ppp->total_amount) . ' ' . lang('paypal_pro') ?>
                    , <?= $this->sma->formatMoney($total_received_stripe->total_amount) . ' ' . lang('stripe') ?> </p>
            </div>
        </div>
        <div class="col-xs-2" style="padding-left:0; padding-right:0; padding-bottom:7px;float:left">
            <div style="margin: 6px 19px;padding:12px 16px; color: #FFF; background: #b2b8bd; height:150px; paddding-bottom:7px;">
                <h4 class="bold text-muted"><?= lang('payments_returned') ?></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_returned->total_amount) ?></h3>

                <p class="text-center"><?= $total_returned->total . ' ' . lang('returned') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>
        <div class="col-xs-2" style="padding-left:2; padding-right:1; padding-bottom:10px;float:left">
            <div style="margin: 5px 24px;padding:15px 20px;color: #FFF; background: #fa603d; padding-left:10px;">
                <h4 class="bold text-muted"><?= lang('payments_sent') ?><br><br></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_paid->total_amount) ?></h3>

                <p class="text-center"><?= $total_paid->total . ' ' . lang('sent') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>
        <div class="col-xs-2" style="padding-left:0; padding-right:0; padding-bottom:10px;float:left;width:calc(100%-5px)">
            <div style="margin: 5px 21px;padding:15px 23px; color: #FFF; background: #8e44ad;">
                <h4 class="bold text-muted"><?= lang('expenses') ?><br><br></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_expenses->total_amount) ?></h3>

                <p class="bold text-center"><?= $total_expenses->total . ' ' . lang('expenses') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>

    </div>
    <div class="row">

        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:10px;">
            <div style="margin: 5px 10px;padding:5px 10px; color: #FFF; background: #ff5454;">
                <h4 class="bold text-muted"><?= lang('profit_loss') ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_sales->total_amount - $total_purchases->total_amount) ?></h3>

                <p class="text-center"><?= $this->sma->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                    - <?= $this->sma->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?><br>&nbsp;
                </p>
            </div>
        </div>
        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:10px;">
            <div style="margin: 5px 10px;padding:5px 10px; color: #FFF; background: #e84c8a;">
                <h4 class="bold text-muted"><?= lang('profit_loss') ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_sales->total_amount - $total_purchases->total_amount - $total_sales->tax) ?></h3>

                <p class="text-center"><?= $this->sma->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                    - <?= $this->sma->formatMoney($total_sales->tax) . ' ' . lang('tax') ?>
                    - <?= $this->sma->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?><br>&nbsp;
                </p>
            </div>
        </div>
        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:10px;">
            <div style="margin: 5px 10px;padding:5px 10px; color: #FFF; background: #428bca;">
                <h4 class="bold text-muted"><?= lang('profit_loss') ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney(($total_sales->total_amount - $total_sales->tax) - ($total_purchases->total_amount - $total_purchases->tax)) ?></h3>

                <p class="text-center">
                    ( <?= $this->sma->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                    - <?= $this->sma->formatMoney($total_sales->tax) . ' ' . lang('tax') ?> ) -
                    ( <?= $this->sma->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?>
                    - <?= $this->sma->formatMoney($total_purchases->tax) . ' ' . lang('tax') ?> )</p>
            </div>
        </div>

    </div><br><br>

    <div class="row">

        <div class="col-xs-12" style="padding-left:10px; padding-right:10px; padding-bottom:10px;">
            <div style="padding:5px 10px; color: #FFF; background: #16a085;">
                <h4 class="bold text-muted"><?= lang('payments') ?></h4>
                <i class="fa fa-pie-chart"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney($total_received->total_amount - $total_returned->total_amount - $total_paid->total_amount - $total_expenses->total_amount) ?></h3>

                <p class="bold text-center"><?= $this->sma->formatMoney($total_received->total_amount) . ' ' . lang('received') ?>
                    - <?= $this->sma->formatMoney($total_returned->total_amount) . ' ' . lang('returned') ?>
                    - <?= $this->sma->formatMoney($total_paid->total_amount) . ' ' . lang('sent') ?>
                    - <?= $this->sma->formatMoney($total_expenses->total_amount) . ' ' . lang('expenses') ?></p>
            </div>
        </div>

    </div>
<div class="row">
    <?php foreach ($warehouses_report as $warehouse_report) { ?>
    <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:0px; margin-bottom:120px; ">
        <div style="margin:5px 10px; padding:15px 10px; color: #FFF; background: #428bca;">
            <div class="small-box padding1010 bblue">
            <h4 class="bold" style="color:#FFF;"><?= $warehouse_report['warehouse']->name.' ('.$warehouse_report['warehouse']->code.')'; ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->sma->formatMoney(($warehouse_report['total_sales']->total_amount) - ($warehouse_report['total_purchases']->total_amount)) ?></h3>

                <p class="bold text-center" style="height:44px">
                    <?= lang('sales').' - '.lang('purchases'); ?>
                </p>
                <hr style="border-color: rgba(255, 255, 255, 0.4);">
                <p class="bold text-center">
                    <?= $this->sma->formatMoney($warehouse_report['total_sales']->total_amount) . ' ' . lang('sales'); ?>
                    - <?= $this->sma->formatMoney($warehouse_report['total_sales']->tax) . ' ' . lang('tax') ?>
                    = <?= $this->sma->formatMoney($warehouse_report['total_sales']->total_amount-$warehouse_report['total_sales']->tax).' '.lang('net_sales'); ?>
                </p>
                <p class="bold text-center">
                    <?= $this->sma->formatMoney($warehouse_report['total_purchases']->total_amount) . ' ' . lang('purchases') ?>
                    - <?= $this->sma->formatMoney($warehouse_report['total_purchases']->tax) . ' ' . lang('tax') ?>
                    = <?= $this->sma->formatMoney($warehouse_report['total_purchases']->total_amount-$warehouse_report['total_purchases']->tax).' '.lang('net_purchases'); ?>
                </p>
                <hr style="border-color: rgba(255, 255, 255, 0.4);">

                <h3 class="bold text-center">
                    <?= $this->sma->formatMoney((($warehouse_report['total_sales']->total_amount-$warehouse_report['total_sales']->tax))-($warehouse_report['total_purchases']->total_amount-$warehouse_report['total_purchases']->tax)); ?>
                </h3>
                <p class="bold text-center"style="height:36px">
                    <?= lang('net_sales').' - '.lang('net_purchases'); ?>
                </p>
                <hr style="border-color: rgba(255, 255, 255, 0.4);">

                <h3 class="bold text-center">
				    <?= $this->sma->formatMoney($warehouse_report['total_expenses']->total_amount); ?>
				</h3>
                <p class="bold text-center">
                    <?= $warehouse_report['total_expenses']->total.' '.lang('expenses'); ?>
                </p>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
</div>