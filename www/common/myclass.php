<?php

require("requires.php");

class Main{
    public function getHomePageSliders(){
        $db = new Table();
        $res = $db->findSql("SELECT * FROM homepage_slider");

        foreach($res as &$r) {
            $text = explode(" ", $r['description']);

            if(sizeof($text) > 2){
                $text[1] = $text[1] . "</br>";
            }

            if(sizeof($text) > 4){
                $text[3] = $text[3] . "</br>";
            }

            $r['description'] = implode(" ", $text);
        }

      
        return $res;
    }

    public function getAbout(){
        $db = new Table();
        return $db->findSql("SELECT * FROM about_us limit 1")[0];
    }

    public function getProjects(){
        $db = new Table();
        return $db->findSql("SELECT * FROM projects");
    }

    public function getTypes(){
        $db = new Table();
        return $db->findSql("SELECT * FROM types");
    }

    public function getNews($trim = false, $limit = 100){
        $db = new Table();

        $results = array();
        $news = array();
        $results= $db->findSql("SELECT * FROM news");

        if($trim){
            foreach ($results as &$result){
                $result['description'] = substr(strip_tags($result['description']),0, $limit) . " ...";
             }
        }
       
        return $results;
    }

    public function getProject($id){

        $db = new Table();

    $project = $db->findSql("SELECT * FROM projects  JOIN types ON (projects.type_id = types.id) WHERE projects.id= {$id}");
        $image = $db->findSql("SELECT * FROM project_images WHERE project_id=" . $id );

        $result = array(
            'project' => $project[0],
            'image' => $image
            );
        return $result;
    }


    public function getSliderDetails($text){
        $db = new Table();

        $sliderDetails = $db->findSql("SELECT * FROM homepage_slider WHERE description=" .$text);
        $sliderText = $db->findSql ("SELECT * FROM projects WHERE title" .$text);

        $results = array (
            'description' => $sliderDetails,
            'title' => $sliderText
            );

        return $results;
    }



    public function getNewsDetail($id){
       
         $db = new Table();

         $new = $db->findSql("SELECT * FROM news WHERE id=" .$id);
         $newsImage = $db->findSql("SELECT * FROM news_images WHERE news_id=". $id);
         $_grid = array($new[0]['image_grid_1'], $new[0]['image_grid_2'], $new[0]['image_grid_3']);
         $grid = array();

         foreach($_grid as $g){
             if($g !== ""){
                $grid[] = $g;
             }
         }
        unset($_grid);

         $newsResults = array(
             'new' => $new,
             'image' => $newsImage,
             'image_grid' => $grid
         );

         unset($newsResults['new'][0]['image_grid_1']);
         unset($newsResults['new'][0]['image_grid_2']);
         unset($newsResults['new'][0]['image_grid_3']);
       
           
          return $newsResults;
    }


    public function getSocialMedia(){
        $db= new Table();
        $socials = $db->findSql("SELECT * FROM social_media");
        $copy = array();

        foreach ($socials as $social) {
            $copy[$social['slug']] = $social['link'];

          }

        return $copy ;
    }


    public function getContact(){
        $db = new Table();
        return $db->findSql("SELECT * FROM contact_us")[0];
    }



}
