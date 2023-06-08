<?php
  if($tableName == "shortcodes" && !empty($id)){
            $tableObj = new Table();
            if($tableObj->tableExists("shortcodes")){
                $shortcodeObj = new Table("shortcodes");
                $shortcodeImgObj = new Table("shortcodes_images");

                $imgCount = $shortcodeImgObj->countWhere(" WHERE `link_shortcode_id` = {$id}");
                $img = $shortcodeImgObj->findWhere("`link_shortcode_id` = {$id} LIMIT 1");
                $img = reset($img);
                if($imgCount > 0){
                    echo "<style>
                    short-tooltip
                    {
                    text-decoration:none;
                    position:relative;
                    }


                    .short-tooltip span
                    {
                    display:none;
                    -moz-border-radius:6px;
                    -webkit-border-radius:6px;
                    border-radius:6px;
                    color:black;
                    background:white; 
                    }


                    .short-tooltip span img
                    {
                    float:left;
                    margin:0px 8px 8px 0;
                    }


                    .short-tooltip:hover span
                    {
                    display:block;
                    position:absolute; 
                    top:0;  
                    left:0;
                    z-index:1000;
                    width:auto;
                    max-width:320px;
                    min-height:10px;
                    border:1px solid black;
                    margin-top:12px;
                    margin-left:302px;
                    overflow:hidden;
                    padding:8px;
                    }
                    </style>";

                    echo "<div class='field'>";
                    echo "<label class='input-control text'>";
                    echo "<div class='label'>";
                    echo "<p>You have {$imgCount} images linked to this ShortCode.<br>
                    To insert them in Text use the following format: %img-<i>ID</i>%
                    Where you replace ID by the image ID.<br>
                    <b>Example for this ShortCode:</b>
                    <a class='short-tooltip' href=''>
                    %img-{$img['id']}%
                    <span><img src='" . thumbnailLink($img['image'] ,200,200). "'></span>
                    </a>

                    </p>";
                    echo "</div>";
                    echo "</label>";
                    echo "</div>";
                }
            }
        }