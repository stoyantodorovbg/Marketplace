<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Setting;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $settingRepo = $this->getDoctrine()->getRepository(Setting::class);
        $setting = $settingRepo->find(3);
        $productsQuantity = $setting->getQuantity();

        $productRepo = $this->getDoctrine()->getRepository(Product::class);
        $topPriorityProducts = $productRepo->findByPriorityLimit($productsQuantity);

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'topPriorityProducts' => $topPriorityProducts

        ]);
    }
}
