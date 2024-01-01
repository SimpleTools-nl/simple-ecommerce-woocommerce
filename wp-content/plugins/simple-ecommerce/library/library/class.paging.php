<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

class lknPaging
{

    /**
     * sayfalama yapılacak url.
     * örneğin index.php?option=component_adi&degisken=$deger gibi
     *
     * @var string
     */
    private $pageUrl;

    /**
     * toplam kaç adet kayıt var.
     *
     * @var integer
     */
    private $totalRecord = '';

    /**
     * bir sayfada maksimum kaç kayıt olacak. $config array'ından okunacak
     *
     * @var integer
     */
    private $recordPerPage;

    /**
     * ilk sayfa için gösterilecek değer. dil dosyasından okunacak
     *
     * @var string
     */
    private $firstPageLink;

    /**
     * Sonraki Sayfayı belirmek için kullanıcak değer
     *
     * @var string
     */
    private $nextPage;

    /**
     * Önceki Sayfayı belirmek için kullanılcak değer
     *
     * @var string
     */
    private $perviousPageLink;

    /**
     * En son sayfayı göstermek için kullanılacak değer. Dil dosyasından okunacak
     *
     * @var string
     */
    private $lastPage;

    /**
     * sayfalamaya başlanacak kayıt sayısı
     *
     * @var integer
     */
    private $start;


    private $extraPagingCssClass;
    private $link_attr;

    function __construct($link, $toplam,$extraPagingCssClass="",$link_attr="")
    {

        $config = lknConfig::getInstance();

        $this->totalRecord = (int)$toplam;
        $this->recordPerPage = (int)$config->get('recordPerPage');
        $this->start = lknInputFilter::filterInput($_GET, 'start', '1', 'INT');
        $this->pageUrl = $link;
        $this->firstPageLink = "<<";
        $this->perviousPageLink = "<";
        $this->nextPage = ">";
        $this->lastPage = ">>";

        $this->extraPagingCssClass=$extraPagingCssClass;

        if ($link_attr!=''){
            $this->link_attr=$link_attr;
        }else{
            $this->link_attr="href";
        }



    }


    /**
     * @param mixed $extraPagingCssClass
     */
    public function setExtraPagingCssClass($extraPagingCssClass)
    {
        $this->extraPagingCssClass = $extraPagingCssClass;
    }


    /**
     * sayfa linklerini yazar
     *
     * @return string
     */
    function sayfaLinkiYaz()
    {

        $txt = '<nav class="paging_front" aria-label="paging_front"><ul class="pagination justify-content-center">';

        $displayed_pages = 10;
        $link = $this->pageUrl;
        $toplamSayfa = $this->recordPerPage ? ceil($this->totalRecord / $this->recordPerPage) : 0;
        $aktifSayfa = $this->start;
        $start_loop = (floor(($aktifSayfa - 1) / $displayed_pages)) * $displayed_pages + 1;
        if ($start_loop + $displayed_pages - 1 < $toplamSayfa) {
            $stop_loop = $start_loop + $displayed_pages - 1;
        } else {
            $stop_loop = $toplamSayfa;
        }


        if ($aktifSayfa > 2) {
            $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ($link) . '" class="page-link  '.$this->extraPagingCssClass.'">' . $this->firstPageLink . '</a></li>';
            $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ("$link&start=" . ($aktifSayfa - 1)) . '" class="page-link  '.$this->extraPagingCssClass.'">' . $this->perviousPageLink . '</a></li>';
        } elseif ($aktifSayfa == 2) {
            $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ($link) . '" class="page-link  '.$this->extraPagingCssClass.'">' . $this->firstPageLink . '</a></li>';
            $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ($link) . '" class="page-link  '.$this->extraPagingCssClass.'">' . $this->perviousPageLink . '</a></li>';
        } else {
            //$txt .= '<li><a '.$this->link_attr.'=""><span class="icon12 minia-icon-arrow-left-3"></span></a></li>';
        }

        for ($i = $start_loop; $i <= $stop_loop; $i++) {

            if ($i == $aktifSayfa) {
                //if the page is active. we do not a live link
                $txt .= '<li  class="page-item active" aria-current="page"><a href="javascript:void(0);" class="page-link" >' . $i . ' </a></li>';
            } elseif ($i == 1) {
                //1 means the first page
                $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ($link) . '"  class="page-link '.$this->extraPagingCssClass.'">' . $i . ' </a></li>';
            } else {
                $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ("$link&start=$i") . '" class="page-link  '.$this->extraPagingCssClass.'">' . $i . ' </a></li>';
            }
        }

        if ($aktifSayfa < $toplamSayfa) {
            $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ("$link&start=" . ($aktifSayfa + 1)) . '" class="page-link  '.$this->extraPagingCssClass.'" >' . $this->nextPage . '</a></li>';
            $txt .= '<li class="page-item"><a '.$this->link_attr.'="' . ("$link&start=$toplamSayfa") . '" class="page-link  '.$this->extraPagingCssClass.'">' . $this->lastPage . '</a></li>';
        } else {
//	    $txt .= '<li><span class="pagenav">' . $this->nextPage . '</span> ';
//	    $txt .= '<li><span class="pagenav">' . $this->lastPage . '</span>';
        }

        $txt .= "</ul></nav>";


//        lknvar_dump($txt,1);
        return '<div class="text-center">'.$txt.'</div>';
    }



    public static function getPageLinks($link, $total)
    {

        $sayfa = new lknPaging($link, (int)$total);
        return $sayfa->sayfaLinkiYaz();
    }


}

?>