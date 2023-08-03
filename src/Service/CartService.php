<?php
// src/Service/CartService.php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Menu;

class CartService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        // Initialize the cart in the session if it doesn't exist yet
        $session = $this->getSession();
        if (!$session->has('cart')) {
            $session->set('cart', []);
        }
    }

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request->getSession();
    }
    public function updateCartItemQuantity(Menu $menu, int $quantity): void
    {
        $cart = $this->getCart();
        if (array_key_exists($menu->getId(), $cart)) {
            $cart[$menu->getId()] = [
                'menu' => $menu,
                'quantity' => $quantity,
            ];
            $this->saveCart($cart);
        }
    }
    public function getTotalCartPrice(): float
    {
        $cart = $this->getCart();
        $totalPrice = 0.0;

        foreach ($cart as $cartItem) {
            $quantity = $cartItem['quantity'];
            $price = $cartItem['menu']->getPrix(); // Assuming the Menu entity has a "getPrice" method to retrieve the item price.
            $totalPrice += $quantity * $price;
        }

        return $totalPrice;
    }

    public function addToCart(Menu $menu, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $menuId = $menu->getId();

        if (array_key_exists($menuId, $cart)) {
            unset($cart[$menuId]);
            $this->saveCart($cart);
        } else {
            // If the menu is not in the cart, add it with the given quantity
            $cart[$menuId] = [
                'menu' => $menu,
                'quantity' => $quantity,
            ];
        }

        $this->saveCart($cart);
    }

    public function removeFromCart(Menu $menu): void
    {
        $cart = $this->getCart();
        $menuId = $menu->getId();

        if (array_key_exists($menuId, $cart)) {
            // Remove the menu from the cart
            unset($cart[$menuId]);
            $this->saveCart($cart);
        }
    }

    public function getCart(): array
    {
        $session = $this->getSession();
        return $session->get('cart', []);
    }

    public function clearCart(): void
    {
        $session = $this->getSession();
        $session->remove('cart');
    }

    private function saveCart(array $cart): void
    {
        $session = $this->getSession();
        $session->set('cart', $cart);
    }

    // ... rest of the CartService code ...
}
