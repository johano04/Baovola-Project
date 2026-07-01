<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CartModel;
use App\Models\CartItemModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;

class Orders extends BaseController
{
    protected $cartModel;
    protected $cartItemModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $productModel;

    public function __construct()
    {
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $userId = $this->request->user->id;

        $orders = $this->orderModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        $ordersWithItems = [];
        foreach ($orders as $order) {
            $items = $this->orderItemModel->select('order_items.*, products.name, products.image_url')
                ->join('products', 'products.id = order_items.product_id')
                ->where('order_id', $order['id'])
                ->findAll();
            $order['items'] = $items;
            $ordersWithItems[] = $order;
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Orders retrieved successfully',
            'data' => $ordersWithItems
        ]);
    }

    public function create()
    {
        $userId = $this->request->user->id;

        $cart = $this->cartModel->where('user_id', $userId)->first();

        if (!$cart) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Cart not found'
            ])->setStatusCode(404);
        }

        $cartItems = $this->cartItemModel->select('cart_items.*, products.price')
            ->join('products', 'products.id = cart_items.product_id')
            ->where('cart_id', $cart['id'])
            ->findAll();

        if (empty($cartItems)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Cart is empty'
            ])->setStatusCode(400);
        }

        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        $orderId = $this->orderModel->insert([
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        foreach ($cartItems as $item) {
            $this->orderItemModel->insert([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price']
            ]);
        }

        $this->cartItemModel->where('cart_id', $cart['id'])->delete();

        $order = $this->orderModel->find($orderId);
        $items = $this->orderItemModel->select('order_items.*, products.name, products.image_url')
            ->join('products', 'products.id = order_items.product_id')
            ->where('order_id', $orderId)
            ->findAll();

        $order['items'] = $items;

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Order created successfully',
            'data' => $order
        ])->setStatusCode(201);
    }
}