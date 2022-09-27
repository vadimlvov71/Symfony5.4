<?php

/**
 * @author Andrei Lupuleasa
 * Date: 23-03-2018
 * Vers: 1
 */

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\MenuRepository;
use App\Service\MenuService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default Controller
 */

class DefaultController extends AbstractController
{
    private MenuService $menuService;
    private ArticleRepository $articleRepository;
    private MenuRepository $menuRepository;

    public function __construct(
        ArticleRepository $articleRepository,
        MenuRepository $menuRepository,
        MenuService $menuService
    ) {
        $this->articleRepository = $articleRepository;
        $this->menuRepository = $menuRepository;
        $this->menuService = $menuService;
    }
    /**
     *  Web site index
     *
     * @Route("/", name="app_default_index", methods={"GET"})
     */
    public function index(){
        $menusHtml = $this->menuService->getMenuHTML($this->menuRepository);
        $articles = $this->articleRepository->findHomePageArticles();
         return $this->render('website/home.html.twig', array(
             "articles" => $articles,
             "menusHtml" => $menusHtml,
         ));
    }
    
    public function page(Request $request, $id){
        $menusHtml = $this->menuService->getMenuHTML($this->menuRepository);
        $article = $this->articleRepository->find($id);
        return $this->render('website/page.html.twig', array(
            "article" => $article,
            "menusHtml" => $menusHtml,
        ));
    }
    /**
     * Loads an article
     *
     * @Route("/ajax-get-files-names" , name="ajaxGetFilesNames")
     */
    public function ajaxGetFilesNamesAction(){
        $articlesDb = $this->articleRepository->findBy([
            "status" => "enabled"
        ]);
        $pathFiles = [];
        $filesNamesArray = [];
        foreach ($articlesDb as $article) {
            $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
            $regexp_link = "(<a\s[^>].*<\/a>)";
            if (preg_match_all("/$regexp_link/siU", $article->getDescription(), $matches)) {
                foreach ($matches[1] as $link) {
                    if (preg_match_all("/$regexp/siU", $link, $matches_item)) {
                        //echo $matches_item[3][0]."<br>";
                        $filesName = strip_tags($matches_item[3][0]);
                        $pathFiles[$filesName] = $matches_item[2][0];
                        $filesNamesArray[] = $filesName;
                    }
                }
            }
        }
        $obj = new \stdClass();
        $obj->status = true;
        $obj->message = "Success";
        $obj->data = $pathFiles;
        $obj->data1 = $filesNamesArray;
        return new JsonResponse($obj);
    }
}
