<?php
// src/Service/MenuService.php
namespace App\Service;

class MenuService
{
    public static function getMenu($menuRepository): array
    {
        $menus = $menuRepository->findBy([
            "type" => "website",
            "visible" => 1,
            "is_root" => 1
        ]);

        $wrapper = [];
        foreach ($menus as $menu) {
            $parent_id = $menu->getId();
            $sub_menus = $menuRepository->findBy(
                [
                "type" => "website",
                "visible" => 1,
                "parent_id" => $parent_id
                ],
                [
                    "order_id" => "ASC"
                ]
            );
            $subMenuLevelOne = [];
            $subMenuLevelTwo = [];
            $subMenuLevelThree = [];
            //echo count($sub_menus)."<br>";
            $sub_menus_count = count($sub_menus);
            foreach ($sub_menus as $sub_menu) {
                $key = self::getJSONData($sub_menu->getRouteParams());
                $subMenuLevelTwo[$key] = $sub_menu->getName();
            }
            if ($sub_menus_count > 0) {
                $subMenuLevelThree[$menu->getName()] = $subMenuLevelTwo;
            } else {
                $key = self::getJSONData($menu->getRouteParams());
                $subMenuLevelThree[$key] = $menu->getName();
            }
            $subMenuLevelOne[count($sub_menus)] = $subMenuLevelThree;
            $wrapper[$parent_id] = $subMenuLevelOne;
        }
        return $wrapper;
    }
    public static function getJSONData($routeParams): string
    {
        $json = json_decode($routeParams, true);
        $key = "";
        if (is_array($json)) {
            $key = $json['id'];
        } else {
            $key = $routeParams;
        }
        return $key;
    }
    public static function getMenuHTML($menuRepository): string
    {
        $menusHtml = "";
        $menus = self::getMenu($menuRepository);
        foreach ($menus as $parentId => $items) {
            foreach ($items as $subMenusCount => $item) {
                //echo $parentId . "subMenusCount:: " . $subMenusCount . "<br>";
                if ($subMenusCount > 0) {
                    foreach ($item as $parentName => $submenu) {
                        $menusHtml .= '<li id="'.$parentId.'">';
                        $menusHtml .= '<a href="#" class="has-submenu" id="sm-1658239201126508-1" 
                                        aria-haspopup="true" 
                                        aria-controls="sm-1658239201126508-2" aria-expanded="false">'
                                        .$parentName.'<span class="caret"></span>
                                        </a>';
                        $menusHtml .= '<ul class="dropdown-menu">';
                        $menusHtml .= self::addPointMenu($submenu);
                        $menusHtml .=   '</li>';
                        $menusHtml .=   '</ul>';
                    }
                } else {
                    foreach ($item as $parentId => $parentName) {
                        $menusHtml .= '<li class=""><a href="/page/'.$parentId.'" target="_blank">';
                        $menusHtml .= $parentName;
                        $menusHtml .= '<i></i></a></li>';
                    }
                }
            }
        }
        return $menusHtml;
    }
    public static function addPointMenu($array){
        $menusHtmlInner = "";
        foreach ($array as $submenuUrl => $submenuName) {
            $url = is_integer($submenuUrl) ? "/page/".$submenuUrl : $submenuUrl;
            $target = is_integer($submenuUrl) ? '' : 'target="_blank"';
            $menusHtmlInner .= '<li class=""><a href="'.$url.'" '.$target.'>';
            $menusHtmlInner .= $submenuName;
            $menusHtmlInner .= '<i></i></a></li>';
        }
        return $menusHtmlInner;
    }
}
