<?php
/*************************************************************************************/
/*      This file is part of the GoogleTagManager package.                           */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace GoogleTagManager\Hook;


use GoogleTagManager\GoogleTagManager;
use GoogleTagManager\Service\GoogleTagService;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\LangQuery;

/**
 * Class FrontHook
 * @package GoogleTagManager\Hook
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class FrontHook extends BaseHook
{
    /**
     * @var GoogleTagService
     */
    private $googleTagService;

    public function __construct(GoogleTagService $googleTagService)
    {
        $this->googleTagService = $googleTagService;
    }

    public function onMainHeadTop(HookRenderEvent $event)
    {
        if ($gtmId = GoogleTagManager::getConfigValue('googletagmanager_gtmId')) {
            $view = $this->getRequest()->get('_view');

            $event->add($this->render('datalayer/thelia-page-view.html', ['data' => $this->googleTagService->getTheliaPageViewParameters()]));

            if (in_array($view, ['category', 'brand', 'search'])) {
                $event->add($this->render('datalayer/view-item-list.html', ['eventName' => 'view_item_list']));
            }

            if ($view === 'product') {
                $event->add($this->render('datalayer/view-item.html', ['eventName' => 'view_item']));
            }

            if (null !== $authAction = $this->getRequest()->getSession()->get(GoogleTagManager::GOOGLE_TAG_TRIGGER_LOGIN)) {
                $event->add($this->render('datalayer/thelia-page-view.html', [
                    'data' => $this->googleTagService->getLogInData($authAction)
                ]));
                $this->getRequest()->getSession()->set(GoogleTagManager::GOOGLE_TAG_TRIGGER_LOGIN, null);
            }

            if ($view === 'order-placed') {
                $event->add($this->render('datalayer/thelia-page-view.html', [
                    'data' => $this->googleTagService->getPurchaseData($this->getRequest()->get('order_id'))
                ]));
            }

            $event->add(
                "<!-- Google Tag Manager -->" .
                "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':" .
                "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0]," .
                "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=" .
                "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);" .
                "})(window,document,'script','dataLayer','" . $gtmId . "');</script>" .
                "<!-- End Google Tag Manager -->"
            );
        }
    }

    public function onMainBodyTop(HookRenderEvent $event)
    {
        $value = GoogleTagManager::getConfigValue('googletagmanager_gtmId');

        if ("" != $value) {
            $event->add("<!-- Google Tag Manager (noscript) -->" .
                "<noscript><iframe src='https://www.googletagmanager.com/ns.html?id=" . $value . "' " .
                "height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>" .
                "<!-- End Google Tag Manager (noscript) -->"
            );
        }
    }

    public function onMainJsInit(HookRenderEvent $event)
    {
        $view = $this->getRequest()->get('_view');

        if (in_array($view, ['category', 'brand', 'search'])) {
            $event->add($this->render('datalayer/select-item.html'));
        }
        $event->add($this->render('datalayer/add-to-cart.html'));
    }

    public function onProductBottom(HookRenderEvent $event)
    {
        $productId = $event->getArgument('product');
        $this->getRequest()->getSession()->set(GoogleTagManager::GOOGLE_TAG_VIEW_ITEM, $productId);
    }

    protected function getLang()
    {
        $lang = $this->getRequest()->getSession()->get("thelia.current.lang");
        if (null === $lang) {
            $lang = LangQuery::create()->filterByByDefault(1)->findOne();
        }
        return $lang;
    }
}