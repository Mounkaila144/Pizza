<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\MenuRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/order')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    #[Route('/change-order-state/{id}', name: 'change_order_state', methods: ['POST'])]
    public function changeOrderState(Request $request,Order $order,OrderRepository $orderRepository)
    {

        $newState = $request->get('state');
        $order->isState() ?$order->setState(false):$order->setState(true);
        $orderRepository->save($order, true);


        // Retourner une réponse JSON pour indiquer le succès
        return new JsonResponse(['success' => true]);
    }
    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OrderRepository $orderRepository): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderRepository->save($order, true);

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, OrderRepository $orderRepository): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderRepository->save($order, true);

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/content/{id}', name: 'app_order_content', methods: ['GET', 'POST'])]
    public function content(Order $order, OrderRepository $orderRepository, MenuRepository $menuRepository): Response
    {
        // Récupérer le contenu du panier depuis la propriété 'data' de l'entité 'Order'
        $cartContent = $order->getData();

        // Initialiser les variables pour stocker les totaux
        $grandTotal = 0;
        $content = [];

        // Parcourir chaque élément du panier
        foreach ($cartContent as $item) {
            $menuId = $item['menuId'];
            $quantity = $item['quantity'];

            // Récupérer le menu depuis la base de données en utilisant $menuId
            $menu = $menuRepository->find($menuId);

            // Vérifier si le menu existe (peut être NULL si l'ID est invalide)
            if ($menu) {
                // Calculer le total pour cet item (menu) en multipliant la quantité par le prix du menu
                $itemTotal = $menu->getPrix() * $quantity;

                // Stocker le contenu de cet item (menu) dans le tableau $content
                $content[] = [
                    'menu' => $menu,
                    'quantity' => $quantity,
                    'totalItem' => $itemTotal,
                ];

                // Ajouter le total de cet item au grand total
                $grandTotal += $itemTotal;
            }
        }

        // Vous pouvez maintenant passer les données nécessaires à votre template Twig pour l'affichage
        return $this->render('order/content.html.twig', [
            'cartContent' => $content, // Pass the $content array instead of $cartContent
            'grandTotal' => $grandTotal,
        ]);
    }
    #[Route('/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, OrderRepository $orderRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $orderRepository->remove($order, true);
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
