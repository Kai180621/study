<?php
namespace App\Controller;

use App\Service\ShopifyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/shopify")]
class ShopifyController extends AbstractController
{
    public function __construct(
        private ShopifyService $shopifyService
    ) {}

    /**
     * ショップ情報を取得する
     */
    #[Route("/", methods: ["GET"])]
    public function index(): Response
    {
        $shopInfo = $this->shopifyService->get_shop_info();
        if (200 !== $shopInfo->getStatusCode()) {
            throw new \Exception('...');
        }
        return $this->json($shopInfo->toArray());
    }

    /**
     * 商品リストを取得する
     */
    #[Route("/products", methods: ["GET"])]
    public function get_products(): Response
    {
        $products = $this->shopifyService->get_products();
        if (200 !== $products->getStatusCode()) {
            throw new \Exception('...');
        }
        return $this->json($products->toArray());
    }

    /**
     * 商品リストを取得する
     */
    #[Route("/gql/products", methods: ["GET"])]
    public function get_gql_products(): Response
    {
        $response = $this->shopifyService->get_gql_products();
        $products = $response->toArray();
        if (isset($products["error"])) {
            return $this->json($products, 400);
        }
        return $this->json($products);
    }

    /**
     * 商品を注文する(REST)
     */
    #[Route("/orders", methods: ["POST"])]
    public function post_orders(): Response
    {
        $response = $this->shopifyService->post_orders();
        dd($response->toArray());
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('...');
        }
        return $this->json($response->toArray());
    }

    /**
     * 商品を注文する(GraphQL)
     */
    #[Route("/gql/orders", methods: ["POST"])]
    public function post_gql_orders(): Response
    {
        $response = $this->shopifyService->post_gql_orders();
        $order = $response->toArray();
        if (isset($order["errors"])) {
            return $this->json($order, 500);
        }
        return $this->json($response->toArray());
    }

    /**
     * 商品を注文する（非同期）
     */
    #[Route("/many_orders_async", methods: ["POST"])]
    public function post_many_orders_async(): Response
    {
        $responses = [];
        for ($i = 1; $i <= 10; $i++) {
            $responses[] = $this->shopifyService->post_orders();
        }

        $statusCodes = [];
        foreach ($responses as $res) {
            $statusCodes[] = $res->getStatusCode();
        }
        var_dump($statusCodes);
    
        return $this->json([
            "message" => "ok"
        ]);
    }

    /**
     * 商品を注文する（同期的）
     */
    #[Route("/many_orders_sync", methods: ["POST"])]
    public function post_many_orders_sync(): Response
    {
        for ($i = 1; $i <= 10; $i++) {
            $response = $this->shopifyService->post_orders();
            $response->getStatusCode();
        }

        return $this->json([
            "message" => "ok"
        ]);
    }

    // /**
    //  * 商品を注文する by GraphQL
    //  * 
    //  * @Route("/gql/orders", methods={"POST"})
    //  * @return Response
    //  */
    // public function post_gql_orders(): Response
    // {
    //     $response = $this->shopifyService->post_gql_orders();
    //     return $this->json($response->toArray());
    // }
}
