<?php

    //Helper class for pagination
    class Pagination {

        public $current_page;
        public $per_page;
        public $total_count;

        public $number_of_pages_to_show = 10;

        public function __construct($page=1, $per_page=20, $total_count=0){
            $this->current_page = (int)$page;
            $this->per_page = (int)$per_page;
            $this->total_count = (int)$total_count;
        }
        //Returns the offset that is inserted in the query
        public function offset() {
            return ($this->current_page - 1) * $this->per_page;
        }
        //Returns the page number of the first page
        public function first_page(){

            $first_page =  $this->current_page - floor($this->number_of_pages_to_show / 2);
            $total_pages = $this->total_pages();

            if($first_page < 1 ){
                return 1;   
            }else{

                if($total_pages - $this->current_page <=  floor($this->number_of_pages_to_show / 2) ){
                    return ($total_pages - ($this->number_of_pages_to_show -1 )) > 1 ? ($total_pages - ($this->number_of_pages_to_show -1 )) : 1;
                }

                return $first_page;   
            }

        }

        //Returns the page number of the last page
        public function last_page(){

            $first_page = $this->first_page();

            $last_page = $first_page + ($this->number_of_pages_to_show-1);
            if($last_page >= $this->total_pages()){
                return $this->total_pages();
            }else{
                return $last_page;
            }
        }

        //Returns the total number of pages
        public function total_pages() {
            return ceil($this->total_count/$this->per_page);
        }
        //Returns the previous page number
        public function previous_page() {
            return $this->current_page - 1;
        }

        //Returns the next page number
        public function next_page() {
            return $this->current_page + 1;
        }
        //Checks if a previous page exists
        public function has_previous_page() {
            return $this->previous_page() >= 1 ? true : false;
        }

        //Checks if a next page exists
        public function has_next_page() {
            return $this->next_page() <= $this->total_pages() ? true : false;
        }


        //function to display the pagination links
        public function displayPagination(){



            $currentPage = basename($_SERVER['PHP_SELF']);


            $pageName =  ShowFileName($currentPage);
            $pageExtension =  ShowFileExtension($currentPage);
            $pageLink = $pageName.'.'.$pageExtension;

            $params = $_GET;

            $html = '';
            $html .= '<div class="pagination_links">';

            if($this->total_pages() > 1) {

                if($this->has_previous_page()) {
                  

                    $params['page'] = $this->previous_page();
                    $paramString = http_build_query($params);
                    $html .= '<a data-page="'.$params['page'].'" href="'.$pageLink."?".$paramString.'">Previous</a>';
                    
                      if($this->first_page() > 1){
                        $html .= '<a href="#">...</a> ';
                    }
                }

                for($i=$this->first_page(); $i <= $this->last_page(); $i++) {
                    $params['page'] = $i;
                    $paramString = http_build_query($params);
                    $html .= '<a  data-page="'.$params['page'].'" href="'.$pageLink."?".$paramString.'">';
                    if($i == $this->current_page) {
                        $html .= '<b>'.$i.'</b>';
                    } else {
                        $html .= $i;
                    }
                    $html .="</a>";
                }

                if($this->has_next_page()) {
                    $preNext = '';
                    if($i < $this->total_pages()){
                        $html .= '<a href="#">...</a> ';
                    }
                    $params['page'] = $this->next_page();
                    $paramString = http_build_query($params);
                    $html .= '<a data-page="'.$params['page'].'" href="'.$pageLink."?".$paramString.'">Next</a>';
                }

            }
            $html .= '</div>';

            return $html;
        }

        //function to display the pagination links
        public function displayPaginationOld(){
            $currentPage = basename($_SERVER['REQUEST_URI']);
            $pageName =  ShowFileName($currentPage);
            $pageExtension =  ShowFileExtension($currentPage);
            $pageLink = $pageName.'.'.$pageExtension;

            $html = '';
            $html .= '<div id="pagination">';

            if($this->total_pages() > 1) {

                if($this->has_previous_page()) {
                    $html .= '<a href="'.$pageLink.'?page='.$this->previous_page().'">Previous</a>';
                }

                for($i=1; $i <= $this->total_pages(); $i++) {
                    $html .= '<a href="'.$pageLink.'?page='.$i.'">';
                    if($i == $this->current_page) {
                        $html .= '<b>'.$i.'</b>';
                    } else {
                        $html .= $i;
                    }
                    $html .="</a>";
                }

                if($this->has_next_page()) {

                    $html .= '<a href="'.$pageLink.'?page='.$this->next_page().'">'.$preNext.'Next</a>';
                }

            }
            $html .= '</div>';

            return $html;
        }


    }

?>