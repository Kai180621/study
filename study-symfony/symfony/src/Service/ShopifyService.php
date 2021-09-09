<?php
declare(strict_types=1);

namespace App\Service;

use GraphQL\Mutation;
use GraphQL\Query;
use GraphQL\RawObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ShopifyService
{
    public function __construct(
        private HttpClientInterface $httpClient, 
        private $shopifyUrl,
        private $shopifyPassword
    ){}

    public function get_shop_info(): ResponseInterface
    {
        return $this->httpClient->request(
            'GET',
            $this->shopifyUrl . '/shop.json',
            [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shopifyPassword,
                    'Content-Type' => 'application/json'
                ]
            ]
        );
    }

    public function get_products(): ResponseInterface
    {
        return $this->httpClient->request(
            'GET',
            $this->shopifyUrl . '/products.json',
            [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shopifyPassword,
                    'Content-Type' => 'application/json'
                ]
            ]
        );
    }

    public function get_gql_products(): ResponseInterface
    {
        return $this->httpClient->request(
            'POST',
            $this->shopifyUrl . '/graphql.json',
            [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shopifyPassword,
                    'Content-Type' => 'application/graphql'
                ],
                'body' => <<<'EOT'
                    {
                        products(first: 100) {
                            edges {
                                node {
                                    id
                                    handle
                                    variants(first: 5) {
                                        edges {
                                            node {
                                                id
                                                title
                                            }
                                        }
                                    }
                                }
                            }
                            pageInfo {
                                hasNextPage
                            }
                        }
                    }
                EOT
            ]
        );
    }

    public function post_orders(string $variantId = "gid://shopify/ProductVariant/40890396999861"): ResponseInterface
    {
        return $this->httpClient->request(
            'POST',
            $this->shopifyUrl . '/draft_orders.json',
            [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shopifyPassword,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    "draft_order" => [
                        "line_items" => [
                            [
                                "variant_id" => $variantId,
                                "quantity" => 1
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function post_gql_orders(int $variantId = 40889756221621): ResponseInterface
    {
        $mutation = (new Mutation('draftOrderCreate'))
            ->setArguments(['input' => "hoge"])
            ->setSelectionSet([
                (new Query("draftOrder"))
                    ->setSelectionSet(["id"]),
                (new Query("userErrors"))
                    ->setSelectionSet([
                        "field",
                        "message"
                    ])
                ]);
            
        dd($mutation);
        
        <<<"EOT"
            mutation draftOrderCreate {
                draftOrderCreat(input: {
                    lineItems: [
                        {
                            variantId: "gid://shopify/ProductVariant/$variantId",
                            quantity: 1
                        }
                    ]
                }) {
                    draftOrder {
                        id
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        EOT;
        return $this->httpClient->request(
            'POST',
            $this->shopifyUrl . '/graphql.json',
            [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shopifyPassword,
                    'Content-Type' => 'application/graphql'
                ],
                'body' => $mutation
            ]
        );
    }

    // public function post_gql_orders(int $variantId = 40889756221621): ResponseInterface
    // {
    //     return $this->httpClient->request(
    //         'POST',
    //         $this->shopifyUrl . '/graphql.json',
    //         [
    //             'headers' => [
    //                 'X-Shopify-Access-Token' => $this->shopifyPassword,
    //                 'Content-Type' => 'application/json'
    //             ],
    //             'body' => "
    //                 mutation {
    //                     order
    //                 }
    //             "
    //         ]
    //     );
    // }
}
