<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/")]
class HelloWorldController extends AbstractController
{
    #[Route("/", methods: ["GET"])]
    public function get_index(): Response
    {
        return $this->json([
          'message' => 'hello world'
        ]);
    }
}
