<?php

namespace Ip\PageBundle\Controller;

use Ip\PageBundle\Entity\Page;
use Ip\PageBundle\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function indexAction()
    {
        $bParameters = $this->getParameter('ip_page');

        return $this->render('IpPageBundle:Page:page.html.twig', [
            'prefix' => $bParameters['assets_path'],
            'includeAssets' => $bParameters['include_assets'],
            'includejQuery' => $bParameters['include_jQuery'],
            'includeBootstrap' => $bParameters['include_bootstrap'],
            'color' => $bParameters['color'],
            'bgcolor' => $bParameters['bgcolor'],
        ]);
    }

    public function iconsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $icons = $em->getRepository('IpPageBundle:FaIcon')->findAll();

        return $this->render('@IpPage/Page/icon.html.twig', [
            'icons' => $icons,
        ]);
    }

    public function saveAction(Request $request)
    {
        return new Response("");
    }
}
