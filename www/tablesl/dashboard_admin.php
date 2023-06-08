<?php
$stats = new Statistics(false);
$daysBack = 20; // get stats for how many days back

$log = new Log();
$allLogs = $log->findAll();

?>
<div class="row">

    <div id="panelChart9" class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">Website Performance last <?=$daysBack;?> days</div>
        </div>
        <div collapse="panelChart9" class="panel-wrapper">
            <div class="panel-body">
                <div class="chart-spline flot-chart"></div>
            </div>
        </div>
        <a href="<?php echo ADMIN_PATH_HTML.DS; ?>stats.php">+ View All</a>
    </div>

    <div class="col-xs-12 text-center1">
        <h2 class="text-thin">History Logs</h2>
        <p>Logs of user activity.</p>
        <div class="list-group mb0">
            <?php foreach($allLogs as $log) {?>
                <a href="#" class="list-group-item bt0">
                    <span class="label label-purple pull-right"><?= date("F j, Y, g:i a",strtotime($log['time'])); ?></span>
                    <?php 
                    $action = h(ucfirst($log['action'])); 
                    $warningClass = 'danger';
                    if (strpos(strtolower($action),'success') !== false) {
                        $warningClass = 'success';
                    }

                    $iconClass = 'fa-calendar';
                    if (strpos(strtolower($action),'login') !== false) {
                        $iconClass = 'fa-user';
                    }else if (strpos(strtolower($action),'deleted') !== false) {
                        $iconClass = 'fa-close';
                    }else if (strpos(strtolower($action),'updated') !== false) {
                        $iconClass = 'fa-edit';
                    }else if (strpos(strtolower($action),'created') !== false) {
                        $iconClass = 'fa-save';
                    }

                    echo '<em class="fa fa-fw '.$iconClass.' mr"></em>';

                    echo '<span class="circle circle-'.$warningClass.' circle-lg text-left"></span>';
                    echo $action;
                    ?> 
                    - <span class='text-primary'><?= h($log['username']); ?></span>
                </a>
                <?php } ?>
        </div>

        <div class="panel-footer clearfix">
            <div class="input-group">
                <a href="<?php echo ADMIN_PATH_HTML.DS; ?>logs.php">+ View All</a>
                <!--<input type="text" placeholder="Search message .." class="form-control input-sm">
                <span class="input-group-btn">
                <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i>
                </button>
                </span>-->
            </div>
        </div>

        <div class="panel panel-default" style="display: none;">
            <div class="panel-heading">
                <div class="pull-right label label-danger">5</div>
                <div class="pull-right label label-success">12</div>
                <div class="panel-title">Team messages</div>
            </div>
            <!-- START list group-->
            <div data-height="180" data-scrollable="" class="list-group">

                <?php foreach($allLogs as $log) {?>
                    <!-- START list group item-->
                    <a href="#" class="list-group-item">
                        <div class="media-box">
                            <div class="pull-left">
                                <img src="img/user/02.jpg" alt="Image" class="media-box-object img-circle thumb32">
                            </div>
                            <div class="media-box-body clearfix">
                                <small class="pull-right"><?= date("F j, Y, g:i a",strtotime($log['time'])); ?></small>
                                <strong class="media-box-heading text-primary">
                                    <span class="circle circle-success circle-lg text-left"></span>
                                    <?= h($log['username']); ?>
                                </strong>
                                <p class="mb-sm">
                                    <small><?= h(ucfirst($log['action'])); ?></small>
                                </p>
                            </div>
                        </div>
                    </a>
                    <!-- END list group item-->
                    <?php } ?>

            </div>
            <!-- END list group-->
            <!-- START panel footer-->
            <div class="panel-footer clearfix">
                <div class="input-group">
                    <a href="<?php echo ADMIN_PATH_HTML.DS; ?>logs.php">+ View All</a>
                    <!--<input type="text" placeholder="Search message .." class="form-control input-sm">
                    <span class="input-group-btn">
                    <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i>
                    </button>
                    </span>-->
                </div>
            </div>
            <!-- END panel-footer-->
        </div>
    </div>
</div>

<script>

    var data = [{
        "label": "Unique",
        "color": "#3a3f51",
        "data": [
            <?php
            $data = array();
            for($i=0;$i<=$daysBack;$i++) {
                $date = date('Y-m-d', strtotime("-$i day"));
                $uniqueVisitors = $stats->uniqueVisitors($date);
                $displayDate = date("d M, Y", strtotime($date));
                $data[] = "['$displayDate', $uniqueVisitors],"; 
            }
            $data = array_reverse($data);
            foreach ($data AS $v) {
                echo $v;
            }
            ?>
        ]
        }, {
            "label": "Pageviews",
            "color": "#1ba3cd",
            "data": [
                <?php
                $data = array();
                for($i=0;$i<=$daysBack;$i++) {
                    $date = date('Y-m-d', strtotime("-$i day"));
                    $pageViews = $stats->pageViews($date);
                    $displayDate = date("d M, Y", strtotime($date));
                    $data[] = "['$displayDate', $pageViews],"; 
                }
                $data = array_reverse($data);
                foreach ($data AS $v) {
                    echo $v;
                }
                ?>
            ]
    }];

    var options = {
        series: {
            lines: {
                show: false
            },
            points: {
                show: true,
                radius: 4
            },
            splines: {
                show: true,
                tension: 0.4,
                lineWidth: 1,
                fill: 0.5
            }
        },
        grid: {
            borderColor: '#eee',
            borderWidth: 1,
            hoverable: true,
            backgroundColor: '#fcfcfc'
        },
        tooltip: true,
        tooltipOpts: {
            content: function (label, x, y) { return x + ' : ' + y; }
        },
        xaxis: {
            tickColor: '#fcfcfc',
            mode: 'categories'
        },
        yaxis: {
            min: 0,
//            max: 150, // optional: use it for a clear represetation
            tickColor: '#eee',
            //position: 'right' or 'left',
            tickFormatter: function (v) {
                return v/* + ' visitors'*/;
            }
        },
        shadowSize: 0
    };

</script>