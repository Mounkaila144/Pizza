<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\ClientRepository;
use App\Repository\MenuRepository;
use App\Repository\OrderRepository;
use App\Service\CartService;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/save-order', name: 'save_order', methods: ['POST'])]
    public function saveOrder(Request $request,OrderRepository $orderRepository, CartService $cartService,ClientRepository $clientRepository): JsonResponse
    {

        // Récupérer les données du formulaire
        $date = new \DateTime('now');
        $state = $request->request->get('state', false);
        // Remplacez "getcurentuser" par la méthode pour obtenir l'utilisateur actuel
        $id = $this->getUser()->getId();;
        $client=$clientRepository->find($id);
        // Par exemple, si le nom d'utilisateur représente le nom du client

        $cart = $cartService->getCart();
        $cartData = [];

        foreach ($cart as $cartItem) {
            $menuId = $cartItem['menu']->getId();
            $quantity = $cartItem['quantity'];
            $cartData[] = ['menuId' => $menuId, 'quantity' => $quantity];
        }
        // Créer une nouvelle entité Order avec les données du formulaire
        $order = new Order();
        $order->setDate($date);
        $order->setClient($client);
        $order->setData($cartData);
        $order->setState($state);

        $orderRepository->save($order, true);
        $cartService->clearCart();

        // Vous pouvez maintenant traiter les éléments du panier et les ajouter à l'entité Order ou effectuer d'autres actions nécessaires

        // Retourner une réponse JSON pour indiquer le succès
        return new JsonResponse(['success' => true]);
    }

    #[Route('/', name: 'app_front')]
    public function index(MenuRepository $menuRepository, CartService $cartService): Response
    {
        $cart = $cartService->getCart();

        return $this->render('menu/front.html.twig', [
            'menus' => $menuRepository->findAll(),
            'cart' => $cart,

        ]);
    }
    #[Route('/cart', name: 'app_front_cart')]
    public function cart(MenuRepository $menuRepository, CartService $cartService): Response
    {
        $cart = $cartService->getCart();


        return $this->render('menu/cart.html.twig', [
            'menus' => $menuRepository->findAll(),
            'total' => $cartService->getTotalCartPrice(),
            'cart' => $cart,

        ]);
    }
    #[Route('/add-to-cart/{id}', name: 'add_to_cart',methods:"GET") ]
    public function add(MenuRepository $menuRepository, CartService $cartService,Menu $menu): Response
    {
        // Get the quantity from the request if necessary
        $quantity = 1;
        $cart = $cartService->getCart();

        // Add the menu item to the cart
        $cartService->addToCart($menu, $quantity);
        return $this->redirectToRoute('app_front',  [
            'menus' => $menuRepository->findAll(),

            'cart' => $cart,

        ]);


    }
    #[Route('/get-cart-data', name: 'get_cart_data',methods:"GET")]
    public function getCartData(CartService $cartService)
    {
        $cartData = $cartService->getCart();

        // You can format the cart data as needed before returning it
        // For example, you can calculate the total price, etc.

        return new JsonResponse($cartData);
    }

    #[Route('/get-cart-item-count', name: "get_cart_item_count",methods:"GET")]

    public function getCartItemCount(CartService $cartService): JsonResponse
    {
        $cartData = $cartService->getCart();
        $itemCount = count($cartData);

        return $this->json(['itemCount' => $itemCount]);
    }

    #[Route('/remove-from-cart/{id}', name: 'remove_from_cart', methods: ["POST"])]
    public function removeFromCart(CartService $cartService, Menu $menu)
    {
        $cartService->removeFromCart($menu);

        return new JsonResponse(['message' => 'Item removed from cart successfully']);
    }
    #[Route('/update-cart-item/{id}', name: 'update_cart_item', methods: ["POST"])]
    public function updateCartItem(Request $request, CartService $cartService, Menu $menu)
    {
        $quantity = $request->request->getInt('quantity', 1);
        $cartService->updateCartItemQuantity($menu, $quantity);

        return new JsonResponse(['message' => 'Cart item quantity updated successfully']);
    }
}
