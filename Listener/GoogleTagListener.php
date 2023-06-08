<?php

namespace GoogleTagManager\Listener;

use GoogleTagManager\GoogleTagManager;
use GoogleTagManager\Service\GoogleTagService;
use ShortCode\Event\ShortCodeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\Loop\LoopExtendsParseResultsEvent;
use Thelia\Core\Event\TheliaEvents;

class GoogleTagListener implements EventSubscriberInterface
{
    /**
     * @var GoogleTagService
     */
    private $googleTagService;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        GoogleTagService $googleTagService,
        RequestStack $requestStack
    ) {
        $this->googleTagService = $googleTagService;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            GoogleTagManager::GOOGLE_TAG_VIEW_LIST_ITEM => ['getViewListItem', 128],
            GoogleTagManager::GOOGLE_TAG_VIEW_ITEM => ['getViewItem', 128],
            TheliaEvents::getLoopExtendsEvent(
                TheliaEvents::LOOP_EXTENDS_PARSE_RESULTS,
                'product'
            ) => ['trackProducts', 128],
            TheliaEvents::CUSTOMER_LOGIN => ['triggerLoginEvent', 128],
            TheliaEvents::CUSTOMER_CREATEACCOUNT => ['triggerRegisterEvent', 128],
        ];
    }

    public function getViewListItem(ShortCodeEvent $event)
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();

        $productIds = $session->get(GoogleTagManager::GOOGLE_TAG_VIEW_LIST_ITEM, []);

        $items = $this->googleTagService->getProductItems($productIds, true);

        $result = [
            'event' => 'view_item_list',
            'ecommerce' => [
                'items' => $items
            ]
        ];

        $event->setResult(json_encode($result));

        $session->set(GoogleTagManager::GOOGLE_TAG_VIEW_LIST_ITEM, null);
    }

    public function getViewItem(ShortCodeEvent $event)
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();

        $productId = $session->get(GoogleTagManager::GOOGLE_TAG_VIEW_ITEM);

        $items = $this->googleTagService->getProductItems([$productId]);

        $result = [
            'event' => 'view_item',
            'ecommerce' => [
                'items' => $items
            ]
        ];

        $event->setResult(json_encode($result));

        $session->set(GoogleTagManager::GOOGLE_TAG_VIEW_ITEM, null);
    }

    public function trackProducts(LoopExtendsParseResultsEvent $event)
    {
        $products = [];
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        if (!in_array($request->get('_view'), ['product', 'category', 'brand', 'search'])){
            $session->set(GoogleTagManager::GOOGLE_TAG_VIEW_LIST_ITEM, null);
            return;
        }

        foreach ($event->getLoopResult() as $product){
            $products[] = $product->get('ID');
        }

        $session->set(GoogleTagManager::GOOGLE_TAG_VIEW_LIST_ITEM, $products);
    }

    public function triggerRegisterEvent(CustomerCreateOrUpdateEvent $event)
    {
        $this->requestStack->getCurrentRequest()->getSession()->set(GoogleTagManager::GOOGLE_TAG_TRIGGER_LOGIN, 'account creation');
    }

    public function triggerLoginEvent(CustomerLoginEvent $event)
    {
        if ($this->requestStack->getCurrentRequest()->getSession()->get(GoogleTagManager::GOOGLE_TAG_TRIGGER_LOGIN) !== "account creation") {
            $this->requestStack->getCurrentRequest()->getSession()->set(GoogleTagManager::GOOGLE_TAG_TRIGGER_LOGIN, 'account authentication');
        }
    }
}