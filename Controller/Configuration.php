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

namespace GoogleTagManager\Controller;


use GoogleTagManager\GoogleTagManager;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;

/**
 * Class Configuration
 * @package GoogleTagManager\Controller
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class Configuration extends BaseAdminController
{
    public function saveAction(Session $session){

        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('googletagmanager'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("googletagmanager.configuration.form");
        $response=null;

        try {
            $vform = $this->validateForm($form);
            $data = $vform->getData();
            $lang = $session->get('thelia.admin.edition.lang');

            GoogleTagManager::setConfigValue('googletagmanager_gtmId', $data['gtmId']);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans("Syntax error"),
                $e->getMessage(),
                $form,
                $e
            );
        }

        return $this->generateSuccessRedirect($form);
    }
}
