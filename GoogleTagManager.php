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

namespace GoogleTagManager;

use Propel\Runtime\Connection\ConnectionInterface;
use ShortCode\ShortCode;
use Thelia\Module\BaseModule;

class GoogleTagManager extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'googletagmanager';

    const GOOGLE_TAG_VIEW_ITEM = 'google_tag_view_item';
    const GOOGLE_TAG_VIEW_LIST_ITEM = 'google_tag_view_list_item';
    const GOOGLE_TAG_TRIGGER_LOGIN = 'google_tag_trigger_login';

    public function postActivation(ConnectionInterface $con = null): void
    {
        ShortCode::createNewShortCodeIfNotExist(self::GOOGLE_TAG_VIEW_LIST_ITEM, self::GOOGLE_TAG_VIEW_LIST_ITEM);
        ShortCode::createNewShortCodeIfNotExist(self::GOOGLE_TAG_VIEW_ITEM, self::GOOGLE_TAG_VIEW_ITEM);
    }

}
