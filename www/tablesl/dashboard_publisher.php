<?php
$stats = new Statistics(false);
$o = new Table();

$orders = $o->findSql("SELECT * FROM member_orders LEFT JOIN members ON ( member_orders.member_id = members.id)");
$details ;
foreach ($orders AS $order) {
    $orderDetails = reset($o->findSql("
        SELECT media_sections.title AS section_title, media_section_pages.title AS page_title,media_section_colors.title AS color_title,
        media_section_page_sizes.title AS size_title, member_order_details.price
        FROM member_order_details 
        LEFT JOIN media ON (media.id = media_id)
        LEFT JOIN media_sections ON (media_sections.id = section_id)
        LEFT JOIN media_section_pages ON (media_section_pages.id = page_id)
        LEFT JOIN media_section_colors ON (media_section_colors.id = color_id)
        LEFT JOIN media_section_page_sizes ON (media_section_page_sizes.id = size_id)
        WHERE member_order_id = {$order['id']}"));    
    $orderDetails['order'] = $order;
    $details[] = $orderDetails;
}
?>
<div class="row">

    <div id="panelChart9" class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">Pending orders:</div>
            <div class="list-group mb0">
                <?php foreach($details as $d) {?>
                    <a href="#" class="list-group-item bt0">

                        <?php 
                        $warningClass = 'warning';

                        $iconClass = 'fa-calendar';

                        echo '<em class="fa fa-fw '.$iconClass.' mr"></em>';

                        echo '<span class="circle circle-'.$warningClass.' circle-lg text-left"></span>';
                        echo $action;
                        ?> 
                        <span class='text-primary'><?php
                        echo "{$d['order']['start_date']} | by {$d['order']['email']}: {$d['section_title']} - {$d['page_title']} - {$d['color_title']} - {$d['size_title']} | USD {$d['price']}";
                        ?></span>
                    </a>
                    <?php } ?>
            </div>
        </div>

    </div>


</div>

