<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    public static $countries = [
        ["ago", "Angola"],
        ["aus", "Australia"],
        ["aut", "Austria"],
        ["bel", "Belgium"],
        ["bih", "Bosnia and Herzegovina"],
        ["bra", "Brazil"],
        ["can", "Canada"],
        ["chl", "Chile"],
        ["chn", "China"],
        ["col", "Colombia"],
        ["hrv", "Croatia"],
        ["cze", "Czech Republic"],
        ["dnk", "Denmark"],
        ["est", "Estonia"],
        ["fin", "Finland"],
        ["fra", "France"],
        ["deu", "Germany"],
        ["grc", "Greece"],
        ["hkg", "Hong Kong"],
        ["hun", "Hungary"],
        ["isl", "Iceland"],
        ["irl", "Ireland"],
        ["imn", "Isle of Man"],
        ["isr", "Israel"],
        ["ita", "Italy"],
        ["jpn", "Japan"],
        ["kor", "Korea (South)"],
        ["lva", "Latvia"],
        ["ltu", "Lithuania"],
        ["lux", "Luxembourg"],
        ["mkd", "Macedonia"],
        ["mex", "Mexico"],
        ["nld", "Netherlands"],
        ["nzl", "New Zealand"],
        ["nor", "Norway"],
        ["per", "Peru"],
        ["phl", "Philippines"],
        ["pol", "Poland"],
        ["prt", "Portugal"],
        ["rou", "Romania"],
        ["rus", "Russian Federation"],
        ["srb", "Serbia"],
        ["sgp", "Singapore"],
        ["svk", "Slovakia"],
        ["svn", "Slovenia"],
        ["zaf", "South Africa"],
        ["swe", "Sweden"],
        ["che", "Switzerland"],
        ["ukr", "Ukraine"],
        ["gbr", "United Kingdom"],
        ["usa", "United States of America"]
    ];

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('/home/index.html.twig', [
            'supported' => self::$countries
        ]);
    }

}
